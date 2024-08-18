<?php
namespace OTW\GeneralWooRingBuilder;

if ( ! defined( 'ABSPATH' ) )	exit;

class Fields{

  use \OTW\GeneralWooRingBuilder\Traits\Singleton;
	
  public $displaytype = array(
    "wrapper_open" => '<table class="form-table">', 
    'wrapper_close' => '</table>', 
    'container_open' => 'tr', 
    'container_close' => 'tr', 
    'container_attributes' => array(
      'class' => 'bbwp_fields_container form-field',
    ),
    'label_open' => '<th scope="row">', 
    'label_close' => '</th>', 
    'input_open' => '<td>', 
    'input_close' => '</td>'
  );

  /*public $displaytype = array(
    "wrapper_open" => '',
    'wrapper_close' => '',
    'container_open' => 'div',
    'container_close' => 'div',
    'container_attributes' => array(
      'class' => 'bbwp_fields_container form-field',
    ),
    'label_open' => '',
    'label_close' => '',
    'input_open' => '',
    'input_close' => ''
  );*/
  
	/******************************************/
	/***** class constructor **********/
	/******************************************/
  public function __construct(){
    if(!wp_script_is('bbwp_fields_js')){
      wp_enqueue_script('bbwp_fields_js', plugin_dir_url(OTW_WOO_RING_BUILDER_PLUGIN_FILE) . 'assets/admin/js/bbwp_fields.js', array('jquery'), '1.0.0');
    }
    if(!wp_style_is('bbwp_fields_css')){
      wp_enqueue_style('bbwp_fields_css', plugin_dir_url(OTW_WOO_RING_BUILDER_PLUGIN_FILE) . 'assets/admin/css/bbwp_fields.css', array(), '1.0.0' );
    }
  }// construct function end here


  /******************************************/
  /***** attributes function start from here *********/
  /******************************************/
	public function attributes($value = NULL){
    $output = '';
		if($value && is_array($value) && count($value) >= 1){
			foreach($value as $key=>$attribue){
        // if($key != 'class'){
          if($attribue)
            $output .= $key.'="'.$attribue.'" ';
          else
            $output .= $key.' ';
        // }
			}
		}
    return $output;
	}

  /******************************************/
  /***** attributes function start from here *********/
  /******************************************/
	public function field_attributes($value = NULL){
    $output = '';
    if(isset($value['attributes']) && is_array($value['attributes']) && count($value['attributes']) >= 1){
      $output = $this->attributes($value['attributes']);
    }
    return $output;
	}

  /******************************************/
  /***** attributes function start from here *********/
  /******************************************/
	public function get_conditions($value = NULL){
    $output = '';
		if($value && is_array($value) && count($value) >= 1){
      $i = 1;
      
      foreach($value as $key=>$attribue){
        $relation = '';
        $operator = '==';
        if(is_array($attribue) && count($attribue) >= 1 && isset($attribue['key']) && isset($attribue['value'])){
          if(isset($attribue['operator']))
            $operator = $attribue['operator'];
          if($i >= 2){
            $relation = ' && ';
            if(isset($attribue['relation']))
              $relation = ' '.$attribue['relation'].' ';
          }

          $output .= $relation.'data.'.$attribue['key'].$operator.'\''.$attribue['value'].'\'';
        }
        else{
          if($attribue)
            $output .= 'data.'.$key.'==\''.$attribue.'\'';
        }
        $i++;
			}
		}
    return $output;
	}

  /******************************************/
  /***** attributes function start from here *********/
  /******************************************/
	public function add_v_show($value, $container_attributes){
    if(isset($value['conditions']))
      $container_attributes['v-show'] = $this->get_conditions($value['conditions']);
    elseif(isset($value['vue_conditions'])){
      $vue_conditions = \OTW\GeneralWooRingBuilder\Conditions::get_vue_conditions($value['vue_conditions']);
      if($vue_conditions)
        $container_attributes['v-show'] = $vue_conditions;
    }
    return $container_attributes;
  }
  

