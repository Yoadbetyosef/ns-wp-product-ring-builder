<?php
namespace OTW\GeneralWooRingBuilder\Traits;

if ( ! defined( 'ABSPATH' ) )	exit;

trait Plugin {

	use Singleton;

	/******************************************/
  /***** get option function**********/
  /******************************************/
  public function get_option($key){

    if(isset(self::$options[$key]))
      return self::$options[$key];
    else
      return NULL;

  }// get_option


	/******************************************/
  /***** get option function **********/
  /******************************************/
  public function set_option($key, $value){
		self::$options[$key] = $value;
		update_option($this->prefix('options'), ArrayToSerializeString(self::$options));
	}// set_option

  /******************************************/
  /***** get option function **********/
  /******************************************/
  public function update_option($key, $value){

		$this->set_option($key, $value);

	}// set_option

  /******************************************/
  /***** get option function **********/
  /******************************************/
  public function set_all_options($values = array()){
		self::$options = $values;
		update_option($this->prefix('options'), ArrayToSerializeString(self::$options));
	}// set_option

  /******************************************/
  /***** get option function **********/
  /******************************************/
  public function update_all_options($values){

		$this->set_all_options($values);

	}// set_option

  /******************************************/
  /***** DeleteField function start from here *********/
  /******************************************/
  public function delete_option($meta_key){
    $output = false;
    $existing_values = self::$options;
    if($existing_values && is_array($existing_values) && count($existing_values) >= 1){
      if(isset($meta_key) && is_array($meta_key) && count($meta_key) >= 1){
        foreach($meta_key as $value){
          if($value && array_key_exists($value, $existing_values))
            unset($existing_values[$value]);
            $output = true;
        }
      }
      elseif(isset($meta_key) && $meta_key && array_key_exists($meta_key, $existing_values)){
        unset($existing_values[$meta_key]);
        $output = true;
      }
    }
    if($output){
      self::$options = $existing_values;
      update_option($this->prefix('options'), ArrayToSerializeString($existing_values));
    }
    return $output;
  }

  /******************************************/
	/***** get plugin prefix with custom string **********/
	/******************************************/
  public function prefix($string = '', $underscore = "_"){

    return $this->prefix.$underscore.$string;

  }// prefix function end here.

  /******************************************/
	/***** get plugin prefix with custom string **********/
	/******************************************/
  public function is_admin_ui(){
    if(!is_admin())
      return false;
    
    if(defined('DOING_AJAX') && DOING_AJAX)
      return false;
    
    return true;

  }// prefix function end here.

}