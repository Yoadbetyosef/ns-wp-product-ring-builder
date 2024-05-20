<?php 
global $product;
 
if( $product->is_type('variable') ) :
     
    $default_attributes = $product->get_default_attributes();
 
    if((isset($main_filters) && !empty($main_filters)) && (!empty($params) && is_array($params))) {
        foreach( $main_filters as $main_filter ) { 
            $attr_slug = $main_filter['attribute'];
 
            if(array_key_exists($attr_slug, $params)) {
                $default_attributes['pa_'.$attr_slug] = $params[$attr_slug];
            }
        }
    }
 
    $variation_id = prb_find_matching_product_variation($product, $default_attributes);
    $variable_product = new WC_Product_Variation( $variation_id );
    $image_id = $variable_product->get_image_id();
   // $variable_product = wc_get_product($variation_id);
    
    $default_variation_regular_price = $variable_product->regular_price;
    $default_variation_sale_price = $variable_product->sale_price; 
    $attributes = $product->get_attributes();

    $product_id = $product->get_id();
    // echo '<pre>'; print_r($attributes); echo '</pre>';
    $product_data = array(
        'product_id' => $product_id,
        'variation_id' => $variation_id
    );

    $variation_3d_model = get_post_meta( $variation_id, 'otw_woo_variation_3d_model', true );
    if(empty($variation_3d_model)){
        $variation_3d_model = '';
        // $variation_3d_model = 'https://wordpress-848560-3382448.cloudwaysapps.com/wp-content/uploads/2023/06/2-Artemis-Round-4-Yellow.glb';
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
            
            $default_image = get_option('gcpb_default_product_image');

            if( !empty($default_image) ) {
                $gallery_images_output .= '<span class="loop_gallery_images" data-src="'.wp_get_attachment_image_url( $default_image, 'full', false ).'"></span>';
            }
    }
    if(!empty($variation_video_url)){
        $variation_video_output = '<video data-video_url="'.$variation_video_url.'" autoplay muted loop playsinline></video>';
    }

    global $product_loop_counter;
    parse_str($_POST['query_string'], $gcpb_params);
    // db($gcpb_params);
    if(isset($gcpb_params['paged']) && $gcpb_params['paged'] == 1 && isset($product_loop_counter) && ($product_loop_counter == 5 || $product_loop_counter == 15)){
        $current_page_id = otw_woo_ring_builder()->get_option('gcpb_listing_page');
        global $wp_query;
        if($wp_query && isset($wp_query->queried_object) && isset($wp_query->queried_object->ID)){
            $current_page_id = $wp_query->queried_object->ID;
            // db($current_page_id);
        }
        elseif(isset($gcpb_params['current_page_id']) && $gcpb_params['current_page_id']){
            $current_page_id = $gcpb_params['current_page_id'];
        }
        $first_promo_link = 'first-promo-link';
        $first_promo_image = 'first-promo-image';
        $first_promo_text = 'first-promo-text';
        if($product_loop_counter == 15){
            $first_promo_link = 'second-promo-link';
            $first_promo_image = 'second-promo-image';
            $first_promo_text = 'second-promo-text';
        }
        $first_promo_image_id = get_post_meta($current_page_id, $first_promo_image, true);
        echo '<li class="gcpb-product-grid-insert">
        <a href="'.get_post_meta($current_page_id, $first_promo_link, true).'">
        ';
        echo '<img src="'.$first_promo_image_id.'" alt="">';
        // echo wp_get_attachment_image( $first_promo_image_id, 'full', false, array('class' => '') );
        echo '<p class="gcpb-product-grid-insert-content">
        '.get_post_meta($current_page_id, $first_promo_text, true).'
        </p>
        </a>
        </li>';
    }
  ?>

    <li class="gcpb-product-card gcpb-product-card-setting" tabindex="0" data-product-id="<?php echo $product_id; ?>" data-variation-id="<?php echo $variation_id; ?>" data-3d-model="<?php echo $variation_3d_model; ?>">
        <div class="gcpb-product-card-wrapper">
            
             <?php  
            if(isset($attributes['pa_ring-style'])): 
                foreach( $attributes['pa_ring-style']->get_terms() as $style): 
                    if($style->name == 'Hidden Halo'){
                        echo ' <div class="gcpb-prod-card__tag">'.$style->name.'</div>';
                    }
                    /*else{
                        echo '<!-- <div class="gcpb-prod-card__tag">'.$style->name.'</div> -->';
                    }*/
                ?>
                    
                <?php endforeach;
            endif; ?> 

            <button class="gcpb-mobile-only gcpb-button gcpb-card-toggle-icon"><a href="#"></a></button>
            <div class="gcpb-prod-card__images">
                <?php echo wp_get_attachment_image( $image_id, 'full', false, array('class' => 'gcpb-first-image') ); ?>
                <div class="gcpb-prod-card__loading__bar"></div>
            </div>
            <ul class="gcpb-media-tabs gcpb-media-tabs-product-loop">
                <li class="gcpb-media-tab tab-3d active">360°</li>
                <li class="gcpb-media-tab tab-gallery">Images</li>
                <li class="gcpb-media-tab tab-video">Video</li>
            </ul>
            <div class="gcpb-product-loop__title">
                <a href="<?php echo $product_link.'?product_id='.$product_id.'&variation_id='.$variation_id; ?>" class="gcpb-bottom-content__content js-product-link" >
                    <!-- <div class="gcpb-bottom-content__content"> -->
                    <h3 class="gcpb-bottom-content__title"><?php the_title(); ?></h3>
                    <div class="gcpb-product-price">
                        <?php
                        if($default_variation_sale_price > 0) : ?>
                            <del><?php echo wc_price($default_variation_regular_price); ?>+</del>
                            <bdi><?php echo wc_price($default_variation_sale_price); ?></bdi>
                        <?php else: ?>
                            <bdi><?php echo wc_price($default_variation_regular_price); ?></bdi>
                        <?php endif; ?>
                    </div>+
                </a>
                    <?php
                    
                    
                    $stone_archive_page_url_data = get_permalink(otw_woo_ring_builder()->get_option('stone_archive_page'));
                    $checkout_complete_page_url_data = get_permalink(otw_woo_ring_builder()->get_option('checkout_complete_page'));

                    if(gcpb_get_current_first_step() == 'stone'){
                        $link_text = 'Select this setting';
                        $stone_archive_page_url = $checkout_complete_page_url_data;
                
                    }else{
                        $link_text = 'Add Diamond >';
                        $stone_archive_page_url = $stone_archive_page_url_data;
                    }
                    $query_args_array = array('product_id' => $product_id, 'variation_id' => $variation_id);
                    $diamond_stock_number = '';
                    if(isset($_GET['stock_num']) && $_GET['stock_num']){
                        $diamond_stock_number = $_GET['stock_num'];
                        $link_text = 'Complete your ring';
                        $stone_archive_page_url = $checkout_complete_page_url_data;
                        $query_args_array['stock_num'] = $_GET['stock_num'];
                    }

                    
                    // $stone_archive_page_url = add_query_arg($query_args_array, $stone_archive_page_url);
                    ?>
                    <a href="<?php echo $product_link.'?product_id='.$product_id.'&variation_id='.$variation_id; ?>" class="gcpb-view-more js-product-link product-slide-link">View More</a>
                    <a href="<?php echo $stone_archive_page_url; ?>" class="gcpb-view-more gcpb-select-setting-toggle otw_select_diamond_dynamic_url" data-complete-url-link="<?php echo $checkout_complete_page_url_data; ?>" data-stone-url-link="<?php echo $stone_archive_page_url_data; ?>" data-stone-url="<?php echo $stone_archive_page_url; ?>" data-product_id="<?php echo $product_id; ?>" data-variation_id="<?php echo $variation_id; ?>" data-stock_num="<?php echo $diamond_stock_number; ?>" data-link_text_complete="Complete your ring" data-link_text="Add Diamond >"><?php echo $link_text; ?></a>
                    <!-- <p class="gcpb-view-setting">View Setting</p> -->
                <!-- </div> -->
            </div>
            
            
            <div class="gcpb-prod-card__hidden-content">
                <div class="gcpb_prod_card_hidden_media_data" style="display:none;" ><?php echo $gallery_images_output; echo $variation_video_output; ?></div>
                <?php 
                    do_action( 'gcpb_product_loop_attributes', $attributes, $default_attributes );

                    // // $stone_archive_page_url = get_permalink(otw_woo_ring_builder()->get_option('stone_archive_page'));
                    // $stone_archive_page_url = add_query_arg(array('product_id' => $product_id, 'variation_id' =>$variation_id), $stone_archive_page_url);

                    // if(gcpb_get_current_first_step() == 'stone'){
                    //     $link_text = 'Select this setting';
                    //     $stone_archive_page_url = get_permalink(otw_woo_ring_builder()->get_option('checkout_complete_page'));
                        
                    // }else{
                    //     $link_text = 'Add Diamond >';
                    //     $stone_archive_page_url = get_permalink(otw_woo_ring_builder()->get_option('stone_archive_page'));
                    // }
                    // // $query_args_array = array('product_id' => $product_id, 'variation_id' => $variation_id);
                    // $diamond_stock_number = '';
                    // if(isset($_GET['stock_num']) && $_GET['stock_num']){
                    //     $diamond_stock_number = $_GET['stock_num'];
                    //     // $query_args_array['stock_num'] = $_GET['stock_num'];
                    // }
                    // // $stone_archive_page_url = add_query_arg($query_args_array, $stone_archive_page_url);

                ?>
                <!-- <a href="<?php echo $stone_archive_page_url; ?>" class="gcpb-select-setting-toggle gcpb-button gcpb-button-alt otw_select_diamond_dynamic_url" data-stone-url="<?php echo $stone_archive_page_url; ?>" data-product_id="<?php echo $product_id; ?>" data-variation_id="<?php echo $variation_id; ?>" data-stock_num="<?php echo $diamond_stock_number; ?>"><?php echo $link_text; ?></a>
                <a href="<?php echo $product_link.'?product_id='.$product_id.'&variation_id='.$variation_id; ?>" class="gcpb-button gcpb-light-btn js-product-link" >More Info</a> -->
                <!-- <button class="gcpb-select-setting-toggle gcpb-button gcpb-button-alt">Add Center Stone</button>-->
            </div>
        </div>
    </li>
<?php else: ?>
    <li class="gcpb-product-card gcpb-product-card-setting" tabindex="0">
        <div class="gcpb-product-card-wrapper">
            <div class="gcpb-prod-card__tag">Low Profile</div>
            <!-- <button class="gcpb-mobile-only gcpb-button gcpb-card-toggle-icon"></button> -->
            <div class="gcpb-prod-card__images">
                <?php the_post_thumbnail(); ?>
                <img class="gcpb-three-d-image" src="/wp-content/themes/hello-theme-child-master/assets/imgs/ring2.jpg" alt=""></a>
            </div>
                    
            <div class="gcpb-bottom-content__content">
                <h3 class="gcpb-bottom-content__title"><?php the_title(); ?></h3>
                <div class="gcpb-product-price">
                    <?php echo $product->get_price_html(); ?>
                </div>
            </div>
            <div class="gcpb-prod-card__hidden-content">
                
                <div class="gcpb-bottom-content__buttons">
                    <a href="/setting-single/" class="gcpb-button gcpb-light-btn" >More Info</a>
                    <button class="gcpb-select-setting-toggle gcpb-button gcpb-button-alt" data-product-id="948732">Add Center Stone</button>
                </div>
            </div>
        </div>
</li>
<?php endif; ?>