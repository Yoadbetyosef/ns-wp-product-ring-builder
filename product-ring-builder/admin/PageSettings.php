<?php
namespace OTW\WooRingBuilder\Admin;

// exit if file is called directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PageSettings extends \OTW\WooRingBuilder\Plugin{

  use \OTW\GeneralWooRingBuilder\Traits\Singleton;
  use \OTW\GeneralWooRingBuilder\Traits\AdminNotices;

  public function __construct(){

    add_action('init', array($this, 'input_handle'));
    add_action( 'admin_menu', array($this,'admin_menu'), 100);

  }// construct function end here

  /******************************************/
  /***** page_bboptions_admin_menu function start from here *********/
  /******************************************/
  public function admin_menu(){
    
    /* add sub menu in our wordpress dashboard main menu */
    //add_menu_page(__('GCP Builder General Setting', 'otw-woo-ring-builder-td'), __('GCP Builder General Setting', 'otw-woo-ring-builder-td'), 'manage_options', $this->prefix, array($this,'add_submenu_page') );
    // db($this->prefix);exit();
    
    add_submenu_page('gcpb-setting', __('General Settings', 'otw-woo-ring-builder-td'), __('General Setting', 'otw-woo-ring-builder-td'), 'manage_options', $this->prefix, array($this,'add_submenu_page') );
  }

  /******************************************/
  /***** add_submenu_page_bboptions function start from here *********/
  /******************************************/
  public function add_submenu_page(){ ?>
    <div class="wrap bytebunch_admin_page_container">
      <div id="icon-tools" class="icon32"></div>
      <div id="poststuff">
	      <?php $this->page_tabs(); ?>          
      </div><!-- poststuff-->
    </div><!-- wrap-->
    <?php 
	//$this->bbwp_flipswitch_css();
  }

  /******************************************/
  /***** default_tab_html function start from here *********/
  /******************************************/
  public function default_tab_html(){ ?>
	<div id="postbox-container" class="postbox-container">
		<form action="" method="post">
		<?php wp_nonce_field($this->prefix('nonce'), $this->prefix('nonce')); ?>
		  <div class="meta-box-sortables ui-sortable">

			<div class="postbox" id="<?php echo $this->prefix('vue_app'); ?>">
			  <div class="postbox-header">                    
				<h3 class="hndle ui-sortable-handle"><span><?php _e('GCP Builder General Setting', 'otw-woo-ring-builder-td'); ?></span></h3>
				<div class="handle-actions hide-if-no-js">
				  <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Author</span><span class="toggle-indicator" aria-hidden="true"></span></button>                    
				</div>
			  </div><!-- postbox-header-->
			  <div class="inside">
          <?php
            $fields = \OTW\GeneralWooRingBuilder\Fields::instance();
            echo $fields->displaytype['wrapper_open'];
            $selected_values = array();
            foreach($this->get_page_fields() as $field){
              echo $fields->{$field['field_type']}($field, $this->get_option($field['meta_key']));
            }
            echo $fields->displaytype['wrapper_close'];
					?>
			  </div><!-- inside-->
			</div><!-- postbox-->


      <div class="postbox" id="<?php echo $this->prefix('vue_app_diamond_api'); ?>">
			  <div class="postbox-header">                    
				<h3 class="hndle ui-sortable-handle"><span><?php _e('Diamond APIs', 'otw-woo-ring-builder-td'); ?></span></h3>
				<div class="handle-actions hide-if-no-js">
				  <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Author</span><span class="toggle-indicator" aria-hidden="true"></span></button>                    
				</div>
			  </div><!-- postbox-header-->
			  <div class="inside">
          <?php
            $fields = \OTW\GeneralWooRingBuilder\Fields::instance();
            echo $fields->displaytype['wrapper_open'];
            $selected_values = array();
            foreach($this->get_diamond_api_fields() as $field){
              // echo $fields->{$field['field_type']}($field, $this->get_option($field['meta_key']));
              if($field['meta_key'] == 'nivoda_api_auth_token')
                $output = $fields->{$field['field_type']}($field, get_option($field['meta_key']), 'vue');
              else
                $output = $fields->{$field['field_type']}($field, $this->get_option($field['meta_key']), 'vue');
              echo $output['html'];
              $selected_values[$field['meta_key']] = $output['selected'];

            }
            echo $fields->displaytype['wrapper_close'];
					?>
			  </div><!-- inside-->
			</div><!-- postbox-->




		  </div><!-- meta-box-sortables-->
		  <?php submit_button('Save Changes'); ?>
		</form>
	  </div><!-- postbox-container-->
  <?php
  $script_abs_path = plugin_dir_path(OTW_WOO_RING_BUILDER_PLUGIN_FILE). 'assets/admin/js/vue-app.js';
  wp_enqueue_script($this->prefix('vue_app'), plugin_dir_url(OTW_WOO_RING_BUILDER_PLUGIN_FILE) . 'assets/admin/js/vue-app.js', array('jquery', 'vue3'), get_file_time($script_abs_path));
  wp_localize_script($this->prefix('vue_app'), $this->prefix('vue_app_diamond_api_svalues'), $selected_values);  
  
  }

  /******************************************/
  /***** page_tabs function start from here *********/
  /******************************************/
  public function page_tabs($is_tabs = false){
    
      $default_tab = null;
      $tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
      
      if($is_tabs){ ?>
        <nav class="nav-tab-wrapper" style="margin-bottom:20px;">
          <a href="?page=<?php echo $this->prefix; ?>" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>"><?php _e('Default Tab', 'otw-woo-ring-builder-td'); ?></a>
          <a href="?page=<?php echo $this->prefix; ?>&tab=settings" class="nav-tab <?php if($tab==='settings'):?>nav-tab-active<?php endif; ?>"><?php _e('Settings', 'otw-woo-ring-builder-td'); ?></a>
          <a href="?page=<?php echo $this->prefix; ?>&tab=tools" class="nav-tab <?php if($tab==='tools'):?>nav-tab-active<?php endif; ?>"><?php _e('Tools', 'otw-woo-ring-builder-td'); ?></a>
        </nav>
      <?php
      }

      if($tab === null || $tab == '')
        $this->default_tab_html();
      elseif($tab == 'settings' && $is_tabs)
        $this->default_tab_html();   
  }

  /******************************************/
  /***** input_handle function start from here *********/
  /******************************************/
  public function input_handle(){
    
    if(!current_user_can('manage_options'))
      return false;

    if(!(isset($_GET['page']) && $_GET['page'] === $this->prefix))
      return false;
    
    if(isset($_GET['get_new_nivoda_auth_token']) && $_GET['get_new_nivoda_auth_token'] == 'yes')
      otw_woo_ring_builder()->nivoda_diamonds->get_auth_token();
    
    if(isset($_POST[$this->prefix('nonce')]) && wp_verify_nonce($_POST[$this->prefix('nonce')], $this->prefix('nonce'))){
      
      $metabox = \OTW\GeneralWooRingBuilder\MetaBox::instance();
      $metabox->Set('saveType', \OTW\WooRingBuilder\Plugin::instance());
      $metabox->Set('skipSaving', array('nivoda_api_auth_token', 'nivoda_api_get_auth_token'));
      $metabox->SaveOptions($this->get_page_fields(), 'verified');
      $metabox->SaveOptions($this->get_diamond_api_fields(), 'verified');

      // $repeater_fields = $this->get_repeater_fields();
      // $repeater = \OTW\GeneralWooRingBuilder\FieldsRepeater::instance();
      // $repeater->Save($repeater_fields, $this->prefix('repeater_setting'), 'verified');
      
      add_action( 'admin_notices', [ $this, 'admin_notices' ] );
    }


  } // input handle function end here

  /******************************************/
  /***** pageURL function start from here *********/
  /******************************************/
  public function pageURL($param = array()){
    $default_values = array('page' => $this->prefix);
    $param = array_merge($default_values, $param);
    return add_query_arg( $param, admin_url( 'admin.php' ) );
    // return add_query_arg($param, get_admin_url(null, 'admin.php?page='.$this->prefix));
  }

  /******************************************/
  /***** pageURL function start from here *********/
  /******************************************/
  public function get_pages_list() {
		$pages = get_pages(); 
		$page_object = array();

    foreach ( $pages as $page ) {
			$page_object[$page->ID] = $page->post_title;
		}

		return $page_object;
	}

  public function get_product_categories(){
    $terms = get_terms( array(
      'taxonomy'   => 'product_cat',
      'hide_empty' => false,
    ));
    $output = array('' => '--Select--');
    // db($output);
    if($terms && is_array($terms) && count($terms) >= 1){
      foreach($terms as $single_term){
        // db($single_term);
        $output[$single_term->term_id] = $single_term->name;
      }
      
    }
    return $output;
  }

  /******************************************/
  /***** get_page_fields function start from here *********/
  /******************************************/
  public function get_page_fields(){
    return array(
      
      'gcpb_listing_page' => array(
        'meta_key' => 'gcpb_listing_page',
        'field_title' => 'Product Archive Page',
        'field_type' => 'select',
        'field_type_values' => $this->get_pages_list()
      ),
      'gcpb_product_page' => array(
        'meta_key' => 'gcpb_product_page',
        'field_title' => 'Single Product Page',
        'field_type' => 'select',
        'field_type_values' => $this->get_pages_list()
      ),
      'stone_archive_page' => array(
        'meta_key' => 'stone_archive_page',
        'field_title' => 'Stone Archive Page',
        'field_type' => 'select',
        'field_type_values' => $this->get_pages_list()
      ),
      'stone_single_page' => array(
        'meta_key' => 'stone_single_page',
        'field_title' => 'Single Stone Page',
        'field_type' => 'select',
        'field_type_values' => $this->get_pages_list()
      ),
      'checkout_complete_page' => array(
        'meta_key' => 'checkout_complete_page',
        'field_title' => 'Checkout Complete Page',
        'field_type' => 'select',
        'field_type_values' => $this->get_pages_list()
      ),
      'setting_category' => array(
        'meta_key' => 'setting_category',
        'field_title' => 'Settings Category',
        'field_type' => 'select',
        'field_type_values' => $this->get_product_categories()
      ),
      'setting_sizes' => array(
        'meta_key' => 'setting_sizes',
        'field_title' => 'Settings Sizes',
        'field_type' => 'textarea',
        'field_disable_autop' => 'on',
      ),
      // 'diamond_price_percentage' => array(
      //   'meta_key' => 'diamond_price_percentage',
      //   'field_title' => 'Diamond Price Percentage',
      //   'field_type' => 'number',
      // ),
      'diamond_price_filter_min_value' => array(
        'meta_key' => 'diamond_price_filter_min_value',
        'field_title' => 'Diamond Price Filter Min Value',
        'field_type' => 'number',
      ),
      'diamond_price_filter_max_value' => array(
        'meta_key' => 'diamond_price_filter_max_value',
        'field_title' => 'Diamond Price Filter Max Value',
        'field_type' => 'number',
      ),
      
    );

  } // get_page_fields function end here


  /******************************************/
  /***** get_page_fields function start from here *********/
  /******************************************/
  public function get_diamond_api_fields(){
    return array(
      'vdb_api' => array(
        'meta_key' => 'vdb_api',
        'field_title' => 'Enable VDB API',
        'field_type' => 'flipswitch',
      ),
      'diamond_api_key' => array(
        'meta_key' => 'diamond_api_key',
        'field_title' => 'Diamond API Key',
        'field_type' => 'text',
        'vue_conditions' => array('vdb_api' => '1'),
      ),
      'diamond_api_token' => array(
        'meta_key' => 'diamond_api_token',
        'field_title' => 'Diamond API Token',
        'field_type' => 'text',
        'vue_conditions' => array('vdb_api' => '1'),
      ),
      'vdb_price_percentage' => array(
        'meta_key' => 'vdb_price_percentage',
        'field_title' => 'Markup Price Percentage',
        'field_type' => 'number',
        'vue_conditions' => array('vdb_api' => '1'),
      ),
      'vdb_api_order' => array(
        'meta_key' => 'vdb_api_order',
        'field_title' => 'Order',
        'field_type' => 'select',
        'field_type_values' => array('1' => '1', '2' => '2'),
        'vue_conditions' => array('vdb_api' => '1'),
      ),

      'nivoda_api' => array(
        'meta_key' => 'nivoda_api',
        'field_title' => 'Enable Nivoda API',
        'field_type' => 'flipswitch',
      ),
      'nivoda_api_username' => array(
        'meta_key' => 'nivoda_api_username',
        'field_title' => 'Username',
        'field_type' => 'text',
        'vue_conditions' => array('nivoda_api' => '1'),
      ),
      'nivoda_api_password' => array(
        'meta_key' => 'nivoda_api_password',
        'field_title' => 'Password',
        'field_type' => 'password',
        'vue_conditions' => array('nivoda_api' => '1'),
      ),
      'nivoda_api_auth_token' => array(
        'meta_key' => 'nivoda_api_auth_token',
        'field_title' => 'Auth Token',
        'field_type' => 'text',
        'vue_conditions' => array('nivoda_api' => '1'),
      ),
      'nivoda_api_get_auth_token' => array(
        'meta_key' => 'nivoda_api_get_auth_token',
        'field_title' => 'Get New Auth Token',
        'field_type' => 'plain_html',
        'html' => '<a href="'.$this->pageURL(array('get_new_nivoda_auth_token' => 'yes')).'" class="button button-default button-hero sf_login">Get New Auth Token</a>',
        'vue_conditions' => array('nivoda_api' => '1'),
      ),
      'nivoda_api_environment' => array(
        'meta_key' => 'nivoda_api_environment',
        'field_title' => 'Environment',
        'field_type' => 'select',
        'field_type_values' => array('staging' => 'Staging', 'production' => 'Production'),
        'vue_conditions' => array('nivoda_api' => '1'),
      ),
      // 'nivoda_price_percentage' => array(
      //   'meta_key' => 'nivoda_price_percentage',
      //   'field_title' => 'Markup Price Percentage',
      //   'field_type' => 'number',
      //   'vue_conditions' => array('nivoda_api' => '1'),
      // ),
      'nivoda_api_order' => array(
        'meta_key' => 'nivoda_api_order',
        'field_title' => 'Order',
        'field_type' => 'select',
        'field_type_values' => array('1' => '1', '2' => '2'),
        'vue_conditions' => array('nivoda_api' => '1'),
      ),
      
    );

  } // get_page_fields function end here
  
}// class end here
