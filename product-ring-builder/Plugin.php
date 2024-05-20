<?php
namespace OTW\WooRingBuilder;

// exit if file is called directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin{

  use \OTW\GeneralWooRingBuilder\Traits\Plugin;
  use \OTW\GeneralWooRingBuilder\Traits\ApiMethods;

  public $prefix = 'otw_woo_ring_builder';
  static $options = array();
  public $diamonds = null;
  public $vdb_diamonds = null;
  public $nivoda_diamonds = null;
  public $pld_diamonds = null;
  public $woo = null;


	/******************************************/
	/***** class constructor **********/
	/******************************************/
  public function __construct(){

    
		// get the plugin options/settings.
    self::$options = SerializeStringToArray(get_option($this->prefix('options')));
    // $default_values = array('input_element_class' => 'autocomplete_address');
    // self::$options = array_merge($default_values, self::$options);

    PluginDefault::instance();
    $this->diamonds = \OTW\WooRingBuilder\Classes\Diamonds::instance();
    $this->vdb_diamonds = \OTW\WooRingBuilder\Classes\GetDiamonds::instance();
    $this->nivoda_diamonds = \OTW\WooRingBuilder\Classes\NivodaGetDiamonds::instance();
    $this->pld_diamonds = \OTW\WooRingBuilder\Classes\PldGetDiamonds::instance();
    $this->woo = \OTW\WooRingBuilder\Classes\Woo::instance();

    add_action('init', function(){
      if(isset($_GET['test']) && $_GET['test'] == 'pld'){
        $args = array();
        $args['ShapeList'] = 'EMERALD';
        $diamonds = $this->pld_diamonds->get_diamonds($args);
        db($diamonds[0]);exit();
      }
    });
  }// construct function end here



  /******************************************/
  /***** Check if elementor is loaded. **********/
  /******************************************/
  public function is_compatible() {
    
		// Check if Elementor installed and activated
		/*if ( ! did_action( 'elementor/loaded' ) ) {
      $this->message = __('OTW Elementor Form CRM require Elementor Pro to be installed and active.', 'otw-elementor-form-crm-td');
      $this->messageClass = 'warning';
			add_action( 'admin_notices', [ $this, 'admin_notices' ] );
			return false;
		}*/
    return true;
    
  }

  public function setcookie($name, $value='', $time='', $path=COOKIEPATH, $cookie_domain='', $secure=true, $http_only=true){

    $samesite = 'Strict';
    if(empty($time))
      $time = (((int)wp_date('U')) + 60*60*24*30*12);


    // db(defined(COOKIE_DOMAIN));
    /*if(!defined(COOKIE_DOMAIN)){
      db($_COOKIE);
      db($name);db($value);db($time);db($path);
      setcookie($name, $value, $time, $path);
      return true;
    }*/

    $cookie_domain = $_SERVER[ 'HTTP_HOST' ];
    if(defined(COOKIE_DOMAIN))
      $cookie_domain = COOKIE_DOMAIN;
    // 60*60 = 1 hour
    // 60*60*24 = 1 day
    //60*60*24*30 = 1 month
    //60*60*24*30*12 = 1 year
    // db($name);db($value);db($time);db($path);db($cookie_domain);exit();
      // db($time);
    // $cookie = setcookie($name, $value, $time, $path, $cookie_domain, $secure, $http_only);//, ['SameSite' => 'Strict']

    // if(get_client_ip() == '182.178.231.168'){
      // $http_only = false;
      // $secure = false;
      // db('test1');db($cookie_domain);db('test2');exit();
    // }
    if(function_exists('WC') && isset(WC()->session) && is_object(WC()->session)){
      WC()->session->set( 'gcpb_'.$name, $value);
    }
    setcookie($name, $value, [
      'expires' => $time,
      'path' => $path,
      'domain' => $cookie_domain,
      'secure' => $secure,
      'httponly' => $http_only,
      'samesite' => $samesite,
    ]);
    // db($cookie);
    
  }

  public function delete_diamond_cookies(){
    $time_past = (((int)wp_date('U')) - 60*60*24*30*12);
    unset($_GET['stock_num']);
    unset($_COOKIE['stock_num']);
    $this->setcookie('stock_num', '', $time_past);
  }

  public function delete_setting_cookies(){
    $time_past = (((int)wp_date('U')) - 60*60*24*30*12);
    unset($_GET['product_id']);
    unset($_COOKIE['product_id']);
    unset($_COOKIE['variation_id']);
    unset($_GET['variation_id']);

    add_action('init', function(){
      if(function_exists('WC')){
        // if(isset($_GET['test'])){
          $items = WC()->cart->get_cart();
          foreach($items as $item => $values) {
            if(isset($values['data']) && $values['data'] && method_exists($values['data'], 'get_id')){
              $_product =  wc_get_product( $values['data']->get_id());
              if($this->is_setting_product($_product)){
                WC()->cart->remove_cart_item( $item );
                // echo "<b>".$_product->get_title().'</b>  <br> Quantity: '.$values['quantity'].'<br>'; 
                // $price = get_post_meta($values['product_id'] , '_price', true);
                // echo "  Price: ".$price."<br>";
              }
              
            }
            
          }
          // db($items);exit();
        // }
        // WC()->cart->empty_cart();
      }
    });
    

    $this->setcookie('product_id', '', $time_past);
    $this->setcookie('variation_id', '', $time_past);
  }

  

  public function get_current_selected_variation_shape(){

    // $otw_fix_variation = null;
    if(!(isset($_GET['variation_id']) && $_GET['variation_id']))
      return '';

    $variation_id = $_GET['variation_id'];
    
    if(!(isset($this->woo) && $this->woo))
      $this->woo = \OTW\WooRingBuilder\Classes\Woo::instance();
    

    if(!(isset($this->woo) && $this->woo && isset($this->woo->current_selected_variation) && $this->woo->current_selected_variation)){
      $variable_product = new \WC_Product_Variation( $variation_id );
      if(!$variable_product)
        return '';
      // $otw_fix_variation = $variable_product;
        // if(!(wp_doing_ajax()))
          $this->woo->current_selected_variation = $variable_product;
    }
    /*else{
      $otw_fix_variation = $this->woo->current_selected_variation;
    }*/

    if(get_client_ip() == '182.178.169.37' && wp_doing_ajax()){
      // db($this->woo);db($this->woo->current_selected_variation);exit();
    }
    
    
    // $attribute = $variable_product->get_attribute( 'attribute_pa_shape' );
    // db($attribute);
    $variation_variations = $this->woo->current_selected_variation->get_variation_attributes();
    // $variation_variations = $otw_fix_variation->get_variation_attributes();
    
    
    if($variation_variations && is_array($variation_variations) && count($variation_variations) >= 1 && isset($variation_variations['attribute_pa_shape']) && $variation_variations['attribute_pa_shape'] && is_string($variation_variations['attribute_pa_shape'])){
      return $variation_variations['attribute_pa_shape'];
    }

    return '';
  }

  public function get_current_selected_variation_shapes(){
    $current_shape = $this->get_current_selected_variation_shape();
    if($current_shape){
      $parent_product = wc_get_product( $this->woo->current_selected_variation->get_parent_id() );
      $all_shapes = $parent_product->get_attribute('pa_shape');
      if($all_shapes){
        $all_shapes_array = explode(',', strtolower(str_replace(" ",'', $all_shapes)));
        if($all_shapes_array && is_array($all_shapes_array) && count($all_shapes_array) >= 1){
          return $all_shapes_array;
        }
      }
    }
    return array();
  }

  /******************************************/
  /***** get_item_data **********/
  /******************************************/
  public function is_setting_product($_product)
  {
      if(!$_product->is_type( 'variation' ))
          return false;

      $product_cats_ids = wc_get_product_term_ids( $_product->get_parent_id(), 'product_cat' );
      if($product_cats_ids && is_array($product_cats_ids) && count($product_cats_ids) >= 1 && in_array($this->get_option('setting_category'), $product_cats_ids)) 
          return true;
      return false;
  }
  
  /******************************************/
  /***** getUploadDirectoryPath function **********/
  /******************************************/
  /*public function getUploadDirectoryPath($folderName = '') {

    if($folderName == '')
      $folderName = $this->prefix;
    //create new directory in uploads folder for new backups.
    $upload = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . '/' .$folderName;
    return $upload_dir;

  }// getUploadDirectoryPath
  */

  
} // BBWP_CustomFields class

