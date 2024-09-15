<?php
namespace OTW\WooRingBuilder\Classes;

// exit if file is called directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NivodaGetDiamonds extends \OTW\WooRingBuilder\Plugin{
	use \OTW\GeneralWooRingBuilder\Traits\Singleton;

	use \OTW\WooRingBuilder\Traits\NivodaLocalDB;

	public function get_diamonds( $args ) {
		return $this->get_local_diamonds( $args );
	}

	public function get_diamonds_min_max() {
		return $this->get_local_diamonds_min_max();
	}

	public function get_diamond_by_stock_num( $stock_num ) {
		return $this->get_local_diamond_by_stock_num( $stock_num );
	}

	public function format_diamond( $diamond ) {
		$output = array(
			'video_url'         => '',
			'stock_num'         => '',
			'id'                => '',
			'image_url'         => '',
			'size'              => '',
			'shape'             => '',
			'shape_api'         => '',
			'total_sales_price' => '',
			'base_sales_price'  => '',
			'color'             => '',
			'clarity'           => '',
			'symmetry'          => '',
			'meas_length'       => '',
			'meas_width'        => '',
			'meas_ratio'        => '',
			'lab'               => '',
			'cert_url'          => '',
		);

		// Handle video URL
		if ( ! empty( $diamond['diamond']['certificate']['video'] ) ) {
			$full_url = explode( '/video/', $diamond['diamond']['certificate']['video'] );
			$output['video_url'] = $full_url[0] . '/video/rsp/autoplay/autoplay';
		}

		// Handle stock number and ID
		if ( ! empty( $diamond['id'] ) ) {
			$stock_num = str_replace( array( 'DIAMOND/', 'nivoda-' ), '', $diamond['id'] );
			$output['stock_num'] = $output['id'] = 'nivoda-' . $stock_num;
		}

		// Handle image URL
		if ( ! empty( $diamond['diamond']['certificate']['image'] ) ) {
			$output['image_url'] = $diamond['diamond']['certificate']['image'];
		}

		// Handle carat size
		if ( ! empty( $diamond['diamond']['certificate']['carats'] ) ) {
			$output['size'] = $diamond['diamond']['certificate']['carats'];
		}

		// Handle shape
		if ( ! empty( $diamond['diamond']['certificate']['shape'] ) ) {
			$output['shape'] = $this->get_shapes_list()[ $diamond['diamond']['certificate']['shape'] ];
			$output['shape_api'] = $diamond['diamond']['certificate']['shape'];
		}

		// Handle price and markup
		$price = ! empty( $diamond['price'] ) ? (int) $diamond['price'] : 0;
		$markup_price = ! empty( $diamond['markup_price'] ) ? (int) $diamond['markup_price'] : 0;

		if ( $price ) {
			$price_to_use = $markup_price ?: $price;
			$output['base_sales_price'] = number_format( $price / 100, 2, '.', '' );
			$output['total_sales_price'] = number_format( $price_to_use / 100, 2, '.', '' );
		}

		// Handle color, clarity, and symmetry
		foreach ( array( 'color', 'clarity', 'symmetry' ) as $attribute ) {
			if ( ! empty( $diamond['diamond']['certificate'][ $attribute ] ) ) {
				$output[ $attribute ] = $diamond['diamond']['certificate'][ $attribute ];
			}
		}

		// Handle measurements
		if ( ! empty( $diamond['diamond']['certificate']['length'] ) ) {
			$output['meas_length'] = $diamond['diamond']['certificate']['length'];
		}

		if ( ! empty( $diamond['diamond']['certificate']['width'] ) ) {
			$output['meas_width'] = $diamond['diamond']['certificate']['width'];
		}

		// Calculate measurement ratio if both length and width are set
		if ( $output['meas_length'] && $output['meas_width'] ) {
			$output['meas_ratio'] = number_format( $output['meas_length'] / $output['meas_width'], 2, '.', '' );
		}

		// Handle lab and certificate URL
		if ( ! empty( $diamond['diamond']['certificate']['lab'] ) ) {
			$output['lab'] = $diamond['diamond']['certificate']['lab'];
		}

		if ( ! empty( $diamond['diamond']['certificate']['pdfUrl'] ) ) {
			$output['cert_url'] = $diamond['diamond']['certificate']['pdfUrl'];
		}

		return $output;
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
