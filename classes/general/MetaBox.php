<?php
namespace OTW\GeneralWooRingBuilder;

if ( ! defined( 'ABSPATH' ) )	exit;

class MetaBox{

	use \OTW\GeneralWooRingBuilder\Traits\Singleton;

  private $prefix = "";
  private $saveType = "option";
  private $dataID = '';
  private $displaytype = array("wrapper_open" => '<table class="form-table">', 'wrapper_close' => '</table>', 'container_open' => '<tr>', 'container_close' => '</tr>', 'label_open' => '<th scope="row">', 'label_close' => '</th>', 'input_open' => '<td>', 'input_close' => '</td>');
  private $skipSaving = array();

  /******************************************/
  /***** DisplayOptions function start from here *********/
  /******************************************/
  public function __construct($prefix = ""){
    if(isset($prefix) && $prefix && is_string($prefix))
      $this->prefix = $prefix;
    /*$this->displaytype = array(
      "wrapper_open" => '<div class="form-wrap">',
      'wrapper_close' => '</div>',
      'container_open' => '<div class="form-field">',
      'container_close' => '</div>',
      'label_open' => '',
      'label_close' => '',
      'input_open' => '',
      'input_close' => ''
    );*/
  }// construct function end here

  

  /******************************************/
  /***** SaveOptions function start from here *********/
  /******************************************/
  public function SaveOptions($existing_values = array(), $nonce = ''){

    $output = false;
    $output_array = array();
    $sanitize = Sanitization::instance();

    if(isset($existing_values) && $existing_values && is_array($existing_values) && count($existing_values) >= 1){
      if($nonce === 'verified' || (isset($_POST[$nonce]) && wp_verify_nonce($_POST[$nonce], $nonce)))
      {
        foreach($existing_values as $key=>$value){
          if(in_array($key, $this->skipSaving))
            continue;

          $dbvalue = "";
          if(isset($_POST[$value['meta_key']])){
            if(is_array($_POST[$value['meta_key']]) && count($_POST[$value['meta_key']]) >= 1){
              $dbvalue = array();
              foreach($_POST[$value['meta_key']] as $selected_value){
                $selected_value = $sanitize->Textfield($selected_value);
                if($selected_value)
                  $dbvalue[] = $selected_value;
              }
            }
            else{
              if($value['field_type'] == 'textarea' || $value['field_type'] == 'editor'){
                if(isset($value['field_allow_all_code']) && $value['field_allow_all_code'] && $value['field_allow_all_code'] == 'on'){
                  if(isset($value['field_disable_autop']) && $value['field_disable_autop'] && $value['field_disable_autop'] == 'on')
                    $dbvalue = wptexturize($sanitize->Textarea($_POST[$value['meta_key']], true));
                  else
                    $dbvalue = wptexturize(wpautop($sanitize->Textarea($_POST[$value['meta_key']], true)));
                }else{
                  if(isset($value['field_disable_autop']) && $value['field_disable_autop'] && $value['field_disable_autop'] == 'on')
                    $dbvalue = wptexturize($sanitize->Textarea($_POST[$value['meta_key']]));
                  else
                    $dbvalue = wptexturize(wpautop($sanitize->Textarea($_POST[$value['meta_key']])));
                }

              }
              else{
                $dbvalue = $sanitize->Textfield($_POST[$value['meta_key']]);

                if(isset($value['empty']) && $value['empty'] == 'no' && empty($dbvalue) && isset($value['default_value'])){
                  $dbvalue = $value['default_value'];
                }
                if($value['field_type'] == 'flipswitch' && $dbvalue && isset($value['return_value']))
                  $dbvalue = $value['return_value'];
                
              }
            }
          }
          else{

            if($value['field_type'] == 'flipswitch' && isset($value['empty_retun_value']))
              $dbvalue = $value['empty_retun_value'];
            else if(isset($value['default_value']) && empty($dbvalue))
              $dbvalue = $value['default_value'];
          }
          if($this->saveType === 'return_array'){
            $output_array[$value['meta_key']] = $dbvalue;
            continue;
          }

          if(is_array($dbvalue) && !is_object($this->saveType)){
            $dbvalue = ArrayToSerializeString($dbvalue);
          }
          
          if(is_object($this->saveType) && method_exists($this->saveType, 'set_option')){
            
            if(isset($value['save_type']) && $value['save_type'] == 'option')
              update_option($value['meta_key'], $dbvalue);
            else{
              $this->saveType->set_option($value['meta_key'], $dbvalue);
            }
              
          }
          elseif($this->saveType === "option")
              update_option($value['meta_key'], $dbvalue);
          elseif($this->saveType === "user" && is_numeric($this->dataID) && $this->dataID >= 1)
              update_user_meta($this->dataID, $value['meta_key'], $dbvalue);
          elseif($this->saveType === "post" && is_numeric($this->dataID) && $this->dataID >= 1)
            update_post_meta($this->dataID, $value['meta_key'], $dbvalue);
          elseif($this->saveType === "term" && is_numeric($this->dataID) && $this->dataID >= 1)
              update_term_meta($this->dataID, $value['meta_key'], $dbvalue);
          elseif($this->saveType === "comment" && is_numeric($this->dataID) && $this->dataID >= 1)
              update_comment_meta($this->dataID, $value['meta_key'], $dbvalue);
        }

        if($this->saveType == "option" || is_object($this->saveType))
          return true;
      }
    }
    if($this->saveType === 'return_array')
      return $output_array;
    return $output;
  }

