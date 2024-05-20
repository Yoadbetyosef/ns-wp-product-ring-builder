<?php
namespace OTW\WooRingBuilder\Classes;

// exit if file is called directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GetDiamonds extends \OTW\WooRingBuilder\Plugin{

  use \OTW\GeneralWooRingBuilder\Traits\Singleton;

  public $diamond_api_endpoint = 'http://apiservices.vdbapp.com/v2/diamonds';

  public function __construct(){

    // add_action('init', array($this, 'init'));
    
  }// construct function end here

  /******************************************/
  /***** init **********/
  /******************************************/
  public function init() {
    
  }



  /******************************************/
  /***** get_diamonds **********/
  /******************************************/
  public function get_diamonds($args){

    if(isset($args['type']) && $args['type'] != 'Lab_grown_Diamond')
      return '';
    
    if(isset($args['page_number_vdb']) && $args['page_number_vdb'] && (int)$args['page_number_vdb'] >= 2)
      $args['page_number'] = (int) $args['page_number_vdb'];
    else
      $args['page_number'] = 1;

    if(isset($args['price_total_from']) && $args['price_total_from'] && isset($args['price_total_to']) && $args['price_total_to'] && $this->get_option('vdb_price_percentage')){
      $price_total_division = ((((int) $this->get_option('vdb_price_percentage')) / 100) + 1);
      $actual_min_price = ((int) $args['price_total_from']) / $price_total_division;
      // if($actual_min_price >= 1)
        $args['price_total_from'] = $actual_min_price;

      $actual_max_price = ((int) $args['price_total_to']) / $price_total_division;
      // if($actual_max_price >= 1)
        $args['price_total_to'] = $actual_max_price;
    }

    $args['preference[]'] = 'total_sales_price ASC';

    /*if(isset($args['price_total_from']) && $args['price_total_from'] && isset($args['price_total_to']) && $args['price_total_to']){
      $price_total_from = ((int) $args['price_total_from']) - get_diamond_price_with_markup_only($args['price_total_from']);
      if($price_total_from >= 1)
        $args['price_total_from'] = $price_total_from;
      $price_total_to = ((int) $args['price_total_to']) - get_diamond_price_with_markup_only($args['price_total_to']);
      if($price_total_to >= 1)
        $args['price_total_to'] = $price_total_to;
    }*/
    

      
      // wp_send_json_success($args);
      $endpoint = add_query_arg($args, $this->diamond_api_endpoint);
      // db(urldecode($endpoint));exit();
      //$headers = array('Authorization' => "Token token=iltz_Ie1tN0qm-ANqF7X6SRjwyhmMtzZsmqvyWOZ83I, api_key=_eTAh9su9_0cnehpDpqM9xA", );

      //live token
      // $headers = array('Authorization' => "Token token=zBit6c061SDMePSr5w24CLWuPWPmoRNmugKPgXUclN0, api_key=_3SZ05DUkNyIub7ohugscYA", );
      $headers = array('Authorization' => "Token token=".$this->get_option('diamond_api_token').", api_key=".$this->get_option('diamond_api_key') );
      $response = otw_woo_ring_builder()->wp_remote_get($endpoint, $headers);
      $error_message = '';
      if(!(!is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) && isset($response['body']))){
          $error_message = 'Sorry, we could not connect with diamonds API';
          // wp_send_json_error($error_message);
          // die();
          return $error_message;
      }
      
      $body = wp_remote_retrieve_body($response);
      
      $body = @json_decode($body, true);
      if(!(isset($body['response']) && isset($body['response']['body']) && isset($body['response']['body']['diamonds']) && isset($body['response']['body']['total_diamonds_found']) && $body['response']['body']['total_diamonds_found'] >= 1)){
          $error_message = 'Sorry, we don\'t have any diamonds for your search.';
          // wp_send_json_error($error_message);
          // die();
          return $error_message;
      }
      // if(get_client_ip() == '182.178.231.168' || get_client_ip() == '39.45.129.135'){
      //   db(json_encode($args));
      //   db($args);
      //   db($body['response']['body']);
      // }
      return $body['response']['body'];
  }

  /******************************************/
  /***** get_diamond_by_stock_num **********/
  /******************************************/
  public function get_diamond_by_stock_num($stock_num){
    $endpoint = $this->diamond_api_endpoint;
    // $endpoint = 'http://apiservices.vdbapp.com/v2/diamonds';
    $args = array(
        'type' => 'Lab_grown_Diamond',
        'markup_mode' => 'true',
        'stock_num' => $stock_num,
        'show_unavailable' => 'true',
        'currency_code' => 'USD',
        'exchange_rate' => '1',
    );
    $endpoint = add_query_arg($args, $endpoint);
    $headers = array('Authorization' => "Token token=".$this->get_option('diamond_api_token').", api_key=".$this->get_option('diamond_api_key') );
    $response = $this->wp_remote_get($endpoint, $headers);


    if(!(!is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) && isset($response['body'])))
      return false;

    $body = wp_remote_retrieve_body($response);
    $body = @json_decode($body, true);

    if(!(isset($body['response']) && isset($body['response']['body']) && isset($body['response']['body']['diamonds']) && isset($body['response']['body']['total_diamonds_found']) && $body['response']['body']['total_diamonds_found'] >= 1))
        return false;

    

    return $this->format_diamond_data($body['response']['body']['diamonds'][0]);
    
    return $response;
  }

  /******************************************/
  /***** get_loop_diamonds **********/
  /******************************************/
  function format_diamond_data($diamond){

    
    if(isset($diamond['total_sales_price']) && $diamond['total_sales_price']){

      // $diamond['orig_sales_price'] = $diamond['total_sales_price'];
      $diamond['total_sales_price'] = get_diamond_price_with_markup($diamond['total_sales_price']);
      
      // $db_rate = (int) $this->get_option('vdb_price_percentage');
      // if($db_rate){
      //   $db_rate = ($diamond['total_sales_price'] * $db_rate)/100;
      //   $diamond['total_sales_price'] += $db_rate;
      // }
      // if(isset($diamond['markup_price']) && isset($diamond['markup_price']) && $diamond['markup_price'])
      //   $diamond['total_sales_price'] = (float)number_format(((int)$diamond['markup_price'] /100), 0, '.', '');
      // else
      //   $diamond['total_sales_price'] = (float)number_format(((int)$diamond['price'] /100), 0, '.', '');
    }

    // if(isset($_GET['test'])){
    //   db($diamond);exit();
    // }

    return $diamond;
  }

  	
}