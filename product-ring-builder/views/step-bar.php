<!-- START STEP BAR -->
<?php
    
    $checkout_complete_page = otw_woo_ring_builder()->get_option('checkout_complete_page');

    $diamond_total = 0;
    $setting_step_title = 'Select your <span>Setting</span>';
    $diamond_step_title = 'Select your <span>Diamond</span>';
    otw_woo_ring_builder()->diamonds->get_current_diamond();
    if(otw_woo_ring_builder()->diamonds && isset(otw_woo_ring_builder()->diamonds->current_diamond) && otw_woo_ring_builder()->diamonds->current_diamond){
        $diamond = otw_woo_ring_builder()->diamonds->current_diamond;
        $diamond_total = $diamond['total_sales_price'];
        $diamond_step_title = $diamond['shape'].'<span>'.$diamond['size'].' Carat '.$diamond['color'].' '.$diamond['clarity'].'</span>';

        // if(get_client_ip() == '182.178.244.62'){
        //     db($diamond);
        //   }
    }
    
    otw_woo_ring_builder()->get_current_selected_variation_shape();
    $setting_total = 0;
    if(isset(otw_woo_ring_builder()->woo) && otw_woo_ring_builder()->woo && isset(otw_woo_ring_builder()->woo->current_selected_variation) && otw_woo_ring_builder()->woo->current_selected_variation){
        $setting_total = otw_woo_ring_builder()->woo->current_selected_variation->get_price();
        $setting_step_title = otw_woo_ring_builder()->woo->current_selected_variation->get_title().' <span>'.otw_woo_ring_builder()->woo->current_selected_variation->get_attribute( 'pa_eo_metal_attr' ).'</span>';
        
    }
    
    $ring_total = 0;
    if($setting_total)
        $ring_total += $setting_total;
    if($diamond_total)
        $ring_total += $diamond_total;

