<?php
namespace OTW\GeneralWooRingBuilder;

if ( ! defined( 'ABSPATH' ) )	exit;

class FieldsRepeater{

	use \OTW\GeneralWooRingBuilder\Traits\Singleton;

  private $displaytype = array("wrapper_open" => '<table class="form-table bbwp_repeater_wrapper">', 'wrapper_close' => '</table>');

	/******************************************/
	/***** class constructor **********/
	/******************************************/
  public function __construct(){
    
    if(!wp_script_is('bbwp_repeater_js'))
      wp_enqueue_script('bbwp_repeater_js', plugin_dir_url(OTW_WOO_RING_BUILDER_PLUGIN_FILE) . 'assets/admin/js/bbwp_repeater.js', array('jquery'), '1.0.0');
     
    if(!wp_style_is('bbwp_repeater_css'))
      wp_enqueue_style( 'bbwp_repeater_css', plugin_dir_url(OTW_WOO_RING_BUILDER_PLUGIN_FILE) . 'assets/admin/css/bbwp_repeater.css', array(), '1.0.1' );

    // $this->displaytype = array(
    //   "wrapper_open" => '<div class="form-wrap">',
    //   'wrapper_close' => '</div>',
    // );

  }// construct function end here

    
  /******************************************/
  /***** Display function start from here *********/
  /******************************************/
  public function Display($fields = array(), $existing_items = array(), $default_display = false, $nonce = ''){
    if(isset($fields) && $fields && is_array($fields) && count($fields) >= 1){

      $fields_html = \OTW\GeneralWooRingBuilder\Fields::instance();

      if($nonce)
        wp_nonce_field($nonce, $nonce);
      ?>
      <div class="bbwp_repeater_parent_wrapper bbwp_fields_container" style="margin-top:20px;">
        <ul class="bbwp_repeatable_fields">
          <?php if(isset($existing_items) && is_array($existing_items) && count($existing_items) >= 1){
            foreach($existing_items as $key=>$item){ ?>
              <li>
                <h3>Item <span class="dashicons dashicons-dismiss bbwp_repeater_delete_item"></span></h3>
                <div class="bbwp_repeater_item_container">
                                   
                  <?php
                    echo $this->displaytype['wrapper_open'];
                    foreach($fields as $field){
                      // $selected_value = '';
                      // if(isset($item[$field['meta_key']]))
                      //   $selected_value = $item[$field['meta_key']];
                      echo $fields_html->{$field['field_type']}($field, $item[$field['meta_key']]);
                    }
                    echo $this->displaytype['wrapper_close'];
                  ?>
                </div><!-- bbwp_repeater_item_container-->
              </li>
            <?php }
          }elseif($default_display){ ?>
          <li>
            <h3>Item <span class="dashicons dashicons-dismiss bbwp_repeater_delete_item" style="display:none;"></span></h3>
            
            <div class="bbwp_repeater_item_container">
              <?php
                echo $this->displaytype['wrapper_open'];
                foreach($fields as $field){
                  echo $fields_html->{$field['field_type']}($field, '');
                }
                echo $this->displaytype['wrapper_close'];
              ?>
            </div><!-- bbwp_repeater_item_container-->
          </li>
          <?php } ?>
        </ul>

        <br>
        <a href="#" class="addButton bbwp_repeater_add_new_item_button button button-default button-hero" data-target="bbwp_repeater_new_item_html"><span class="dashicons dashicons-plus"></span> Add New Item</a>
        
        <div class="bbwp_repeater_new_item_html" style="display:none;">
          <h3>Item <span class="dashicons dashicons-dismiss bbwp_repeater_delete_item"></span></h3>
          <div class="bbwp_repeater_item_container">
            <?php 
              echo $this->displaytype['wrapper_open'];
              foreach($fields as $field){
                echo $fields_html->{$field['field_type']}($field, '');
              }
              echo $this->displaytype['wrapper_close'];
            ?>
          </div><!-- bbwp_repeater_item_container-->
        </div><!-- bbwp_repeater_new_item_html -->

      </div><!-- bbwp_repeater_parent_wrapper-->
      <?php
    }
  }