  /******************************************/
  /***** Set function start from here *********/
  /******************************************/
  public function Set($property, $value = NULL){
    if(isset($property) && $property){
      if(isset(self::$$property))
        self::$$property = $value;
      else
        $this->$property = $value;
    }
  }

  /******************************************/
  /***** prefix function start from here *********/
  /******************************************/
  public function prefix($string = '', $underscore = "_"){
    return $this->prefix.$underscore.$string;
  }


  
  /******************************************/
  /***** DisplayOptions function start from here *********/
  /******************************************/
  public function DisplayOptions($existing_values = array(), $nonce = ''){
    if(isset($existing_values) && $existing_values && is_array($existing_values) && count($existing_values) >= 1){
      wp_nonce_field($nonce, $nonce);
      
      echo $this->displaytype['wrapper_open'];

      foreach($existing_values as $value){

				if($value['field_type'] != 'hidden')
        	echo $this->displaytype['container_open'];

        $field_description = '';
        if(isset($value['field_description']))
          $field_description = '<p class="description">'.$value['field_description'].'</p>';

				if($value['field_type'] != 'hidden')
        	echo $this->displaytype['label_open'].'<label for="'.$value['meta_key'].'">'.$value['field_title'].'</label>'.$field_description.$this->displaytype['label_close'].$this->displaytype['input_open'];

				$default_value = "";
				$selected_value = "";
        if(isset($value['default_value']) && $value['default_value'])
          $default_value = $value['default_value'];

        if($this->saveType === "option")
          $selected_value = get_option($value['meta_key']);
        elseif($this->saveType === "user" && is_numeric($this->dataID) && $this->dataID >= 1)
          $selected_value = get_user_meta($this->dataID, $value['meta_key'], true);
        elseif($this->saveType === "post" && is_numeric($this->dataID) && $this->dataID >= 1)
          $selected_value = get_post_meta($this->dataID, $value['meta_key'], true);
        elseif($this->saveType === "term" && is_numeric($this->dataID) && $this->dataID >= 1)
          $selected_value = get_term_meta($this->dataID, $value['meta_key'], true);
        elseif($this->saveType === "comment" && is_numeric($this->dataID) && $this->dataID >= 1)
          $selected_value = get_comment_meta($this->dataID, $value['meta_key'], true);

        if(!(isset($selected_value) && $selected_value))
          $selected_value = $default_value;
        if(isset($value['field_duplicate']) && $value['field_duplicate'] == 'on'){
          $selected_value = SerializeStringToArray($selected_value);
        }

        if($value['field_type'] == 'text' || $value['field_type'] == 'password' || $value['field_type'] == 'number' || $value['field_type'] == 'hidden'){
          if(isset($value['field_duplicate']) && $value['field_duplicate'] == 'on'){
            echo '<div><input type="text" class="field_duplicate regular-text bb_new_tag" data-name="'.$value['meta_key'].'" />
            <input type="button" class="button tagadd bb_tagadd" value="Add"><div class="bbtagchecklist input_bbtagchecklist">';
            if($selected_value && is_array($selected_value) && count($selected_value) >= 1){
              foreach ($selected_value as $field_type_value) {
                echo '<span><input type="text" value="'.esc_attr($field_type_value).'" name="'.$value['meta_key'].'[]" class="regular-text" /><a href="#" class="bb_delete_it bb_dismiss_icon">&nbsp;</a></span>';
              }
            }
            echo '</div></div>';
          }
          else
            echo '<input type="'.$value['field_type'].'" name="'.$value['meta_key'].'" id="'.$value['meta_key'].'" value="'.esc_attr($selected_value).'" class="regular-text">';
				}
        elseif($value['field_type'] == 'image'){
          if(isset($value['field_duplicate']) && $value['field_duplicate'] == 'on'){
            //<p class="description">You can use Ctrl+Click to select multiple images from media library.</p>
            echo '<input type="button" id="" class="bytebunch_multiple_upload_button button" value="Select Images" data-name="'.$value['meta_key'].'">';
            echo '<div class="bb_multiple_images_preview bb_image_preview">';
            if($selected_value && is_array($selected_value) && count($selected_value) >= 1){
              foreach ($selected_value as $field_type_value) {
                echo '<span><img src="'.$field_type_value.'"><a href="#" class="bb_dismiss_icon bb_delete_it">&nbsp;</a><input type="hidden" name="'.$value['meta_key'].'[]" value="'.esc_attr($field_type_value).'" /></span>';
              }
            }
            echo '<div class="clearboth"></div></div>';
          }else{
            echo '<input type="text" name="'.$value['meta_key'].'" id="'.$value['meta_key'].'" value="'.esc_attr($selected_value).'" class="regular-text">
            <input type="button" id="" class="bytebunch_file_upload_button button" value="Select Image">';
            echo '<div class="bb_single_image_preview bb_image_preview">';
            if($selected_value){
              echo '<span><img src="'.$selected_value.'"><a href="#" class="bb_dismiss_icon">&nbsp;</a></span>';
            }
            echo '<div class="clearboth"></div></div>';
          }

        }
        elseif($value['field_type'] == 'file'){
          echo '<input type="text" name="'.$value['meta_key'].'" id="'.$value['meta_key'].'" value="'.esc_attr($selected_value).'" class="regular-text">
              <input type="button" id="" class="bytebunch_file_upload_button button" value="'.__('Upload File', 'bbwp-custom-fields').'">';
        }
        elseif($value['field_type'] == 'editor'){
          $setting = array('textarea_rows' => 10, 'textarea_name' => $value['meta_key'], 'teeny' => false, 'tinymce' => true, 'quicktags' => true);
          wp_editor($selected_value, $value['meta_key'], $setting);
        }
        elseif($value['field_type'] == 'textarea'){
          echo '<textarea name="'.$value['meta_key'].'" id="'.$value['meta_key'].'" rows="5">'.$selected_value.'</textarea>';
        }
        elseif($value['field_type'] == 'color'){
          echo '<input type="text" name="'.$value['meta_key'].'" id="'.$value['meta_key'].'" value="'.esc_attr($selected_value).'" class="bytebunch-wp-color-picker regular-text">';
        }
        elseif($value['field_type'] == 'date'){
          echo '<input type="text" name="'.$value['meta_key'].'" id="'.$value['meta_key'].'" value="'.esc_attr($selected_value).'" class="bytebunch-wp-date-picker regular-text">';
        }
        elseif($value['field_type'] == 'select'){
          echo '<select name="'.$value['meta_key'].'" id="'.$value['meta_key'].'">';
          foreach($value['field_type_values'] as $key=>$field_type_value){
            if($key == $selected_value)
              echo '<option value="'.esc_attr($key).'" selected="selected">'.esc_html($field_type_value).'</option>';
            else
              echo '<option value="'.esc_attr($key).'">'.esc_html($field_type_value).'</option>';
          }
          echo '</select>';
        }
        elseif($value['field_type'] == 'radio'){
          foreach($value['field_type_values'] as $key=>$field_type_value){
            if($key == $selected_value)
              echo ' <input type="radio" id="'.$value['meta_key'].$key.'" value="'.esc_attr($field_type_value).'" name="'.$value['meta_key'].'" checked="checked" /> <label for="'.$value['meta_key'].$key.'">'.esc_html($field_type_value).'</label> ';
            else
              echo ' <input type="radio" id="'.$value['meta_key'].$key.'" value="'.esc_attr($field_type_value).'" name="'.$value['meta_key'].'" /> <label for="'.$value['meta_key'].$key.'">'.esc_html($field_type_value).'</label> ';
            echo '&nbsp;&nbsp;';
          }
        }
        elseif($value['field_type'] == 'checkbox'){
          if($selected_value)
            echo '<input type="'.$value['field_type'].'" name="'.$value['meta_key'].'" id="'.$value['meta_key'].'" checked="checked">';
          else
            echo '<input type="'.$value['field_type'].'" name="'.$value['meta_key'].'" id="'.$value['meta_key'].'">';
        }
        elseif($value['field_type'] == 'checkbox_list'){
          $selected_value = SerializeStringToArray($selected_value);
          if(!($selected_value && is_array($selected_value)))
            $selected_value = array();
          foreach($value['field_type_values'] as $key=>$field_type_value){
            if(in_array($field_type_value, $selected_value))
              echo ' <input type="checkbox" id="'.$value['meta_key'].$key.'" value="'.esc_attr($field_type_value).'" name="'.$value['meta_key'].'[]" checked="checked" /> <label for="'.$value['meta_key'].$key.'">'.esc_html($field_type_value).'</label> ';
            else
              echo ' <input type="checkbox" id="'.$value['meta_key'].$key.'" value="'.esc_attr($field_type_value).'" name="'.$value['meta_key'].'[]" /> <label for="'.$value['meta_key'].$key.'">'.esc_html($field_type_value).'</label> ';
            echo '&nbsp;&nbsp;';
          }
				}
				if($value['field_type'] != 'hidden'){
					echo $this->displaytype['input_close'];
					echo $this->displaytype['container_close'];
				}
      }
      echo $this->displaytype['wrapper_close'];
    }
  }
} // BBWP_CustomFields class