  /******************************************/	
  /***** FlipSwitch function start from here *********/
  /******************************************/
  public function common($value, $selected_value = '', $return_type = ''){

    $output = '';

    if($selected_value === NULL && isset($value['default_value']))
      $selected_value = $value['default_value'];

    if(isset($value['empty']) && $value['empty'] == 'no' && empty($selected_value) && isset($value['default_value'])){
      $selected_value = $value['default_value'];
    }

    $repeater = '';
    $is_repeater = 'no';
    if(isset($value['is_repeater']) && $value['is_repeater'] == 'on'){
      $repeater = '[]';
      if(isset($value['field_duplicate']) && $value['field_duplicate'] == 'on'){
        $is_repeater = 'yes';
      }
    }
    $field_name = $value['meta_key'].$repeater;

    $field_description = '';
    if(isset($value['field_description']))
      $field_description = '<p class="description">'.$value['field_description'].'</p>';

   
    $container_attributes = $this->displaytype['container_attributes'];
    $container_attributes = $this->add_v_show($value, $container_attributes);

    $output .= '<'.$this->displaytype['container_open'].' '.$this->attributes($container_attributes).'>';

    $output .= $this->displaytype['label_open'].'<label for="'.$value['meta_key'].'">'.$value['field_title'].'</label>'.$field_description.$this->displaytype['label_close'].$this->displaytype['input_open'];
    
    if($value['field_type'] == 'editor'){
      ob_start();
      $setting = array('textarea_rows' => 10, 'textarea_name' => $field_name, 'teeny' => false, 'tinymce' => true, 'quicktags' => true);
      wp_editor($selected_value, $field_name, $setting);
      $output .= ob_get_clean();
    } 
    elseif($value['field_type'] == 'textarea')
      $output .= '<textarea name="'.$field_name.'" id="'.$value['meta_key'].'" rows="10" cols="30">'.$selected_value.'</textarea>';
    elseif($value['field_type'] == 'radio'){
      foreach($value['field_type_values'] as $key=>$field_type_value){
        $checked = '';
        if($key == $selected_value)
          $checked = 'checked="checked"';
        $output .= ' <input type="radio" id="'.$value['meta_key'].$key.'" value="'.esc_attr($key).'" name="'.$field_name.'" '.$checked.' /> <label for="'.$value['meta_key'].$key.'">'.esc_html($field_type_value).'</label> ';
        $output .= '&nbsp;&nbsp;';
      }
    }
    elseif($value['field_type'] == 'checkbox_list'){
      $selected_value = SerializeStringToArray($selected_value);
      foreach($value['field_type_values'] as $key=>$field_type_value){
        $checked = '';
        if(in_array($key, $selected_value))
          $checked = 'checked="checked"';
        $output .= ' <input type="checkbox" id="'.$value['meta_key'].$key.'" value="'.esc_attr($key).'" name="'.$value['meta_key'].'[]" '.$checked.' /> <label for="'.$value['meta_key'].$key.'">'.esc_html($field_type_value).'</label> ';
        $output .= '&nbsp;&nbsp;';
      }
    }

    $output .= $this->displaytype['input_close'];
    $output .= '</'.$this->displaytype['container_close'].'>';
    
    if($return_type == 'vue')
      return array('html' => $output, 'selected' => $selected_value);
    else
      return $output;
    
  }
  
	/******************************************/	
  /***** FlipSwitch function start from here *********/
  /******************************************/
  public function flipswitch($value, $selected_value = '', $return_type = ''){

    
    $output = '';

    if($selected_value === NULL && isset($value['default_value']))
      $selected_value = $value['default_value'];

    if(isset($value['empty']) && $value['empty'] == 'no' && empty($selected_value) && isset($value['default_value'])){
      $selected_value = $value['default_value'];
    }

    $field_description = '';
    if(isset($value['field_description']))
      $field_description = '<p class="description">'.$value['field_description'].'</p>';
    
    $checked = '';
    if($selected_value){
      $checked = ' checked="checked"';
    }

    if(isset($value['return_value']) && $selected_value && $selected_value === $value['return_value']){
      $selected_value = '1';
    }

    $repeater = '';
    $hidden_input = '';
    if(isset($value['is_repeater']) && $value['is_repeater'] == 'on'){
      $repeater = '[]';
      $default_selected_value = 0;
      if($checked)
        $default_selected_value = 1;
      $hidden_input = '<input type="hidden" name="'.$value['meta_key'].$repeater.'" value="'.$default_selected_value.'" />';
      $repeater = '_repeater[]';
    }

    $container_attributes = $this->displaytype['container_attributes'];
    $container_attributes = $this->add_v_show($value, $container_attributes);
    
    $output .= '<'.$this->displaytype['container_open'].' '.$this->attributes($container_attributes).'>';
    
    $output .= $this->displaytype['label_open'].'<label for="'.$value['meta_key'].'">'.$value['field_title'].'</label>'.$field_description.$this->displaytype['label_close'].$this->displaytype['input_open'];
    
    if($value['field_type'] == 'flipswitch'){
      $output .= (isset($value['before_html']) ? $value['before_html'] : '').'<input type="checkbox" class="bbwp_flipswitch bbwp_fields_checkbox_repeater" value="1" true-value="1" v-model="data.'.$value['meta_key'].'" id="'.$value['meta_key'].'" name="'.$value['meta_key'].$repeater.'" '.$checked.' '.(isset($value['attributes']) ? $this->attributes($value['attributes']):'').'>'.(isset($value['after_html']) ? $value['after_html'] : '');
      $output .= $hidden_input;
    }
    elseif($value['field_type'] == 'checkbox'){
      $output .= '<input type="'.$value['field_type'].'" class="bbwp_fields_checkbox_repeater" value="1" name="'.$value['meta_key'].$repeater.'" '.$checked.'" id="'.$value['meta_key'].'" '.$this->attributes($value).'>'.(isset($value['after_html']) ? $value['after_html'] : '');
      $output .= $hidden_input;
    }
    
    $output .=  $this->displaytype['input_close'];
    $output .=  '</'.$this->displaytype['container_close'].'>';
    
    if($return_type == 'vue')
      return array('html' => $output, 'selected' => $selected_value);
    else
      return $output;
  }

