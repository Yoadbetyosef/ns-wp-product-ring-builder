<?php
namespace OTW\WooRingBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PluginDefault extends Plugin{
	use \OTW\GeneralWooRingBuilder\Traits\Singleton;
	use \OTW\WooRingBuilder\Traits\LocalDBCron;

	public function __construct() {
		$this->set_get_variables();

		// add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

		if ( is_admin() ) {
			// add_filter( 'plugin_action_links_' . plugin_basename( OTW_WOO_RING_BUILDER_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );

			register_activation_hook( plugin_basename( OTW_WOO_RING_BUILDER_PLUGIN_FILE ), array( $this, 'PluginActivation' ) );

			register_deactivation_hook( plugin_basename( OTW_WOO_RING_BUILDER_PLUGIN_FILE ), array( $this, 'PluginDeactivation' ) );

			// \OTW\WooRingBuilder\Admin\PageSettings::instance();

			\OTW\WooRingBuilder\Admin\VariationsMetaData::instance();

			add_action( 'admin_enqueue_scripts', array( $this, 'wp_admin_style_scripts' ) );

			add_filter( 'upload_mimes', array( $this, 'upload_mimes' ) );
		}

		if ( session_status() === PHP_SESSION_NONE && ! headers_sent() ) {
			session_start(
				array(
					'read_and_close' => true,
				)
			);
		}

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		if ( $this->get_option( 'nivoda_api' ) ) {
			$this->local_db_cron_init();
		}

		add_filter( 'wp_all_export_available_data', array( $this, 'wp_all_export_available_data' ) );

		$this->empty_cart();
	}

	// function redirect_settings() {
	//  global $post;

	//  if ( is_singular( 'product' ) && is_product() && isset( $post->ID ) ) {
	//      $product = wc_get_product( $post->ID );

	//      if ( $product &&
	//      $product->is_type( 'variable' ) &&
	//      has_term( 'crb_setting', 'product_cat' )
	//      ) {
	//          foreach ( $product->get_available_variations() as $variation_values ) {
	//              foreach ( $variation_values['attributes'] as $key => $attribute_value ) {
	//                  $attribute_name = str_replace( 'attribute_', '', $key );

	//                  $default_value = $product->get_variation_default_attribute( $attribute_name );

	//                  if ( $default_value == $attribute_value ) {
	//                      $is_default_variation = true;
	//                  } else {
	//                      $is_default_variation = false;

	//                      break;
	//                  }
	//              }

	//              if ( isset( $is_default_variation ) && $is_default_variation ) {
	//                  $variation_id = $variation_values['variation_id'];

	//                  break;
	//              }
	//          }

	//          if ( isset( $variation_id ) && $variation_id ) {
	//              $redirect_url = get_permalink( $this->get_option( 'gcpb_product_page' ) );

	//              $redirect_url = add_query_arg(
	//                  array(
	//                      'first_step'   => 'setting',
	//                      'setting_data' => 'reset_diamond',
	//                      'product_id'   => $post->ID,
	//                      'variation_id' => $variation_id,
	//                  ),
	//                  $redirect_url
	//              );

	//              wp_safe_redirect( $redirect_url );

	//              exit();
	//          }
	//      }
	//  }
	// }

	public function set_get_variables() {
		if ( isset( $_GET['setting_data'] ) && $_GET['setting_data'] == 'reset_all' ) {
			$this->delete_setting_cookies();

			$this->delete_diamond_cookies();
		}

		if ( isset( $_GET['setting_data'] ) && $_GET['setting_data'] == 'reset_diamond' ) {
			$this->delete_diamond_cookies();
		}

		if ( isset( $_GET['setting_data'] ) && $_GET['setting_data'] == 'reset_setting' ) {
			$this->delete_setting_cookies();
		}

		if ( isset( $_COOKIE['product_id'] ) && $_COOKIE['product_id'] ) {
			$_COOKIE['old_product_id'] = $_COOKIE['product_id'];

			if ( ! isset( $_GET['product_id'] ) ) {
				$_GET['product_id'] = $_COOKIE['product_id'];
			}
		}

		if ( isset( $_COOKIE['variation_id'] ) && $_COOKIE['variation_id'] ) {
			$_COOKIE['old_variation_id'] = $_COOKIE['variation_id'];

			if ( ! isset( $_GET['variation_id'] ) ) {
				$_GET['variation_id'] = $_COOKIE['variation_id'];
			}
		}

		if ( isset( $_COOKIE['stock_num'] ) && $_COOKIE['stock_num'] ) {
			$_COOKIE['old_stock_num'] = $_COOKIE['stock_num'];

			if ( ! isset( $_GET['stock_num'] ) ) {
				$_GET['stock_num'] = $_COOKIE['stock_num'];
			}
		}

		if ( isset( $_GET['stock_num'] ) ) {
			$this->update_variation_with_new_shape();
		}

		$this->wp_footer_cookies();
	}

