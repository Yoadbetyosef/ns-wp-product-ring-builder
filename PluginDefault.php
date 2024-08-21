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

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

		if ( is_admin() ) {
			add_filter( 'plugin_action_links_' . plugin_basename( OTW_WOO_RING_BUILDER_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );

			register_activation_hook( plugin_basename( OTW_WOO_RING_BUILDER_PLUGIN_FILE ), array( $this, 'PluginActivation' ) );

			\OTW\WooRingBuilder\Admin\PageSettings::instance();
			\OTW\WooRingBuilder\Admin\VariationsMetaData::instance();

			add_action( 'admin_enqueue_scripts', array( $this, 'wp_admin_style_scripts' ) );

			add_filter( 'upload_mimes', array( $this, 'upload_mimes' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_admin_style_scripts' ) );

			add_action( 'wp', array( $this, 'redirect_settings' ), 1 );
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

	function redirect_settings() {
		global $post;

		if ( is_singular( 'product' ) && is_product() && isset( $post->ID ) ) {
			$product = wc_get_product( $post->ID );

			if ( $product &&
			$product->is_type( 'variable' ) &&
			has_term( 'crb_setting', 'product_cat' )
			) {
				foreach ( $product->get_available_variations() as $variation_values ) {
					foreach ( $variation_values['attributes'] as $key => $attribute_value ) {
						$attribute_name = str_replace( 'attribute_', '', $key );

						$default_value = $product->get_variation_default_attribute( $attribute_name );

						if ( $default_value == $attribute_value ) {
							$is_default_variation = true;
						} else {
							$is_default_variation = false;

							break;
						}
					}

					if ( isset( $is_default_variation ) && $is_default_variation ) {
						$variation_id = $variation_values['variation_id'];

						break;
					}
				}

				if ( isset( $variation_id ) && $variation_id ) {
					$redirect_url = get_permalink( $this->get_option( 'gcpb_product_page' ) );

					$redirect_url = add_query_arg(
						array(
							'first_step'   => 'setting',
							'setting_data' => 'reset_diamond',
							'product_id'   => $post->ID,
							'variation_id' => $variation_id,
						),
						$redirect_url
					);

					wp_safe_redirect( $redirect_url );

					exit();
				}
			}
		}
	}

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
			$this->start_cron_event();
		}
	}

	public function PluginDeactivation() {
	}

	public function plugins_loaded() {
		load_plugin_textdomain(
			'otw-woo-ring-builder-td',
			false,
			plugin_dir_path( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'languages/'
		);
	}

	public function wp_admin_style_scripts() {
		if ( is_admin() ) {
			wp_register_script( 'vue3', plugin_dir_url( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'assets/admin/js/vue-global-3-2-11.js', array(), '3.2.11' );

			wp_enqueue_script( 'bbwp_fields_image_js', plugin_dir_url( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'assets/admin/js/bbwp_fields_image.js', array( 'jquery' ), '1.0.0' );

			wp_enqueue_script( 'bbwp_fields_js', plugin_dir_url( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'assets/admin/js/bbwp_fields.js', array( 'jquery' ), '1.0.0' );

			wp_enqueue_style( 'bbwp_fields_css', plugin_dir_url( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'assets/admin/css/bbwp_fields.css', array(), '1.0.0' );
		} else {
			wp_register_script( 'pixotronics', 'https://dist.pixotronics.com/webgi/runtime/viewer-0.7.86.js', array( 'jquery' ), '0.7.86' );

			wp_register_script( 'swiper_otw', plugin_dir_url( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'assets/js/swiper.min.js', array( 'jquery' ), '5.3.6' );

			wp_enqueue_style( 'swiper_css_otw', plugin_dir_url( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'assets/css/swiper.min.css' );

			wp_enqueue_style( 'lightbox-css', plugin_dir_url( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'assets/css/lightbox.min.css', array(), '2.11.4' );

			wp_register_script( 'lightbox', plugin_dir_url( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'assets/js/lightbox.min.js', array( 'jquery' ), '2.11.4' );

			wp_enqueue_script( 'lightbox' );

			wp_register_script( 'touch-punch', 'https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js', array( 'jquery-ui-slider' ), '0.2.3' );

			$script_abs_path = plugin_dir_path( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'assets/frontend/js/script.js';

			wp_register_script( $this->prefix( 'script' ), plugin_dir_url( OTW_WOO_RING_BUILDER_PLUGIN_FILE ) . 'assets/frontend/js/script.js', array( 'jquery', 'swiper_otw' ), get_file_time( $script_abs_path ) );

			wp_enqueue_script( 'pixotronics' );

			if ( is_page( $this->get_option( 'stone_archive_page' ) ) ) {
				wp_enqueue_script( 'jquery-ui-slider' );

				wp_enqueue_script( 'touch-punch' );
			}

			wp_enqueue_script( $this->prefix( 'script' ) );

			$wp_scripts = wp_scripts();

			wp_enqueue_style( 'jquery-ui-css', '//code.jquery.com/ui/' . $wp_scripts->registered['jquery-ui-core']->ver . '/themes/base/jquery-ui.css', false, $wp_scripts->registered['jquery-ui-core']->ver, false );

			$js_variables = array(
				'ajax_url'     => admin_url( 'admin-ajax.php' ),
				'wp_is_mobile' => wp_is_mobile(),
			);

			global $wp_query;

			if ( $wp_query && isset( $wp_query->queried_object ) && isset( $wp_query->queried_object->ID ) && isset( $wp_query->queried_object->post_type ) && $wp_query->queried_object->post_type == 'page' ) {
				$current_page_id = $wp_query->queried_object->ID;

				$js_variables['current_page_id'] = $current_page_id;

				if ( isset( $wp_query->queried_object->post_parent ) && $wp_query->queried_object->post_parent == $this->get_option( 'gcpb_listing_page' ) ) {
					$attribute_slug = get_post_meta( $current_page_id, 'attribute-slug', true );

					$query_id = get_post_meta( $current_page_id, 'query-id', true );

					if ( $attribute_slug && $query_id ) {
						$term = get_term_by( 'id', $query_id, $attribute_slug );

						if ( $term ) {
							$js_variables['attribute_slug'] = $attribute_slug;

							$js_variables['attribute_term'] = strtolower( $term->slug );
						}
					}
				}
			}

			if ( gcpb_get_current_first_step() == 'stone' || isset( $_GET['stock_num'] ) ) {
				otw_woo_ring_builder()->diamonds->get_current_diamond();

				if ( otw_woo_ring_builder()->diamonds && isset( otw_woo_ring_builder()->diamonds->current_diamond ) && otw_woo_ring_builder()->diamonds->current_diamond ) {
					$diamond = otw_woo_ring_builder()->diamonds->current_diamond;

					if ( isset( $diamond['shape'] ) && $diamond['shape'] ) {
						$js_variables['diamond_shape'] = strtolower( $diamond['shape'] );
					}
				}
			}

			$js_variables['diamond_min_price_filter'] = 300;
			$js_variables['diamond_max_price_filter'] = 42000;

			$js_variables['diamond_min_price_filter_value'] = 300;
			$js_variables['diamond_max_price_filter_value'] = 42000;
			if ( $this->get_option( 'diamond_price_filter_min_value' ) ) {
				$js_variables['diamond_min_price_filter'] = $this->get_option( 'diamond_price_filter_min_value' );
				$js_variables['diamond_min_price_filter_value'] = $this->get_option( 'diamond_price_filter_min_value' );
			}
			if ( $this->get_option( 'diamond_price_filter_max_value' ) ) {
				$js_variables['diamond_max_price_filter'] = $this->get_option( 'diamond_price_filter_max_value' );
				$js_variables['diamond_max_price_filter_value'] = $this->get_option( 'diamond_price_filter_max_value' );
			}

			$js_variables['gcpb_listing_page'] = $this->get_option( 'gcpb_listing_page' );
			$js_variables['gcpb_product_page'] = $this->get_option( 'gcpb_product_page' );
			$js_variables['checkout_complete_page'] = $this->get_option( 'checkout_complete_page' );
			$js_variables['stone_archive_page'] = $this->get_option( 'stone_archive_page' );

			$js_variables['diamond_min_carat_filter'] = 0.3;
			$js_variables['diamond_max_carat_filter'] = 14.6;
			$js_variables['diamond_min_carat_filter_value'] = 2.5;
			$js_variables['diamond_max_carat_filter_value'] = 3.5;

			$js_variables['ip'] = get_client_ip();
			wp_localize_script( $this->prefix( 'script' ), $this->prefix, $js_variables );
		}
	}

	public function init() {
		if ( $this->get_option( 'nivoda_api' ) ) {
			$this->local_db_cron_init();
		}

		add_filter( 'wp_all_export_available_data', array( $this, 'wp_all_export_available_data' ) );

		$this->empty_cart();

		add_action( 'wp_footer', array( $this, 'wp_footer' ) );
	}

	public function wp_footer() {
		?>
			<script>
				jQuery(document).ready(function($){
				$(".elementor-menu-cart__subtotal strong").html("Total: ");
				});
			</script>	
		<?php
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

	public function w3_modal() {
		?>
			<div id="otw_w3_modal" class="otw_w3_modal">
				<div class="otw_w3_modal_content">
				<div class="otw_w3_modal_header">
					<span class="otw_w3_close">X</span>
				</div>
				<div class="otw_w3_modal_body">
				</div>
				</div>
			</div>

			<script>
				jQuery(document).ready(function($){
			
					$(document).on('click', '.open_w3_modal', function(event){
						$('.otw_w3_modal').show();
					});

					$(document).on('click', '.otw_w3_close', function(event){
						$('.otw_w3_modal').hide();
					});

					$(document).on('click', function(event){
						if ($(event.target).hasClass('otw_w3_modal')) {
							$('.otw_w3_modal').hide();
						}
					});


					$(document).on('click', '.gcpb-product-wrapper webgi-viewer', function(event){
						if($(this).attr('src')){
							let three_d_viewer_id = generateRandomString(8);
							let embed_html = '<webgi-viewer src="'+$(this).attr('src')+'" id="'+three_d_viewer_id+'" disposeOnRemove="true" style="width: 100%; height: 500px; z-index: 9999999; display: block; position:relative;" />';
							if($('.otw_w3_modal_body').find("webgi-viewer").length <= 0){
								$('.otw_w3_modal_body').append(embed_html);
								dom_setup_three_d_viewer();
							}
							$('.otw_w3_modal').show();
						}
						
					});

				});
			</script>

			<style>
				.otw_w3_modal {
					display: none; /* Hidden by default */
					position: fixed; /* Stay in place */
					z-index: 999999; /* Sit on top */
					padding-top: 100px; /* Location of the box */
					left: 0;
					top: 0;
					width: 100%; /* Full width */
					height: 100%; /* Full height */
					overflow: auto; /* Enable scroll if needed */
					background-color: rgb(0,0,0); /* Fallback color */
					background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
				}

				/* Modal Content */
				.otw_w3_modal_content {
					position: relative;
					background-color: #fefefe;
					margin: auto;
					padding: 0;
					border: 1px solid #888;
					width: 80%;
					box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
					-webkit-animation-name: animatetop;
					-webkit-animation-duration: 0.4s;
					animation-name: animatetop;
					animation-duration: 0.4s
				}

				/* Add Animation */
				@-webkit-keyframes animatetop {
					from {top:-300px; opacity:0} 
					to {top:0; opacity:1}
				}

				@keyframes animatetop {
					from {top:-300px; opacity:0}
					to {top:0; opacity:1}
				}

				/* The Close Button */
				.otw_w3_close {
					color: #000;
					float: right;
					font-size: 24px;
					font-weight: bold;
				}

				.otw_w3_close:hover,
				.otw_w3_close:focus {
					color: #f00;
					text-decoration: none;
					cursor: pointer;
				}

				.otw_w3_modal_header {
					padding: 2px 16px;
					/* background-color: #5cb85c; */
					color: white;
				}

				.otw_w3_modal_body {padding: 30px 16px 30px 16px;}

				.otw_w3_modal_footer {
					padding: 2px 16px;
					background-color: #5cb85c;
					color: white;
				}
				/* ********************* */
				/* The Modal */
				/* ********************* */
			</style>
		<?php
	}

	public function wp_footer_css() {
		?>
			<style>
			
			</style>  
		<?php
	}
}
