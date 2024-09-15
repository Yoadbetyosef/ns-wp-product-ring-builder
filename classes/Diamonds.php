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

			// Initialize default args
			$args = array(
				'type'             => 'Lab_grown_Diamond',
				'page_size'        => $this->page_size,
				'page_number'      => 1,
				'exchange_rate'    => '1',
				'show_unavailable' => 'true',
				'with_images'      => true,
				'sortBy'           => 'price',
				'sortOrder'        => 'ASC',
			);

			// Handle pagination
			if ( isset( $params['paged'] ) && ! empty( $params['paged'] ) ) {
				$args['paged'] = (int) $params['paged'];
			}

			if ( isset( $params['limit'] ) && ! empty( $params['limit'] ) ) {
				$args['posts_per_page'] = (int) $params['limit'];
			}

			// Handle sorting
			if ( isset( $params['sortBy'] ) && isset( $params['sortOrder'] ) ) {
				$args['sortBy'] = $params['sortBy'];
				$args['sortOrder'] = $params['sortOrder'];
			}

			// Handle page number
			if ( isset( $params['page_number'] ) && (int) $params['page_number'] >= 2 ) {
				$args['page_number'] = (int) $params['page_number'];
			}

			// Handle diamond attributes
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

			// Handle type
			if ( isset( $params['type'] ) ) {
				$args['type'] = $params['type'];
			}

			// Handle shape
			if ( isset( $params['shape'] ) && isset( $diamonds_api_shapes[ $params['shape'] ] ) ) {
				$args['shapes[]'] = $diamonds_api_shapes[ $params['shape'] ];
			}

			// Handle color
			if ( isset( $params['color'] ) && ! empty( $params['color'] ) ) {
				$colors = explode( '-', $params['color'] );
				$args['color_from'] = $diamonds_api_color[ $colors[0] ] ?? $colors[0];
				$args['color_to']   = $diamonds_api_color[ $colors[1] ] ?? $colors[1];
			}

			// Handle clarity
			if ( isset( $params['clarity'] ) && ! empty( $params['clarity'] ) ) {
				$clarity = explode( '-', $params['clarity'] );
				$args['clarity_from'] = $this->get_clarity_value( $clarity[0] );
				$args['clarity_to']   = $this->get_clarity_value( $clarity[1] );
			}

			// Handle price
			if ( isset( $params['price'] ) && ! empty( $params['price'] ) ) {
				$prices = explode( '-', $params['price'] );
				if ( isset( $prices[0] ) ) {
					$args['price_total_from'] = $prices[0];
				}
				if ( isset( $prices[1] ) ) {
					$args['price_total_to'] = $prices[1];
				}
			}

			// Handle carat
			if ( isset( $params['carat'] ) && ! empty( $params['carat'] ) ) {
				$carats = explode( '-', $params['carat'] );
				if ( isset( $carats[0] ) ) {
					$args['size_from'] = $carats[0];
				}
				if ( isset( $carats[1] ) ) {
					$args['size_to'] = $carats[1];
				}
			}

			// Handle cut, polish, and symmetry
			foreach ( array( 'cut', 'polish', 'symmetry' ) as $attribute ) {
				if ( isset( $params[ $attribute ] ) && isset( ${"diamonds_api_{$attribute}"}[ $params[ $attribute ] ] ) ) {
					$args[ "{$attribute}_from" ] = ${"diamonds_api_{$attribute}"}[ $params[ $attribute ] ];
					$args[ "{$attribute}_to" ] = 'Excellent';
				}
			}

			// Fetch data from API
			$api_data = $this->get_diamond_list( $args );

			// Handle results
			$total_diamonds_found = $api_data['total_diamonds_found'] ?? 0;

			$data = $api_data['data'] ?? array();

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

				$data = otw_woo_ring_builder()->nivoda_diamonds->get_diamond_by_stock_num( $stock_num );

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

	public function get_diamond_list( $args ) {
		$total_diamonds_found = 0;

		$data = array();

		$error = '';

		$query_response_all_data = otw_woo_ring_builder()->nivoda_diamonds->get_diamonds( $args );

		if ( ! (
			$query_response_all_data &&
			is_array( $query_response_all_data ) &&
			count( $query_response_all_data ) >= 1 &&
			isset(
				$query_response_all_data['diamonds_by_query']
			) &&
			isset( $query_response_all_data['diamonds_by_query']['items'] ) &&
			is_array(
				$query_response_all_data['diamonds_by_query']['items']
			)
		) ) {
			if ( is_string( $query_response_all_data ) ) {
				$error = $query_response_all_data;
			} else {
				$error = 'Unknown Error';
			}

			if ( $error ) {
				return array(
					'error' => $error,
				);
			}
		}

		$query_response = $query_response_all_data['diamonds_by_query']['items'];

		$counter = 1;

		foreach ( $query_response as $diamond ) {
			if ( isset( $diamond['api'] ) && $diamond['api'] == '1' ) {
				$formated_diamond = otw_woo_ring_builder()->nivoda_diamonds->format_diamond( $diamond );
			} else {
				$formated_diamond = otw_woo_ring_builder()->nivoda_diamonds->format_diamond( $diamond );

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

	public function get_clarity_value( $clarity ) {
		$clarity_map = array(
			'VVS' => 'VVS1',
			'VS'  => 'VS1',
			'SI'  => 'SI1',
			'FL'  => 'FL',
		);
		return $clarity_map[ $clarity ] ?? $clarity;
	}

	public function get_diamond_by_stock_num( $stock_num ) {
		if ( isset( $this->current_diamond ) &&
			$this->current_diamond &&
			isset( $this->current_diamond['stock_num'] ) &&
			$this->current_diamond['stock_num'] == $stock_num
		) {
			return $this->current_diamond;
		}

		if ( function_exists( 'WC' ) &&
			isset( WC()->session ) &&
			is_object( WC()->session )
		) {
			$sessioned_diamond = WC()->session->get( 'gcpb_current_diamond' );

			if ( $sessioned_diamond &&
				isset( $sessioned_diamond['stock_num'] ) &&
				$_GET['stock_num'] == $sessioned_diamond['stock_num']
			) {
				$this->current_diamond = $sessioned_diamond;

				return $this->current_diamond;
			}
		}

		$diamond = otw_woo_ring_builder()->nivoda_diamonds->get_diamond_by_stock_num( $stock_num );

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
		} else {
			if ( ! ( isset( $_GET['stock_num'] ) && $_GET['stock_num'] ) ) {
				return false;
			}

			$stock_num = $_GET['stock_num'];
		}

		$diamond = $this->get_diamond_by_stock_num( $stock_num );

		if ( ! is_array( $diamond ) ) {
			$error_message = 'Sorry, we could not connect with diamonds API';

			return $error_message;
		}

		return $diamond;
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
}
