<?php
namespace OTW\GeneralWooRingBuilder\Traits;

if ( ! defined( 'ABSPATH' ) )	exit;

trait ApiMethods{

  /******************************************/
  /***** get_leads_fields function start from here *********/
  /******************************************/
  public function wp_remote_post($endpoint, $body = array(), $headers = array(), $method = 'POST'){
    

    $default_headers = array(
      // 'Authorization' => 'Bearer '.$this->accessToken,
      // 'Content-Type' => 'application/x-www-form-urlencoded',
      'Content-Type' => 'application/json',
    );
    $headers = array_merge($default_headers, $headers);

    $options = [
      'headers'     => $headers,
      'timeout'     => 60,
      'redirection' => 5,
      'blocking'    => true,
      'httpversion' => '1.0',
      'sslverify'   => true,
      'data_format' => 'body',
      'method'      => $method,
    ];
    if($body && is_array($body) && count($body) >= 1){
      if(isset($body['upload']) && file_exists( $body['upload'] )){
        $fp = fopen($body['upload'], 'rb');
        $size = filesize($body['upload']);
        $options['body'] = fread( $fp, $size );
      }else{
        if(isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/x-www-form-urlencoded')
          $body = http_build_query( $body );
        else
          $body = wp_json_encode( $body );
        $options['body'] = $body;
      }
      
    }
    
    $response = wp_remote_post( $endpoint, $options );
    return $response;
	}

  /******************************************/
  /***** get_leads_fields function start from here *********/
  /******************************************/
  public function wp_remote_get($endpoint, $headers = array(), $method = 'GET'){

    $default_headers = array(
      // 'Authorization' => 'Bearer '.$this->accessToken,
      // 'Content-Type' => 'application/x-www-form-urlencoded',
      'Content-Type' => 'application/json',
    );
    $headers = array_merge($default_headers, $headers);

    $options = [
      'headers'     => $headers,
      'timeout'     => 60,
      'redirection' => 5,
      'blocking'    => true,
      'httpversion' => '1.0',
      'sslverify'   => true,
      'data_format' => 'body',
      'method'      => $method,
    ];
    $response = wp_remote_get( $endpoint, $options );
    return $response;
  }


}