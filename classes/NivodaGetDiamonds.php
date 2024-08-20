<?php
namespace OTW\WooRingBuilder\Classes;

// exit if file is called directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NivodaGetDiamonds extends \OTW\WooRingBuilder\Plugin{
	use \OTW\GeneralWooRingBuilder\Traits\Singleton;

	use \OTW\WooRingBuilder\Traits\NivodaLocalDB;

	// public $diamond_api_endpoint = 'http://wdc-intg-customer-staging.herokuapp.com/api/diamonds';

	public $diamond_api_endpoint = 'https://integrations.nivoda.net/api/diamonds';

	public function __construct() {
		if ( $this->get_option( 'nivoda_api_environment' ) == 'staging' ) {
			$this->diamond_api_endpoint = 'http://wdc-intg-customer-staging.herokuapp.com/api/diamonds';
		}
	}

	function convert_nivoda_to_vdb( $diamond ) {
		$output = array();

		$output['video_url'] = '';

		if ( isset( $diamond['diamond']['certificate'] ) && isset( $diamond['diamond']['certificate']['video'] ) && $diamond['diamond']['certificate']['video'] ) {
			$full_url = explode( '/video/', $diamond['diamond']['certificate']['video'] );

			$output['video_url'] = $full_url[0] . '/video/rsp/autoplay/autoplay';
		}

		$output['stock_num'] = '';

		$output['id'] = '';

		if ( isset( $diamond['id'] ) && $diamond['id'] ) {
			$stock_num = str_replace( array( 'DIAMOND/', 'nivoda-' ), array( '', '' ), $diamond['id'] );

			$output['stock_num'] = 'nivoda-' . $stock_num;

			$output['id'] = 'nivoda-' . $stock_num;
		}

		$output['image_url'] = '';

		if ( isset( $diamond['diamond']['certificate'] ) && isset( $diamond['diamond']['certificate']['image'] ) && $diamond['diamond']['certificate']['image'] ) {
			$output['image_url'] = $diamond['diamond']['certificate']['image'];
		}

		$output['size'] = '';
		if ( isset( $diamond['diamond']['certificate'] ) && isset( $diamond['diamond']['certificate']['carats'] ) && $diamond['diamond']['certificate']['carats'] ) {
			$output['size'] = $diamond['diamond']['certificate']['carats'];
		}

		$output['shape'] = '';
		if ( isset( $diamond['diamond']['certificate'] ) && isset( $diamond['diamond']['certificate']['shape'] ) && $diamond['diamond']['certificate']['shape'] ) {
			$output['shape'] = $this->get_shapes_list()[ $diamond['diamond']['certificate']['shape'] ];

			$output['shape_api'] = $diamond['diamond']['certificate']['shape'];
		}

		$output['total_sales_price'] = '';

		if ( isset( $diamond['price'] ) && isset( $diamond['price'] ) && $diamond['price'] ) {
			if ( isset( $diamond['upload'] ) && $diamond['upload'] && $diamond['upload'] == 'csv' ) {
				if ( isset( $diamond['markup_price'] ) && isset( $diamond['markup_price'] ) && $diamond['markup_price'] ) {
					$output['total_sales_price'] = (float) number_format( ( (int) $diamond['markup_price'] ), 0, '.', '' );
				} else {
					$output['total_sales_price'] = (float) number_format( ( (int) $diamond['price'] ), 0, '.', '' );
				}

				$output['base_sales_price'] = (float) number_format( ( (int) $diamond['price'] ), 0, '.', '' );
			} else {
				if ( isset( $diamond['markup_price'] ) && isset( $diamond['markup_price'] ) && $diamond['markup_price'] ) {
					$output['total_sales_price'] = (float) number_format( ( (int) $diamond['markup_price'] / 100 ), 0, '.', '' );
				} else {
					$output['total_sales_price'] = (float) number_format( ( (int) $diamond['price'] / 100 ), 0, '.', '' );
				}

				$output['base_sales_price'] = (float) number_format( ( (int) $diamond['price'] / 100 ), 0, '.', '' );
			}
		}
		$output['color'] = '';
		if ( isset( $diamond['diamond']['certificate'] ) && isset( $diamond['diamond']['certificate']['color'] ) && $diamond['diamond']['certificate']['color'] ) {
			$output['color'] = $diamond['diamond']['certificate']['color'];
		}

		$output['clarity'] = '';
		if ( isset( $diamond['diamond']['certificate'] ) && isset( $diamond['diamond']['certificate']['clarity'] ) && $diamond['diamond']['certificate']['clarity'] ) {
			$output['clarity'] = $diamond['diamond']['certificate']['clarity'];
		}

		$output['symmetry'] = '';
		if ( isset( $diamond['diamond']['certificate'] ) && isset( $diamond['diamond']['certificate']['symmetry'] ) && $diamond['diamond']['certificate']['symmetry'] ) {
			$output['symmetry'] = $diamond['diamond']['certificate']['symmetry'];
		}

		$output['meas_length'] = '';
		if ( isset( $diamond['diamond']['certificate'] ) && isset( $diamond['diamond']['certificate']['length'] ) && $diamond['diamond']['certificate']['length'] ) {
			$output['meas_length'] = $diamond['diamond']['certificate']['length'];
		}

		$output['meas_width'] = '';
		$output['meas_ratio'] = '';
		if ( isset( $diamond['diamond']['certificate'] ) && isset( $diamond['diamond']['certificate']['width'] ) && $diamond['diamond']['certificate']['width'] ) {
			$output['meas_width'] = $diamond['diamond']['certificate']['width'];
		}

		$output['lab'] = '';
		if ( isset( $diamond['diamond']['certificate'] ) && isset( $diamond['diamond']['certificate']['lab'] ) && $diamond['diamond']['certificate']['lab'] ) {
			$output['lab'] = $diamond['diamond']['certificate']['lab'];
		}

		$output['cert_url'] = '';
		if ( isset( $diamond['diamond']['certificate'] ) && isset( $diamond['diamond']['certificate']['pdfUrl'] ) && $diamond['diamond']['certificate']['pdfUrl'] ) {
			$output['cert_url'] = $diamond['diamond']['certificate']['pdfUrl'];
		}

		if ( $output['meas_length'] && $output['meas_width'] ) {
			$meas_width = (float) $output['meas_width'];
			if ( $meas_width >= 0.1 ) {
				$output['meas_ratio'] = (float) number_format( ( (float) $output['meas_length'] / (float) $output['meas_width'] ), 2, '.', '' );
			}
		}

		return $output;
	}

	public function get_auth_token() {
		$auth_token = get_option( 'nivoda_api_auth_token' );

		if ( isset( $_GET['get_new_nivoda_auth_token'] ) && $_GET['get_new_nivoda_auth_token'] == 'yes' ) {
			$auth_token = '';
		}

		if ( $auth_token ) {
			return $auth_token;
		}

		$auth_token = '';

		$body = 'query {authenticate{username_and_password(username:"' . $this->get_option( 'nivoda_api_username' ) . '",password:"' . $this->get_option( 'nivoda_api_password' ) . '"){token}}}';

		$body = array( 'query' => $body );

		$response = $this->wp_remote_post( $this->diamond_api_endpoint, $body );

		if ( ! ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) && isset( $response['body'] ) ) ) {
			return $auth_token;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return $auth_token;
		}

		$body = @json_decode( $body, true );
		if ( ! ( isset( $body['data'] ) && isset( $body['data']['authenticate'] ) && isset( $body['data']['authenticate']['username_and_password'] ) && isset( $body['data']['authenticate']['username_and_password']['token'] ) && $body['data']['authenticate']['username_and_password']['token'] ) ) {
			return $auth_token;
		}

		update_option( 'nivoda_api_auth_token', $body['data']['authenticate']['username_and_password']['token'] );
		return $body['data']['authenticate']['username_and_password']['token'];
	}

	public function get_diamonds( $args ) {
		if ( $this->nivoda_api_type == 'local' ) {
			return $this->get_local_diamonds( $args );
		}

		if ( isset( $args['page_number_nivoda'] ) && $args['page_number_nivoda'] && (int) $args['page_number_nivoda'] >= 2 ) {
			$args['page_number'] = (int) $args['page_number_nivoda'];
		} else {
			$args['page_number'] = 1;
		}

		$output_diamonds = array();

		$auth_token = $this->get_auth_token();

		$search_query = '{availability:AVAILABLE';

		$search_query .= ',has_image:true';

		if ( isset( $args['type'] ) && $args['type'] ) {
			if ( $args['type'] == 'Lab_grown_Diamond' ) {
				$search_query .= ',labgrown:true';
			} else {
				$search_query .= ',labgrown:false';
			}
		}

		if ( isset( $args['shapes[]'] ) && $args['shapes[]'] ) {
			$search_query .= ',shapes:["' . implode( '","', $this->get_shape_types( $args['shapes[]'] ) ) . '"]';
		}

		if ( isset( $args['size_from'] ) && $args['size_from'] && isset( $args['size_to'] ) && $args['size_to'] ) {
			$search_query .= ',sizes:{from:' . $args['size_from'] . ',to:' . $args['size_to'] . '}';
		}

		if ( isset( $args['price_total_from'] ) && $args['price_total_from'] && isset( $args['price_total_to'] ) && $args['price_total_to'] ) {
			$search_query .= ',dollar_value:{from:' . $args['price_total_from'] . ',to:' . $args['price_total_to'] . '}';
		}

		if ( isset( $args['color_from'] ) && $args['color_from'] && isset( $args['color_to'] ) && $args['color_to'] ) {
			$found_colors = get_all_values_between_range( $args['color_from'], $args['color_to'], $this->get_colors_list() );

			if ( $found_colors ) {
				$search_query .= ',color:[' . implode( ',', $found_colors ) . ']';
			}
		}

		if ( isset( $args['clarity_from'] ) && $args['clarity_from'] && isset( $args['clarity_to'] ) && $args['clarity_to'] ) {
			$found_colors = get_all_values_between_range( $args['clarity_from'], $args['clarity_to'], $this->get_clarity_list() );

			if ( $found_colors ) {
				$search_query .= ',clarity:[' . implode( ',', $found_colors ) . ']';
			}
		}

		$search_query .= '}';

		$offset = '';

		if ( isset( $args['page_number'] ) && (int) $args['page_number'] >= 2 && isset( $args['page_size'] ) ) {
			$offset .= ',offset:' . ( ( (int) $args['page_number'] - 1 ) * ( (int) $args['page_size'] ) );
		}

		if ( isset( $args['page_size'] ) && $args['page_size'] ) {
			$offset .= ',limit:' . $args['page_size'];
		}

		$query = 'query{
      diamonds_by_query(order:{type:price,direction:ASC},query:' . $search_query . $offset . '){total_count,items{id,price,markup_price,diamond{video,image,certificate{id,certNumber,carats,cut,clarity,polish,symmetry,color,shape,image,video,lab,pdfUrl,length,width,depth}}}},
      diamonds_by_query_count(query:' . $search_query . ')
    }';

		if ( isset( $args['diamonds_by_query_count'] ) && $args['diamonds_by_query_count'] == 'no' ) {
			$query = 'query{
        diamonds_by_query(order:{type:price,direction:ASC},query:' . $search_query . $offset . '){total_count,items{id,price,markup_price,diamond{video,image,certificate{id,certNumber,carats,cut,clarity,polish,symmetry,color,shape,image,video,lab,pdfUrl,length,width,depth}}}}
      }';
		}

		$body = array( 'query' => $query );

		$headers = array(
			'Authorization' => 'Bearer ' . $auth_token,
		);

		$response = $this->wp_remote_post( $this->diamond_api_endpoint, $body, $headers );

		if ( ! ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) && isset( $response['body'] ) ) ) {
			$error_message = 'Sorry, we could not connect with diamonds API';

			return $error_message;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			$error_message = 'Sorry, we don\'t have any diamonds for your search.';

			return $error_message;
		}

		$body = @json_decode( $body, true );

		if ( ! ( isset( $body['data'] ) && isset( $body['data']['diamonds_by_query'] ) && isset( $body['data']['diamonds_by_query']['items'] ) && is_array( $body['data']['diamonds_by_query']['items'] ) && count( $body['data']['diamonds_by_query']['items'] ) >= 1 /*&& isset($body['data']['diamonds_by_query_count']) && $body['data']['diamonds_by_query_count'] >= 1 && isset($body['data']['authenticate']['username_and_password']['token']) && $body['data']['authenticate']['username_and_password']['token']*/ ) ) {
			$error_message = 'Sorry, we don\'t have any diamonds for your search.';

			return $error_message;
		}

		return $body['data'];
	}

	public function get_diamond_by_stock_num( $stock_num ) {
		if ( $this->nivoda_api_type == 'local' ) {
			return $this->get_local_diamond_by_stock_num( $stock_num );
		}

		$endpoint = $this->diamond_api_endpoint;

		$query = 'query{
      get_diamond_by_id(diamond_id:"' . str_replace( array( 'DIAMOND/', 'nivoda-' ), array( '', '' ), $stock_num ) . '"){id,price,markup_price,diamond{video,image,certificate{id,certNumber,carats,cut,clarity,polish,symmetry,color,shape,image,video,lab,pdfUrl,length,width,depth}}}
    }';

		$body = array( 'query' => $query );

		$headers = array(
			'Authorization' => 'Bearer ' . $this->get_auth_token(),
		);

		$response = $this->wp_remote_post( $this->diamond_api_endpoint, $body, $headers );

		if ( ! ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) && isset( $response['body'] ) ) ) {
			$error_message = 'Sorry, we could not connect with diamonds API';

			return $error_message;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			$error_message = 'Sorry, we don\'t have any diamonds for your search.';

			return $error_message;
		}

		$body = @json_decode( $body, true );

		if ( ! ( isset( $body['data'] ) && isset( $body['data']['get_diamond_by_id'] ) && is_array( $body['data']['get_diamond_by_id'] ) && count( $body['data']['get_diamond_by_id'] ) >= 1 ) ) {
			$error_message = 'Sorry, we don\'t have any diamonds for your search.';

			return $error_message;
		}

		$diamond = $this->convert_nivoda_to_vdb( $body['data']['get_diamond_by_id'] );

		return $diamond;
	}

	public function get_shape_types( $shape ) {
		$shape = strtoupper( $shape );
		$output = array();
		foreach ( $this->get_shapes_list() as $key => $single_shape ) {
			if ( $shape == $single_shape ) {
				$output[] = $key;
			}
		}
		return $output;
	}

	public function get_colors_list() {
		return array(
			'D',
			'E',
			'F',
			'G',
			'H',
			'I',
			'J',
			'K',
			'L',
			'M',
			'N',
			'NO',
			'O',
			'OP',
			'PR',
			'P',
			'Q',
			'QR',
			'R',
			'S',
			'SZ',
			'ST',
			'T',
			'U',
			'UV',
			'V',
			'W',
			'WX',
			'X',
			'Y',
			'YZ',
			'Z',
			'FANCY',
		);
	}

	public function get_clarity_list() {
		return array(
			'FL',
			'IF',
			'VVS1',
			'VVS2',
			'VS1',
			'VS2',
			'SI1',
			'SI2',
			'SI3',
			'I1',
			'I2',
			'I3',
		);
	}

	public function get_shapes_list() {
		return array(
			'ROUND'                    => 'ROUND',
			'OCTAGONAL'                => 'OCTAGONAL',
			'ASSCHER'                  => 'ASSCHER',
			'ROUND MODIFIED BRILLIANT' => 'ROUND',
			'OTHER'                    => 'OTHER',
			'EMERALD'                  => 'EMERALD',
			'PENTAGONAL'               => 'PENTAGONAL',
			'RECTANGULAR'              => 'RECTANGULAR',
			'BRIOLETTE'                => 'BRIOLETTE',
			'PEAR MODIFIED BRILLIANT'  => 'PEAR',
			'OLD EUROPEAN'             => 'OLD EUROPEAN',
			'SQUARE'                   => 'SQUARE',
			'PEAR'                     => 'PEAR',
			'CUSHION B'                => 'CUSHION',
			'KITE'                     => 'KITE',
			'EUROPEAN'                 => 'EUROPEAN',
			'HEXAGONAL'                => 'HEXAGONAL',
			'BULLET'                   => 'BULLET',
			'RECTANGLE'                => 'RECTANGLE',
			'TRAPEZOID'                => 'TRAPEZOID',
			'HALFMOON'                 => 'HALFMOON',
			'SHIELD'                   => 'SHIELD',
			'OVAL MIXED CUT'           => 'OVAL',
			'OVAL'                     => 'OVAL',
			'TRAPEZE'                  => 'TRAPEZE',
			'BAGUETTE'                 => 'BAGUETTE',
			'CUSHION MODIFIED'         => 'CUSHION',
			'CUSHION BRILLIANT'        => 'CUSHION',
			'RADIANT'                  => 'RADIANT',
			'FAN'                      => 'FAN',
			'TETRAGONAL'               => 'TETRAGONAL',
			'TAPERED BAGUETTE'         => 'TAPERED BAGUETTE',
			'CUSHION'                  => 'CUSHION',
			'SQUARE EMERALD'           => 'EMERALD',
			'HEART'                    => 'HEART',
			'ASCHER'                   => 'ASCHER',
			'HALF MOON'                => 'HALF MOON',
			'PRAD'                     => 'PRAD',
			'LOZENGE'                  => 'LOZENGE',
			'PRINCESS'                 => 'PRINCESS',
			'HEPTAGONAL'               => 'HEPTAGONAL',
			'TRILLIANT'                => 'TRILLIANT',
			'ROSE'                     => 'ROSE',
			'SQUARE RADIANT'           => 'SQUARE',
			'FLANDERS'                 => 'FLANDERS',
			'OLD MINER'                => 'OLD MINER',
			'MARQUISE'                 => 'MARQUISE',
			'NONAGONAL'                => 'NONAGONAL',
			'EUROPEAN CUT'             => 'EUROPEAN CUT',
			'TRIANGULAR'               => 'TRIANGULAR',
		);
	}
}
