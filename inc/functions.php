<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'alert' ) ) {
	function alert( $alertText ) {
		echo '<script type="text/javascript">';
		echo "alert(\"$alertText\");";
		echo '</script>';
	}
}

if ( ! function_exists( 'js_log' ) ) {
	function js_log( $alertText ) {
		echo '<script type="text/javascript">';
		echo "console.log(\"$alertText\")";
		echo '</script>';
	}

}

if ( ! function_exists( 'db' ) ) {
	function db( $array1 ) {
		echo '<pre>';
		var_dump( $array1 );
		echo '</pre>';
	}
}

if ( ! function_exists( 'dbt' ) ) {
	function dbt( $array1, $ip = '', $exit = true ) {
		if ( in_array( $_SERVER['REMOTE_ADDR'], array( '127.0.0.1', '::1', $ip ) ) ) {
			echo '<pre>';
			var_dump( $array1 );
			echo '</pre>';
			if ( $exit ) {
				exit();
			}
		}
	}
}

if ( ! function_exists( 'generate_key' ) ) {
	function generate_key( $length = 40 ) {
			$keyset = 'abcdefghijklmnopqrstuvqxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			$key    = '';

		for ( $i = 0; $i < $length; $i++ ) {
			$key .= substr( $keyset, wp_rand( 0, strlen( $keyset ) - 1 ), 1 );
		}

		return $key;
	}
}

if ( ! function_exists( 'dbh' ) ) {
	function dbh( $debug_data ) {
		echo '<div style="display:none">';
		db( $debug_data );
		echo '</div>';
	}
}

if ( ! function_exists( 'get_file_time' ) ) {
	function get_file_time( $file ) {
		return date( 'ymd-Gis', filemtime( $file ) );
	}
}

if ( ! function_exists( 'get_client_ip' ) ) {
	function get_client_ip( $default = '' ) {
		$ipaddress = '';
		//HTTP_CF_IPCOUNTRY
		if ( getenv( 'HTTP_CF_CONNECTING_IP' ) ) {
			$ipaddress = getenv( 'HTTP_CF_CONNECTING_IP' );
		} elseif ( getenv( 'HTTP_CLIENT_IP' ) ) {
			$ipaddress = getenv( 'HTTP_CLIENT_IP' );
		} elseif ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' );
		} elseif ( getenv( 'HTTP_X_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED' );
		} elseif ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
		} elseif ( getenv( 'HTTP_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED' );
		} elseif ( getenv( 'REMOTE_ADDR' ) ) {
			$ipaddress = getenv( 'REMOTE_ADDR' );
		} else {
			$ipaddress = 'UNKNOWN';
		}
		if ( ! empty( $default ) && $default == $ipaddress ) {
			return true;
		}
		return $ipaddress;
	}
}

if ( ! function_exists( 'ArrayToSerializeString' ) ) {
	function ArrayToSerializeString( $array ) {
		if ( isset( $array ) && is_array( $array ) && count( $array ) >= 1 ) {
			return serialize( $array );
		} else {
			return serialize( array() );
		}
	}
}

if ( ! function_exists( 'SerializeStringToArray' ) ) {
	function SerializeStringToArray( $string ) {
		if ( isset( $string ) && is_array( $string ) && count( $string ) >= 1 ) {
			return $string;
		} elseif ( isset( $string ) && $string && @unserialize( $string ) ) {
			return unserialize( $string );
		} else {
			return array();
		}
	}
}

if ( ! function_exists( 'JsonStringToArray' ) ) {
	function JsonStringToArray( $string ) {
		$output = array();
		if ( isset( $string ) && is_array( $string ) && count( $string ) >= 1 ) {
			$output = $string;
		} elseif ( isset( $string ) && $string ) {
			$string = json_decode( $string, true );
			if ( $string && is_array( $string ) && count( $string ) >= 1 ) {
				$output = $string;
			}
		}
		return $output;
	}
}

if ( ! function_exists( 'ArrayToJsonString' ) ) {
	function ArrayToJsonString( $array ) {
		if ( isset( $array ) && is_array( $array ) && count( $array ) >= 1 ) {
			return json_encode( $array );
		} else {
			return json_encode( array() );
		}
	}
}

if ( ! function_exists( 'gcpb_stone_archive_page' ) ) {
	function gcpb_stone_archive_page() {
		return get_permalink( otw_woo_ring_builder()->get_option( 'stone_archive_page' ) );
	}
}

