<?php
//  ini_set('display_errors', 1);
//  ini_set('display_startup_errors', 1);
//  error_reporting(E_ALL);
 
class Ring_Builder {
	
	private $options;
 	/**
	* Instance
	* @access private
	* @static
	*
	*/
	private static $_instance = null;

	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
 		// add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
 		add_action( 'wp_enqueue_scripts', [ $this, 'ring_builder_frontend_script' ] );

		add_action( 'wp_ajax_nopriv_fetch_products', [ $this, 'fetch_products_func'] );
		add_action( 'wp_ajax_fetch_products', [ $this, 'fetch_products_func'] );	

		/* variation loader inside archive loop */
		add_action( 'wp_ajax_nopriv_fetch_product_single_variation', [ $this, 'fetch_product_single_variation_func'] );
		add_action( 'wp_ajax_fetch_product_single_variation', [ $this, 'fetch_product_single_variation_func'] );	

		add_action( 'gcpb_product_loop_attributes' , [ $this, 'gcpb_product_loop_attributes_func'] , 10, 2 );
		add_action( 'gcpb_single_product_attributes' , [ $this, 'gcpb_single_product_attributes_func'] , 10, 3 );

		/* variation loader on single product page */
		add_action( 'wp_ajax_nopriv_gcpb_single_product_variation_data', [ $this, 'gcpb_single_product_variation_data_func'] );
		add_action( 'wp_ajax_gcpb_single_product_variation_data', [ $this, 'gcpb_single_product_variation_data_func'] );	

		/* store product state to temp table */
		add_action( 'wp_ajax_nopriv_gcpb_link_single_product', [ $this, 'gcpb_link_single_product_func'] );
		add_action( 'wp_ajax_gcpb_link_single_product', [ $this, 'gcpb_link_single_product_func'] );	
		
