<!-- START PRODUCT LOOP -->
<?php
$data_settings = '';

if(!empty($args)) {
    $data_settings = " data-settings='".json_encode($args)."'";
} ?>
<ul class="gcpb-product-archive-grid js-product-archive-grid gcpb-preload"<?php echo $data_settings; ?>></ul>

<div class="gcpb-type-of-stone-popup" data-product-id="948732">
    <div class="gcpb-type-of-stone-popup-wrapper">
        <button class="gcpb-close-icon gcpb-select-setting-toggle"> <img src="/wp-content/themes/hello-theme-child-master/assets/images/close-icon.png"></button>
        <button class="gcpb-button gcpb-alt-btn gcpb-stone-selection-btn" data-stone-type="natural-diamond"><a href="/select-stone/">Natural Diamond</a></button>
        <button class="gcpb-button gcpb-alt-btn gcpb-stone-selection-btn" data-stone-type="lab-diamond">Lab Diamond</button>
    </div>
</div>
<!-- END PRODUCT LOOP -->