  /******************************************/	
  /***** FlipSwitch function start from here *********/
  /******************************************/
  public function plain_html($value, $selected_value = '', $return_type = ''){

    $output = '';
    if($selected_value === NULL && isset($value['default_value']))
      $selected_value = $value['default_value'];
      
    if(isset($value['empty']) && $value['empty'] == 'no' && empty($selected_value) && isset($value['default_value'])){
      $selected_value = $value['default_value'];
    }

    $repeater = '';
    $is_repeater = 'no';
    if(isset($value['is_repeater']) && $value['is_repeater'] == 'on'){
      $repeater = '[]';
      if(isset($value['field_duplicate']) && $value['field_duplicate'] == 'on'){
        $is_repeater = 'yes';
      }
    }

    $field_name = $value['meta_key'].$repeater;

    $field_description = '';
    if(isset($value['field_description']))
      $field_description = '<p class="description">'.$value['field_description'].'</p>';

    $container_attributes = $this->displaytype['container_attributes'];
    $container_attributes = $this->add_v_show($value, $container_attributes);
    
    if($value['field_type'] != 'hidden'){
      $output .= '<'.$this->displaytype['container_open'].' '.$this->attributes($container_attributes).'>';
      $output .= $this->displaytype['label_open'].'<label for="'.$value['meta_key'].'">'.$value['field_title'].'</label>'.$field_description.$this->displaytype['label_close'].$this->displaytype['input_open'];
    }
    
    $output .= '<div id="'.$value['meta_key'].'" class="regular-text" '.$this->field_attributes($value).'>'.$value['html'].'</div>';

    if($value['field_type'] != 'hidden'){
      $output .= $this->displaytype['input_close'];
      $output .= '</'.$this->displaytype['container_close'].'>';
    }
    
    if($return_type == 'vue')
      return array('html' => $output, 'selected' => $selected_value);
    else
      return $output;
  }

