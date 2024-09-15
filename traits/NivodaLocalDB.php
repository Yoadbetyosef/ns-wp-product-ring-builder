<?php
namespace OTW\WooRingBuilder\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait NivodaLocalDB{
	public $nivoda_api_type = 'local';

	public function get_local_diamonds( $args ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'otw_diamonds';

		$query = "SELECT * FROM $table_name WHERE d_status=1";

		$query = $this->get_search_query( $query, $args );

		if ( isset( $args['sortBy'] ) && isset( $args['sortOrder'] ) ) {
			$query .= ' ORDER BY ' . $args['sortBy'] . ' ' . $args['sortOrder'];
		} else {
			$query .= ' ORDER BY price ASC';
		}

		error_log( '** get_local_diamonds :: $query:' . $query );

		$args_pagination = array(
			'items_per_page' => 20,
			'sql'            => $query,
		);

		if ( isset( $args['page_number_nivoda'] ) && $args['page_number_nivoda'] >= 2 ) {
			$args_pagination['current_page'] = $args['page_number_nivoda'];
		}

		$pagination = $this->get_pagination( $args_pagination );

		if ( $pagination['total_rows_found'] && $pagination['total_rows_found'] >= 1 ) {
			$results = $wpdb->get_results(
				$pagination['sql'],
				ARRAY_A
			);

			if ( $results ) {
				$body = array();
				$body['diamonds_by_query']['items'] = $results;
				$body['diamonds_by_query_count'] = $pagination['total_rows_found'];
				return $body;
			}
		}

		$error_message = 'Sorry, we don\'t have any diamonds for your search.';

		return $error_message;
	}

	public function get_local_diamonds_min_max() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'otw_diamonds';

		$result = $wpdb->get_row(
			'
				SELECT 
						MAX(price) AS highest_price, 
						MIN(price) AS lowest_price, 
						MAX(carat_size) AS highest_carat, 
						MIN(carat_size) AS lowest_carat 
				FROM ' . $wpdb->prefix . 'otw_diamonds
				WHERE d_status = 1
		'
		);

