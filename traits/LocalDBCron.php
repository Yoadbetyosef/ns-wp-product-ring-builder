<?php
namespace OTW\WooRingBuilder\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait LocalDBCron {
	public function local_db_cron_init() {
		error_log( '** local_db_cron_init **' );

		if ( ! $this->nivoda_diamonds ) {
			$this->nivoda_diamonds = \OTW\WooRingBuilder\Classes\NivodaGetDiamonds::instance();
		}

		if ( ! $this->diamonds ) {
			$this->diamonds = \OTW\WooRingBuilder\Classes\Diamonds::instance();
		}

		add_filter( 'cron_schedules', array( $this, 'add_custom_cron_schedules' ) );

		add_action( $this->prefix . '_every_two_hour', array( $this, 'every_two_hour_cron' ) );

		add_action( $this->prefix . '_every_four_hour', array( $this, 'every_four_hour_cron' ) );

		add_action( $this->prefix . '_every_ten_minute', array( $this, 'every_ten_minute_cron' ) );

		add_action( $this->prefix . '_every_twenty_minute', array( $this, 'every_twenty_minute_cron' ) );

		add_action( $this->prefix . '_nivoda_copy_import_files', array( $this, 'nivoda_copy_import_files' ) );

		$files_list = $this->get_option( 'import_nivoda_csv_files' );
	}

	////////////////////////
	//// CRON SCHEDULES ////
	////////////////////////

	public function add_custom_cron_schedules( $schedules ) {
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

		return $schedules;
	}

