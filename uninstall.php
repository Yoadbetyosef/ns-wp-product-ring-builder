<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$prefix = 'otw_woo_ring_builder';

delete_option( $prefix . '_options' );