		if ( $result ) {
			return array(
				'price_max' => $result->highest_price,
				'price_min' => $result->lowest_price,
				'carat_max' => $result->highest_carat,
				'carat_min' => $result->lowest_carat,
			);
		}
	}

	public function get_local_diamond_by_stock_num( $stock_num ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'otw_diamonds';

		$stock_num = sanitize_text_field( $stock_num );

		$query = "SELECT * FROM $table_name WHERE stock_num=%s";

		$query = $wpdb->prepare( $query, $stock_num );

		$results = $wpdb->get_results(
			$query,
			ARRAY_A
		);

		if ( $results ) {
			return $this->convert_local_to_vdb( $results[0] );
		}

		return 'This diamond is not available.';
	}

	public function get_search_query( $query, $args ) {
		global $wpdb;

		if ( ! $this->nivoda_diamonds ) {
			$this->nivoda_diamonds = \OTW\WooRingBuilder\Classes\NivodaGetDiamonds::instance();
		}

		// Handle diamond type: Natural_Diamond or Lab
		if ( isset( $args['type'] ) && in_array( $args['type'], array( 'Natural_Diamond', 'Lab' ), true ) ) {
			$diamond_type = $args['type'] === 'Natural_Diamond' ? 'natural' : 'lab';

			$query .= $wpdb->prepare( ' AND d_type = %s', $diamond_type );
		}

		// Handle shape
		if ( isset( $args['shape'] ) && ! empty( $args['shape'] ) ) {
			$shape = sanitize_text_field( $args['shape'] );

			$query .= $wpdb->prepare( ' AND shape = %s', $shape );
		}

		// Handle price range
		if ( ! empty( $args['price_total_from'] ) && ! empty( $args['price_total_to'] ) ) {
			$price_total_from = (int) $args['price_total_from'];
			$price_total_to = (int) $args['price_total_to'];
			$query .= $wpdb->prepare( ' AND (price >= %d AND price <= %d)', $price_total_from, $price_total_to );
		}

		// Handle only price from
		if ( ! empty( $args['price_total_from'] ) && empty( $args['price_total_to'] ) ) {
			$price_total_from = (int) $args['price_total_from'];
			$query .= $wpdb->prepare( ' AND price >= %d', $price_total_from );
		}

		// Handle only price to
		if ( empty( $args['price_total_from'] ) && ! empty( $args['price_total_to'] ) ) {
			$price_total_to = (int) $args['price_total_to'];
			$query .= $wpdb->prepare( ' AND price <= %d', $price_total_to );
		}

		// Handle carat size range
		if ( ! empty( $args['size_from'] ) && ! empty( $args['size_to'] ) ) {
			$size_from = (float) $args['size_from'];
			$size_to = (float) $args['size_to'];
			$query .= $wpdb->prepare( ' AND (carat_size >= %f AND carat_size <= %f)', $size_from, $size_to );
		}

		// Handle only carat size from
		if ( ! empty( $args['size_from'] ) && empty( $args['size_to'] ) ) {
			$size_from = (float) $args['size_from'];
			$query .= $wpdb->prepare( ' AND carat_size >= %f', $size_from );
		}

		// Handle only carat size to
		if ( empty( $args['size_from'] ) && ! empty( $args['size_to'] ) ) {
			$size_to = (float) $args['size_to'];
			$query .= $wpdb->prepare( ' AND carat_size <= %f', $size_to );
		}

		// Handle color range
		if ( ! empty( $args['color_from'] ) && ! empty( $args['color_to'] ) ) {
			$color_from = strtoupper( sanitize_text_field( $args['color_from'] ) );
			$color_to = strtoupper( sanitize_text_field( $args['color_to'] ) );

			$found_colors = get_all_values_between_range( $color_from, $color_to, $this->nivoda_diamonds->get_colors_list() );

			if ( $found_colors ) {
				$sanitize_colors = array_map( 'sanitize_text_field', $found_colors );
				$fancy_query = in_array( 'FANCY', $sanitize_colors, true ) ? ' OR color LIKE "%Fancy%"' : '';
				$query .= ' AND (color IN ("' . implode( '", "', $sanitize_colors ) . '")' . $fancy_query . ')';
			}
		}

		// Handle clarity range
		if ( ! empty( $args['clarity_from'] ) && ! empty( $args['clarity_to'] ) ) {
			$found_clarity = get_all_values_between_range( $args['clarity_from'], $args['clarity_to'], $this->nivoda_diamonds->get_clarity_list() );

			if ( $found_clarity ) {
				$sanitize_clarity = array_map( 'sanitize_text_field', $found_clarity );
				$query .= ' AND (clarity IN ("' . implode( '", "', $sanitize_clarity ) . '"))';
			}
		}

		return $query;
	}

	public function convert_local_to_vdb( $diamond ) {
		if ( isset( $diamond['carat_size'] ) ) {
			$diamond['size'] = $diamond['carat_size'];
		}

		if ( isset( $diamond['price'] ) ) {
			$diamond['total_sales_price'] = $diamond['price'];
		} elseif ( isset( $diamond['base_price'] ) ) {
			$diamond['total_sales_price'] = $diamond['base_price'];
		}

		$diamond['short_title'] = $diamond['size'] . ' carats ' . $diamond['color'] . ' ' . $diamond['clarity'] . ' ';

		return $diamond;
	}

	public function get_pagination( $args ) {
		global $wpdb;

		$defaults = array(
			'query_var'      => 'paged',
			'items_per_page' => '10',
			'output'         => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$args['total_rows_found'] = 0;

		$total_query = 'SELECT COUNT(1) FROM (' . $args['sql'] . ') AS combined_table';

		$total = $wpdb->get_var( $total_query );

		$page = 1;

		if ( isset( $args['current_page'] ) && is_numeric( $args['current_page'] ) && $args['current_page'] >= 2 ) {
			$page = abs( (int) $args['current_page'] );
		}

		$args['current_page'] = $page;

		$offset = ( $page * $args['items_per_page'] ) - $args['items_per_page'];

		$totalPage = ceil( $total / $args['items_per_page'] );

		if ( $total && $total >= 1 ) {
			$args['total_pages'] = $totalPage;
			$args['total_rows_found'] = $total;
			$args['sql'] .= " LIMIT ${offset}, " . $args['items_per_page'];

			if ( $totalPage > 1 ) {
			}
		}

		return $args;
	}

	public function current_get_client_ip( $default = '' ) {
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