  /******************************************/	
  /***** FlipSwitch function start from here *********/
  /******************************************/
  public function text($value, $selected_value = '', $return_type = ''){

    $output = '';
    if($selected_value === NULL && isset($value['default_value']))
      $selected_value = $value['default_value'];
      
    if(isset($value['empty']) && $value['empty'] == 'no' && empty($selected_value) && isset($value['default_value'])){
      $selected_value = $value['default_value'];
    }

    $repeater = '';
    $is_repeater = 'no';
    
    if(isset($value['is_repeater']) && $value['is_repeater'] == 'on'){
      $repeater = '[]';
      if(isset($value['field_duplicate']) && $value['field_duplicate'] == 'on'){
        $is_repeater = 'yes';
      }
    }

    $field_name = $value['meta_key'].$repeater;

    $field_description = '';
    if(isset($value['field_description']))
      $field_description = '<p class="description">'.$value['field_description'].'</p>';

    $container_attributes = $this->displaytype['container_attributes'];
    $container_attributes = $this->add_v_show($value, $container_attributes);
    
    if($value['field_type'] != 'hidden'){
      $output .= '<'.$this->displaytype['container_open'].' '.$this->attributes($container_attributes).'>';
      $output .= $this->displaytype['label_open'].'<label for="'.$value['meta_key'].'">'.$value['field_title'].'</label>'.$field_description.$this->displaytype['label_close'].$this->displaytype['input_open'];
    }
    
    if(isset($value['field_duplicate']) && $value['field_duplicate'] == 'on'){
      $selected_value = SerializeStringToArray($selected_value);
      $output .= '<div><input type="'.$value['field_type'].'" class="regular-text bbwp_fields_tags_add" data-name="'.$field_name.'" data-repeater-field="'.$is_repeater.'" />
      <input type="button" class="button bbwp_fields_tag_add_button" value="Add"><div class="bbwp_fields_tag_check_list">';
      $output .= '<input type="hidden" value="" name="'.$field_name.'[]" class="regular-text" data-name="'.$value['meta_key'].'" data-repeater="'.$is_repeater.'" />';
      if($selected_value && is_array($selected_value) && count($selected_value) >= 1){
        foreach ($selected_value as $field_type_value) {
          $output .= '<span><input type="'.$value['field_type'].'" value="'.esc_attr($field_type_value).'" name="'.$field_name.'[]" class="regular-text" data-name="'.$value['meta_key'].'" data-repeater="'.$is_repeater.'" /><a href="#" class="bbwp_fields_delete_id bbwp_dismiss_icon">&nbsp;</a></span>';
        }
      }
      $output .= '</div></div>';
    }
    else
      $output .= '<input v-model="data.'.$value['meta_key'].'" type="'.$value['field_type'].'" name="'.$field_name.'" id="'.$value['meta_key'].'" '.(($return_type != 'vue')? 'value="'.esc_attr($selected_value).'"':'').' class="regular-text" '.$this->field_attributes($value).'>';

    if($value['field_type'] != 'hidden'){
      $output .= $this->displaytype['input_close'];
      $output .= '</'.$this->displaytype['container_close'].'>';
    }
    
    if($return_type == 'vue')
      return array('html' => $output, 'selected' => $selected_value);
    else
      return $output;
  }

  /******************************************/	
  /***** FlipSwitch function start from here *********/
  /******************************************/
  public function select($value, $selected_value = '', $return_type = ''){

    $output = '';

    if($selected_value === NULL && isset($value['default_value']))
      $selected_value = $value['default_value'];

    if(isset($value['empty']) && $value['empty'] == 'no' && empty($selected_value) && isset($value['default_value'])){
      $selected_value = $value['default_value'];
    }

    $repeater = '';
    if(isset($value['is_repeater']) && $value['is_repeater'] == 'on'){
      $repeater = '[]';
    }

    $field_description = '';
    if(isset($value['field_description']))
      $field_description = '<p class="description">'.$value['field_description'].'</p>';

    $container_attributes = $this->displaytype['container_attributes'];
    $container_attributes = $this->add_v_show($value, $container_attributes);

    $output .= '<'.$this->displaytype['container_open'].' '.$this->attributes($container_attributes).'>';

    $output .= $this->displaytype['label_open'].'<label for="'.$value['meta_key'].'">'.$value['field_title'].'</label>'.$field_description.$this->displaytype['label_close'].$this->displaytype['input_open'];
    

      $output .= '<select name="'.$value['meta_key'].$repeater.'" id="'.$value['meta_key'].'" v-model="data.'.$value['meta_key'].'">';
      foreach($value['field_type_values'] as $key=>$field_type_value){
        if($key == $selected_value)
          $output .= '<option value="'.esc_attr($key).'" selected="selected">'.esc_html($field_type_value).'</option>';
        else
          $output .= '<option value="'.esc_attr($key).'">'.esc_html($field_type_value).'</option>';
      }
      $output .= '</select>';

      $output .= $this->displaytype['input_close'];
      $output .= '</'.$this->displaytype['container_close'].'>';
    
    if($return_type == 'vue')
      return array('html' => $output, 'selected' => $selected_value);
    else
      return $output;
  }

  /******************************************/	
  /***** FlipSwitch function start from here *********/
  /******************************************/
  public function select2($value, $selected_value = '', $return_type = ''){
    
    if(!wp_script_is('bbwp_fields_select2_js'))
      wp_enqueue_script('bbwp_fields_select2_js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'));

