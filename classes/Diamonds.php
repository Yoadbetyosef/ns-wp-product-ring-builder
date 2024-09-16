<?php
namespace OTW\WooRingBuilder\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diamonds extends \OTW\WooRingBuilder\Plugin {
	use \OTW\GeneralWooRingBuilder\Traits\Singleton;

	public $page_size = 20;

	public $diamonds_api_clarity = array(
		'0' => 'FL',
		'1' => 'IF',
		'2' => 'VVS1',
		'3' => 'VVS2',
		'4' => 'VS1',
		'5' => 'VS2',
		'6' => 'SI1',
		'7' => 'SI2',
	);

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_action( 'wp_ajax_nopriv_fetch_stones', array( $this, 'fetch_stones' ) );

		add_action( 'wp_ajax_fetch_stones', array( $this, 'fetch_stones' ) );

		add_action( 'wp_ajax_nopriv_fetch_stone_by_id', array( $this, 'fetch_stone_by_id' ) );

		add_action( 'wp_ajax_fetch_stone_by_id', array( $this, 'fetch_stone_by_id' ) );

		add_action( 'wp_ajax_nopriv_fetch_stones_min_max', array( $this, 'fetch_stones_min_max' ) );

		add_action( 'wp_ajax_fetch_stones_min_max', array( $this, 'fetch_stones_min_max' ) );
	}

	public function fetch_stones() {
		if ( isset( $_POST['query_string'] ) && ! empty( $_POST['query_string'] ) ) {
			parse_str( $_POST['query_string'], $params );

			if ( isset( $params['paged'] ) && ! empty( $params['paged'] ) ) {
				$args['paged'] = $params['paged'];
			}

			if ( isset( $params['limit'] ) && ! empty( $params['limit'] ) ) {
				$args['posts_per_page'] = $params['limit'];
			}

			$diamonds_api_shapes = gcpb_diamond_shapes_array();

			$diamonds_api_color = array(
				'0' => 'D',
				'1' => 'E',
				'2' => 'F',
				'3' => 'G',
				'4' => 'H',
				'5' => 'I',
				'6' => 'J',
				'7' => 'K',
				'8' => 'L',
			);

			$diamonds_api_cut = array(
				'0' => 'Fair',
				'1' => 'Good',
				'2' => 'Very Good',
			);

			$diamonds_api_polish = array(
				'0' => 'Fair',
				'1' => 'Good',
				'2' => 'Very Good',
			);

			$diamonds_api_symmetry = array(
				'0' => 'Fair',
				'1' => 'Good',
				'2' => 'Very Good',
			);

			$args = array(
				'type'               => 'Lab_grown_Diamond',
				'markup_mode'        => 'true',
				'page_size'          => $this->page_size,
				'page_number_nivoda' => 1,
				'page_number_vdb'    => 1,
				'page_number'        => 1,
				'exchange_rate'      => '1',
				'show_unavailable'   => 'true',
				'with_images'        => true,
				'sortBy'             => 'price',
				'sortOrder'          => 'ASC',
			);

			if ( isset( $params['sortBy'] ) && isset( $params['sortOrder'] ) ) {
				$args['sortBy'] = $params['sortBy'];

				$args['sortOrder'] = $params['sortOrder'];
			}

			if ( isset( $params['page_number_vdb'] ) && $params['page_number_vdb'] && (int) $params['page_number_vdb'] >= 2 ) {
				$args['page_number_vdb'] = (int) $params['page_number_vdb'];
			}

			if ( isset( $params['page_number_nivoda'] ) && $params['page_number_nivoda'] && (int) $params['page_number_nivoda'] >= 2 ) {
				$args['page_number_nivoda'] = (int) $params['page_number_nivoda'];
			}

			if ( isset( $params['page_number'] ) && $params['page_number'] ) {
				if ( (int) $params['page_number'] >= 2 ) {
					$args['page_number'] = (int) $params['page_number'];
				} else {
					$args['page_number_vdb'] = 1;
					$args['page_number_nivoda'] = 1;
				}
			}

			if ( isset( $params['type'] ) ) {
				$args['type'] = $params['type'];
			}

			if ( isset( $params['shape'] ) && $params['shape'] && isset( $diamonds_api_shapes[ $params['shape'] ] ) ) {
				error_log( 'shape' . $diamonds_api_shapes[ $params['shape'] ] );

				$args['shape'] = $diamonds_api_shapes[ $params['shape'] ];
			}

			if ( isset( $params['color'] ) && $params['color'] ) {
				$colors = explode( '-', $params['color'] );

				if ( is_array( $colors ) &&
					isset( $colors[0] ) &&
					isset( $colors[1] ) &&
					isset( $diamonds_api_color[ $colors[0] ] ) &&
					isset( $diamonds_api_color[ $colors[1] ] )
				) {
					$args['color_from'] = $diamonds_api_color[ $colors[0] ];
					$args['color_to'] = $diamonds_api_color[ $colors[1] ];
				}

				if ( is_array( $colors ) && isset( $colors[0] ) ) {
					$args['color_from'] = $colors[0];
				}

				if ( is_array( $colors ) && isset( $colors[1] ) ) {
					$args['color_to'] = $colors[1];
				}
			}

			if ( isset( $params['clarity'] ) && $params['clarity'] ) {
				$clarity = explode( '-', $params['clarity'] );

				if ( isset( $clarity[0] ) && isset( $clarity[1] ) ) {
					if ( $clarity[0] === 'VVS' ) {
						$args['clarity_from'] = 'VVS1';
					} elseif ( $clarity[0] === 'VS' ) {
						$args['clarity_from'] = 'VS1';
					} elseif ( $clarity[0] === 'SI' ) {
						$args['clarity_from'] = 'SI1';
					} elseif ( $clarity[0] === 'FL' ) {
						$args['clarity_from'] = 'FL';
					}

					if ( $clarity[1] === 'VVS' ) {
						$args['clarity_to'] = 'VVS2';
					} elseif ( $clarity[1] === 'VS' ) {
						$args['clarity_to'] = 'VS2';
					} elseif ( $clarity[1] === 'SI' ) {
						$args['clarity_to'] = 'SI2';
					} elseif ( $clarity[1] === 'FL' ) {
						$args['clarity_to'] = 'IF';
					}
				}
			}

			if ( isset( $params['price'] ) && $params['price'] ) {
				$prices = explode( '-', $params['price'] );

				if ( is_array( $prices ) && isset( $prices[0] ) ) {
					$args['price_total_from'] = $prices[0];
				}

				if ( is_array( $prices ) && isset( $prices[1] ) ) {

					$args['price_total_to'] = $prices[1];
				}
			}

			if ( isset( $params['carat'] ) && $params['carat'] ) {
				$carats = explode( '-', $params['carat'] );

				if ( is_array( $carats ) && isset( $carats[0] ) ) {
					$args['size_from'] = $carats[0];
				}

				if ( is_array( $carats ) && isset( $carats[1] ) ) {
					$args['size_to'] = $carats[1];
				}
			}

			if ( isset( $params['cut'] ) && $params['cut'] && isset( $diamonds_api_cut[ $params['cut'] ] ) ) {
				$args['cut_from'] = $diamonds_api_cut[ $params['cut'] ];
				$args['cut_to'] = 'Excellent';
			}

			if ( isset( $params['polish'] ) && $params['polish'] && isset( $diamonds_api_polish[ $params['polish'] ] ) ) {
				$args['polish_from'] = $diamonds_api_polish[ $params['polish'] ];
				$args['polish_to'] = 'Excellent';
			}

			if ( isset( $params['symmetry'] ) && $params['symmetry'] && isset( $diamonds_api_symmetry[ $params['symmetry'] ] ) ) {
				$args['symmetry_from'] = $diamonds_api_symmetry[ $params['symmetry'] ];
				$args['symmetry_to'] = 'Excellent';
			}

			$i = 1;

			$total_diamonds_found = 0;

			$error_vdb = '';

			$error_nivoda = '';

			$all_active_apis = $this->get_api_order();

			$data = array();

			if ( is_array( $all_active_apis ) && count( $all_active_apis ) >= 1 ) {
				foreach ( $all_active_apis as $key => $single_api ) {
					$api_data = $this->{'get_' . $key . '_diamonds_data'}( $args );

					if ( isset( $api_data['total_diamonds_found'] ) && $api_data['total_diamonds_found'] >= 1 ) {
						$total_diamonds_found += $api_data['total_diamonds_found'];
					}

					if ( isset( $args[ 'page_number_' . $key ] ) && (int) $args[ 'page_number_' . $key ] >= 2 ) {
						$args[ 'page_number_' . $key ] = (int) $args[ 'page_number_' . $key ] + 1;
					} else {
						$args[ 'page_number_' . $key ] = 2;
					}

					if ( is_array( $api_data ) && isset( $api_data['data'] ) && $api_data['data'] ) {
						$data = array_merge( $data, $api_data['data'] );
					}

					++$i;
				}
			}

			wp_send_json_success(
				array(
					'message'     => 'success',
					'data'        => wp_json_encode( $data ),
					'page_number' => $args['page_number'],
					'page_size'   => $args['page_size'],
					'total'       => $total_diamonds_found,
				)
			);

			die();
		}
	}

	public function fetch_stone_by_id() {
		if ( isset( $_POST['query_string'] ) && ! empty( $_POST['query_string'] ) ) {
			parse_str( $_POST['query_string'], $params );

			if ( isset( $params['diamond_id'] ) && ! empty( $params['diamond_id'] ) ) {
				$stock_num = $params['diamond_id'];

				if ( $this->is_nivoda_diamond( $stock_num ) ) {
					$data = otw_woo_ring_builder()->nivoda_diamonds->get_diamond_by_stock_num( $stock_num );
				} else {
					$data = otw_woo_ring_builder()->vdb_diamonds->get_diamond_by_stock_num( $stock_num );
				}

				if ( ! is_array( $data ) ) {
					wp_send_json_success(
						array(
							'message' => 'error',
						)
					);
				} else {
					wp_send_json_success(
						array(
							'message' => 'success',
							'test'    => 'test',
							'data'    => wp_json_encode( $data ),
						)
					);
				}

				die();
			}
		}
	}

	public function fetch_stones_min_max() {
		$result = otw_woo_ring_builder()->nivoda_diamonds->get_diamonds_min_max();

		wp_send_json_success( $result );

		die();
	}

	public function get_diamond_by_stock_num( $stock_num ) {
		if ( isset( $stock_num ) &&
			isset( $this->current_diamond ) &&
			$this->current_diamond &&
			isset( $this->current_diamond['stock_num'] ) &&
			$this->current_diamond['stock_num'] === $stock_num
		) {
			return $this->current_diamond;
		}

		if ( function_exists( 'WC' ) && isset( WC()->session ) && is_object( WC()->session ) ) {
			$sessioned_diamond = WC()->session->get( 'gcpb_current_diamond' );

			if ( $sessioned_diamond && isset( $sessioned_diamond['stock_num'] ) ) {
				$this->current_diamond = $sessioned_diamond;

				return $this->current_diamond;
			}
		}

		if ( $this->is_nivoda_diamond( $stock_num ) ) {
			$diamond = otw_woo_ring_builder()->nivoda_diamonds->get_diamond_by_stock_num( $stock_num );
		} else {
			$diamond = otw_woo_ring_builder()->vdb_diamonds->get_diamond_by_stock_num( $stock_num );
		}

		if ( ! is_array( $diamond ) ) {
			$error_message = 'Sorry, we could not connect with diamonds API';
			return $error_message;
		}

		$this->current_diamond = $diamond;

		if ( function_exists( 'WC' ) &&
			isset( WC()->session ) &&
			is_object( WC()->session )
		) {
			WC()->session->set( 'gcpb_current_diamond', $diamond );
		}

		return $diamond;
	}

	public function get_current_diamond( $diamond_id = null ) {
		if ( isset( WC()->session ) && WC()->session->get( 'next_session' ) === true ) {
			$stock_num = WC()->session->get( 'next_diamond_id' );
		}

		$diamond = $this->get_diamond_by_stock_num( $stock_num );

		if ( ! is_array( $diamond ) ) {
			$error_message = 'Sorry, we could not connect with diamonds API';

			return $error_message;
		}

		return $diamond;
	}

	public function get_api_order() {
		$output = array();

		$vdb_api = $this->get_option( 'vdb_api' );

		$vdb_api_order = (int) $this->get_option( 'vdb_api_order' );

		$nivoda_api = $this->get_option( 'nivoda_api' );

		$nivoda_api_order = (int) $this->get_option( 'nivoda_api_order' );

		if ( $vdb_api && $vdb_api_order ) {
			$output['vdb'] = $vdb_api_order;
		}

		if ( $nivoda_api && $nivoda_api_order ) {
			$output['nivoda'] = $nivoda_api_order;
		}

		if ( $output ) {
			asort( $output );
		}

		return $output;
	}

	public function get_vdb_diamonds_data( $args ) {
		$total_diamonds_found = 0;

		$data = array();

		$error = '';

		$query_response_all_data = otw_woo_ring_builder()->vdb_diamonds->get_diamonds( $args );

		if ( ! (
			$query_response_all_data &&
			is_array( $query_response_all_data ) &&
			count( $query_response_all_data ) >= 1 &&
			isset( $query_response_all_data['diamonds'] )
		) ) {
			if ( is_string( $query_response_all_data ) ) {
				$error = $query_response_all_data;
			} else {
				$error = 'Unknown Error';
			}

			if ( $error ) {
				return array( 'error' => $error );
			}
		}

		$query_response = $query_response_all_data['diamonds'];

		foreach ( $query_response as $diamond ) {
			if ( $this->exclude_diamond( $diamond ) ) {
				continue;
			}

			$diamond = otw_woo_ring_builder()->vdb_diamonds->format_diamond_data( $diamond );

			$data[] = $diamond;
		}

		if ( isset( $query_response_all_data['total_diamonds_found'] ) ) {
			$total_diamonds_found = $query_response_all_data['total_diamonds_found'];
		}

		return array(
			'total_diamonds_found' => $total_diamonds_found,
			'data'                 => $data,
		);
	}

	public function get_nivoda_diamonds_data( $args ) {
		$total_diamonds_found = 0;

		$data = array();

		$error = '';

		$query_response_all_data = otw_woo_ring_builder()->nivoda_diamonds->get_diamonds( $args );

		if ( ! (
			$query_response_all_data &&
			is_array( $query_response_all_data ) &&
			count( $query_response_all_data ) >= 1 &&
			isset( $query_response_all_data['diamonds_by_query'] ) &&
			isset( $query_response_all_data['diamonds_by_query']['items'] ) &&
			is_array( $query_response_all_data['diamonds_by_query']['items'] )
		) ) {
			if ( is_string( $query_response_all_data ) ) {
				$error = $query_response_all_data;
			} else {
				$error = 'Unknown Error';
			}

			if ( $error ) {
				return array( 'error' => $error );
			}
		}

		$query_response = $query_response_all_data['diamonds_by_query']['items'];

		$counter = 1;

		foreach ( $query_response as $diamond ) {
			if ( isset( $diamond['api'] ) && $diamond['api'] == '1' ) {
				$formated_diamond = otw_woo_ring_builder()->nivoda_diamonds->convert_local_to_vdb( $diamond );
			} else {
				$formated_diamond = otw_woo_ring_builder()->nivoda_diamonds->convert_nivoda_to_vdb( $diamond );

				if ( $this->exclude_diamond( $formated_diamond ) ) {
					continue;
				}
			}

			$data[] = $formated_diamond;

			++$counter;
		}

		if ( isset( $query_response_all_data['diamonds_by_query_count'] ) &&
			$query_response_all_data['diamonds_by_query_count'] >= 1
		) {
			$total_diamonds_found = $query_response_all_data['diamonds_by_query_count'];
		}

		return array(
			'total_diamonds_found' => $total_diamonds_found,
			'data'                 => $data,
		);
	}

	public function exclude_diamond( $diamond ) {
		$diamond_shape = strtolower( $diamond['shape'] );

		if ( ! in_array( $diamond['clarity'], $this->diamonds_api_clarity ) ) {
			return true;
		}

		$diamonds_api_shapes = gcpb_diamond_shapes_array();

		if ( ! isset( $diamonds_api_shapes[ $diamond_shape ] ) ) {
			return true;
		}

		if ( ! isset( $diamond['image_url'] ) || empty( $diamond['image_url'] ) ) {
			return true;
		}

		$selected_setting_shapes = $this->get_current_selected_variation_shapes();

		if ( $selected_setting_shapes &&
			is_array( $selected_setting_shapes ) &&
			count( $selected_setting_shapes ) >= 1 &&
			! in_array( $diamond_shape, $selected_setting_shapes )
		) {
			return true;
		}

		return false;
	}

	public function is_nivoda_diamond( $stock_num ) {
		$result = substr( $stock_num, 0, 7 );
		if ( $result == 'nivoda-' ) {
			return true;
		}
		return false;
	}
}
