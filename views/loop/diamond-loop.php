<?php
/******************************************/
/***** SerializeStringToArray **********/
/******************************************/
if(!function_exists("get_looped_diamond_html")){
  function get_looped_diamond_html($diamond){
    $video_url = '';
    if(isset($diamond['video_url']) && $diamond['video_url']){
        $args = array('autospin' => 'infinite', 'fullscreen' => 'false', 'btn' => 0);
        $diamond['video_url'] = add_query_arg($args, $diamond['video_url']);
    }
    $stone_single_page_url = gcpb_stone_single_page();
    $stone_single_page_url_full = add_query_arg(array('stock_num' => $diamond['stock_num']), $stone_single_page_url);

    // $checkout_complete_page_url = gcpb_checkout_complete_page();

    if(gcpb_get_current_first_step() == 'stone'){
        $checkout_complete_page_url = get_permalink(otw_woo_ring_builder()->get_option('gcpb_listing_page'));
    }else{
        $checkout_complete_page_url = get_permalink(otw_woo_ring_builder()->get_option('checkout_complete_page'));
    }
    $checkout_complete_page_url_full = gcpb_add_cookies_query_args($checkout_complete_page_url);
    $checkout_complete_page_url_full = add_query_arg(array('stock_num' => $diamond['stock_num']), $checkout_complete_page_url_full);
    $diamond_shape = $diamond['shape'];
    if(isset($diamond['shape_api']) && $diamond['shape_api'])
        $diamond_shape = $diamond['shape_api'];

    $orig_sales_price = 0;
    if(isset($diamond['orig_sales_price']) && $diamond['orig_sales_price'])
        $orig_sales_price = $diamond['orig_sales_price'];

    // $single_stone_page_url = add_query_arg(array(/*'diamond_id' => $diamond['id'], */'stock_num' => $diamond['stock_num']), trailingslashit(home_url()).'stone-single/');
    // $complete_setting_page_url = add_query_arg(array(/*'diamond_id' => $diamond['id'], */'stock_num' => $diamond['stock_num']), trailingslashit(home_url()).'complete/');
    $output = '<div class="gcpb-product-card gcpb-product-card-stone" data-3d-model-id="'.$diamond['id'].'" data-3d-model="'.$diamond['video_url'].'" data-stock_num="'.$diamond['stock_num'].'">
                <div class="gcpb-product-card-wrapper">
                    <button class="gcpb-mobile-only gcpb-button gcpb-card-toggle-icon"></button>
                    <div class="gcpb-prod-card__images">
                        <img class="gcpb-first-image" src="'. $diamond['image_url'].'" alt=""></a>
                    </div>
                    <div class="gcpb-product-card-content">
                        <h3 class="gcpb-prod-card__title">'.$diamond['size'].'<span>ct</span> <span>'.$diamond_shape.'</span></h3>
                        <div class="gcpb-product-price">
                            <!-- <del class="gcpb-del">$700</del> -->
                            <bdi class="gcpb-sales-price" data-total_price_orig="'.$orig_sales_price.'">'.wc_price($diamond['total_sales_price']).'</bdi>
                        </div>
                        <div class="gcpb-diamond-data">
                            <div>Carat: <span class="gcpb-diamond-data-value">'.$diamond['size'].'</span></div>
                            <div>Color: <span class="gcpb-diamond-data-value">'.$diamond['color'].'</span></div>
                            <div>Clarity: <span class="gcpb-diamond-data-value">'.$diamond['clarity'].'</span></div>
                            <div>Cut: <span class="gcpb-diamond-data-value">'.$diamond['symmetry'].'</span></div>
                            <div>Dimensions: <span class="gcpb-diamond-data-value">'.$diamond['meas_length'].'/'.$diamond['meas_width'].'</span></div>
                            <div>Ratio: <span class="gcpb-diamond-data-value">'.$diamond['meas_ratio'].'</span></div>
                            <!-- <div>Lab: <span class="gcpb-diamond-data-value">'.$diamond['lab'].'</span></div>-->
                        </div>
                    </div>
                    <div class="gcpb-prod-card__bottom-content">
                        <a href="'.$stone_single_page_url_full.'" class="gcpb-button gcpb-light-btn" >More Info</a>
                        <a href="'.$checkout_complete_page_url_full.'" class="gcpb-button gcpb-button-alt">Select Diamond</a>
                    </div>
                    <div class="gcpb-mobile-content gcpb-mobile-only">
                        <div class="gcpb-image-wrapper"></div>
                    </div>
                </div>
            </div>';
            return $output;
  }
}