<?php
namespace OTW\WooRingBuilder\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait LocalDBCron {
	public function local_db_cron_init() {
		// error_log( '** local_db_cron_init **' );

		if ( ! $this->nivoda_diamonds ) {
			$this->nivoda_diamonds = \OTW\WooRingBuilder\Classes\NivodaGetDiamonds::instance();
		}

		if ( ! $this->diamonds ) {
			$this->diamonds = \OTW\WooRingBuilder\Classes\Diamonds::instance();
		}

		add_filter( 'cron_schedules', array( $this, 'add_custom_cron_schedules' ) );

		add_action( $this->prefix . '_every_thirty_second', array( $this, 'every_thirty_second_cron' ) );
		add_action( $this->prefix . '_every_one_minute', array( $this, 'every_one_minute_cron' ) );
		add_action( $this->prefix . '_every_five_minute', array( $this, 'every_five_minute_cron' ) );
		add_action( $this->prefix . '_every_ten_minute', array( $this, 'every_ten_minute_cron' ) );
		add_action( $this->prefix . '_every_twenty_minute', array( $this, 'every_twenty_minute_cron' ) );
		add_action( $this->prefix . '_every_two_hour', array( $this, 'every_two_hour_cron' ) );
		add_action( $this->prefix . '_every_four_hour', array( $this, 'every_four_hour_cron' ) );
		add_action( $this->prefix . '_every_two_day', array( $this, 'every_two_day_cron' ) );

		add_action( $this->prefix . '_nivoda_copy_import_files', array( $this, 'nivoda_copy_import_files' ) );

		$files_list = $this->get_option( 'import_nivoda_csv_files' );
	}

	////////////////////////
	//// CRON SCHEDULES ////
	////////////////////////