if ( ! function_exists( 'gcpb_stone_single_page' ) ) {
	function gcpb_stone_single_page() {
		return get_permalink( otw_woo_ring_builder()->get_option( 'stone_single_page' ) );
	}
}

if ( ! function_exists( 'gcpb_checkout_complete_page' ) ) {
	function gcpb_checkout_complete_page() {
		return get_permalink( otw_woo_ring_builder()->get_option( 'checkout_complete_page' ) );
	}
}

if ( ! function_exists( 'gcpb_product_page' ) ) {
	function gcpb_product_page() {
		return get_permalink( otw_woo_ring_builder()->get_option( 'gcpb_product_page' ) );
	}
}

if ( ! function_exists( 'gcpb_listing_page' ) ) {
	function gcpb_listing_page() {
		$gcpb_product_archive = get_option( 'gcpb_listing_page' );
		return get_permalink( otw_woo_ring_builder()->get_option( 'gcpb_listing_page' ) );
	}
}

if ( ! function_exists( 'gcpb_diamond_shapes_array' ) ) {
	function gcpb_diamond_shapes_array() {
		return array(
			'asscher'  => 'Asscher',
			'cushion'  => 'Cushion',
			'emerald'  => 'Emerald',
			'heart'    => 'Heart',
			'marquise' => 'Marquise',
			'oval'     => 'Oval',
			'pear'     => 'Pear',
			'princess' => 'Princess',
			'radiant'  => 'Radiant',
			'round'    => 'Round',
		);
	}
}

if ( ! function_exists( 'otw_get_single_variation_gallery_content' ) ) {
	function otw_get_single_variation_gallery_content( $variable_product ) {
		$output = '';
		$variation_id = $variable_product->get_id();
		$variation_3d_model     = get_post_meta( $variation_id, 'otw_woo_variation_3d_model', true );
		$variation_gallery_images   = get_post_meta( $variation_id, 'otw_woo_variation_gallery_images', true );
		$variation_video_url = get_post_meta( $variation_id, 'otw_woo_variation_video_url', true );

		$image_id = $variable_product->get_image_id();
		$hide_image = '';

		$output .= '<div class="gcpb-product-image" data-variation-id="' . $variation_id . '" data-3d-model="' . $variation_3d_model . '">';
		$output .= '<picture>';
		if ( ! empty( $variation_3d_model ) ) {
			//if(!wp_is_mobile())
			//$output .= '<webgi-viewer src="'.$variation_3d_model.'" style="width: 100%; height: 500px; z-index: 1; display: block; position:relative;" disposeonremove="true"></webgi-viewer>';
			$hide_image = 'style="display:none;"';
		}
		$output .= '</picture>';
		// $output .= '<img class="gcpb-mobile-gallery-icon gcpb-gallery-toggle gcpb-mobile-only" src="/wp-content/themes/hello-theme-child-master/assets/images/gallery-icon.svg" alt="">';
		$output .= '</div><!-- gcpb-product-image -->';
		$output .= '<div class="tryon-btns">';
		$output .= '<button class="tryon-btn tryiton-btn gcpb-button" id="Ar_Btn_A">Try <span>it</span> on</button>';
		$output .= '<button class="tryon-btn diamond-size-btn gcpb-button" id="Ar_Btn_D"><span>View</span> Diamond Size</button>';
		$output .= '</div><!-- tryon-btns -->';
		$output .= '<div class="gcpb-thumbnails">';
		$output .= '<div class="gcpb-thumbnails__top">';

		$output .= '<img class="gcpb-close-gallery gcpb-gallery-toggle gcpb-mobile-only" src="/wp-content/themes/hello-theme-child-master/assets/images/close-icon.png" alt="">';

		$output .= '</div><!-- gcpb-thumbnails__top -->';

		// $output .= '<!-- <div class="gcpb-product-thumbnails gcpb-scrollbar gcpb-horizontal"> -->';

		if ( ! wp_is_mobile() ) {
			$output .= '<ul class="gcpb-media-tabs">
          <li class="gcpb-media-tab tab-3d">360°</li>
          <li class="gcpb-media-tab tab-gallery active">Images</li>
          <li class="gcpb-media-tab tab-video">Video</li>
      </ul>';
		}

		$output .= '<div class="gcpb-thumbnails__content" style="display:none;">';

		if ( ! empty( $variation_gallery_images ) ) {
			// $output .= '<!-- <div class="gcpb-product-gallery-thumbs"> -->';

			$output .= '<img src="' . wp_get_attachment_image_url( $image_id, 'full', false ) . '" alt="" class="gcpb-product-gallery-thumb">';
			$gallery_counter = 1;
			foreach ( $variation_gallery_images as $attachment_url ) {
				if ( $gallery_counter == 2 && ! empty( $variation_video_url ) ) {
					$output .= '<video data-video_url="' . $variation_video_url . '" autoplay muted loop playsinline></video>';
				}
				$output .= '<img src="' . $attachment_url . '" alt="" class="gcpb-product-gallery-thumb" data-thumb="' . $attachment_url . '">';
				++$gallery_counter;
			}

			$default_image = get_option( 'gcpb_default_product_image' );

			if ( ! empty( $default_image ) ) {
				$default_image_url = wp_get_attachment_image_url( $default_image, 'full', false );
				$output .= '<img src="' . $default_image_url . '" alt="" class="gcpb-product-gallery-thumb" data-thumb="' . $default_image_url . '">';
			}
		} elseif ( ! empty( $variation_video_url ) ) {
					$output .= '<video data-video_url="' . $variation_video_url . '" autoplay muted loop playsinline></video>';
		}
		$output .= '</div><!-- gcpb-thumbnails__content -->';
		$output .= '</div><!-- gcpb-thumbnails -->';

		$output .= '<div class="gcpb-prod-card__loading__bar"></div>
    
    ';

		return $output;
	}
}

