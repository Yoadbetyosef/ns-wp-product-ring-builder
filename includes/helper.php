<?php

function prb_locate_template( $template_name ) {

	// Set default plugin templates path.
	if ( ! $default_path ) :
		$default_path = PRB_DIR . 'views/'; // Path to the template folder
	endif;

	// Get plugins template file.
	$template = $default_path . $template_name;

	return apply_filters( 'wcpt_locate_template', $template, $template_name, $template_path, $default_path );
}

function prb_get_template( $template_name, $args = array(), $tempate_path = '', $default_path = '' ) {

	if ( is_array( $args ) && isset( $args ) ) :
		extract( $args );
	endif;

	$template_file = prb_locate_template( $template_name, $tempate_path, $default_path );

	if ( ! file_exists( $template_file ) ) :
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), '1.0.0' );
		return;
	endif;

	include $template_file;
}

function prb_find_matching_product_variation( $product, $attributes ) {
	foreach ( $attributes as $key => $value ) {
		if ( strpos( $key, 'attribute_' ) === 0 ) {
			continue;
		}
		unset( $attributes[ $key ] );
		$attributes[ sprintf( 'attribute_%s', $key ) ] = $value;
	}
	if ( class_exists( 'WC_Data_Store' ) ) {
		$data_store = WC_Data_Store::load( 'product' );
		return $data_store->find_matching_product_variation( $product, $attributes );
	} else {
		return $product->get_matching_variation( $attributes );
	}
}

function gcpb_main_filters() {
	$gcpb_filters = get_option( 'gcpb_filter_attributes' );
	$main_filters = array();

	if ( ! empty( $gcpb_filters ) ) {
		foreach ( $gcpb_filters as $gcpb_filter ) {
			$main_filters[] = array(
				'attribute' => $gcpb_filter['main-attribute'],
				'is_filter' => ( isset( $gcpb_filter['is_filter'] ) && $gcpb_filter['is_filter'] == 'yes' ) ? 'yes' : 'no',
			);
		}
	}

	return $main_filters;
}


function gcpb_product_attributes_data() {
	$gcpb_filters = get_option( 'gcpb_filter_attributes' );
	$main_filters = array();

	if ( ! empty( $gcpb_filters ) ) {
		foreach ( $gcpb_filters as $gcpb_filter ) {

			if ( isset( $gcpb_filter['gcpb_loop_visibility'] ) && $gcpb_filter['gcpb_loop_visibility'] == 'yes' ) {
				$main_filters['attribute'][] = $gcpb_filter['main-attribute'];
				$main_filters['terms'][ $gcpb_filter['main-attribute'] ] = $gcpb_filter['gcpb-sub-attributes'];
			}
		}
	}

	return $main_filters;
}

function gcpb_array_to_css( $rules, $indent = 0 ) {
	$css = '';
	$prefix = str_repeat( '  ', $indent );

	foreach ( $rules as $key => $value ) {
		if ( is_array( $value ) ) {
			$selector = $key;
			$properties = $value;

			$css .= $prefix . "$selector {\n";
			$css .= $prefix . gcpb_array_to_css( $properties, $indent + 1 );
			$css .= $prefix . "}\n";
		} else {
			$property = $key;
			$css .= $prefix . "$property: $value;\n";
		}
	}

	return $css;
}



function gcpb_get_variation_id_by_query( $product, $query ) {

	if ( ! $product->is_type( 'variable' ) ) {
		return;
	}

	$default_attributes = $product->get_default_attributes();
	$main_filters = gcpb_main_filters();

	if ( ( isset( $main_filters ) && ! empty( $main_filters ) ) && ( ! empty( $query ) && is_array( $query ) ) ) {
		foreach ( $main_filters as $main_filter ) {
			$attr_slug = $main_filter['attribute'];

			if ( array_key_exists( $attr_slug, $query ) ) {
				$default_attributes[ 'pa_' . $attr_slug ] = $query[ $attr_slug ];
			}
		}
	}

	$variation_id = prb_find_matching_product_variation( $product, $default_attributes );

	return $variation_id;
}

function gcpb_product_storage( $data ) {
	$product_storage = Ring_Storage::instance();

	if ( is_user_logged_in() ) {
		$data['user_id'] = get_current_user_id();
	} elseif ( isset( $_COOKIE['gcpb_user_data'] ) && ! empty( $_COOKIE['gcpb_user_data'] ) ) {

			$data['user_id'] = $_COOKIE['gcpb_user_data'];
	}

	$product_storage->gcpb_insert_product_log( $data );
}