		/* store product state to temp table */
		add_action( 'wp_ajax_nopriv_gcpb_fetch_3d_model_image', [ $this, 'gcpb_fetch_3d_model_image_func'] );
		add_action( 'wp_ajax_gcpb_fetch_3d_model_image', [ $this, 'gcpb_fetch_3d_model_image_func'] );	
		
  	}

	public function init() {
		add_action( 'product_steps', [ $this,'product_steps_func'] );
		add_action( 'product_filters', [ $this,'product_filters_func'] );
		add_action( 'prb_product_listing', [ $this,'prb_product_listing_func'], 10 , 1 );

		add_action('wp_head', function(){
			// echo '<webgi-viewer id="viewer_id_test" src="https://wordpress-848560-3382448.cloudwaysapps.com/wp-content/uploads/2023/06/2-Artemis-Princess-4-Rose.glb" style="width: 100%; height: calc(100vw / var(--columns) - calc(var(--section-padding) * 2)); z-index: 1; display: block; position:relative;" autoManageViewers="true"></webgi-viewer>';
		});
 	}
	 
	public function ring_builder_frontend_script() {
		$script_abs_path = plugin_dir_path(OTW_WOO_RING_BUILDER_PLUGIN_FILE). 'assets/js/ring-builder.js';
		wp_register_script( 'gcpb-builder', plugins_url('assets/js/ring-builder.js', dirname(dirname(__FILE__))), array( 'jquery' ), get_file_time($script_abs_path), true );
		wp_enqueue_script('gcpb-builder');

		$ajax_variables = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		);

		$gcpb_products_per_page = get_option( 'gcpb_products_per_page' );

		if(!empty($gcpb_products_per_page)) {
			$ajax_variables['gcpb_products_per_page'] = $gcpb_products_per_page;
		}
		global $wp_query;
		if($wp_query && isset($wp_query->queried_object) && isset($wp_query->queried_object->ID)){
			$current_page_id = $wp_query->queried_object->ID;
			$ajax_variables['current_page_id'] = $current_page_id;
		}
		wp_localize_script( 'gcpb-builder', 'ajax_ring_obj', $ajax_variables );

		$script_abs_path = plugin_dir_path(OTW_WOO_RING_BUILDER_PLUGIN_FILE). 'assets/css/ring-builder.css';
		wp_register_style( 'gcpb-builder', plugins_url('assets/css/ring-builder.css', dirname(dirname(__FILE__))), array(), get_file_time($script_abs_path).rand() );
		wp_enqueue_style( 'gcpb-builder' );

			
		$dynamic_css = $this->gcpb_dynamic_css();
		
		if(!empty($dynamic_css)) {
			wp_add_inline_style( 'gcpb-builder', $dynamic_css );
		}
		
	}

	public function product_steps_func() {

		prb_get_template( 'step-bar.php' );
	}

	public function product_filters_func() {
		prb_get_template( 'ring-filters.php' );
	}

	public function prb_product_listing_func($args = array()) {
	//	echo '<pre>'; print_r($args); echo '</pre>';
		prb_get_template( 'product-listing.php', $args );
	}

	public function gcpb_product_loop_attributes_func($attributes, $default_attributes) {
		// db($attributes);exit();
 		$loop_attributes = gcpb_product_attributes_data();
		// echo '<pre>'; print_r($default_attributes); echo '</pre>';

 		if(isset($loop_attributes['attribute']) && !empty($loop_attributes['attribute'])) {
			
			foreach( $attributes as $key => $attribute_item ) {
				$key = str_replace('pa_','',$key);

				if(in_array($key,$loop_attributes['attribute'])) {

					if(isset($loop_attributes['terms'][$key]) && !empty($loop_attributes['terms'][$key])) {
						
						$terms = $loop_attributes['terms'][$key];
						
						if( $default_attributes['pa_'.$key] ) {
							$current_attribute = $default_attributes['pa_'.$key];
 						}
  
						if(isset($attributes['pa_'.$key]) && !empty($attributes['pa_'.$key])) {
							// db($attributes['pa_'.$key]);exit();
							?>
							<div class="gcpb-available-container">
								<ul class="gcpb-available-wrapper gcpb-scrollbar gcpb-horizontal js-gcpb-loop-attribute" data-attribute="<?php echo $key; ?>">
									<?php foreach( $attribute_item->get_terms() as $metal_attribute ) {
										$image_key = array_search($metal_attribute->term_id, array_column($terms,'sub-attribute'));  
										$thumb_id = $terms[$image_key]['gcpb_attribute_icon']; 
										
										$active_variation = (isset($current_attribute) && !empty($current_attribute) && $current_attribute == $metal_attribute->slug ) ? ' selected': ''; ?>
										
										<li href="#" class="gcpb-swatch-box<?php echo $active_variation; ?>" data-value="<?php echo $metal_attribute->slug; ?>">
											<?php $textureImg = wp_get_attachment_image_src( $thumb_id ); ?>
											<img class="gcpb-available-swatch gcpb-swatch" src="<?php echo $textureImg[0]; ?>" alt="<?php echo $metal_filter->name; ?>">
											<div class="gcpb-available-title"><?php echo $metal_attribute->name; ?></div>
										</li>
									<?php } ?>
								</ul>
							</div>
						<?php }
					}
				}
			}
		}
	}

	public function fetch_products_func() {

		if(isset($_POST['query_string']) && !empty($_POST['query_string'])) {
 
			$main_filters = gcpb_main_filters();

			parse_str($_POST['query_string'], $params);
   
			$args = array(
				'post_type'	=> 'product',
				'post_status' => 'publish', 
				'posts_per_page' => 10,
				'orderby'   => 'ID',
				'order'     => 'desc',
 				'tax_query' => array()
			);

			if(isset($params['sort']) && !empty($params['sort'])) {
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = '_price';

				if( $params['sort'] == 'price-ascending') {
					$args['order'] = 'asc';
				} elseif(  $params['sort'] == 'price-descending' ) {
					$args['order'] = 'desc';
				} else {
					$args['meta_key'] = 'total_sales';
					$args['orderby'] = 'meta_value_num';
					$args['order'] = 'desc';
				}
			}

			if(isset($params['paged']) && !empty($params['paged'])) {
				$args['paged'] = $params['paged'];
			}

			if(isset($params['limit']) && !empty($params['limit'])) {
				$args['posts_per_page'] = $params['limit'];
			}
			
			$setting_term = get_term_by('id', otw_woo_ring_builder()->get_option('setting_category'), 'product_cat');
			$args['tax_query'][]  = array(
				'taxonomy'        => 'product_cat',
				'field'           => 'slug',
				'terms'           =>  array($setting_term->slug),
				'operator'        => 'IN',
			);

			if((isset($main_filters) && !empty($main_filters)) && (!empty($params) && is_array($params))) {
				foreach( $main_filters as $main_filter ) { 
					$attribute_slug = $main_filter['attribute'];
		 
					if(array_key_exists($attribute_slug, $params)) {
						$args['tax_query'][] = array(
							'taxonomy'        => 'pa_'.$attribute_slug,
							'field'           => 'slug',
							'terms'           =>  array($params[$attribute_slug]),
							'operator'        => 'IN',
						);
					}
				}
			}

			/* overrrite with settings args */

			if(isset($params['settings_args']) && !empty($params['settings_args'])) {

				if(isset($params['settings_args']['per_page']) && !empty($params['settings_args']['per_page'])) {
					$args['posts_per_page'] = $params['settings_args']['per_page'];
				}
				
				if(isset($params['settings_args']['style_attribute']) && !empty($params['settings_args']['style_attribute'])) {
					$style_attr_id = $params['settings_args']['style_attribute'];
				
					$args['tax_query'][] = array(
						'taxonomy'        => 'pa_ring-style',
						'field'           => 'slug',
						'terms'           =>  array($style_attr_id),
						'operator'        => 'IN',
					);
				}

				if(isset($params['settings_args']['post__not_in']) && !empty($params['settings_args']['post__not_in'])) {
					$post__not_in = $params['settings_args']['post__not_in'];
					$args['post__not_in'] = $post__not_in;
				}

 				// echo '<pre>'; print_r($params); echo '</pre>';
				// die('sd');
			}
		 
			//  echo '<pre>'; print_r($args); echo '</pre>';
			$output_products = '';
			$output_loader = '';
			$products_found = false;

			/* get product page from setting page */
			$gcpb_product_page = get_option( 'gcpb_product_page' );
			$args['orderby'] = 'menu_order';
			// $args['orderby'] = 'title menu_order';
			$args['order'] = 'asc';
			$products = new \WP_Query( $args );
			$result['total'] = $products->found_posts;


			if( $products->have_posts() ) {
				$count = $products->post_count;
				global $product_loop_counter;
				$product_loop_counter = 1;
				while ( $products->have_posts() ) {
					$products->the_post();
					ob_start();
					prb_get_template( 'loop/product-loop.php', array(
						'main_filters' => $main_filters, 
						'params' => $params,
						'product_link' => get_permalink( $gcpb_product_page )
					));
					$output_products .= ob_get_clean();
					$product_loop_counter++;
				}
				
				if($count >= $args['posts_per_page']) {
					$output_loader = '<div class="product-loader"><button type="button" class="gcpb-button gcpb-button-alt button js-load-products" data-paged="'.($args['paged']+1).'">Load More</button></div>';
				}

				$products_found = true;
			} else {
				// $gcpb_no_found_shortocde =  get_option('gcpb_no_product_found_shortocde');

				// if(!empty($gcpb_no_found_shortocde)) {
				// 	ob_start();
				// 	echo do_shortcode('[elementor-template id="42655"]');
				// 	$output_products = ob_get_contents();
 				// }
			}

 			echo wp_json_encode( array(
				'product_data' => $output_products,
				'product_loader' => $output_loader
			) );
			die();
		}

		die();
	}

	public function fetch_product_single_variation_func() {

		if(isset($_POST['variation_query']) && !empty($_POST['variation_query'])) {
			parse_str($_POST['variation_query'], $params);

			if(isset($params['product_id']) && !empty($params['product_id'])) {

				$product_id = $params['product_id'];
				$product = wc_get_product( wc_clean( $product_id ) );
				
				if ( $product->is_type( 'variable' ) ) {

					$default_attributes = $product->get_default_attributes();
					$main_filters = gcpb_main_filters();
    
					if((isset($main_filters) && !empty($main_filters)) && (!empty($params) && is_array($params))) {
						foreach( $main_filters as $main_filter ) { 
							$attr_slug = $main_filter['attribute'];
				 
							if(array_key_exists($attr_slug, $params)) {
								$default_attributes['pa_'.$attr_slug] = $params[$attr_slug];
							}
						}
					}
 				
					$variation_id = prb_find_matching_product_variation($product, $default_attributes);

					$variable_product = new \WC_Product_Variation( $variation_id );
					$default_variation_regular_price = $variable_product->regular_price;
					$default_variation_sale_price = $variable_product->sale_price; 
					$image_id = $variable_product->get_image_id();

					/* get product page from setting page */
					$gcpb_product_page = get_option( 'gcpb_product_page' );
					$product_link = get_permalink( $gcpb_product_page );
					$product_link = $product_link.'?product_id='.$product_id.'&variation_id='.$variation_id;

					/* 3d model image for variation */
					$variation_3d_model = get_post_meta( $variation_id, 'otw_woo_variation_3d_model', true );
					if(empty($variation_3d_model)){
						$variation_3d_model = '';
						// $variation_3d_model = 'https://wordpress-848560-3382448.cloudwaysapps.com/wp-content/uploads/2023/06/2-Artemis-Round-4-Yellow.glb';
					}

					if(!empty($variation_3d_model)) {
						// $model_image = '<webgi-viewer id="viewer_'.$variation_id.'" src="'.$variation_3d_model.'" style="width: 100%; height: calc(100vw / var(--columns) - calc(var(--section-padding) * 2)); z-index: 1; display: block; position:relative;" disposeOnRemove="true"></webgi-viewer>';
						// $model_image = '<webgi-viewer id="viewer_'.$variation_id.'" src="'.$variation_3d_model.'" style="width: 100%; height: calc(100vw / var(--columns) - calc(var(--section-padding) * 2)); z-index: 1; display: block; position:relative;" autoManageViewers="true"></webgi-viewer>';
						$model_image = $variation_3d_model;
					}

					$gallery_images_output = '';
					$variation_video_output = '';
					$variation_gallery_images 	= get_post_meta( $variation_id, 'otw_woo_variation_gallery_images', true );
					$variation_video_url = get_post_meta( $variation_id, 'otw_woo_variation_video_url', true );
					if(!empty($variation_gallery_images)){
							
						$gallery_images_output .= '<span class="loop_gallery_images" data-src="'.wp_get_attachment_image_url( $image_id, 'full', false ).'"></span>';
						$gallery_counter = 1;
						  foreach( $variation_gallery_images as $attachment_url ){
							$gallery_images_output .= '<span class="loop_gallery_images" data-src="'.$attachment_url.'"></span>';
						  }
					}
					if(!empty($variation_video_url)){
						$variation_video_output = '<video data-video_url="'.$variation_video_url.'" autoplay muted loop playsinline></video>';
					}

					if($default_variation_sale_price > 0) { 
						$price ='<del>'.wc_price($default_variation_regular_price).'</del><bdi>'.wc_price($default_variation_sale_price).'</bdi>'; 
					} else { 
						$price = '<bdi>'.wc_price($default_variation_regular_price).'</bdi>';
					} 
					
				}

				$data = array(
					'price' => $price,
					'image_org' => wp_get_attachment_image( $image_id, 'full', false, array('class' => 'gcpb-first-image gcpb-featured-image') ),
					'image_hover' => $model_image,
					'variation_id' => $variation_id,
					'product_link' => $product_link,
					'gallery_images' => $gallery_images_output,
					'variation_video' => $variation_video_output,
				);
				
				echo wp_json_encode( $data );
				// echo wp_json_encode(array('price' => '2.2'));
				die();
			}
 		}
	die();
	}


	
	function gcpb_dynamic_css($dynamic_css = '') {

		$dynamic_css = '';
		$gcpb_accent_color = get_option( 'gcpb_accent_color' );
 
		$stylesheet = array(
			':root' => array(),
		);

		if(!empty($gcpb_accent_color)) {
			$stylesheet[':root']['--accent-color'] = $gcpb_accent_color;
		}

		$gcpb_border_radius = get_option( 'gcpb_border_radius' );

		if(!empty($gcpb_border_radius)) {
			
			if( $gcpb_border_radius == 'square' ) {
				$stylesheet[':root']['--button-border-radius'] = 0;
				$stylesheet[':root']['--border-radius'] = 0;
			} elseif( $gcpb_border_radius == 'rounded' ) {
				$stylesheet[':root']['--button-border-radius'] = '100vw';
				$stylesheet[':root']['--border-radius'] = '1em';
			}
		}

		return gcpb_array_to_css($stylesheet);
 	}

	
	public function gcpb_single_product_attributes_func($product, $product_id, $variable_product) {

		$attributes = $product->get_attributes();

 		$loop_attributes = gcpb_product_attributes_data();

		if(!empty($variable_product)) {
			$variation_attributes = $variable_product->get_variation_attributes();
			//print_r($variation_attributes);
		}
		
		if(isset($loop_attributes['attribute']) && !empty($loop_attributes['attribute'])) {
			
			foreach( $attributes as $key => $attribute_item ) {
				$key = str_replace('pa_','',$key);

				if(in_array($key,$loop_attributes['attribute'])) {

					if(isset($loop_attributes['terms'][$key]) && !empty($loop_attributes['terms'][$key])) {
						$terms = $loop_attributes['terms'][$key];
	
						if( $variation_attributes['attribute_pa_'.$key] ) {
							$current_attribute = $variation_attributes['attribute_pa_'.$key];
 						}

						if(isset($attributes['pa_'.$key]) && !empty($attributes['pa_'.$key])) { ?>
							<div class="gcpb-available-container">
								<ul class="gcpb-available-wrapper gcpb-scrollbar gcpb-horizontal js-gcpb-single-variation" data-attribute="<?php echo $key; ?>">
									<?php foreach( $attribute_item->get_terms() as $attribute_item ) {
								
										$image_key = array_search($attribute_item->term_id, array_column($terms,'sub-attribute'));
										$thumb_id = $terms[$image_key]['gcpb_attribute_icon'];
								
											$active_variation = (isset($current_attribute) && !empty($current_attribute) && $current_attribute == $attribute_item->slug ) ? ' selected': ''; ?>
								
											<li href="#" class="gcpb-swatch-box<?php echo $active_variation; ?>" data-value="<?php echo $attribute_item->slug; ?>">
												<?php $textureImg = wp_get_attachment_image_src( $thumb_id ); ?>
												<img class="gcpb-swatch gcpb-available-swatch" src="<?php echo $textureImg[0]; ?>" alt="<?php echo $metal_filter->name; ?>">
												<div class="gcpb-available-title"><?php echo $attribute_item->name; ?></div>
											</li>
									<?php } ?>
								</ul>
							</div>
						<?php } 
 					}
				}
			}
		}
	}

 	public function gcpb_single_product_variation_data_func() {

		if(isset($_POST['variation_query']) && !empty($_POST['variation_query'])) {
			
			parse_str($_POST['variation_query'], $params);

			if(isset($params['product_id']) && !empty($params['product_id'])) {

				$product_id = $params['product_id'];
				$product = wc_get_product( wc_clean( $product_id ) );
				
				if ( $product->is_type( 'variable' ) ) {

 					$variation_id = gcpb_get_variation_id_by_query($product , $params);

					$variable_product = new \WC_Product_Variation( $variation_id );

					$default_variation_regular_price = $variable_product->regular_price;
					$default_variation_sale_price = $variable_product->sale_price; 
					$image_id = $variable_product->get_image_id();
					
					$variation_3d_model 	= get_post_meta( $variation_id, 'otw_woo_variation_3d_model', true );
					if(empty($variation_3d_model)){
						$variation_3d_model = '';
						// $variation_3d_model = 'https://wordpress-848560-3382448.cloudwaysapps.com/wp-content/uploads/2023/06/2-Artemis-Round-4-Yellow.glb';
					}

					$variation_gallery_images 	= get_post_meta( $variation_id, 'otw_woo_variation_gallery_images', true );
					$variation_video_url = get_post_meta( $variation_id, 'otw_woo_variation_video_url', true );
					if($default_variation_sale_price > 0) { 
						$price ='<del>'.wc_price($default_variation_regular_price).'</del><bdi>'.wc_price($default_variation_sale_price).'</bdi>'; 
					} else { 
						$price = '<bdi>'.wc_price($default_variation_regular_price).'</bdi>';
					} 

					$image_url = wp_get_attachment_image_url( $image_id, 'full', false); 
					// <div class="gcpb-product-image-wrapper">
					$output .= otw_get_single_variation_gallery_content($variable_product);
					
					//</div><!-- gcpb-product-image-wrapper -->
				}

				$data = array(
					'gcpb_fragments' => array(
						'.gcpb-product-price' => $price,
						'.gcpb-product-image-wrapper' => $output,
					),
					'variation_id' => $variation_id,
					'variation_3d_model' => $variation_3d_model,
				);
				
				echo wp_json_encode( $data );
				die();
			}
 		}
	
		exit();
	}

	public function gcpb_link_single_product_func() {
		print_r($_POST);

		$data = array(
			'variation_id' => $_POST['variation_id'],
			'product_id' => $_POST['product_id']
		);

		gcpb_product_storage($data);
 		
 		exit();
	}

	public function gcpb_fetch_3d_model_image_func() {
		// print_r($_POST);
		$image = '';
		$success = false;

		if(isset($_POST['variation_id']) && !empty($_POST['variation_id'])) {

			$variation_3d_model = get_post_meta( $_POST['variation_id'], 'otw_woo_variation_3d_model', true );
			if(empty($variation_3d_model)){
				$variation_3d_model = '';
				// $variation_3d_model = 'https://wordpress-848560-3382448.cloudwaysapps.com/wp-content/uploads/2023/06/2-Artemis-Round-4-Yellow.glb';
			}

			if(!empty($variation_3d_model)) {
				$image = $variation_3d_model;
				$success = true;
				// $image .= '<img src="'.$variation_3d_model.'" alt="" class="gcpb-second-image">';
				// $image = 'https://wordpress-848560-3382448.cloudwaysapps.com/wp-content/uploads/2023/03/Liori_0277-rose.glb';
			}

			
		}
		
		// $image = 'https://wordpress-848560-3382448.cloudwaysapps.com/wp-content/uploads/2023/03/Liori_0277-rose.glb';
		// $image = 'https://dist.pixotronics.com/webgi/assets/gltf/cube_diamond_sample.gltf';
		// $image = 'https://model3d.shopifycdn.com/models/o/0b4415401102bde8/flush-set-round-clip.glb';
		// $image = 'https://wordpress-848560-3382448.cloudwaysapps.com/wp-content/uploads/2023/06/2-Artemis-Cushion-4-Yellow.glb';
		// $success = true;

		echo wp_json_encode( array(
			'model_image' => $image,
			'success' => $success
		));
 
		die();
	}
}
 
Ring_Builder::instance(); ?>