if(!function_exists("get_step_bar_setting_step")){
    function get_step_bar_setting_step($step_count = 1, $setting_total, $setting_step_title) {
        $gcpb_listing_page = otw_woo_ring_builder()->get_option('gcpb_listing_page');
        $gcpb_product_page = otw_woo_ring_builder()->get_option('gcpb_product_page');
        
        $gcpb_product_page_full_url = get_permalink($gcpb_product_page);
        $gcpb_product_page_full_url = gcpb_add_cookies_query_args($gcpb_product_page_full_url);

        // $setting_reset_page_url = get_permalink($gcpb_listing_page);
        $setting_reset_page_url = add_query_arg(array('setting_data' => 'reset_setting'), get_permalink($gcpb_listing_page));

        $gcpb_active = '';
        $step_status = 'to-do';
        $extra_classes = ' gcpb-step-not-completed';
        
        if($setting_total){
            $step_status = 'completed';
            $extra_classes = ' gcpb-step-completed';
        }
        if(is_page($gcpb_listing_page) || is_page($gcpb_product_page)){ 
            $gcpb_active = 'gcpb-active';
            $step_status = 'current';
        }
        
        ?>
        <div class="gcpb-step gcpb-step<?php echo $step_count?> <?php echo $gcpb_active.$extra_classes; ?>" data-step-status="<?php echo $step_status; ?>">
            <span class="gcpb-step__number"><?php echo $step_count?></span>
            <a href="<?php echo gcpb_listing_page(); ?>" class="gcpb-step__title_link">
            <div class="gcpb-step__title">
                <?php echo $setting_step_title; ?>
            </div>
            </a>
            <?php if($setting_total){ ?>
            <div class="gcpb-step__right-side">
                <div class="gcpb-step__price"><?php echo wc_price($setting_total); ?></div>
                <div class="gcpb-step__action-btns">
                    <a href="<?php echo $gcpb_product_page_full_url; ?>" class="gcpb-view-btn">View</a>
                    <a href="<?php echo $setting_reset_page_url; ?>" class="gcpb-remove-btn">Remove</a>
                </div>
            </div>
            <?php } ?>
            <img src="/wp-content/plugins/product-ring-builder/assets/img/step-setting-icon.png" class="gcpb-step__image" alt="">
        </div>
        <?php
    }
}
if(!function_exists("get_step_bar_stone_step")){
    function get_step_bar_stone_step($step_count = 1, $diamond_total, $diamond_step_title) {
        $stone_archive_page = otw_woo_ring_builder()->get_option('stone_archive_page');
        $stone_single_page = otw_woo_ring_builder()->get_option('stone_single_page');
        $stone_single_page_full_url = get_permalink($stone_single_page);

        $stone_single_page_full_url = gcpb_add_cookies_query_args($stone_single_page_full_url);

        $stone_single_page_full_url_reset = add_query_arg(array('setting_data' => 'reset_diamond'), get_permalink($stone_archive_page));

        $gcpb_active = '';
        $step_status = 'to-do';
        $extra_classes = ' gcpb-step-not-completed';
        
        if($diamond_total){
            $step_status = 'completed';
            $extra_classes = ' gcpb-step-completed';
        }
        if(is_page($stone_archive_page) || is_page($stone_single_page)){
            $gcpb_active = 'gcpb-active';
            $step_status = 'current';
        }

        ?>
        <div class="gcpb-step gcpb-step<?php echo $step_count?> <?php echo $gcpb_active.$extra_classes; ?>" data-step-status="<?php echo $step_status; ?>">
            <span class="gcpb-step__number"><?php echo $step_count?></span>
            <a class="gcpb-step__title gcpb-step__title_link" href="<?php echo get_permalink($stone_archive_page); ?>">
                <?php echo $diamond_step_title; ?>
            </a>
            <?php if($diamond_total){ ?>
            <div class="gcpb-step__right-side">
                <div class="gcpb-step__price"><?php echo wc_price($diamond_total); ?></div>
                <div class="gcpb-step__action-btns">
                    <a href="<?php echo $stone_single_page_full_url; ?>" class="gcpb-view-btn">View</a>
                    <a href="<?php echo $stone_single_page_full_url_reset; ?>" class="gcpb-remove-btn">Remove</a>
                </div>
            </div>
            <?php } ?>
            <img src="/wp-content/plugins/product-ring-builder/assets/img/step-diamond-icon.png" class="gcpb-step__image" alt="">

        </div>
        <?php
    }
}
?>
<div class="gcpb-step-bar">
    
    <?php
    
        if(gcpb_get_current_first_step() == 'stone'){
            get_step_bar_stone_step(1, $diamond_total, $diamond_step_title);
            get_step_bar_setting_step(2, $setting_total, $setting_step_title);
        }
        else{
            get_step_bar_setting_step(1, $setting_total, $setting_step_title);
            get_step_bar_stone_step(2, $diamond_total, $diamond_step_title);
        }

        $third_step_url = '';
        if($setting_total && $diamond_total)
            $third_step_url = get_permalink($checkout_complete_page);
    ?>

    
    <div class="gcpb-step gcpb-step3 <?php if(is_page($checkout_complete_page)){ echo 'gcpb-active'; } ?>" data-step-status="to-do" data-url='<?php echo $third_step_url; ?>'>
        <span class="gcpb-step__number">3</span>
        <div class="gcpb-step__title">
            Complete your <span>Ring</span>
        </div>
        <div class="gcpb-step__right-side">
            <div class="gcpb-step__price"><?php echo wc_price($ring_total); ?></div>
            <div class="gcpb-step__action-btns">
                <a href="#" class="gcpb-view-btn">View</a>
                <a href="#" class="gcpb-remove-btn">x</a>
            </div>
        </div>
        <img src="/wp-content/plugins/product-ring-builder/assets/img/step-complete-icon.png" class="gcpb-step__image" alt="">
    </div>

</div>
<!-- END STEP BAR -->