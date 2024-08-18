<?php
namespace OTW\WooRingBuilder\Classes;

// exit if file is called directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PldGetDiamonds extends \OTW\WooRingBuilder\Plugin{

  use \OTW\GeneralWooRingBuilder\Traits\Singleton;

  // public $diamond_api_endpoint = 'http://wdc-intg-customer-staging.herokuapp.com/api/diamonds';
  public $diamond_api_endpoint = 'https://api.pld.live/stockshare/RQWESDF-TYHDGV-NATURAL-SPARKAL';

  public function __construct(){

    // if($this->get_option('nivoda_api_environment') == 'staging')
    //   $this->diamond_api_endpoint = 'http://wdc-intg-customer-staging.herokuapp.com/api/diamonds';
    
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

    if(isset($args['page_number_nivoda']) && $args['page_number_nivoda'] && (int)$args['page_number_nivoda'] >= 2)
      $args['page_number'] = (int) $args['page_number_nivoda'];
    else
      $args['page_number'] = 1;

    $output_diamonds = array();

    $endpoint = add_query_arg($args, $this->diamond_api_endpoint);

    $response = $this->wp_remote_get($endpoint);
    db($endpoint);

    if(!(!is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) && isset($response['body']))){
      $error_message = 'Sorry, we could not connect with pld diamonds API';
      return $error_message;
    }

    $body = wp_remote_retrieve_body($response);
    if(empty($body)){
      $error_message = 'Sorry, we don\'t have any diamonds for your search.';
      return $error_message;
    }
    $body = @json_decode($body, true);
    // if(isset($_GET['test'])){
    //   db($body);
    // }
    if(!(is_array($body) && count($body) >= 1)){
      $error_message = 'Sorry, we don\'t have any diamonds for your search.';
      return $error_message;
      // return $output_diamonds;
    }
    // db($body['data']['diamonds_by_query']);
    return $body;
  }

}