    if(!wp_style_is('bbwp_fields_select2_css'))
      wp_enqueue_style('bbwp_fields_select2_css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    
    $output = '';

    if($selected_value === NULL && isset($value['default_value']))
      $selected_value = $value['default_value'];

    if(isset($value['empty']) && $value['empty'] == 'no' && empty($selected_value) && isset($value['default_value'])){
      $selected_value = $value['default_value'];
    }

    $repeater = '';
    if(isset($value['is_repeater']) && $value['is_repeater'] == 'on'){
      $repeater = '[]';
    }

    $field_description = '';
    if(isset($value['field_description']))
      $field_description = '<p class="description">'.$value['field_description'].'</p>';

    $container_attributes = $this->displaytype['container_attributes'];
    $container_attributes = $this->add_v_show($value, $container_attributes);

    $output .= '<'.$this->displaytype['container_open'].' '.$this->attributes($container_attributes).'>';

    $output .= $this->displaytype['label_open'].'<label for="'.$value['meta_key'].'">'.$value['field_title'].'</label>'.$field_description.$this->displaytype['label_close'].$this->displaytype['input_open'];

    $output .= '<select multiple="multiple" class="bbwp_fields_select_two_input" name="'.$value['meta_key'].$repeater.'[]" id="'.$value['meta_key'].'" v-model="data.'.$value['meta_key'].'">';
    foreach($value['field_type_values'] as $key=>$field_type_value){
      $is_selected = '';
      if(is_array($selected_value) && count($selected_value) >= 1 && array_key_exists($key, $selected_value))
        $is_selected = 'selected="selected"';
      $output .= '<option value="'.esc_attr($key).'" '.$is_selected.'>'.esc_html($field_type_value).'</option>';
      
    }
    $output .= '</select>';

    $output .= $this->displaytype['input_close'];
    $output .= '</'.$this->displaytype['container_close'].'>';
    
    if($return_type == 'vue')
      return array('html' => $output, 'selected' => $selected_value);
    else
      return $output;
  }

  /******************************************/	
  /***** FlipSwitch function start from here *********/
  /******************************************/
  public function image($value, $selected_value = '', $return_type = ''){
    
    wp_enqueue_media();
    if(!wp_script_is('bbwp_fields_image_js'))
      wp_enqueue_script('bbwp_fields_image_js', plugin_dir_url(OTW_WOO_RING_BUILDER_PLUGIN_FILE) . 'assets/admin/js/bbwp_fields_image.js', array('jquery'), '1.0.0');
      
    
    $output = '';

    if(isset($value['empty']) && $value['empty'] == 'no' && empty($selected_value) && isset($value['default_value'])){
      $selected_value = $value['default_value'];
    }

    
    $repeater = '';
    $is_repeater = 'no';
    if(isset($value['is_repeater']) && $value['is_repeater'] == 'on'){
      $repeater = '[]';
      if(isset($value['field_duplicate']) && $value['field_duplicate'] == 'on'){
        $is_repeater = 'yes';
      }
    }
    $field_name = $value['meta_key'].$repeater;

    $field_description = '';
    if(isset($value['field_description']))
      $field_description = '<p class="description">'.$value['field_description'].'</p>';

   
    $container_attributes = $this->displaytype['container_attributes'];
    $container_attributes = $this->add_v_show($value, $container_attributes);

    $output .= '<'.$this->displaytype['container_open'].' '.$this->attributes($container_attributes).'>';

    $output .= $this->displaytype['label_open'].'<label for="'.$value['meta_key'].'">'.$value['field_title'].'</label>'.$field_description.$this->displaytype['label_close'].$this->displaytype['input_open'];
    
    if($value['field_type'] == 'image'){
      if(isset($value['field_duplicate']) && $value['field_duplicate'] == 'on'){
        $selected_value = SerializeStringToArray($selected_value);
        //<p class="description">You can use Ctrl+Click to select multiple images from media library.</p>
        $output .= '<input type="button" id="" class="bbwp_fields_image_multiple_upload_button button" value="Select Images" data-repeater-field="'.$is_repeater.'" data-name="'.$field_name.'">';
        $output .= '<div class="bbwp_fields_image_multiple_preview bbwp_fields_image_preview">';
        $output .= '<input type="hidden" name="'.$field_name.'[]" value="" data-name="'.$value['meta_key'].'" data-repeater="'.$is_repeater.'" />';
        if($selected_value && is_array($selected_value) && count($selected_value) >= 1){
          foreach ($selected_value as $key=>$field_type_value) {
            $output .= '<span><img src="'.$field_type_value.'"><a href="#" class="bbwp_dismiss_icon bbwp_fields_delete_id">&nbsp;</a>';
            $output .= '<input type="hidden" name="'.$field_name.'[]" value="'.esc_attr($field_type_value).'" data-name="'.$value['meta_key'].'" data-repeater="'.$is_repeater.'" /></span>';
          }
        }
        $output .= '<div class="clearboth"></div></div>';
      }
      else{
        $output .= '<input type="text" name="'.$field_name.'" id="'.$value['meta_key'].'" value="'.esc_attr($selected_value).'" class="regular-text">
        <input type="button" id="" class="bbwp_fields_image_file_upload_button button" value="Select Image">';
        $output .= '<div class="bbwp_fields_image_single_preview bbwp_fields_image_preview">';
        if($selected_value){
          $output .= '<span><img src="'.$selected_value.'"><a href="#" class="bbwp_dismiss_icon">&nbsp;</a></span>';
        }
        $output .= '<div class="clearboth"></div></div>';
      }
    }elseif($value['field_type'] == 'file'){
      
      $output .= '<input type="text" name="'.$value['meta_key'].'" id="'.$value['meta_key'].'" value="'.esc_attr($selected_value).'" class="regular-text">
              <input type="button" id="" class="bbwp_fields_image_file_upload_button button" value="'.__('Upload File', 'bbwp-custom-fields').'">';
    }
    
    $output .= $this->displaytype['input_close'];
    $output .= '</'.$this->displaytype['container_close'].'>';
    
    
    if($return_type == 'vue')
      return array('html' => $output, 'selected' => $selected_value);
    else
      return $output;
  }