if ( ! function_exists( 'gcpb_get_current_first_step' ) ) {
	function gcpb_get_current_first_step() {
		$first_step = 'setting';
		if ( isset( $_COOKIE['first_step'] ) && $_COOKIE['first_step'] == 'stone' ) {
			$first_step = 'stone';
		}

		return $first_step;
	}
}

if ( ! function_exists( 'gcpb_add_cookies_query_args' ) ) {
	function gcpb_add_cookies_query_args( $url ) {
		$query_args_array = array();

		if ( isset( $_GET['product_id'] ) && $_GET['product_id'] ) {
			$query_args_array['product_id'] = $_GET['product_id'];
		}
		if ( isset( $_GET['variation_id'] ) && $_GET['variation_id'] ) {
			$query_args_array['variation_id'] = $_GET['variation_id'];
		}
		if ( isset( $_GET['stock_num'] ) && $_GET['stock_num'] ) {
			$query_args_array['stock_num'] = $_GET['stock_num'];
		}
		if ( $query_args_array ) {
			$url = add_query_arg( $query_args_array, $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'ArraytoSelectList' ) ) {
	function ArraytoSelectList( $array, $sValue = '' ) {
		$output = '';
		foreach ( $array as $key => $value ) {
			if ( $key == $sValue ) {
				$output .= '<option value="' . esc_attr( $key ) . '" selected="selected">' . esc_html( $value ) . '</option>';
			} else {
				$output .= '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
			}
		}
		return $output;
	}
}

if ( ! function_exists( 'get_diamond_price_with_markup' ) ) {
	function get_diamond_price_with_markup( $price ) {

		$db_rate = (int) otw_woo_ring_builder()->get_option( 'vdb_price_percentage' );

		if ( $db_rate ) {
			$db_rate = ( $price * $db_rate ) / 100;
			$price += $db_rate;
		}
		return $price;
	}
}

if ( ! function_exists( 'get_diamond_price_with_markup_only' ) ) {
	function get_diamond_price_with_markup_only( $price ) {
		$price = (int) $price;
		$db_rate = (int) otw_woo_ring_builder()->get_option( 'vdb_price_percentage' );
		if ( $db_rate ) {
			$price = ( $price * $db_rate ) / 100;
		}
		return $price;
	}
}

if ( ! function_exists( 'get_all_values_between_range' ) ) {
	function get_all_values_between_range( $from, $to, $array ) {
		$output = array();

		if ( in_array( $from, $array ) && in_array( $to, $array ) ) {
			$from_found = false;
			$to_found = false;
			foreach ( $array as $value ) {
				if ( $from == $value ) {
					$from_found = true;
				}
				if ( $to == $value ) {
					$to_found = true;
				}
				if ( $from_found ) {
					$output[] = $value;
				}
				if ( $to_found ) {
					// $output[] = $value;
					break;
				}
			}
		}
		return $output;
		// return implode(',', $output);
	}
}

// add_filter(
//  'gettext',
//  function ( $translated_text, $original_text, $domain ) {
//      if ( 'Checkout' === $original_text ) {
//          $translated_text = 'Secure Checkout';
//      }
//      return $translated_text;
//  },
//  10,
//  3
// );

function otw_woo_ring_builder() {
	return \OTW\WooRingBuilder\Plugin::instance();
}
