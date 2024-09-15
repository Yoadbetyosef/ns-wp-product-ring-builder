<?php
namespace OTW\WooRingBuilder\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VariationsMetaData extends \OTW\WooRingBuilder\Plugin{
	use \OTW\GeneralWooRingBuilder\Traits\Singleton;
	use \OTW\GeneralWooRingBuilder\Traits\AdminNotices;

	public function __construct() {
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'gallery_admin_html' ), 10, 3 );

		add_action( 'woocommerce_save_product_variation', array( $this, 'save_product_variation' ), 10, 2 );
	}

	public function gallery_admin_html( $loop, $variation_data, $variation ) {
		$variation_id   = absint( $variation->ID );

		$gallery_images = get_post_meta( $variation_id, 'otw_woo_variation_gallery_images', true );

		$meta_box_fields = $this->get_variation_fields();

		if ( is_array( $meta_box_fields ) && count( $meta_box_fields ) >= 1 ) {

			wp_nonce_field( $this->prefix( 'nonce' ), $this->prefix( 'nonce' ) );

			$fields = \OTW\GeneralWooRingBuilder\Fields::instance();

			$fields->displaytype = array(
				'wrapper_open'         => '<div class="form-wrap">',
				'wrapper_close'        => '</div>',
				'container_open'       => 'div',
				'container_close'      => 'div',
				'container_attributes' => array(
					'class' => 'bbwp_fields_container form-row form-row-full form-field',
				),
				'label_open'           => '',
				'label_close'          => '',
				'input_open'           => '',
				'input_close'          => '',
			);

			echo $fields->displaytype['wrapper_open'];

			foreach ( $meta_box_fields as $field ) {
				$orig_meta_key = $field['meta_key'];
				$field['meta_key'] = $orig_meta_key . '[' . $variation_id . ']';
				echo $fields->{$field['field_type']}( $field, get_post_meta( $variation_id, $orig_meta_key, true ) );
			}

			echo $fields->displaytype['wrapper_close'];
		}
	}

	public function save_product_variation( $variation_id, $loop ) {
		if ( isset( $_POST[ $this->prefix( 'nonce' ) ] ) && wp_verify_nonce( $_POST[ $this->prefix( 'nonce' ) ], $this->prefix( 'nonce' ) ) ) {
			$meta_box_fields = $this->get_variation_fields();

			$sanitize = \OTW\GeneralWooRingBuilder\Sanitization::instance();

			foreach ( $meta_box_fields as $key => $value ) {
				$dbvalue = '';

				if ( isset( $_POST[ $value['meta_key'] ][ $variation_id ] ) ) {
					if ( is_array( $_POST[ $value['meta_key'] ][ $variation_id ] ) && count( $_POST[ $value['meta_key'] ][ $variation_id ] ) >= 1 ) {
						$dbvalue = array();

						foreach ( $_POST[ $value['meta_key'] ][ $variation_id ] as $selected_value ) {
							$selected_value = $sanitize->Textfield( $selected_value );

							if ( $selected_value ) {
								$dbvalue[] = $selected_value;
							}
						}
					} else {
						$dbvalue = $sanitize->Textfield( $_POST[ $value['meta_key'] ][ $variation_id ] );
					}
				}

				update_post_meta( $variation_id, $value['meta_key'], $dbvalue );
			}
		}
	}

	public function get_variation_fields() {
		return array(
			'otw_woo_variation_3d_model'       => array(
				'meta_key'    => 'otw_woo_variation_3d_model',
				'field_title' => '3d Model',
				'field_type'  => 'file',
			),
			'otw_woo_variation_gallery_images' => array(
				'meta_key'        => 'otw_woo_variation_gallery_images',
				'field_type'      => 'image',
				'field_title'     => 'Gallery Images',
				'field_duplicate' => 'on',
			),
			'otw_woo_variation_video_url'      => array(
				'meta_key'    => 'otw_woo_variation_video_url',
				'field_type'  => 'text',
				'field_title' => 'Video URL',
			),
		);
	}
}
