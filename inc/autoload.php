<?php

require_once plugin_dir_path( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'inc' . DIRECTORY_SEPARATOR . 'functions.php';

spl_autoload_register(
	function ( $class ) {
		$allowed_prefixes = array(
			array(
				'namespace' => 'OTW\GeneralWooRingBuilder',
				'base_dir'  => plugin_dir_path( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'classes' . DIRECTORY_SEPARATOR . 'general' . DIRECTORY_SEPARATOR,
			),
			array(
				'namespace' => 'OTW\WooRingBuilder',
				'base_dir'  => plugin_dir_path( OTW_WOO_RING_BUILDER_PLUGIN_FILE ),
			),
		);

		foreach ( $allowed_prefixes as $prefix ) {
			if ( false === strpos( $class, $prefix['namespace'] ) ) {
				continue;
			}

			$len = strlen( $prefix['namespace'] );

			$relative_class_string = substr( $class, $len + 1 );

			$relative_class_dir = explode( '\\', $relative_class_string );

			$relative_class = array_pop( $relative_class_dir );

			$relative_dir = '';

			if ( $relative_class_dir && is_array( $relative_class_dir ) && count( $relative_class_dir ) >= 1 ) {
				$relative_dir = implode( DIRECTORY_SEPARATOR, $relative_class_dir );
			}

			$dir_path = strtolower(
				preg_replace(
					array( '/([a-z])([A-Z])/', '/_/', '/\\\/' ),
					array( '$1$2', '-', DIRECTORY_SEPARATOR ),
					$relative_dir
				)
			);

			if ( $dir_path ) {
				$file = $prefix['base_dir'] . $dir_path . DIRECTORY_SEPARATOR . $relative_class . '.php';
			} else {
				$file = $prefix['base_dir'] . $relative_class . '.php';
			}

			if ( file_exists( $file ) ) {
				include_once $file;

				break;
			}
		}
	}
);
