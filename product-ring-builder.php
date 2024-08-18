<?php
/*
Plugin Name: Ring Builder
Plugin URI: https://otw.design/
Description: Ring Builder
Author: OTW Design
Version: 1.0.0
Author URI: https://otw.design/
Text Domain:       otw-woo-ring-builder-td
Domain Path:       /languages
License:           GPL v2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

define( 'PRB_DIR', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_WITH_CLASSES__FILE__', __FILE__ );

define('OTW_WOO_RING_BUILDER_PLUGIN_FILE', __FILE__);
include_once plugin_dir_path(OTW_WOO_RING_BUILDER_PLUGIN_FILE).'inc/autoload.php';
otw_woo_ring_builder();

// plugin main classs

include( PRB_DIR. 'includes/helper.php'); 
include( PRB_DIR. 'includes/class/class-ring-storage.php');
include( PRB_DIR. 'includes/class/class-ring-builder.php');
include( PRB_DIR. 'includes/class/class-ring-setting.php');