	public function update_variation_with_new_shape() {
		if ( isset( $_GET['stock_num'] ) && $_GET['stock_num'] ) {
			add_action(
				'init',
				function () {
					otw_woo_ring_builder()->diamonds->get_current_diamond();

					if ( otw_woo_ring_builder()->diamonds &&
					isset(
						otw_woo_ring_builder()->diamonds->current_diamond
					) &&
					otw_woo_ring_builder()->diamonds->current_diamond
					) {
						$diamond = otw_woo_ring_builder()->diamonds->current_diamond;

						if ( isset( $_GET['variation_id'] ) &&
						$_GET['variation_id'] &&
						isset( $_GET['product_id'] ) &&
						$_GET['product_id']
						) {
							$current_shape = otw_woo_ring_builder()->get_current_selected_variation_shape();

							$diamond_shape = strtolower( $diamond['shape'] );

							if ( $current_shape &&
							$diamond['shape'] &&
							strtolower( $current_shape ) != $diamond_shape &&
							class_exists( 'WC_Data_Store' )
							) {
								if ( ( isset( $_COOKIE['old_stock_num'] ) && $_COOKIE['old_stock_num'] != $_GET['stock_num'] ) ||
								! isset( $_COOKIE['stock_num'] ) ||
								( isset( $_COOKIE['old_variation_id'] ) && $_COOKIE['old_variation_id'] == $_GET['variation_id'] )
								) {
									$parent_product = wc_get_product( $_GET['product_id'] );
									$data_store = \WC_Data_Store::load( 'product' );
									$variable_product = new \WC_Product_Variation( $_GET['variation_id'] );

									if ( $parent_product && $variable_product ) {
										$attributes = $variable_product->get_attributes();
										$tax_attributes = array( 'attribute_pa_shape' => $diamond_shape );
										foreach ( $attributes as $key => $attribute ) {
											if ( $key == 'pa_shape' ) {
													continue;
											}
											$tax_attributes[ 'attribute_' . $key ] = $attribute;
										}

										$found_products = $data_store->find_matching_product_variation( $parent_product, $tax_attributes );

										if ( $found_products && is_integer( $found_products ) ) {
											$_GET['variation_id'] = $found_products;
											otw_woo_ring_builder()->woo->current_selected_variation = null;
											$current_selected_shape = strtolower( otw_woo_ring_builder()->get_current_selected_variation_shape() );
											$this->setcookie( 'variation_id', $found_products );
										}
									}
								} else {
									$this->delete_diamond_cookies();
									otw_woo_ring_builder()->diamonds->current_diamond = null;
									return true;
								}
							}
						}
					}
				}
			);

		}
	}

	public function wp_footer_cookies() {
		if ( isset( $_GET['product_id'] ) && $_GET['product_id'] ) {
			$this->setcookie( 'product_id', $_GET['product_id'] );
		}

		if ( isset( $_GET['variation_id'] ) && $_GET['variation_id'] ) {
			$this->setcookie( 'variation_id', $_GET['variation_id'] );
		}

		if ( isset( $_GET['stock_num'] ) && $_GET['stock_num'] ) {
			$this->setcookie( 'stock_num', $_GET['stock_num'] );
		}

		if ( isset( $_GET['first_step'] ) && $_GET['first_step'] == 'stone' ) {
			$this->setcookie( 'first_step', 'stone' );

			$_COOKIE['first_step'] = 'stone';
		} elseif ( isset( $_GET['first_step'] ) && $_GET['first_step'] == 'setting' ) {
			$this->setcookie( 'first_step', 'setting' );

			$_COOKIE['first_step'] = 'setting';
		}
	}

	function upload_mimes( $mimes ) {
		$mimes['glb']  = 'application/octet-stream';

		return $mimes;
	}

	public function plugin_action_links( $links ) {
		$page_url = add_query_arg( array( 'page' => $this->prefix ), admin_url( 'admin.php' ) );

		$links[] = '<a href="' . $page_url . '">' . __( 'Settings', 'otw-woo-ring-builder-td' ) . '</a>';

		return $links;
	}

	public function PluginActivation() {
		global $wpdb;

		$this->create_custom_table();

		if ( $this->get_option( 'nivoda_api' ) ) {
			$this->local_db_cron_init();
			$this->start_cron_event();
		}
	}

	public function PluginDeactivation() {
		$this->local_db_cron_init();
		$this->clear_scheduled_cron_jobs();
	}

	// public function plugins_loaded() {
	//  load_plugin_textdomain(
	//      'otw-woo-ring-builder-td',
	//      false,
	//      plugin_dir_path( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'languages/'
	//  );
	// }

	public function wp_admin_style_scripts() {
		if ( is_admin() ) {
			wp_register_script( 'vue3', plugin_dir_url( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'assets/admin/js/vue-global-3-2-11.js', array(), '3.2.11' );

			wp_enqueue_script( 'bbwp_fields_image_js', plugin_dir_url( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'assets/admin/js/bbwp_fields_image.js', array( 'jquery' ), '1.0.0' );

			wp_enqueue_script( 'bbwp_fields_js', plugin_dir_url( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'assets/admin/js/bbwp_fields.js', array( 'jquery' ), '1.0.0' );

			wp_enqueue_style( 'bbwp_fields_css', plugin_dir_url( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'assets/admin/css/bbwp_fields.css', array(), '1.0.0' );
		}
	}

	public function wp_all_export_available_data( $available_data ) {
		if ( isset( $available_data['existing_meta_keys'] ) && isset( $available_data['woo_data'] ) ) {
			$new_keys = array( 'otw_woo_variation_3d_model', 'otw_woo_variation_gallery_images', 'otw_woo_variation_video_url' );
			$available_data['existing_meta_keys'] = array_merge( $new_keys, $available_data['existing_meta_keys'] );

		}

		return $available_data;
	}

	public function empty_cart() {
		if ( isset( $_REQUEST['has_setting'] ) &&
			$_REQUEST['has_setting'] == 'yes' &&
			function_exists( 'WC' )
		) {
			WC()->cart->empty_cart();
		}
	}
}