	public function every_ten_minute_cron() {
		error_log( '** every_ten_minute_cron **' );

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
				$single_file['name'] == 'labgrown.csv' ||
				$single_file['name'] == 'natural_diamonds.csv'
				)
				) {
					$db_lastmodunix = (int) $this->get_option( $single_file['name'] . 'lastmodunix' );

					if ( $db_lastmodunix != $single_file['lastmodunix'] ) {
						$this->update_option( $single_file['name'] . 'lastmodunix', $single_file['lastmodunix'] );

						wp_schedule_single_event( wp_date( 'U' ) + 60, $this->prefix . '_nivoda_copy_import_files' );
					}
				}
			}
		}
	}

	public function every_twenty_minute_cron() {
		error_log( '** every_twenty_minute_cron **' );

		if ( get_transient( 'csv_import_lock' ) ) {
			error_log( 'CSV import already running. Retrying in 20 min...' );

			return false;
		}

		set_transient( 'csv_import_lock', true, 2 * 60 * MINUTE_IN_SECONDS );

		error_log( '** Starting CSV Import **' );

		// import
		$this->run_csv_import();

		// Done processing delete transient...
		delete_transient( 'csv_import_lock' );

		error_log( '** CSV Import Completed **' );

		return true;
	}

	public function every_two_hour_cron() {
		error_log( '** every_two_hour_cron **' );

		if ( get_transient( 'csv_import_lock' ) ) {
			error_log( 'CSV import already running. Retrying in two hour...' );

			return false;
		}

		set_transient( 'csv_import_lock', true, 60 * 4 * MINUTE_IN_SECONDS );

		error_log( '** Starting CSV Import **' );

		// import
		$this->run_csv_import();

		// Done processing delete transient...
		delete_transient( 'csv_import_lock' );

		error_log( '** CSV Import Completed **' );

		return true;
	}

	public function every_four_hour_cron() {
		error_log( '** every_four_hour_cron **' );

		$this->update_option( 'current_import_file', array() );

		$this->update_option( 'import_nivoda_csv_files', array() );

		$this->get_diamonds_from_csv();
	}

	public function start_cron_event() {
		error_log( '** start_cron_event **' );

		$events = array(
			$this->prefix . '_every_four_hour'     => 'every_four_hour',
			$this->prefix . '_every_ten_minute'    => 'every_ten_minute',
			$this->prefix . '_every_twenty_minute' => 'every_twenty_minute',
		);

		foreach ( $events as $hook => $recurrence ) {
			if ( ! wp_next_scheduled( $hook ) ) {
				wp_schedule_event( wp_date( 'U' ) + 1, $recurrence, $hook );
			}
		}
	}

	private function clear_scheduled_cron_jobs() {
		delete_transient( 'csv_import_lock' );

		$events = array(
			$this->prefix . '_every_four_hour',
			$this->prefix . '_every_ten_minute',
			$this->prefix . '_every_twenty_minute',
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
		error_log( '** run_csv_import ** ' );

		log_all_options();

		try {
			$files_list = $this->get_option( 'import_nivoda_csv_files' );

			error_log( print_r( $files_list, true ) );

			if ( ! (
				$files_list &&
				is_array( $files_list ) &&
				count( $files_list ) >= 1
			) ) {
				return false;
			}

			$current_file = $this->get_option( 'current_import_file' );

			error_log( '$current_file: ' . print_r( $current_file, true ) );

			if ( ! $current_file ) {
				$this->add_file_to_import_que( $files_list );

				return false;
			}

			if ( $current_file &&
			isset( $current_file['rows'] ) &&
			isset( $current_file['rows_imported'] ) &&
			$current_file['rows_imported'] < $current_file['rows']
			) {
				error_log( '** $current_file[rows_imported] < $current_file[rows] **' );

				if ( ! file_exists( $current_file['absolute_path'] ) ) {
					$this->remove_file_from_import_que( $current_file );

					return false;
				}

				$fileHandle = fopen( $current_file['absolute_path'], 'r' );

				if ( ! $fileHandle || ! flock( $fileHandle, LOCK_EX ) ) {
					fclose( $fileHandle );

					return false;
				}

				if ( isset( $current_file['last_position'] ) ) {
					fseek( $fileHandle, $current_file['last_position'] );
				}

				$maxLines = 2000;

				$columns = fgetcsv( $fileHandle );

				while ( $maxLines > 0 && $columns ) {
					--$maxLines;

					if ( ! isset( $current_file['headers'] ) ) {
						$current_file['headers'] = $columns;

						$current_file['last_position'] = ftell( $fileHandle );

						++$current_file['rows_imported'];

						$this->update_option( 'current_import_file', $current_file );

						continue;
					}

					if ( count( $current_file['headers'] ) == count( $columns ) ) {
						$db_diamond = array_combine( $current_file['headers'], $columns );

						$this->update_insert_new_csv_diamond( $db_diamond );
					}

					$current_file['last_position'] = ftell( $fileHandle );

					++$current_file['rows_imported'];

					$this->update_option( 'current_import_file', $current_file );
				}

				fclose( $fileHandle );
			}

			if ( $current_file &&
			isset( $current_file['rows'] ) &&
			isset( $current_file['rows_imported'] ) &&
			$current_file['rows_imported'] >= $current_file['rows']
			) {
				error_log( '** $current_file[rows_imported] >= $current_file[rows] **' );

				$diamond_type = 'lab';

				if ( $current_file['name'] == 'natural_diamonds.csv' ) {
					$diamond_type = 'natural';
				}

				$this->delete_old_nivoda_diamonds( ' AND d_type = "' . $diamond_type . '"' );

				wp_delete_file( $current_file['absolute_path'] );

				$this->remove_file_from_import_que( $current_file );
			}
		} finally {
			delete_transient( 'csv_import_lock' );
		}

		return true;
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
		error_log( '** add_file_to_import_que ** ' );

		$first_file = reset( $files_list );

		$list_worksheet_info = $this->list_worksheet_info(
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
			$this->remove_file_from_import_que( $first_file );
		}
	}

	public function remove_file_from_import_que( $current_file ) {
		error_log( '** remove_file_from_import_que ** ' );

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

	public function update_insert_new_csv_diamond( $db_diamond ) {
		error_log( '** update_insert_new_csv_diamond ** ' );

		$diamond = array();

		if (
		! isset( $db_diamond['markup_price'] ) || empty( $db_diamond['markup_price'] ) ||
		! isset( $db_diamond['price'] ) || empty( $db_diamond['price'] ) ||
		! isset( $db_diamond['carats'] ) || empty( $db_diamond['carats'] ) ||
		! isset( $db_diamond['stock_id'] ) || empty( $db_diamond['stock_id'] ) ||
		! isset( $db_diamond['shape'] ) || empty( $db_diamond['shape'] ) ||
		! isset( $db_diamond['video'] ) || empty( $db_diamond['video'] ) ||
		! isset( $db_diamond['image'] ) || empty( $db_diamond['image'] ) ||
		! isset( $db_diamond['col'] ) || empty( $db_diamond['col'] )
		) {
			return false;
		}

		if ( ! isset( $db_diamond['col'] ) || empty( $db_diamond['col'] ) ) {
			$db_diamond['col'] = '';
		}
		if ( ! isset( $db_diamond['clar'] ) || empty( $db_diamond['clar'] ) ) {
			$db_diamond['clar'] = '';
		}
		if ( ! isset( $db_diamond['symm'] ) || empty( $db_diamond['symm'] ) ) {
			$db_diamond['symm'] = '';
		}
		if ( ! isset( $db_diamond['length'] ) || empty( $db_diamond['length'] ) ) {
			$db_diamond['length'] = '';
		}
		if ( ! isset( $db_diamond['width'] ) || empty( $db_diamond['width'] ) ) {
			$db_diamond['width'] = '';
		}
		if ( ! isset( $db_diamond['lab'] ) || empty( $db_diamond['lab'] ) ) {
			$db_diamond['lab'] = '';
		}
		if ( ! isset( $db_diamond['video'] ) || empty( $db_diamond['video'] ) ) {
			$db_diamond['video'] = '';
		}
		if ( ! isset( $db_diamond['image'] ) || empty( $db_diamond['image'] ) ) {
			$db_diamond['image'] = 'https://wordpress-1167849-4081671.cloudwaysapps.com/wp-content/uploads/2023/10/cat_halo-300.webp';
		}

		$diamond['id'] = $db_diamond['stock_id'];
		$diamond['upload'] = 'csv';
		$diamond['markup_price'] = $db_diamond['markup_price'];
		$diamond['price'] = $db_diamond['price'];
		$diamond['diamond']['certificate']['video'] = $db_diamond['video'];
		$diamond['diamond']['certificate']['image'] = $db_diamond['image'];
		$diamond['diamond']['certificate']['carats'] = $db_diamond['carats'];
		$diamond['diamond']['certificate']['shape'] = $db_diamond['shape'];
		$diamond['diamond']['certificate']['color'] = $db_diamond['col'];
		$diamond['diamond']['certificate']['clarity'] = $db_diamond['clar'];
		$diamond['diamond']['certificate']['symmetry'] = $db_diamond['symm'];
		$diamond['diamond']['certificate']['length'] = $db_diamond['length'];
		$diamond['diamond']['certificate']['width'] = $db_diamond['width'];
		$diamond['diamond']['certificate']['lab'] = $db_diamond['lab'];

		if ( isset( $db_diamond['pdf'] ) && $db_diamond['pdf'] ) {
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

		$this->insert_new_diamond( $formated_diamond, array( 'new_diamond_key' => $this->get_option( 'last_nivoda_update_key' ) ) );
	}

	public function list_worksheet_info( $pFilename ) {

		$fileHandle = fopen( $pFilename, 'r' );

		if ( ! $fileHandle ) {
			return false;
		}

		$worksheetInfo = array();

		$worksheetInfo['worksheetName'] = 'Worksheet';

		$worksheetInfo['lastColumnLetter'] = 'A';

		$worksheetInfo['lastColumnIndex'] = 0;

		$worksheetInfo['totalRows'] = 0;

		$worksheetInfo['totalColumns'] = 0;

		$rowData = fgetcsv( $fileHandle, 0 );

		while ( $rowData !== false ) {
			++$worksheetInfo['totalRows'];

			$worksheetInfo['lastColumnIndex'] = max(
				$worksheetInfo['lastColumnIndex'],
				count( $rowData ) - 1
			);
		}

		$worksheetInfo['totalColumns'] = $worksheetInfo['lastColumnIndex'] + 1;

		fclose( $fileHandle );

		return $worksheetInfo;
	}

	public function delete_old_nivoda_diamonds( $where = '' ) {
		error_log( '** delete_old_nivoda_diamonds ** ' );

		$last_update_key = $this->get_option( 'last_nivoda_update_key' );

		if ( $last_update_key ) {
			global $wpdb;

			$query = 'DELETE FROM ' . $wpdb->prefix . "otw_diamonds WHERE last_update_key != '" . $last_update_key . "'";

			$query .= $where;

			$wpdb->query( $query );
		}
	}

	public function insert_new_diamond( $formated_diamond, $nivoda_cron_status ) {
		error_log( '** insert_new_diamond ** ' );

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

		$inserted = $wpdb->replace( $wpdb->prefix . 'otw_diamonds', $data, $format );
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
