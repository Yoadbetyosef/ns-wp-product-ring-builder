<?php
namespace OTW\WooRingBuilder\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait NivodaLocalDB{
	public $nivoda_api_type = 'local';

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

		$pagination = $this->wpbb_paginate_links( $args_pagination );

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

	public function get_search_query( $query, $args ) {
		if ( ! $this->nivoda_diamonds ) {
			$this->nivoda_diamonds = \OTW\WooRingBuilder\Classes\NivodaGetDiamonds::instance();
		}

		if ( isset( $args['type'] ) && $args['type'] == 'Natural_Diamond' ) {
			$query .= " AND d_type = 'natural'";
		} else {
			$query .= " AND d_type = 'lab'";
		}

		if ( isset( $args['shapes[]'] ) && $args['shapes[]'] ) {
			$shape = sanitize_user( $args['shapes[]'] );
			$query .= " AND shape LIKE '%" . $shape . "%'";
		}

		if ( isset( $args['price_total_from'] ) &&
			$args['price_total_from'] &&
			isset( $args['price_total_to'] ) &&
			$args['price_total_to']
		) {
			$price_total_from = (int) $args['price_total_from'];

			$price_total_to = (int) $args['price_total_to'];

			$query .= " AND (price >= {$price_total_from} AND price <= {$price_total_to})";
		}

		if ( isset( $args['size_from'] ) &&
			$args['size_from'] &&
			isset( $args['size_to'] ) &&
			$args['size_to']
		) {
			$size_from = (float) $args['size_from'];

			$size_to = (float) $args['size_to'];

			$query .= " AND (carat_size >= {$size_from} AND carat_size <= {$size_to})";
		}

		if ( isset( $args['color_from'] ) &&
			$args['color_from'] &&
			isset( $args['color_to'] ) &&
			$args['color_to']
		) {
			$args['color_from'] = strtoupper( $args['color_from'] );

			$args['color_to'] = strtoupper( $args['color_to'] );

			$found_colors = get_all_values_between_range(
				$args['color_from'],
				$args['color_to'],
				$this->nivoda_diamonds->get_colors_list()
			);

			if ( $found_colors ) {
				$fancy_query = '';

				$sanitize_colors = array();

				foreach ( $found_colors as $key => $single_color ) {
					if ( $single_color === 'FANCY' ) {
						$fancy_query = ' OR color LIKE "%Fancy%"';
					}

					$sanitize_colors[ $key ] = sanitize_user( $single_color );
				}

				$query .= ' AND (color IN ("' . ( implode( '", "', $sanitize_colors ) ) . '")' . $fancy_query . ')';
			}
		}

		if ( isset( $args['clarity_from'] ) &&
			$args['clarity_from'] &&
			isset( $args['clarity_to'] ) &&
			$args['clarity_to']
		) {
			$found_clarity = get_all_values_between_range( $args['clarity_from'], $args['clarity_to'], $this->nivoda_diamonds->get_clarity_list() );

			if ( $found_clarity ) {
				$sanitize_clarity = array();

				foreach ( $found_clarity as $key => $single_clarity ) {
					$sanitize_clarity[ $key ] = sanitize_user( $single_clarity );
				}

				$query .= ' AND (clarity IN ("' . ( implode( '", "', $sanitize_clarity ) ) . '"))';
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

	public function wpbb_paginate_links( $args ) {
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