	public function add_custom_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['every_thirty_second'] ) ) {
			$schedules['every_thirty_second'] = array(
				'interval' => 30,
				'display'  => __( 'Every 30 second' ),
			);
		}

		if ( ! isset( $schedules['every_one_minute'] ) ) {
			$schedules['every_one_minute'] = array(
				'interval' => 60,
				'display'  => __( 'Every 1 minute' ),
			);
		}

		if ( ! isset( $schedules['every_five_minute'] ) ) {
			$schedules['every_five_minute'] = array(
				'interval' => 60 * 5,
				'display'  => __( 'Every 5 minute' ),
			);
		}

		if ( ! isset( $schedules['every_ten_minute'] ) ) {
			$schedules['every_ten_minute'] = array(
				'interval' => 60 * 10,
				'display'  => __( 'Every 10 minute' ),
			);
		}

		if ( ! isset( $schedules['every_twenty_minute'] ) ) {
			$schedules['every_twenty_minute'] = array(
				'interval' => 60 * 20,
				'display'  => __( 'Every 20 minute' ),
			);
		}

		if ( ! isset( $schedules['every_two_hour'] ) ) {
			$schedules['every_two_hour'] = array(
				'interval' => 60 * 60 * 2,
				'display'  => __( 'Every 2 hour' ),
			);
		}

		if ( ! isset( $schedules['every_four_hour'] ) ) {
			$schedules['every_four_hour'] = array(
				'interval' => 60 * 60 * 4,
				'display'  => __( 'Every 4 hour' ),
			);
		}

		if ( ! isset( $schedules['every_two_day'] ) ) {
			$schedules['every_two_day'] = array(
				'interval' => 60 * 60 * 24 * 2,
				'display'  => __( 'Every 2 day' ),
			);
		}

		return $schedules;
	}

	public function every_thirty_second_cron() {
		// error_log( '** every_thirty_second_cron **' );
		$this->run_csv_import();
	}

	public function every_one_minute_cron() {
		// error_log( '** every_one_minute_cron **' );

		$this->run_csv_import();
	}

	public function every_five_minute_cron() {
		// error_log( '** every_five_minute_cron **' );

		$this->run_csv_import();
	}

	public function every_ten_minute_cron() {
		// error_log( '** every_ten_minute_cron **' );

		$files_list = $this->get_option( 'import_nivoda_csv_files' );

		if ( $files_list && is_array( $files_list ) && count( $files_list ) >= 1 ) {
			return false;
		}

		$file_system = \OTW\GeneralWooRingBuilder\FileSystem::instance();

		$abs_path = ABSPATH . 'nivoda/';

		$dir_files = $file_system->scandir( $abs_path );

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

					if ( $db_lastmodunix !== $single_file['lastmodunix'] ) {
						$this->update_option( $single_file['name'] . 'lastmodunix', $single_file['lastmodunix'] );

						wp_schedule_single_event( wp_date( 'U' ) + 60, $this->prefix . '_nivoda_copy_import_files' );
					}
				}
			}
		}
	}

	public function every_twenty_minute_cron() {
		// error_log( '** every_twenty_minute_cron **' );
		$this->run_csv_import();
	}

	public function every_two_hour_cron() {
		// error_log( '** every_two_hour_cron **' );
		$this->run_csv_import();
	}

	public function every_four_hour_cron() {
		// error_log( '** every_four_hour_cron **' );

		$this->update_option( 'current_import_file', array() );

		$this->update_option( 'import_nivoda_csv_files', array() );

		$this->get_diamonds_from_csv();
	}

	public function every_two_day_cron() {
		// error_log( '** every_two_day_cron **' );

		$this->update_option( 'current_import_file', array() );

		$this->update_option( 'import_nivoda_csv_files', array() );

		$this->get_diamonds_from_csv();
	}

	public function start_cron_event() {
		// error_log( '** start_cron_event **' );

		$events = array(
			// $this->prefix . '_every_five_minute' => 'every_five_minute',
			// $this->prefix . '_every_twenty_minute' => 'every_twenty_minute',
			// $this->prefix . '_every_two_hour'     => 'every_two_hour',
			$this->prefix . '_every_two_day'    => 'every_two_day',
			$this->prefix . '_every_ten_minute' => 'every_ten_minute',
			$this->prefix . '_every_one_minute' => 'every_one_minute',
		);

		foreach ( $events as $hook => $recurrence ) {
			if ( ! wp_next_scheduled( $hook ) ) {
				wp_schedule_event( wp_date( 'U' ) + 1, $recurrence, $hook );
			}
		}
	}

	private function clear_scheduled_cron_jobs() {
		$events = array(
			$this->prefix . '_every_thirty_second',
			$this->prefix . '_every_one_minute',
			$this->prefix . '_every_five_minute',
			$this->prefix . '_every_ten_minute',
			$this->prefix . '_every_twenty_minute',
			$this->prefix . '_every_two_hour',
			$this->prefix . '_every_four_hour',
			$this->prefix . '_every_two_day',
		);

		foreach ( $events as $hook ) {
			$timestamp = wp_next_scheduled( $hook );

			while ( $timestamp ) {
				wp_unschedule_event( $timestamp, $hook );

				$timestamp = wp_next_scheduled( $hook );
			}
		}
	}

	////////////////////////

	public function run_csv_import() {
		// error_log( '** run_csv_import ** ' );

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

		// error_log( 'run_csv_import - current_file: ' . print_r( $current_file, true ) );

		if ( ! $current_file ) {
			$this->add_file_to_import_que( $files_list );

			return false;
		}

		// IMPORT PROCESS

		if ( $current_file &&
		isset( $current_file['rows'] ) &&
		isset( $current_file['rows_imported'] ) &&
		$current_file['rows_imported'] < $current_file['rows']
		) {
			// error_log( '** CSV import processing **' );
			// error_log( 'rows: ' . $current_file['rows'] );
			// error_log( 'rows imported: ' . $current_file['rows_imported'] );

			if ( ! file_exists( $current_file['absolute_path'] ) ) {
				// error_log( '** File does not exist **: ' . $current_file['absolute_path'] );

				$this->remove_file_from_import_que( $current_file );

				return false;
			}

			$fileHandle = fopen( $current_file['absolute_path'], 'r' );

			if ( ! $fileHandle || ! flock( $fileHandle, LOCK_EX ) ) {
				// error_log( '** File locked **' );

				if ( $fileHandle ) {
					fclose( $fileHandle );
				}

				return false;
			}

			if ( isset( $current_file['last_position'] ) ) {
				// error_log( '** Last position **: ' . $current_file['last_position'] );

				fseek( $fileHandle, $current_file['last_position'] );
			}

			$maxLines = 5000;

			// error_log( '** Max lines **: ' . $maxLines );

			$columns = fgetcsv( $fileHandle );

			if ( $columns === false ) {
				// error_log( '** Failed to read columns from CSV **' );
			}

			while ( $maxLines > 0 && $columns ) {
				--$maxLines;

				if ( ! isset( $current_file['headers'] ) ) {
					$current_file['headers'] = $columns;

					// error_log( '** Headers set **: ' . print_r( $columns, true ) );

					$current_file['last_position'] = ftell( $fileHandle );

					// error_log( '** New last position **: ' . $current_file['last_position'] );

					++$current_file['rows_imported'];

					$this->update_option( 'current_import_file', $current_file );

					$columns = fgetcsv( $fileHandle );  // Read next line

					continue;
				}

				if ( count( $current_file['headers'] ) == count( $columns ) ) {
					$db_diamond = array_combine( $current_file['headers'], $columns );

					if ( $db_diamond === false ) {
					} else {
						$this->update_insert_new_csv_diamond( $db_diamond );
					}
				}

				$current_file['last_position'] = ftell( $fileHandle );

				// error_log( '** Updated last position **: ' . $current_file['last_position'] );

				++$current_file['rows_imported'];

				$this->update_option( 'current_import_file', $current_file );

				$columns = fgetcsv( $fileHandle );  // Read next line

				if ( $columns === false ) {
					// error_log( '** Failed to read columns from CSV **' );
				}
			}

			fclose( $fileHandle );

			return false;
		}

		if ( $current_file &&
			isset( $current_file['rows'] ) &&
			isset( $current_file['rows_imported'] ) &&
			$current_file['rows_imported'] >= $current_file['rows']
			) {
			// error_log( '** csv import finishing **' );

			$diamond_type = 'lab';

			if ( $current_file['name'] === 'natural_diamonds.csv' ) {
				$diamond_type = 'natural';
			}

			$this->delete_old_nivoda_diamonds( ' AND d_type = "' . $diamond_type . '"' );

			wp_delete_file( $current_file['absolute_path'] );

			$this->remove_file_from_import_que( $current_file );

			// error_log( $diamond_type . ' diamonds import success' );

			return false;
		}
	}

	public function update_insert_new_csv_diamond( $db_diamond ) {
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
				// error_log( '** invalid diamond: missing ' . $field . ' **' );
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

		$this->insert_new_diamond(
			$formated_diamond,
			array( 'new_diamond_key' => $this->get_option( 'last_nivoda_update_key' ) )
		);
	}

	public function insert_new_diamond( $formated_diamond, $nivoda_cron_status ) {
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

	public function nivoda_copy_import_files() {
		$file_system = \OTW\GeneralWooRingBuilder\FileSystem::instance();

		$abs_path = ABSPATH . 'nivoda/';

		$dir_files = $file_system->scandir( $abs_path );

		if ( $dir_files && is_array( $dir_files ) && count( $dir_files ) >= 1 ) {
			$files_list = array();

			foreach ( $dir_files as $single_file ) {
				if ( isset( $single_file['lastmodunix'] ) &&
					$single_file['lastmodunix'] &&
					isset( $single_file['type'] ) &&
					$single_file['type'] == 'f' &&
					isset( $single_file['name'] ) &&
					(
						$single_file['name'] == 'labgrown.csv' ||
						$single_file['name'] == 'natural_diamonds.csv'
					)
				) {
					$db_lastmodunix = (int) $this->get_option( $single_file['name'] . 'lastmodunix' );

					if ( $db_lastmodunix == $single_file['lastmodunix'] ) {
						$rtval = copy( $single_file['absolute_path'], $abs_path . 'import/' . $single_file['name'] );

						wp_delete_file( $single_file['absolute_path'] . '.bk' );

						$rtval = copy( $single_file['absolute_path'], $single_file['absolute_path'] . '.bk' );

						wp_delete_file( $single_file['absolute_path'] );
					}
				}
			}
		}
	}

	public function get_diamonds_from_csv() {
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
						$single_file['name'] == 'labgrown.csv' ||
						$single_file['name'] == 'natural_diamonds.csv'
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

	public function add_file_to_import_que( $files_list ) {
		// error_log( '** add_file_to_import_que ** ' );

		$first_file = reset( $files_list );

		// error_log( 'add_file_to_import_que -- current_file: ' . print_r( $first_file, true ) );

		$list_worksheet_info = $this->list_worksheet_info(
			$first_file['absolute_path']
		);

		// error_log( '$list_worksheet_info: ' . print_r( $list_worksheet_info, true ) );

		if ( $list_worksheet_info &&
		isset( $list_worksheet_info['totalRows'] ) &&
		$list_worksheet_info['totalRows'] >= 1
		) {
			$first_file['rows'] = $list_worksheet_info['totalRows'];

			$first_file['rows_imported'] = 0;

			$this->update_option( 'current_import_file', $first_file );

			// error_log( '** add_file_to_import_que -- one file added: ' . print_r( $this->get_option( 'current_import_file' ), true ) );

		} else {
			// error_log( '** remove_file_to_import_que ** ' );

			$this->remove_file_from_import_que( $first_file );
		}
	}

	public function remove_file_from_import_que( $current_file ) {
		// error_log( '** remove_file_from_import_que ** ' );

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
			$this->add_file_to_import_que( $files_list );
		}
	}



	public function list_worksheet_info( $pFilename ) {
		// error_log( '** list_worksheet_info **' );
		// error_log( 'list_worksheet_info: filename: ' . $pFilename );

		if ( ! file_exists( $pFilename ) ) {
			// error_log( 'File does not exist' );
			return false;
		}

		$fileHandle = fopen( $pFilename, 'r' );

		if ( ! $fileHandle ) {
			// error_log( 'list_worksheet_info: no file handle' );
			return false;
		}

		$worksheetInfo = array(
			'worksheetName'    => 'Worksheet',
			'lastColumnLetter' => 'A',
			'lastColumnIndex'  => 0,
			'totalRows'        => 0,
			'totalColumns'     => 0,
		);

		$chunkSize = 8192; // Size of each chunk in bytes

		$buffer = '';

		while ( ! feof( $fileHandle ) ) {
			$buffer .= fread( $fileHandle, $chunkSize );

			// Split buffer into lines
			$lines = explode( "\n", $buffer );

			// Keep the last incomplete line in the buffer
			$buffer = array_pop( $lines );

			foreach ( $lines as $line ) {
				$line = trim( $line ); // Trim any extra whitespace

				if ( $line === '' ) {
					continue; // Skip empty lines
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

		// Process any remaining data in the buffer
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

		// error_log( 'list_worksheet_info -- worksheetInfo: ' . print_r( $worksheetInfo, true ) );

		return $worksheetInfo;
	}

	public function delete_old_nivoda_diamonds( $where = '' ) {
		$last_update_key = $this->get_option( 'last_nivoda_update_key' );

		if ( $last_update_key ) {
			global $wpdb;

			$query = 'DELETE FROM ' . $wpdb->prefix . "otw_diamonds WHERE last_update_key != '" . $last_update_key . "'";

			$query .= $where;

			$wpdb->query( $query );
		}
	}



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