  /******************************************/	
  /***** FlipSwitch function start from here *********/
  /******************************************/
  public function color($value, $selected_value = '', $return_type = ''){

    wp_enqueue_script('wp-color-picker');

    $output = '';

    if($selected_value === NULL && isset($value['default_value']))
      $selected_value = $value['default_value'];
      
    if(isset($value['empty']) && $value['empty'] == 'no' && empty($selected_value) && isset($value['default_value'])){
      $selected_value = $value['default_value'];
    }

    $repeater = '';
    if(isset($value['is_repeater']) && $value['is_repeater'] == 'on'){
      $repeater = '[]';
    }

    $field_description = '';
    if(isset($value['field_description']))
      $field_description = '<p class="description">'.$value['field_description'].'</p>';

    $container_attributes = $this->displaytype['container_attributes'];
    $container_attributes = $this->add_v_show($value, $container_attributes);

    $output .= '<'.$this->displaytype['container_open'].' '.$this->attributes($container_attributes).'>';

    $output .= $this->displaytype['label_open'].'<label for="'.$value['meta_key'].'">'.$value['field_title'].'</label>'.$field_description.$this->displaytype['label_close'].$this->displaytype['input_open'];
  
    $output .= '<input type="text" name="'.$value['meta_key'].'" id="'.$value['meta_key'].'" value="'.esc_attr($selected_value).'" class="bbwp_fields_wp_color_picker regular-text">';

    $output .= $this->displaytype['input_close'];
    $output .= '</'.$this->displaytype['container_close'].'>';
    
    if($return_type == 'vue')
      return array('html' => $output, 'selected' => $selected_value);
    else
      return $output;
  }


  /******************************************/	
  /***** editor function start from here *********/
  /******************************************/
  public function textarea($value, $selected_value = '', $return_type = ''){
    return $this->common($value, $selected_value, $return_type);
  }

  public function editor($value, $selected_value = '', $return_type = ''){
    return $this->common($value, $selected_value, $return_type);
  }

  public function number($value, $selected_value = '', $return_type = ''){
    return $this->text($value, $selected_value, $return_type);
  }

  public function email($value, $selected_value = '', $return_type = ''){
    return $this->text($value, $selected_value, $return_type);
  }

  public function password($value, $selected_value = '', $return_type = ''){
    return $this->text($value, $selected_value, $return_type);
  }
  
  public function file($value, $selected_value = '', $return_type = ''){
    return $this->image($value, $selected_value, $return_type);
  }

  public function radio($value, $selected_value = '', $return_type = ''){
    return $this->common($value, $selected_value, $return_type);
  }
  
  public function checkbox($value, $selected_value = '', $return_type = ''){
    return $this->flipswitch($value, $selected_value, $return_type);
  }

  public function checkbox_list($value, $selected_value = '', $return_type = ''){
    return $this->common($value, $selected_value, $return_type);
  }
  
} // BBWP_CustomFields class

