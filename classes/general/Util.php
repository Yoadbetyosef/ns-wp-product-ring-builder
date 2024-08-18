<?php
namespace OTW\GeneralWooRingBuilder;

if ( ! defined( 'ABSPATH' ) )	exit;

class Util{

  use \OTW\GeneralWooRingBuilder\Traits\Singleton;

  // Generates our secret key
  public static function generate_key($length = 40)
  {
      $keyset = 'abcdefghijklmnopqrstuvqxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      $key    = '';

      for ($i = 0; $i < $length; $i++) {
          $key .= substr($keyset, wp_rand(0, strlen($keyset) - 1), 1);
      }

      return $key;
  }

  public static function formatBytes($bytes, $precision = 2)
  {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow   = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
  }

  public static function get_client_ip() {
    $ipaddress = '';
    //HTTP_CF_IPCOUNTRY
    if (getenv('HTTP_CF_CONNECTING_IP'))
        $ipaddress = getenv('HTTP_CF_CONNECTING_IP');
    else if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
      $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
  }

  public static function generate_int($number_values)
  {
  	$number_values = $number_values-2;
  	$lastid = rand(0,9);
  	for($i=0; $i <= $number_values; $i++)
  	{
  		$lastid .= rand(0,9);
  	}
  	return $lastid;
  }

  public static function is_rest_api_request() {
    if ( empty( $_SERVER['REQUEST_URI'] ) ) {
        // Probably a CLI request
        return false;
    }

    $rest_prefix         = trailingslashit( rest_get_url_prefix() );
    $is_rest_api_request = strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) !== false;

    return $is_rest_api_request;
  }

  public static function convert_pipe_string_to_array($string){
    $output = array();
    if($string){
      $options = preg_split( "/\\r\\n|\\r|\\n/", $string );
      if($options && !empty($options)){
        foreach ( $options as $key => $option ) {
          $option_value = esc_attr( $option );
					$option_label = esc_html( $option );

					if ( false !== strpos( $option, '|' ) ) {
						list( $label, $value ) = explode( '|', $option );
						$option_value = esc_attr( $value );
						$option_label = esc_html( $label );
					}

					$option_value = trim($option_value);
					$option_label = trim($option_label);

          if($option_value)
            $output[$option_value] = $option_label;
          else
            $output[] = $option_label;
        }
      }      
    }    
    return $output;
  }

  public static function get_user_request_log($log_data = array()){

    $log_data['time'] = wp_date('U');
    $log_data['IP'] = self::get_client_ip();
    
    if(isset($_SERVER['HTTP_CF_IPCOUNTRY']) && $_SERVER['HTTP_CF_IPCOUNTRY'])
      $log_data['request_country'] =  $_SERVER['HTTP_CF_IPCOUNTRY'];

    if(isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'])
      $log_data['user_agent_string'] =  $_SERVER['HTTP_USER_AGENT']; 

    if(isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'])
      $log_data['request_uri'] =  $_SERVER['REQUEST_URI'];    
    
    $user_data = array();
    if(is_user_logged_in()){
      $current_user = wp_get_current_user();
      $user_data['id'] = $current_user->ID;
      $user_data['username'] = $current_user->user_login;
      $user_data['user_email'] = $current_user->user_email;
      $user_data['display_name'] = $current_user->display_name;
    }
    $log_data['user_data'] = $user_data;
    $log_data['request_args'] = $_REQUEST;
    
    return $log_data;
  }
}