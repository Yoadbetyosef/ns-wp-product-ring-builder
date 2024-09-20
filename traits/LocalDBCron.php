<?php
namespace OTW\WooRingBuilder\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait LocalDBCron {
	public function local_db_cron_init() {
		if ( ! $this->nivoda_diamonds ) {
			$this->nivoda_diamonds = \OTW\WooRingBuilder\Classes\NivodaGetDiamonds::instance();
		}

		if ( ! $this->diamonds ) {
			$this->diamonds = \OTW\WooRingBuilder\Classes\Diamonds::instance();
		}

		add_filter( 'cron_schedules', array( $this, 'add_custom_cron_schedules' ) );

		add_action( $this->prefix . '_every_five_minutes', array( $this, 'every_five_minutes_cron' ) );

		add_action( $this->prefix . '_every_one_hour', array( $this, 'every_one_hour_cron' ) );

		add_action( $this->prefix . '_every_one_day', array( $this, 'every_one_day_cron' ) );

		$files_list = $this->get_option( 'import_nivoda_csv_files' );
	}

	// RUN CRON SCHEDULES

	public function add_custom_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['every_five_minutes'] ) ) {
			$schedules['every_five_minutes'] = array(
				'interval' => 60 * 5,
				'display'  => __( 'Every 5 minutes' ),
			);
		}

		if ( ! isset( $schedules['every_one_hour'] ) ) {
			$schedules['every_one_hour'] = array(
				'interval' => 60 * 60,
				'display'  => __( 'Every 1 hour' ),
			);
		}

		if ( ! isset( $schedules['every_one_day'] ) ) {
			$schedules['every_one_day'] = array(
				'interval' => 60 * 60 * 24,
				'display'  => __( 'Every 1 day' ),
			);
		}

		return $schedules;
	}

	public function every_five_minutes_cron() {
		$this->run_csv_import();
	}

	public function every_one_hour_cron() {
		$this->nivoda_watch_import_dir();
	}

	public function every_one_day_cron() {
		$this->nivoda_reset_csv_queue();
	}

	public function start_cron_event() {
		$events = array(
			$this->prefix . '_every_one_day'      => 'every_one_day',
			$this->prefix . '_every_one_hour'     => 'every_one_hour',
			$this->prefix . '_every_five_minutes' => 'every_five_minutes',
		);

		foreach ( $events as $hook => $recurrence ) {
			if ( ! wp_next_scheduled( $hook ) ) {
				wp_schedule_event( wp_date( 'U' ) + 1, $recurrence, $hook );
			}
		}
	}

	public function end_cron_event() {
		$events = array(
			$this->prefix . '_every_one_day',
			$this->prefix . '_every_one_hour',
			$this->prefix . '_every_five_minutes',
		);

		foreach ( $events as $hook ) {
			$timestamp = wp_next_scheduled( $hook );

			while ( $timestamp ) {
				wp_unschedule_event( $timestamp, $hook );
			}
		}
	}

	// IMPORT CSV DIAMONDS IN DB

	public function run_csv_import() {
		// CHECK FILE LIST

		$files_list = $this->get_option( 'import_nivoda_csv_files' );

		if ( ! (
				$files_list &&
				is_array( $files_list ) &&
				count( $files_list ) >= 1
			) ) {
			return false;
		}

		// CHECK CURRENT FILE

		$current_file = $this->get_option( 'current_import_file' );

		if ( ! $current_file ) {
			$this->add_file_import_queue( $files_list );

			return false;
		}

		// IMPORT PROCESS

		if ( $current_file &&
		isset( $current_file['rows'] ) &&
		isset( $current_file['rows_imported'] ) &&
		$current_file['rows_imported'] < $current_file['rows']
		) {
			if ( ! file_exists( $current_file['absolute_path'] ) ) {
				$this->del_file_import_queue( $current_file );

				return false;
			}

			error_log( $current_file['name'] . ' is being imported to db' );
			error_log( $current_file['rows_imported'] . 'rows imported' );

			$fileHandle = fopen( $current_file['absolute_path'], 'r' );

			if ( ! $fileHandle || ! flock( $fileHandle, LOCK_EX ) ) {
				if ( $fileHandle ) {
					fclose( $fileHandle );
				}

				return false;
			}

			if ( isset( $current_file['last_position'] ) ) {
				fseek( $fileHandle, $current_file['last_position'] );
			}

			$maxLines = 10000;

			$columns = fgetcsv( $fileHandle );

			while ( $maxLines > 0 && $columns ) {
				--$maxLines;

				if ( ! isset( $current_file['headers'] ) ) {
					$current_file['headers'] = $columns;

					$current_file['last_position'] = ftell( $fileHandle );

					++$current_file['rows_imported'];

					$this->update_option( 'current_import_file', $current_file );

					$columns = fgetcsv( $fileHandle );

					continue;
				}

				if ( count( $current_file['headers'] ) === count( $columns ) ) {
					$db_diamond = array_combine( $current_file['headers'], $columns );

					if ( $db_diamond !== false ) {
						$formated_diamond = $this->format_new_diamond( $db_diamond );

						if ( $formated_diamond !== false ) {
							$this->add_new_diamond(
								$formated_diamond,
								array( 'new_diamond_key' => $this->get_option( 'last_nivoda_update_key' ) )
							);
						}
					}
				}

				$current_file['last_position'] = ftell( $fileHandle );

				++$current_file['rows_imported'];

				$this->update_option( 'current_import_file', $current_file );

				$columns = fgetcsv( $fileHandle );
			}

			fclose( $fileHandle );

			return false;
		}

		// REMOVE OLD DIAMOND PROCESS WHEN IMPORT IS OVER

		if ( $current_file &&
			isset( $current_file['rows'] ) &&
			isset( $current_file['rows_imported'] ) &&
			$current_file['rows_imported'] >= $current_file['rows']
			) {
			$diamond_type = 'lab';

			error_log( $current_file['name'] . ' file has been imported successfully in db' );

			if ( $current_file['name'] === 'natural_diamonds.csv' ) {
				$diamond_type = 'natural';
			}

			$this->del_old_diamond( ' AND d_type = "' . $diamond_type . '"' );

			wp_delete_file( $current_file['absolute_path'] );

			$this->del_file_import_queue( $current_file );

			return false;
		}
	}

	public function get_worksheet_info( $pFilename ) {
		if ( ! file_exists( $pFilename ) ) {
			return false;
		}

		$fileHandle = fopen( $pFilename, 'r' );

		if ( ! $fileHandle ) {
			return false;
		}

		$worksheetInfo = array(
			'worksheetName'    => 'Worksheet',
			'lastColumnLetter' => 'A',
			'lastColumnIndex'  => 0,
			'totalRows'        => 0,
			'totalColumns'     => 0,
		);

		$chunkSize = 8192;

		$buffer = '';

		while ( ! feof( $fileHandle ) ) {
			$buffer .= fread( $fileHandle, $chunkSize );

			$lines = explode( "\n", $buffer );

			$buffer = array_pop( $lines );

			foreach ( $lines as $line ) {
				$line = trim( $line );

				if ( $line === '' ) {
					continue;
				}

				$rowData = str_getcsv( $line );

				if ( $rowData !== false ) {
					++$worksheetInfo['totalRows'];

					$worksheetInfo['lastColumnIndex'] = max(
						$worksheetInfo['lastColumnIndex'],
						count( $rowData ) - 1
					);
				}
			}
		}

		if ( ! empty( $buffer ) ) {
			$rowData = str_getcsv( $buffer );

			if ( $rowData !== false ) {
				++$worksheetInfo['totalRows'];

				$worksheetInfo['lastColumnIndex'] = max(
					$worksheetInfo['lastColumnIndex'],
					count( $rowData ) - 1
				);
			}
		}

		$worksheetInfo['totalColumns'] = $worksheetInfo['lastColumnIndex'] + 1;

		fclose( $fileHandle );

		return $worksheetInfo;
	}

	public function format_new_diamond( $db_diamond ) {
		$required_fields = array(
			'markup_price',
			'price',
			'carats',
			'stock_id',
			'shape',
			'video',
			'image',
			'col',
			'clar',
		);

		foreach ( $required_fields as $field ) {
			if ( empty( $db_diamond[ $field ] ) ) {
				return false;
			}
		}

		$defaults = array(
			'col'    => '',
			'clar'   => '',
			'symm'   => '',
			'length' => '',
			'width'  => '',
			'lab'    => '',
			'video'  => '',
			'image'  => 'https://example.com/default_image.webp',
		);

		$db_diamond = array_merge( $defaults, $db_diamond );

		$diamond = array(
			'id'           => $db_diamond['stock_id'],
			'upload'       => 'csv',
			'markup_price' => $db_diamond['markup_price'],
			'price'        => $db_diamond['price'],
			'diamond'      => array(
				'certificate' => array(
					'video'    => $db_diamond['video'],
					'image'    => $db_diamond['image'],
					'carats'   => $db_diamond['carats'],
					'shape'    => $db_diamond['shape'],
					'color'    => $db_diamond['col'],
					'clarity'  => $db_diamond['clar'],
					'symmetry' => $db_diamond['symm'],
					'length'   => $db_diamond['length'],
					'width'    => $db_diamond['width'],
					'lab'      => $db_diamond['lab'],
				),
			),
		);

		if ( isset( $db_diamond['pdf'] ) ) {
			$diamond['diamond']['certificate']['pdfUrl'] = $db_diamond['pdf'];
		}

		if ( ! $this->nivoda_diamonds ) {
			$this->nivoda_diamonds = \OTW\WooRingBuilder\Classes\NivodaGetDiamonds::instance();
		}

		if ( ! $this->diamonds ) {
			$this->diamonds = \OTW\WooRingBuilder\Classes\Diamonds::instance();
		}

		$formated_diamond = $this->nivoda_diamonds->convert_nivoda_to_vdb( $diamond );

		if ( isset( $db_diamond['lg'] ) && $db_diamond['lg'] ) {
			$formated_diamond['lg'] = $db_diamond['lg'];
		}

		return $formated_diamond;
	}

	public function add_new_diamond( $formated_diamond, $nivoda_cron_status ) {
		global $wpdb;

		$d_status = 1;

		if ( $this->diamonds->exclude_diamond( $formated_diamond ) ) {
			$d_status = 0;
		}

		$data = array(
			'api'             => '1',
			'stock_num'       => $formated_diamond['stock_num'],
			'd_type'          => 'lab',
			'price'           => $formated_diamond['total_sales_price'],
			'base_price'      => $formated_diamond['base_sales_price'],
			'carat_size'      => $formated_diamond['size'],
			'shape'           => $formated_diamond['shape'],
			'shape_api'       => $formated_diamond['shape_api'],
			'color'           => $formated_diamond['color'],
			'clarity'         => $formated_diamond['clarity'],
			'symmetry'        => $formated_diamond['symmetry'],
			'meas_length'     => $formated_diamond['meas_length'],
			'meas_width'      => $formated_diamond['meas_width'],
			'meas_ratio'      => $formated_diamond['meas_ratio'],
			'lab'             => $formated_diamond['lab'],
			'cert_url'        => $formated_diamond['cert_url'],
			'video_url'       => $formated_diamond['video_url'],
			'image_url'       => $formated_diamond['image_url'],
			'd_status'        => $d_status,
			'last_update_key' => $nivoda_cron_status['new_diamond_key'],
		);

		if ( isset( $formated_diamond['lg'] ) && $formated_diamond['lg'] ) {
			$data['d_type'] = $formated_diamond['lg'];
		}

		$format = array(
			'%s',
			'%s',
			'%s',
			'%d',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
		);

		// insert or update a row
		$inserted = $wpdb->replace( $wpdb->prefix . 'otw_diamonds', $data, $format );
	}

	public function del_old_diamond( $where = '' ) {
		$last_update_key = $this->get_option( 'last_nivoda_update_key' );

		if ( $last_update_key ) {
			global $wpdb;

			error_log( 'Deleting diamonds from last update key ' . $last_update_key );

			$query = 'DELETE FROM ' . $wpdb->prefix . "otw_diamonds WHERE last_update_key != '" . $last_update_key . "'";

			$query .= $where;

			$wpdb->query( $query );
		}
	}

	public function add_file_import_queue( $files_list ) {
		$first_file = reset( $files_list );

		$list_worksheet_info = $this->get_worksheet_info(
			$first_file['absolute_path']
		);

		if ( $list_worksheet_info &&
		isset( $list_worksheet_info['totalRows'] ) &&
		$list_worksheet_info['totalRows'] >= 1
		) {
			$first_file['rows'] = $list_worksheet_info['totalRows'];

			$first_file['rows_imported'] = 0;

			$this->update_option( 'current_import_file', $first_file );
		} else {
			$this->del_file_import_queue( $first_file );
		}
	}

	public function del_file_import_queue( $current_file ) {
		$files_list = $this->get_option( 'import_nivoda_csv_files' );

		if ( ! ( $files_list && is_array( $files_list ) && count( $files_list ) >= 1 ) ) {
			return false;
		}

		if ( isset( $files_list[ $current_file['name'] ] ) ) {
			unset( $files_list[ $current_file['name'] ] );

			$this->update_option( 'current_import_file', array() );

			$this->update_option( 'import_nivoda_csv_files', $files_list );
		}

		if ( $files_list && is_array( $files_list ) && count( $files_list ) >= 1 ) {
			$this->add_file_import_queue( $files_list );
		}
	}

	// CHECK FOR NEW NIVODA CSV FILES IN IMPORT DIR

	public function nivoda_watch_import_dir() {
		$files_list = $this->get_option( 'import_nivoda_csv_files' );

		if ( $files_list && is_array( $files_list ) && count( $files_list ) >= 1 ) {
			return false;
		}

		$file_system = \OTW\GeneralWooRingBuilder\FileSystem::instance();

		$nivoda_import_path = ABSPATH . 'nivoda/import';

		$dir_files = $file_system->scandir( $nivoda_import_path );

		if ( $dir_files && is_array( $dir_files ) && count( $dir_files ) >= 1 ) {
			$files_list = array();

			foreach ( $dir_files as $single_file ) {
				if ( isset( $single_file['lastmodunix'] ) &&
				$single_file['lastmodunix'] &&
				isset( $single_file['type'] ) &&
				$single_file['type'] == 'f' &&
				isset( $single_file['name'] ) &&
				(
				$single_file['name'] === 'labgrown.csv' ||
				$single_file['name'] === 'natural_diamonds.csv'
				)
				) {
					error_log( 'Copying File to process csv import: ' . $single_file['name'] );

					$db_lastmodunix = (int) $this->get_option( $single_file['name'] . 'lastmodunix' );

					if ( $db_lastmodunix !== $single_file['lastmodunix'] ) {
						$initial_size = filesize( $single_file['absolute_path'] );

						sleep( 5 );

						$new_size = filesize( $single_file['absolute_path'] );

						if ( $initial_size === $new_size ) {
							$this->update_option(
								$single_file['name'] . 'lastmodunix',
								$single_file['lastmodunix']
							);

							$this->nivoda_copy_import_files();
						} else {
							error_log( 'File upload in progress: ' . $single_file['name'] );
						}
					}
				}
			}
		}
	}

	public function nivoda_copy_import_files() {
		$file_system = \OTW\GeneralWooRingBuilder\FileSystem::instance();

		$nivoda_path = ABSPATH . 'nivoda/';

		$nivoda_import_path = $nivoda_path . 'import/';

		$dir_files = $file_system->scandir( $nivoda_import_path );

		if ( $dir_files && is_array( $dir_files ) && count( $dir_files ) >= 1 ) {
			$files_list = array();

			foreach ( $dir_files as $single_file ) {
				if ( isset( $single_file['lastmodunix'] ) &&
					$single_file['lastmodunix'] &&
					isset( $single_file['type'] ) &&
					$single_file['type'] == 'f' &&
					isset( $single_file['name'] ) &&
					(
						$single_file['name'] === 'labgrown.csv' ||
						$single_file['name'] === 'natural_diamonds.csv'
					)
				) {
					$db_lastmodunix = (int) $this->get_option( $single_file['name'] . 'lastmodunix' );

					if ( $db_lastmodunix === $single_file['lastmodunix'] ) {
						$rtval = copy(
							$single_file['absolute_path'],
							$nivoda_path . $single_file['name']
						);

						$rtval = copy(
							$single_file['absolute_path'],
							$single_file['absolute_path'] . '.bk'
						);
					}
				}
			}
		}
	}

	// RESET QUEUE AND START NEW IMPORT

	public function nivoda_reset_csv_queue() {
		$this->update_option( 'current_import_file', array() );

		$this->update_option( 'import_nivoda_csv_files', array() );

		$file_system = \OTW\GeneralWooRingBuilder\FileSystem::instance();

		$abs_path = ABSPATH . 'nivoda';

		$dir_files = $file_system->scandir( $abs_path );

		if ( $dir_files && is_array( $dir_files ) && count( $dir_files ) >= 1 ) {
			$files_list = array();

			foreach ( $dir_files as $single_file ) {
				if ( isset( $single_file['type'] ) &&
					$single_file['type'] == 'f' &&
					isset( $single_file['name'] ) &&
					(
						$single_file['name'] === 'labgrown.csv' ||
						$single_file['name'] === 'natural_diamonds.csv'
					)
				) {
					$files_list[ $single_file['name'] ] = $single_file;
				}
			}
		}

		if ( isset( $files_list ) &&
			is_array( $files_list ) &&
			$files_list && count( $files_list ) >= 1
		) {
			$this->update_option( 'last_nivoda_update_key', wp_date( 'U' ) );

			$this->update_option( 'import_nivoda_csv_files', $files_list );
		}
	}

	// CREATE DIAMOND DB TABLE

	public function create_custom_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'otw_diamonds';

		$current_version = '1.1';

		$table_version = $this->get_option( 'db_version' );

		if ( ( $table_version !== $current_version ) ||
		isset( $_GET['create_custom_table'] )
		) {
			if ( isset( $_GET['create_custom_table'] ) ) {
				$wpdb->query(
					$wpdb->prepare( 'DROP TABLE IF EXISTS %s', $table_name )
				);
			}

			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
        api varchar(255) NULL,
        stock_num varchar(255) UNIQUE NULL,
        d_type varchar(255) NULL,
        price bigint(20) unsigned NULL,
        base_price bigint(20) unsigned NULL,
        carat_size FLOAT NULL,
        shape varchar(255) NULL,
        shape_api varchar(255) NULL,
        color varchar(255) NULL,
        clarity varchar(255) NULL,
        symmetry varchar(255) NULL,
        meas_length FLOAT NULL,
        meas_width FLOAT NULL,
        meas_ratio FLOAT NULL,
        lab varchar(255) NULL,
        cert_url TINYTEXT NULL,
        video_url TINYTEXT NULL,
        image_url TINYTEXT NULL,
        d_status tinyint(1) DEFAULT 1 NULL,
        last_update_key varchar(255) NULL
      ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			dbDelta( $sql );

			$this->update_option( 'db_version', $current_version );
		}
	}
}
