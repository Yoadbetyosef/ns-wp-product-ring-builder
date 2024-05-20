<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// define('OTW_WOO_RING_BUILDER_PLUGIN_FILE', __FILE__);
// include_once plugin_dir_path(OTW_WOO_RING_BUILDER_PLUGIN_FILE).'includes/autoload.php';
// \BBWP\Engine\PluginDefault::instance()->PluginUninstall();

$prefix = 'otw_woo_ring_builder';
delete_option($prefix.'_options');