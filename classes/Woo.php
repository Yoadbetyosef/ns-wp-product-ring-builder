<?php
namespace OTW\WooRingBuilder\Classes;

// exit if file is called directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo extends \OTW\WooRingBuilder\Plugin {
	use \OTW\GeneralWooRingBuilder\Traits\Singleton;

	public $current_selected_variation = null;

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		// add_action( 'init', array( $this, 'catch_url_params' ) );

		// 1
		add_action( 'wp_ajax_nopriv_gcpb_add_to_cart', array( $this, 'gcpb_add_to_cart' ) );
		add_action( 'wp_ajax_gcpb_add_to_cart', array( $this, 'gcpb_add_to_cart' ) );

		// 2
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 25, 2 );

		// 3
		add_action( 'woocommerce_add_to_cart', array( $this, 'woocommerce_add_to_cart' ), 99, 6 );

		// 4
		add_filter( 'woocommerce_cart_item_price', array( $this, 'cart_item_price' ), 10, 3 );

		// 5
		add_filter( 'woocommerce_get_item_data', array( $this, 'get_item_data' ), 10, 2 );

		// *
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'before_calculate_totals' ), 11 );

		// *
		add_action( 'woocommerce_add_order_item_meta', array( $this, 'add_order_item_meta' ), 10, 3 );

		// *
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'checkout_create_order_line_item' ), 10, 4 );

		add_filter( 'gettext', array( $this, 'gettext' ), 10, 3 );
	}

	// public function catch_url_params() {
	// 	error_log( 'URL parameters detected: ' . print_r( $_GET, true ) );
	// }

	// *
	public function gcpb_add_to_cart() {
		$data = array( 'error' => true );

		if ( isset( $_POST['variation_id'] ) &&
			$_POST['variation_id'] &&
			isset( $_POST['product_id'] ) &&
			$_POST['product_id']
		) {
			$product_id = $_POST['product_id'];

			$variation_id = $_POST['variation_id'];

			$quantity = 1;

			$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $variation_id, $quantity );

			$product_status = get_post_status( $product_id );

			$cart_items = WC()->cart->get_cart();

			foreach ( $cart_items as $cart_key => $cart_item ) {
				if ( $this->is_setting_product( $cart_item['data'] ) ) {
					WC()->cart->remove_cart_item( $cart_key );
				}
			}

			if ( $passed_validation &&
				WC()->cart->add_to_cart( $variation_id, $quantity ) &&
				'publish' === $product_status
			) {
				do_action( 'woocommerce_ajax_added_to_cart', $variation_id );

				$data = array( 'error' => false );

				ob_start();

				woocommerce_mini_cart();

				$data['div.widget_shopping_cart_content'] = ob_get_clean();

				$data['span.cart-items-count'] = WC()->cart->get_cart_contents_count();
			}
		}

		wp_send_json( $data );

		die();
	}

	// *
	public function add_cart_item_data( $cart_item_data, $product_id ) {
		$parent_product = wc_get_product( $product_id );

		$woocommerce_product_extra_data = isset( $_SERVER['HTTP_WOOCOMMERCE_PRODUCT_EXTRA_DATA'] )
			? sanitize_text_field( $_SERVER['HTTP_WOOCOMMERCE_PRODUCT_EXTRA_DATA'] )
			: null;

		if ( isset( $woocommerce_product_extra_data ) ) {
			$extra_data = json_decode( stripslashes( $woocommerce_product_extra_data ), true );

			if ( json_last_error() === JSON_ERROR_NONE ) {
				WC()->session->set( 'next_session', true );

				WC()->session->set( 'next_diamond_id', $extra_data['diamond_id'] );

				// $ring_size = isset( $extra_data['ring_size'] ) ? sanitize_text_field( $extra_data['ring_size'] ) : '';
			}
		}

		if ( ! $parent_product ) {
			return $cart_item_data;
		}

		$product_cats_ids = wc_get_product_term_ids( $parent_product->get_id(), 'product_cat' );

		if ( ! (
			$product_cats_ids &&
			is_array( $product_cats_ids ) &&
			count( $product_cats_ids ) >= 1 &&
			in_array( $this->get_option( 'setting_category' ), $product_cats_ids )
		) ) {
			return $cart_item_data;
		}

		if ( ! (
			otw_woo_ring_builder()->diamonds &&
			isset( otw_woo_ring_builder()->diamonds->current_diamond ) &&
			otw_woo_ring_builder()->diamonds->current_diamond
		) ) {
			otw_woo_ring_builder()->diamonds->get_current_diamond();
		}

		if ( ! (
			otw_woo_ring_builder()->diamonds &&
			isset( otw_woo_ring_builder()->diamonds->current_diamond ) &&
			otw_woo_ring_builder()->diamonds->current_diamond
		) ) {
			return $cart_item_data;
		}

		$diamond = otw_woo_ring_builder()->diamonds->current_diamond;

		$cart_item_data['diamond'] = $diamond;

		WC()->session->set( 'diamond', $diamond );

		// if ( isset( $ring_size ) ) {
		// 	$cart_item_data['ring_size'] = $ring_size;

		// 	WC()->session->set( 'ring_size', $ring_size );
		// }

		// if ( isset( $_REQUEST['size-selector'] ) && $_REQUEST['size-selector'] ) {
		// 	$cart_item_data['ring_size'] = $_REQUEST['size-selector'];

		// 	WC()->session->set( 'ring_size', $_REQUEST['size-selector'] );
		// }

		return $cart_item_data;
	}

	// *
	public function woocommerce_add_to_cart(
		$cart_id,
		$product_id,
		$request_quantity,
		$variation_id,
		$variation,
		$cart_item_data
	) {
		if ( empty( $variation_id ) || ! $variation_id ) {
			return true;
		}

		$variable_product = new \WC_Product_Variation( $variation_id );

		if ( ! $variable_product ) {
			return true;
		}

		if ( ! $this->is_setting_product( $variable_product ) ) {
			return true;
		}

		$cart_items = WC()->cart->get_cart();

		foreach ( $cart_items as $cart_key => $cart_item ) {
			if ( $this->is_setting_product( $cart_item['data'] ) && $cart_key != $cart_id ) {
				WC()->cart->remove_cart_item( $cart_key );
			}
		}
	}

	// *
	public function cart_item_price( $price_html, $cart_item, $cart_item_key ) {
		if ( $this->is_setting_product( $cart_item['data'] ) ) {
			if ( ! (
				otw_woo_ring_builder()->diamonds &&
				isset( otw_woo_ring_builder()->diamonds->current_diamond ) &&
				otw_woo_ring_builder()->diamonds->current_diamond
			) ) {
				otw_woo_ring_builder()->diamonds->get_current_diamond();
			}

			if ( otw_woo_ring_builder()->diamonds &&
				isset( otw_woo_ring_builder()->diamonds->current_diamond ) &&
				otw_woo_ring_builder()->diamonds->current_diamond
			) {
				$diamond = otw_woo_ring_builder()->diamonds->current_diamond;

				$_product = wc_get_product( $cart_item['data']->get_id() );

				if ( $this->is_setting_product( $_product ) &&
					( ( (float) $cart_item['data']->get_price() ) <= (float) $_product->get_price() )
				) {
					$total_ring_price = ( (float) $diamond['total_sales_price'] ) + ( (float) $cart_item['data']->get_price() );

					return wc_price( $total_ring_price );
				}
			}
		}

		return $price_html;

		if ( isset( $cart_item['custom_price'] ) ) {
			$args = array( 'price' => 40 );

			if ( WC()->cart->display_prices_including_tax() ) {
				$product_price = wc_get_price_including_tax( $cart_item['data'], $args );
			} else {
				$product_price = wc_get_price_excluding_tax( $cart_item['data'], $args );
			}

			return wc_price( $product_price );
		}

		return $price_html;
	}

	// *
	public function get_item_data( $item_data, $cart_item ) {
		if ( ! is_array( $item_data ) ) {
			$item_data = array();
		}

		$_product = $cart_item['data'];

		if ( ! $_product || ! isset( $cart_item['diamond'] ) ) {
			return $item_data;
		}

		if ( ! (
			$this->is_setting_product( $_product ) &&
			isset( $_GET['stock_num'] )
		) ) {
			return $item_data;
		}

		if ( ! (
				otw_woo_ring_builder()->diamonds &&
				isset(
					otw_woo_ring_builder()->diamonds->current_diamond
				) &&
			otw_woo_ring_builder()->diamonds->current_diamond
		) ) {
			return $item_data;
		}

		if ( ! (
			otw_woo_ring_builder()->diamonds &&
			isset( otw_woo_ring_builder()->diamonds->current_diamond ) &&
			otw_woo_ring_builder()->diamonds->current_diamond
		) ) {
			otw_woo_ring_builder()->diamonds->get_current_diamond();
		}

		$diamond = otw_woo_ring_builder()->diamonds->current_diamond;

		if ( $diamond['stock_num'] != $cart_item['diamond']['stock_num'] ) {
			return $item_data;
		}

		// if ( isset( $cart_item['ring_size'] ) && $cart_item['ring_size'] ) {
		//  $item_data[] = array(
		//      'type'  => 'text',
		//      'name'  => 'ring_size',
		//      'key'   => 'Ring Size',
		//      'value' => $cart_item['ring_size'] . ' ',
		//  );
		// }

		$item_data[] = array(
			'type'  => 'text',
			'name'  => 'Stone',
			'key'   => 'Stone',
			'value' => $diamond['size'] . ' carats ' . $diamond['color'] . ' ' . $diamond['clarity'] . ' ',
		);

		if ( isset( $diamond['meas_length'] ) && $diamond['meas_length'] ) {
			$item_data[] = array(
				'type'  => 'text',
				'name'  => 'Dimensions',
				'key'   => 'Dimensions',
				'value' => $diamond['meas_length'] . '/' . $diamond['meas_width'],
			);
		}

		if ( isset( $diamond['cert_num'] ) && $diamond['cert_num'] ) {
			$item_data[] = array(
				'type'  => 'text',
				'name'  => 'Certificate',
				'key'   => 'Certificate',
				'value' => $diamond['cert_num'],
			);
		}

		return $item_data;
	}

	// *
	public function checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {
		if ( ! isset( $values['diamond'] ) ) {
			return false;
		}

		// if ( isset( $values['ring_size'] ) ) {
		//  $item->add_meta_data( 'Ring Size: ', $values['ring_size'] );
		// }

		$item->add_meta_data( 'Stone', $values['diamond']['short_title'] );

		if ( isset( $values['diamond']['meas_length'] ) && $values['diamond']['meas_length'] ) {
			$item->add_meta_data( 'Dimensions', $values['diamond']['meas_length'] . '/' . $values['diamond']['meas_width'] );
		}

		if ( isset( $values['diamond']['cert_num'] ) && $values['diamond']['cert_num'] ) {
			$item->add_meta_data( 'Certificate', $values['diamond']['cert_num'] );
		}

		$item->add_meta_data( 'Diamond SKU: ', $values['diamond']['stock_num'] );
	}

	// *
	public function before_calculate_totals( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item ) {
			if ( $this->is_setting_product( $cart_item['data'] ) ) {
				if ( ! ( otw_woo_ring_builder()->diamonds && isset( otw_woo_ring_builder()->diamonds->current_diamond ) && otw_woo_ring_builder()->diamonds->current_diamond ) ) {
					otw_woo_ring_builder()->diamonds->get_current_diamond();
				}

				if ( otw_woo_ring_builder()->diamonds && isset( otw_woo_ring_builder()->diamonds->current_diamond ) && otw_woo_ring_builder()->diamonds->current_diamond ) {
					$diamond = otw_woo_ring_builder()->diamonds->current_diamond;

					$_product = wc_get_product( $cart_item['data']->get_id() );

					if ( $this->is_setting_product( $_product ) && ( ( (float) $cart_item['data']->get_price() ) <= (float) $_product->get_price() ) ) {
						$total_ring_price = ( (float) $diamond['total_sales_price'] ) + ( (float) $cart_item['data']->get_price() );

						$cart_item['data']->set_price( $total_ring_price );

						error_log( 'condition 3: ' . print_r( $cart_item, true ) );
					}
				} else {
					$stone_archive_page = otw_woo_ring_builder()->get_option( 'stone_archive_page' );

					$stone_single_page = otw_woo_ring_builder()->get_option( 'stone_single_page' );

					$stone_single_page_full_url = get_permalink( $stone_archive_page );

					wp_redirect( $stone_single_page_full_url );

					exit();
				}
			}
		}
	}

	// *
	public function add_order_item_meta( $item_id, $cart_item, $cart_item_key ) {
		error_log( 'add_order_item_meta' );

		if ( isset( $cart_item['diamond'] ) ) {
			wc_add_order_item_meta( $item_id, 'diamond_data', $cart_item['diamond'] );
		}
	}

	public function gettext( $translated_text, $original_text, $domain ) {
		if ( 'Checkout' === $original_text ) {
			$translated_text = 'Secure Checkout';
		}

		if ( 'Subtotal:' === $original_text ) {
			$translated_text = 'Total:';
		}

		return $translated_text;
	}

	function admin_order_data_after_order_details( $order ) {
		$delivery_order_id = wc_get_order_item_meta( $order->get_id(), 'diamond_data' );

		db( $delivery_order_id );

		exit();

		$delivery_id = ! empty( $delivery_order_id ) ? $delivery_order_id : '<span style="color:red">' . __( 'Not yet.' ) . '</span>';

		echo '<br clear="all"><p><strong>' . __( 'Delivery Order Id' ) . ':</strong> ' . $delivery_id . '</p>';
	}

	public function is_setting_product( $_product ) {
		if ( ! $_product->is_type( 'variation' ) ) {
			return false;
		}

		$product_cats_ids = wc_get_product_term_ids( $_product->get_parent_id(), 'product_cat' );

		if ( $product_cats_ids &&
			is_array( $product_cats_ids ) &&
			count( $product_cats_ids ) >= 1 &&
			in_array( $this->get_option( 'setting_category' ), $product_cats_ids )
		) {
			return true;
		}

		return false;
	}

	public function add_to_cart_redirect( $url ) {
		if ( isset( $_GET['add-to-cart'] ) && $_GET['add-to-cart'] && isset( $_GET['product_id'] ) ) {
			return wc_get_cart_url();
		}

		return $url;
	}
}

//buggy orders
//74171
//74476
//https://wordpress-1167849-4081336.cloudwaysapps.com/select-diamond/?stock_num=LV22-48