  /******************************************/
  /***** Save function start from here *********/
  /******************************************/
  public function Save($fields = array(), $meta_key = '',  $nonce = '', $save_type = 'option',  $data_id = ''){

    $output = false;
    $sanitize = Sanitization::instance();

    $save_array = array();
    if(isset($fields) && $fields && is_array($fields) && count($fields) >= 1){
      if($nonce === 'verified' || (isset($_POST[$nonce]) && wp_verify_nonce($_POST[$nonce], $nonce)))
      {
        $last_field = array('meta_key' =>'product_id');
        
        if(isset($_POST[$last_field['meta_key']]) && is_array($_POST[$last_field['meta_key']]) && count($_POST[$last_field['meta_key']]) >= 1){
          
          $i = 1;
          foreach($_POST[$last_field['meta_key']] as $key=>$posted_value){
            if(count($_POST[$last_field['meta_key']]) == $i)
              continue;
            $insert_into_array = false;
            $repeater_item_values = array();
            foreach($fields as $value){
              
              $dbvalue = "";
              
              if(isset($_POST[$value['meta_key']][$key]) && is_array($_POST[$value['meta_key']][$key]) && count($_POST[$value['meta_key']][$key]) >= 1){
                $dbvalue = array();
                foreach($_POST[$value['meta_key']][$key] as $selected_value){
                  $selected_value = $sanitize->Textfield($selected_value);
                  if($selected_value)
                    $dbvalue[] = $selected_value;
                }
              }else{
                if($value['field_type'] == 'textarea' || $value['field_type'] == 'editor'){
                  if(isset($value['field_allow_all_code']) && $value['field_allow_all_code'] && $value['field_allow_all_code'] == 'on'){
                    if(isset($value['field_disable_autop']) && $value['field_disable_autop'] && $value['field_disable_autop'] == 'on')
                      $dbvalue = wptexturize($sanitize->Textarea($_POST[$value['meta_key']][$key], true));
                    else
                      $dbvalue = wptexturize(wpautop($sanitize->Textarea($_POST[$value['meta_key']][$key], true)));
                  }else{
                    if(isset($value['field_disable_autop']) && $value['field_disable_autop'] && $value['field_disable_autop'] == 'on')
                      $dbvalue = wptexturize($sanitize->Textarea($_POST[$value['meta_key']][$key]));
                    else
                      $dbvalue = wptexturize(wpautop($sanitize->Textarea($_POST[$value['meta_key']][$key])));
                  }
  
                }
                else{
                  
                  $dbvalue = $sanitize->Textfield($_POST[$value['meta_key']][$key]);
  
                  if(isset($value['empty']) && $value['empty'] == 'no' && empty($dbvalue) && isset($value['default_value'])){
                    $dbvalue = $value['default_value'];
                  }
                }
              }

              
              $repeater_item_values[$value['meta_key']] = $dbvalue;
              if($dbvalue)
                $insert_into_array = true;
            }
            if($insert_into_array)
              $save_array[] = $repeater_item_values;
            $i++;
          }
          
        }
        
      }
    }
    
    if(is_object($save_type) && method_exists($save_type, 'set_option')){
      $save_type->set_option($meta_key, ArrayToSerializeString($save_array));
    }
    elseif($save_type === "option")
        update_option($meta_key, ArrayToSerializeString($save_array));
    elseif($save_type === "user" && is_numeric($data_id) && $data_id >= 1)
        update_user_meta($data_id, $meta_key, ArrayToSerializeString($save_array));
    elseif($save_type === "post" && is_numeric($data_id) && $data_id >= 1)
      update_post_meta($data_id, $meta_key, ArrayToSerializeString($save_array));
    elseif($save_type === "term" && is_numeric($data_id) && $data_id >= 1)
        update_term_meta($data_id, $meta_key, ArrayToSerializeString($save_array));
    elseif($save_type === "comment" && is_numeric($data_id) && $data_id >= 1)
        update_comment_meta($data_id, $meta_key, ArrayToSerializeString($save_array));

  }
} // BBWP_CustomFields class

