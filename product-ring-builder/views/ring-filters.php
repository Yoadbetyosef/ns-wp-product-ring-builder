<?php 
$gcpb_filters = get_option( 'gcpb_filter_attributes' );
 
$required_attributes = array(
    'eo_metal_attr',
    'ring-style',
    'shape'
);

//$attributes = wc_get_attribute_taxonomies(); 
// $filters = array();

// if( !empty($attributes) ) {
//     foreach( $attributes as $attribute ) {

//         if(in_array($attribute->attribute_name,$required_attributes)) {
//             $filters[$attribute->attribute_name] = get_terms( array('taxonomy' => 'pa_' . $attribute->attribute_name, 'fields' => 'all' ) );
//         }
//     }
// }
//echo '<pre>'; print_r($filters); echo '</pre>'; ?>
<!-- START SETTING FILTERS -->
<div class="gcpb-filters-container">
    <div class="gcpb-custom-filters-wrapper gcpb-setting-filters">
        <button class="gcpb-filter-reset-btn gcpb-filter-reset-btn-setting gcpb-mobile-hidden">Clear Filters</button>
        <!-- <button class="gcpb-mobile-filter-btn gcpb-mobile-filter-toggle gcpb-mobile-only">Filters</button> -->
        <div class="gcpb-custom-filters">
            <!-- <div class="gcpb-mobile-filters-actions gcpb-mobile-only">
                <div class="gcpb-filter-reset-btn"></div>
                <div class="gcpb-mobile-filter-toggle gcpb-top-line"></div>
                <div class="gcpb-mobile-filter-toggle gcpb-close-btn"></div>
            </div> -->
            <div class="gcpb-mobile-active-filters__wrapper">
                <div class="gcpb-mobile-active-filters gcpb-mobile-only">
                    Shown with: <span class="gcpb-active-shape"></span><span class="gcpb-active-metal">| </span><span class="gcpb-active-style">| </span>
                </div>
                <button class="gcpb-filter-reset-btn gcpb-filter-reset-btn-setting gcpb-mobile-only">Clear Filters</button>
            </div>
            <div class="gcpb-filters-wrapper">
                <?php if(!empty($gcpb_filters)): 
                    foreach( $gcpb_filters as $gcpb_filter ): 

                        if(isset($gcpb_filter['main-attribute']) && !empty($gcpb_filter['main-attribute'])) :

                            $attribute_slug       = $gcpb_filter['main-attribute'];
                            $attribute_taxonomy   = 'pa_' . $attribute_slug; 
                            $is_filter = (isset($gcpb_filter['is_filter']) && $gcpb_filter['is_filter'] == 'yes') ? 'yes': 'no'; ?>

                            <div class="gcpb-filter-container gcpb-<?php echo $attribute_slug; ?>-filter-container" tabindex="0">
                                <div class="gcpb-filter-wrapper">
                                    <div class="gcpb-filter-title">
                                        <?php echo wc_attribute_label( $attribute_taxonomy ); ?>:
                                        <span class="gcpb-selected-item"></span>
                                    </div>

                                    <?php if(isset($gcpb_filter['gcpb-sub-attributes']) && !empty($gcpb_filter['gcpb-sub-attributes'])):  ?>
                                        <div class="prb-filter gcpb-filter gcpb-scrollbar gcpb-horizontal">

                                            <?php foreach( $gcpb_filter['gcpb-sub-attributes'] as $term ): 
                                                 $metal_filter = get_term_by('id', $term['sub-attribute'] , $attribute_taxonomy); 
                                                
                                             //   print_r($metal_filter); ?>
                                                <button type="button" class="gcpb-custom-filter-button gcpb-active" data-filter-items="<?php echo $is_filter; ?>" data-filter-name="<?php echo $attribute_slug; ?>" data-filter-value="<?php echo $metal_filter->slug; ?>" data-filter-display-value="<?php echo $metal_filter->name; ?>">
                                                    <?php $textureImg = wp_get_attachment_image_src( $term['gcpb_attribute_icon'] ); ?>
                                                    <div class="gcpb-custom-filter-button-popup-text" ><?php echo $metal_filter->name; ?></div>
                                                    <img class="gcpb-custom-filters-button-icon" src="<?php echo $textureImg[0]; ?>" alt="<?php echo $metal_filter->name; ?>">
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif;
                    endforeach;
                endif; ?>
             </div>

        </div>
        <!-- <div class="gcpb-sort-by">
            <div class="gcpb-selected-sort-bys ">
                <div class="gcpb-filter-title">Sort by:</div>
                <div class="gcpb-select" data-collapsible-container="">
                    <button type="button" class="gcpb-select-button gcpb-sorting-button">
                        <div class="gcpb-select-button-texts">
                        <div class="gcpb-select-button-selected-value">Best Selling</div>
                        </div>
                        <div class="gcpb-select-button-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                        </svg>
                        </div>
                    </button>

                    <div class="gcpb-select-options" data-collapsible-content="" tabindex="0">
                        <button type="button" class="gcpb-select-option" data-select-option="" data-value="price-ascending" data-display-value="Price (low-to-high)">
                        Price (low-to-high)
                        </button>
                        <button type="button" class="gcpb-select-option" data-select-option="" data-value="price-descending" data-display-value="Price (high-to-low)">
                        Price (high-to-low)
                        </button>
                        <button type="button" class="gcpb-select-option selected" data-select-option="" data-value="best-selling" data-display-value="Best Selling">
                        Best Selling
                        </button></div>
                </div>
                <div class="gbpb-columns-count">
                    <img src="/wp-content/themes/hello-theme-child-master/assets/images/column-grid-icon.png" alt="columns grid" class="gcpb-columns-grid-icon">
                    <div class="gcpb-columns-amounts">
                        <button class="gcpb-column-amount" data-columns="2">2</button>
                        <button class="gcpb-column-amount" data-columns="3">3</button>
                        <button class="gcpb-column-amount" data-columns="4">4</button>
                        <button class="gcpb-column-amount active selected" data-columns="5">5</button>
                    </div>
                </div>
            </div>
        </div> -->
    </div>
</div>
<!-- END SETTING FILTERS -->
