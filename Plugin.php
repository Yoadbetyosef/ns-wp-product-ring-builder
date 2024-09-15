<?php
namespace OTW\WooRingBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin{
	use \OTW\GeneralWooRingBuilder\Traits\Plugin;
	use \OTW\GeneralWooRingBuilder\Traits\ApiMethods;

	public $prefix = 'otw_woo_ring_builder';
	static $options = array();
	public $diamonds = null;
	// public $vdb_diamonds = null;
	public $nivoda_diamonds = null;
	// public $pld_diamonds = null;
	public $woo = null;

	public function __construct() {
		self::$options = SerializeStringToArray( get_option( $this->prefix( 'options' ) ) );

		PluginDefault::instance();

		$this->diamonds = \OTW\WooRingBuilder\Classes\Diamonds::instance();
		// $this->vdb_diamonds = \OTW\WooRingBuilder\Classes\GetDiamonds::instance();
		$this->nivoda_diamonds = \OTW\WooRingBuilder\Classes\NivodaGetDiamonds::instance();
		// $this->pld_diamonds = \OTW\WooRingBuilder\Classes\PldGetDiamonds::instance();
		$this->woo = \OTW\WooRingBuilder\Classes\Woo::instance();
	}

	public function setcookie( $name, $value = '', $time = '', $path = COOKIEPATH, $cookie_domain = '', $secure = true, $http_only = true ) {
		$samesite = 'Strict';

		if ( empty( $time ) ) {
			$time = ( ( (int) wp_date( 'U' ) ) + 60 * 60 * 24 * 30 * 12 );
		}

		$cookie_domain = $_SERVER['HTTP_HOST'];

		if ( defined( COOKIE_DOMAIN ) ) {
			$cookie_domain = COOKIE_DOMAIN;
		}

		if ( function_exists( 'WC' ) && isset( WC()->session ) && is_object( WC()->session ) ) {
			WC()->session->set( 'gcpb_' . $name, $value );
		}

		setcookie(
			$name,
			$value,
			array(
				'expires'  => $time,
				'path'     => $path,
				'domain'   => $cookie_domain,
				'secure'   => $secure,
				'httponly' => $http_only,
				'samesite' => $samesite,
			)
		);
	}

	public function delete_diamond_cookies() {
		$time_past = ( ( (int) wp_date( 'U' ) ) - 60 * 60 * 24 * 30 * 12 );
		unset( $_GET['stock_num'] );
		unset( $_COOKIE['stock_num'] );
		$this->setcookie( 'stock_num', '', $time_past );
	}

	public function delete_setting_cookies() {
		$time_past = ( ( (int) wp_date( 'U' ) ) - 60 * 60 * 24 * 30 * 12 );
		unset( $_GET['product_id'] );
		unset( $_COOKIE['product_id'] );
		unset( $_COOKIE['variation_id'] );
		unset( $_GET['variation_id'] );

		add_action(
			'init',
			function () {
				if ( function_exists( 'WC' ) ) {
					$items = WC()->cart->get_cart();
					foreach ( $items as $item => $values ) {
						if ( isset( $values['data'] ) && $values['data'] && method_exists( $values['data'], 'get_id' ) ) {
							$_product = wc_get_product( $values['data']->get_id() );
							if ( $this->is_setting_product( $_product ) ) {
									WC()->cart->remove_cart_item( $item );
							}
						}
					}
				}
			}
		);

		$this->setcookie( 'product_id', '', $time_past );

		$this->setcookie( 'variation_id', '', $time_past );
	}

	public function get_current_selected_variation_shape() {
		if ( ! ( isset( $_GET['variation_id'] ) && $_GET['variation_id'] ) ) {
			return '';
		}

		$variation_id = $_GET['variation_id'];

		if ( ! ( isset( $this->woo ) && $this->woo ) ) {
			$this->woo = \OTW\WooRingBuilder\Classes\Woo::instance();
		}

		if ( ! ( isset( $this->woo ) && $this->woo && isset( $this->woo->current_selected_variation ) && $this->woo->current_selected_variation ) ) {
			$variable_product = new \WC_Product_Variation( $variation_id );
			if ( ! $variable_product ) {
				return '';
			}

			$this->woo->current_selected_variation = $variable_product;
		}

		$variation_variations = $this->woo->current_selected_variation->get_variation_attributes();

		if ( $variation_variations && is_array( $variation_variations ) && count( $variation_variations ) >= 1 && isset( $variation_variations['attribute_pa_shape'] ) && $variation_variations['attribute_pa_shape'] && is_string( $variation_variations['attribute_pa_shape'] ) ) {
			return $variation_variations['attribute_pa_shape'];
		}

		return '';
	}

	public function get_current_selected_variation_shapes() {
		$current_shape = $this->get_current_selected_variation_shape();
		if ( $current_shape ) {
			$parent_product = wc_get_product( $this->woo->current_selected_variation->get_parent_id() );
			$all_shapes = $parent_product->get_attribute( 'pa_shape' );
			if ( $all_shapes ) {
				$all_shapes_array = explode( ',', strtolower( str_replace( ' ', '', $all_shapes ) ) );
				if ( $all_shapes_array && is_array( $all_shapes_array ) && count( $all_shapes_array ) >= 1 ) {
					return $all_shapes_array;
				}
			}
		}
		return array();
	}

	public function is_setting_product( $_product ) {
		if ( ! $_product->is_type( 'variation' ) ) {
			return false;
		}

		$product_cats_ids = wc_get_product_term_ids( $_product->get_parent_id(), 'product_cat' );

		if ( $product_cats_ids && is_array( $product_cats_ids ) && count( $product_cats_ids ) >= 1 && in_array( $this->get_option( 'setting_category' ), $product_cats_ids ) ) {
			return true;
		}
		return false;
	}
}
