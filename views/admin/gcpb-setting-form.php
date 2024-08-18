<div class="wrap">
    <h2><?php _e('GCP Builder Settings'); ?></h2>

    <?php 
        // echo '<pre>'; 
        // print_r($_POST); 
        // print_r(get_option('gcpb_products_per_page'));

        // print_r(get_option('gcpb_filter_attributes'));
        // echo '</pre>';
     ?>

    <!-- <form method="post" action="https://wordpress-848560-3382448.cloudwaysapps.com/wp-admin/admin.php?page=gcpb-setting"> -->
    <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
        <table class="form-table">
        <?php 
        // echo '<pre>'; print_r($options); echo '</pre>';
        $settings = Ring_Builder_Setting::instance();
        foreach( $options as $field ) {
            $settings->gcpb_form_fields($field);
        } ?>
        </table>
        <input type="hidden" name="action" value="gcpb_process_setting">
        <input type="hidden" name="page" value="gcpb-setting">
        <?php submit_button(); ?>
    </form>
</div>