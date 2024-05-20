<?php
namespace OTW\WooRingBuilder\Classes;

// exit if file is called directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NivodaGetDiamonds extends \OTW\WooRingBuilder\Plugin{

  use \OTW\GeneralWooRingBuilder\Traits\Singleton;
  use \OTW\WooRingBuilder\Traits\NivodaLocalDB;

  // public $diamond_api_endpoint = 'http://wdc-intg-customer-staging.herokuapp.com/api/diamonds';
  public $diamond_api_endpoint = 'https://integrations.nivoda.net/api/diamonds';

  public function __construct(){

    if($this->get_option('nivoda_api_environment') == 'staging')
      $this->diamond_api_endpoint = 'http://wdc-intg-customer-staging.herokuapp.com/api/diamonds';
    
    // add_action('init', array($this, 'init'));
    
  }// construct function end here

  /******************************************/
  /***** init **********/
  /******************************************/
  public function init() {
    
  }

  /******************************************/
  /***** get_loop_diamonds **********/
  /******************************************/
  function convert_nivoda_to_vdb($diamond){
    $output = array();
    
    $output['video_url'] = '';
    if(isset($diamond['diamond']['certificate']) && isset($diamond['diamond']['certificate']['video']) && $diamond['diamond']['certificate']['video']){
      $full_url = explode('/video/', $diamond['diamond']['certificate']['video']);
      
      $output['video_url'] = $full_url[0].'/video/rsp/autoplay/autoplay';
    }

    $output['stock_num'] = '';
    $output['id'] = '';
    if(isset($diamond['id']) && $diamond['id']){
      $stock_num = str_replace(array('DIAMOND/', 'nivoda-'), array('',''), $diamond['id']);
      $output['stock_num'] = 'nivoda-'.$stock_num;
      $output['id'] = 'nivoda-'.$stock_num;
    }

    $output['image_url'] = '';
    if(isset($diamond['diamond']['certificate']) && isset($diamond['diamond']['certificate']['image']) && $diamond['diamond']['certificate']['image'])
      $output['image_url'] = $diamond['diamond']['certificate']['image'];

    $output['size'] = '';
    if(isset($diamond['diamond']['certificate']) && isset($diamond['diamond']['certificate']['carats']) && $diamond['diamond']['certificate']['carats'])
      $output['size'] = $diamond['diamond']['certificate']['carats'];

    $output['shape'] = '';
    if(isset($diamond['diamond']['certificate']) && isset($diamond['diamond']['certificate']['shape']) && $diamond['diamond']['certificate']['shape']){
      $output['shape'] = $this->get_shapes_list()[$diamond['diamond']['certificate']['shape']];
      $output['shape_api'] = $diamond['diamond']['certificate']['shape'];
    }
      

    $output['total_sales_price'] = '';

    if(isset($diamond['price']) && isset($diamond['price']) && $diamond['price']){
      if(isset($diamond['upload']) && $diamond['upload'] && $diamond['upload'] == 'csv'){
        if(isset($diamond['markup_price']) && isset($diamond['markup_price']) && $diamond['markup_price'])
          $output['total_sales_price'] = (float)number_format(((int)$diamond['markup_price'] ), 0, '.', '');
        else
          $output['total_sales_price'] = (float)number_format(((int)$diamond['price'] ), 0, '.', '');
        $output['base_sales_price'] = (float)number_format(((int)$diamond['price'] ), 0, '.', '');
      }else{
        if(isset($diamond['markup_price']) && isset($diamond['markup_price']) && $diamond['markup_price'])
          $output['total_sales_price'] = (float)number_format(((int)$diamond['markup_price'] /100), 0, '.', '');
        else
          $output['total_sales_price'] = (float)number_format(((int)$diamond['price'] /100), 0, '.', '');
        $output['base_sales_price'] = (float)number_format(((int)$diamond['price'] /100), 0, '.', '');
      }
      
      
    }
    $output['color'] = '';
    if(isset($diamond['diamond']['certificate']) && isset($diamond['diamond']['certificate']['color']) && $diamond['diamond']['certificate']['color'])
      $output['color'] = $diamond['diamond']['certificate']['color'];

    $output['clarity'] = '';
    if(isset($diamond['diamond']['certificate']) && isset($diamond['diamond']['certificate']['clarity']) && $diamond['diamond']['certificate']['clarity'])
      $output['clarity'] = $diamond['diamond']['certificate']['clarity'];

    $output['symmetry'] = '';
    if(isset($diamond['diamond']['certificate']) && isset($diamond['diamond']['certificate']['symmetry']) && $diamond['diamond']['certificate']['symmetry'])
      $output['symmetry'] = $diamond['diamond']['certificate']['symmetry'];

    $output['meas_length'] = '';
    if(isset($diamond['diamond']['certificate']) && isset($diamond['diamond']['certificate']['length']) && $diamond['diamond']['certificate']['length'])
      $output['meas_length'] = $diamond['diamond']['certificate']['length'];

    $output['meas_width'] = '';
    $output['meas_ratio'] = '';
    if(isset($diamond['diamond']['certificate']) && isset($diamond['diamond']['certificate']['width']) && $diamond['diamond']['certificate']['width'])
      $output['meas_width'] = $diamond['diamond']['certificate']['width'];

    $output['lab'] = '';
    if(isset($diamond['diamond']['certificate']) && isset($diamond['diamond']['certificate']['lab']) && $diamond['diamond']['certificate']['lab'])
      $output['lab'] = $diamond['diamond']['certificate']['lab'];

    $output['cert_url'] = '';
    if(isset($diamond['diamond']['certificate']) && isset($diamond['diamond']['certificate']['pdfUrl']) && $diamond['diamond']['certificate']['pdfUrl'])
      $output['cert_url'] = $diamond['diamond']['certificate']['pdfUrl'];
      
    if($output['meas_length'] && $output['meas_width']){
      $meas_width = (float)$output['meas_width'];
      if($meas_width >= 0.1)
        $output['meas_ratio'] = (float)number_format(((float)$output['meas_length']/ (float)$output['meas_width']), 2, '.', '');
    }
    // if(isset($_GET['test'])){
    //   db($output);
    // }
    return $output;
  }
  /******************************************/
  /***** get_auth_token **********/
  /******************************************/
  public function get_auth_token(){
    //staging auth totken
    // $auth_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6ImM1YWRiZWM0LTRkZjQtNDhlMC1iY2RlLTMxZmYxYjgxOGE5MiIsInJvbGUiOiJDVVNUT01FUiIsInN1YnR5cGUiOm51bGwsImNvdW50cnkiOiJHQiIsInB0IjoiREVGQVVMVCIsImlmIjoiIiwiY2lkIjoiZTk3MDEyYzYtOGE3Ni00NzNmLTljZjctMzBlMGU2ZjI3MWRhIiwiZ2VvX2NvdW50cnkiOiJHQiIsImFwaSI6dHJ1ZSwiYXBpX2giOnRydWUsImFwaV9jIjp0cnVlLCJhcGlfbyI6dHJ1ZSwiYXBpX3IiOnRydWUsImlhdCI6MTY5NTY2NDgzMCwiZXhwIjoxNjk1NzUxMjMwfQ.u5BKLy6zqPurVdNzJEGQlqbrz482uh1jAdY9lsbEpc4';
    // $auth_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6ImM1YWRiZWM0LTRkZjQtNDhlMC1iY2RlLTMxZmYxYjgxOGE5MiIsInJvbGUiOiJDVVNUT01FUiIsInN1YnR5cGUiOm51bGwsImNvdW50cnkiOiJHQiIsInB0IjoiREVGQVVMVCIsImlmIjoiIiwiY2lkIjoiZTk3MDEyYzYtOGE3Ni00NzNmLTljZjctMzBlMGU2ZjI3MWRhIiwiZ2VvX2NvdW50cnkiOiJHQiIsImFwaSI6dHJ1ZSwiYXBpX2giOnRydWUsImFwaV9jIjp0cnVlLCJhcGlfbyI6dHJ1ZSwiYXBpX3IiOnRydWUsImlhdCI6MTY5NTgzMzgyOCwiZXhwIjoxNjk1OTIwMjI4fQ.YNKvCyh69tCfCNIChOLUS8vWvQa8lEcQpoFvoMrwoLc';
    //live auth token
    // $auth_token = 'eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJqakNTTDAxd3o0THAwaFlpTHp6VGJqYUJTYm5XT3hMbGJhUlRNWTJrSUFvIn0.eyJleHAiOjE3MDAxNTI4MzEsImlhdCI6MTY5NTgzMjgzMSwianRpIjoiZTNhMGUzNjMtZDljMy00NzkwLWI2ZjYtNjdjNDY1ZmFkZjkyIiwiaXNzIjoiaHR0cHM6Ly9rZXljbG9hay12Mi5uaXZvZGFhcGkubmV0L2F1dGgvcmVhbG1zL05pdm9kYSIsImF1ZCI6ImFjY291bnQiLCJzdWIiOiJmOjZkN2QxZGE5LTYwMzQtNGU2ZC1hNzBhLWVlMDQ0Zjc5NmFmMTp5b2FkQG5hdHVyZXNwYXJrbGUuY29tIiwidHlwIjoiQmVhcmVyIiwiYXpwIjoibml2b2RhYXBwIiwic2Vzc2lvbl9zdGF0ZSI6IjE0MDYzZjRkLWJhOGQtNDhiZS1iYTM0LWQ5ODE2NzEwYjg1MyIsImFjciI6IjEiLCJyZWFsbV9hY2Nlc3MiOnsicm9sZXMiOlsib2ZmbGluZV9hY2Nlc3MiLCJ1bWFfYXV0aG9yaXphdGlvbiJdfSwicmVzb3VyY2VfYWNjZXNzIjp7ImFjY291bnQiOnsicm9sZXMiOlsibWFuYWdlLWFjY291bnQiLCJtYW5hZ2UtYWNjb3VudC1saW5rcyIsInZpZXctcHJvZmlsZSJdfX0sInNjb3BlIjoib3BlbmlkIGV4dHJhX2luZm9fZm9yX25pdm9kYSBlbWFpbCBwcm9maWxlIiwic2lkIjoiMTQwNjNmNGQtYmE4ZC00OGJlLWJhMzQtZDk4MTY3MTBiODUzIiwiYXBpX28iOmZhbHNlLCJsYXN0TmFtZSI6IkJldCBZb3NlZiAiLCJjb3VudHJ5IjoiVVMiLCJlbWFpbF92ZXJpZmllZCI6ZmFsc2UsInJvbGUiOiJDVVNUT01FUiIsInB0IjoiREVGQVVMVCIsImFwaV9yIjpmYWxzZSwiYXBpX2giOmZhbHNlLCJwcmVmZXJyZWRfdXNlcm5hbWUiOiJ5b2FkQG5hdHVyZXNwYXJrbGUuY29tIiwiZ2l2ZW5fbmFtZSI6IllvYWQgIiwiZmlyc3ROYW1lIjoiWW9hZCAiLCJhcGlfYyI6ZmFsc2UsImdlb19jb3VudHJ5IjoiVVMiLCJuYW1lIjoiWW9hZCAgQmV0IFlvc2VmICIsImlkIjoiYTg3NmNhNmUtOGE3Ni00OGQ2LWE1OTItM2IyYzY0ODRmMzQxIiwiYXBpIjpmYWxzZSwiZmFtaWx5X25hbWUiOiJCZXQgWW9zZWYgIiwiZW1haWwiOiJ5b2FkQG5hdHVyZXNwYXJrbGUuY29tIiwiY2lkIjoiMWY4MjdhMzMtMWZhZS00Mjk1LTllMTAtOTFlMDA5YTYyYjUzIn0.EcDRfw8k7UlBRxoH9Ops_jE8c98auxVNIEGvcxQ6SSRYcguPNiTIeYRUuIPY_ssbZ0YXN2x3VWrtN12Xe8PbaLmgC1cgx8o1MLeUYS4BtZbWG6ydnZOnnpeuTYggye-1bQxN0sDr2neVo_cP5y3sfQh03DkZhiUd58fkLZBB59xpvGSHAHztwJs_zaJ0ey4jxCVxRim3Py9hSgXusABvlYDVu3dy5AxdX8_MTAtcd67spAxCdmP-C5c0zB9zvSl8lc_HTlp1fdgtIEgPj47HIb53dKnCVbTycGdslUQPYsLSOPBKQ6rmUiQJKIL80D7QAvk_NboppatKsQP47ROWTw';
    // $auth_token = 'eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJqakNTTDAxd3o0THAwaFlpTHp6VGJqYUJTYm5XT3hMbGJhUlRNWTJrSUFvIn0.eyJleHAiOjE3MDAxNTMxNzUsImlhdCI6MTY5NTgzMzE3NSwianRpIjoiMzJiZDY1ZjAtY2VjYy00MTVjLWJiZjAtNzBjM2RjZTA0ZmI0IiwiaXNzIjoiaHR0cHM6Ly9rZXljbG9hay12Mi5uaXZvZGFhcGkubmV0L2F1dGgvcmVhbG1zL05pdm9kYSIsImF1ZCI6ImFjY291bnQiLCJzdWIiOiJmOjZkN2QxZGE5LTYwMzQtNGU2ZC1hNzBhLWVlMDQ0Zjc5NmFmMTp5b2FkQG5hdHVyZXNwYXJrbGUuY29tIiwidHlwIjoiQmVhcmVyIiwiYXpwIjoibml2b2RhYXBwIiwic2Vzc2lvbl9zdGF0ZSI6IjYwZDZlY2M5LTg4YjUtNGRmNy04YjFkLTZkMTNmNDU0MDZkMSIsImFjciI6IjEiLCJyZWFsbV9hY2Nlc3MiOnsicm9sZXMiOlsib2ZmbGluZV9hY2Nlc3MiLCJ1bWFfYXV0aG9yaXphdGlvbiJdfSwicmVzb3VyY2VfYWNjZXNzIjp7ImFjY291bnQiOnsicm9sZXMiOlsibWFuYWdlLWFjY291bnQiLCJtYW5hZ2UtYWNjb3VudC1saW5rcyIsInZpZXctcHJvZmlsZSJdfX0sInNjb3BlIjoib3BlbmlkIGV4dHJhX2luZm9fZm9yX25pdm9kYSBlbWFpbCBwcm9maWxlIiwic2lkIjoiNjBkNmVjYzktODhiNS00ZGY3LThiMWQtNmQxM2Y0NTQwNmQxIiwiYXBpX28iOmZhbHNlLCJsYXN0TmFtZSI6IkJldCBZb3NlZiAiLCJjb3VudHJ5IjoiVVMiLCJlbWFpbF92ZXJpZmllZCI6ZmFsc2UsInJvbGUiOiJDVVNUT01FUiIsInB0IjoiREVGQVVMVCIsImFwaV9yIjpmYWxzZSwiYXBpX2giOmZhbHNlLCJwcmVmZXJyZWRfdXNlcm5hbWUiOiJ5b2FkQG5hdHVyZXNwYXJrbGUuY29tIiwiZ2l2ZW5fbmFtZSI6IllvYWQgIiwiZmlyc3ROYW1lIjoiWW9hZCAiLCJhcGlfYyI6ZmFsc2UsImdlb19jb3VudHJ5IjoiVVMiLCJuYW1lIjoiWW9hZCAgQmV0IFlvc2VmICIsImlkIjoiYTg3NmNhNmUtOGE3Ni00OGQ2LWE1OTItM2IyYzY0ODRmMzQxIiwiYXBpIjpmYWxzZSwiZmFtaWx5X25hbWUiOiJCZXQgWW9zZWYgIiwiZW1haWwiOiJ5b2FkQG5hdHVyZXNwYXJrbGUuY29tIiwiY2lkIjoiMWY4MjdhMzMtMWZhZS00Mjk1LTllMTAtOTFlMDA5YTYyYjUzIn0.OJYxgixXe4x2cBSyvzqSpJvtTN--DCiegTuM-bRfuNy3NMHmnUaxTRugE3Wne8jtLZ77yC424J3lkVBBrKVdblT1HWcDNCtf99Zyb3h2T95UUSUPNx9zeS69IRn0ETvRDvNq8FOITe_MZeneLwKTiM5yc4acq59iaYUpU6pEp8vepK1VLwH72EPw95is9SwZbR0JV1ljIeMaifVfoaaQRcnxICiJ5EztZHBv9bRQ9NJ0gwI4f8WG69xbSVplU4Caf2cAg9giACsh893bTDcrHSwXLlqRhcAqX_dnxQWy16KtI3AIDRmbCaDpWxl6PTS4KZaBXaWwszPN5UmQxNO5Ww';
    // $auth_token = 'eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICJqakNTTDAxd3o0THAwaFlpTHp6VGJqYUJTYm5XT3hMbGJhUlRNWTJrSUFvIn0.eyJleHAiOjE3MDAyNTY3MzMsImlhdCI6MTY5NTkzNjczMywianRpIjoiZjdjMDRlNmYtNDJhNi00ZGVmLTk2YzMtN2M3YmRhNzc3NTA5IiwiaXNzIjoiaHR0cHM6Ly9rZXljbG9hay12Mi5uaXZvZGFhcGkubmV0L2F1dGgvcmVhbG1zL05pdm9kYSIsImF1ZCI6ImFjY291bnQiLCJzdWIiOiJmOjZkN2QxZGE5LTYwMzQtNGU2ZC1hNzBhLWVlMDQ0Zjc5NmFmMTp5b2FkQG5hdHVyZXNwYXJrbGUuY29tIiwidHlwIjoiQmVhcmVyIiwiYXpwIjoibml2b2RhYXBwIiwic2Vzc2lvbl9zdGF0ZSI6IjkxZTBkZTQ0LTU1MzMtNDQwOS1hZWIzLTEwOTk4YmM5ZWI0MyIsImFjciI6IjEiLCJyZWFsbV9hY2Nlc3MiOnsicm9sZXMiOlsib2ZmbGluZV9hY2Nlc3MiLCJ1bWFfYXV0aG9yaXphdGlvbiJdfSwicmVzb3VyY2VfYWNjZXNzIjp7ImFjY291bnQiOnsicm9sZXMiOlsibWFuYWdlLWFjY291bnQiLCJtYW5hZ2UtYWNjb3VudC1saW5rcyIsInZpZXctcHJvZmlsZSJdfX0sInNjb3BlIjoib3BlbmlkIGV4dHJhX2luZm9fZm9yX25pdm9kYSBlbWFpbCBwcm9maWxlIiwic2lkIjoiOTFlMGRlNDQtNTUzMy00NDA5LWFlYjMtMTA5OThiYzllYjQzIiwiYXBpX28iOnRydWUsImxhc3ROYW1lIjoiQmV0IFlvc2VmICIsImNvdW50cnkiOiJVUyIsImVtYWlsX3ZlcmlmaWVkIjpmYWxzZSwicm9sZSI6IkNVU1RPTUVSIiwicHQiOiJERUZBVUxUIiwiYXBpX3IiOnRydWUsImFwaV9oIjp0cnVlLCJwcmVmZXJyZWRfdXNlcm5hbWUiOiJ5b2FkQG5hdHVyZXNwYXJrbGUuY29tIiwiZ2l2ZW5fbmFtZSI6IllvYWQgIiwiZmlyc3ROYW1lIjoiWW9hZCAiLCJhcGlfYyI6ZmFsc2UsImdlb19jb3VudHJ5IjoiVVMiLCJuYW1lIjoiWW9hZCAgQmV0IFlvc2VmICIsImlkIjoiYTg3NmNhNmUtOGE3Ni00OGQ2LWE1OTItM2IyYzY0ODRmMzQxIiwiYXBpIjp0cnVlLCJmYW1pbHlfbmFtZSI6IkJldCBZb3NlZiAiLCJlbWFpbCI6InlvYWRAbmF0dXJlc3BhcmtsZS5jb20iLCJjaWQiOiIxZjgyN2EzMy0xZmFlLTQyOTUtOWUxMC05MWUwMDlhNjJiNTMifQ.hWXeJb6cSUQLsoa1BkhuSu-BbxgjZtGUBkuKGlrjatz30nvz9ioj7NAOn5HoCrX8TpmtD2544gncgyVoD-jrHzqSYpUCkfn5wKv3qMRBPfVSPkdDWp2vb3bmbzamOIWnnKLF_0w9-qXjfU3uQEBvy9K7J_Iznq_yiSz4n-XTXMUyJlR9b4ZGco0MIwpz8AjU7rQLlG1aznT62RZHgk8wRhC5tpqHsQ0GogQiFpxJkpBm1bZvkzGvjH4UEDVLVE3sOO0aGLXd8Oa8iC-I2Nkg6AI4KAncYrhJoP5bwyo0WrFGALmWiNESLZjfLDLKwmBG8_z1rYIYQh5L7JIJ15ushQ';
    $auth_token = get_option('nivoda_api_auth_token');

    if(isset($_GET['get_new_nivoda_auth_token']) && $_GET['get_new_nivoda_auth_token'] == 'yes')
      $auth_token = '';

    if($auth_token)
      return $auth_token;

    $auth_token = '';
    //staging username and password
    // $body = "query {authenticate{username_and_password(username:\"testaccount@sample.com\",password:\"staging-nivoda-22\"){token}}}";

    //live username and password
    $body = "query {authenticate{username_and_password(username:\"".$this->get_option('nivoda_api_username')."\",password:\"".$this->get_option('nivoda_api_password')."\"){token}}}";
    
    $body = ['query' => $body];
    $response = $this->wp_remote_post($this->diamond_api_endpoint, $body);
    if(!(!is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) && isset($response['body'])))
      return $auth_token;

    $body = wp_remote_retrieve_body($response);
    if(empty($body))
      return $auth_token;

    $body = @json_decode($body, true);
    if(!(isset($body['data']) && isset($body['data']['authenticate']) && isset($body['data']['authenticate']['username_and_password']) && isset($body['data']['authenticate']['username_and_password']['token']) && $body['data']['authenticate']['username_and_password']['token']))
      return $auth_token;
    
    update_option('nivoda_api_auth_token', $body['data']['authenticate']['username_and_password']['token']);
    return $body['data']['authenticate']['username_and_password']['token'];
  }


  /******************************************/
  /***** get_diamonds **********/
  /******************************************/
  public function get_diamonds($args){

    if($this->nivoda_api_type == 'local'){
      return $this->get_local_diamonds($args);
    }

    if(isset($args['page_number_nivoda']) && $args['page_number_nivoda'] && (int)$args['page_number_nivoda'] >= 2)
      $args['page_number'] = (int) $args['page_number_nivoda'];
    else
      $args['page_number'] = 1;

    $output_diamonds = array();
    $auth_token = $this->get_auth_token();
    // db($auth_token);
    //working query
    
    $search_query = '{availability:AVAILABLE';
    $search_query .= ',has_image:true';
    // $search_query .= ',color:[D,E]';
    
    if(isset($args['type']) && $args['type']){
      if($args['type'] == 'Lab_grown_Diamond')
        $search_query .= ',labgrown:true';
      else
        $search_query .= ',labgrown:false';
    }

    if(isset($args['shapes[]']) && $args['shapes[]']){
      // $search_query .= ',shapes:["'.strtoupper($args['shapes[]']).'"]';
      $search_query .= ',shapes:["'.implode('","',$this->get_shape_types($args['shapes[]'])).'"]';
    }
    if(isset($args['size_from']) && $args['size_from'] && isset($args['size_to']) && $args['size_to']){
      $search_query .= ',sizes:{from:'.$args['size_from'].',to:'.$args['size_to'].'}';
    }
    if(isset($args['price_total_from']) && $args['price_total_from'] && isset($args['price_total_to']) && $args['price_total_to']){
      // $args['price_total_to'] = (int)$args['price_total_to'] * 100;
      // $args['price_total_from'] = (int)$args['price_total_from'] * 100;
      $search_query .= ',dollar_value:{from:'.$args['price_total_from'].',to:'.$args['price_total_to'].'}';
    }
    
    

    if(isset($args['color_from']) && $args['color_from'] && isset($args['color_to']) && $args['color_to']){
      $found_colors = get_all_values_between_range($args['color_from'], $args['color_to'], $this->get_colorsS_list());
      if($found_colors)
        $search_query .= ',color:['.implode(',', $found_colors).']';
    }

    if(isset($args['clarity_from']) && $args['clarity_from'] && isset($args['clarity_to']) && $args['clarity_to']){
      $found_colors = get_all_values_between_range($args['clarity_from'], $args['clarity_to'], $this->get_clarity_list());
      if($found_colors)
        $search_query .= ',clarity:['.implode(',', $found_colors).']';
    }

    // if(isset($args['filter_ids']) && $args['filter_ids']){
      // $search_query .= ',filter_ids:"'.str_replace(array('DIAMOND/'), array(''), $args['filter_ids']).'"';
    // }
    
    
    // $search_query_json = json_encode($search_query);
    // $search_query_json = str_replace(array('"'), array(''), $search_query_json);
    $search_query .= '}';
    
    $offset = '';
    // $args['page_number'] = 2;
    if(isset($args['page_number']) && (int)$args['page_number'] >= 2 && isset($args['page_size']))
      $offset .= ',offset:'.(((int) $args['page_number'] - 1) * ((int)$args['page_size']));
    if(isset($args['page_size']) && $args['page_size'])
      $offset .= ',limit:'.$args['page_size'];
    $query = 'query{
      diamonds_by_query(order:{type:price,direction:ASC},query:'.$search_query.$offset.'){total_count,items{id,price,markup_price,diamond{video,image,certificate{id,certNumber,carats,cut,clarity,polish,symmetry,color,shape,image,video,lab,pdfUrl,length,width,depth}}}},
      diamonds_by_query_count(query:'.$search_query.')
    }';
    if(isset($args['diamonds_by_query_count']) && $args['diamonds_by_query_count'] == 'no'){
      $query = 'query{
        diamonds_by_query(order:{type:price,direction:ASC},query:'.$search_query.$offset.'){total_count,items{id,price,markup_price,diamond{video,image,certificate{id,certNumber,carats,cut,clarity,polish,symmetry,color,shape,image,video,lab,pdfUrl,length,width,depth}}}}
      }';
    }
    
    // if(get_client_ip() == '182.178.231.168'){
    //   db($query);
    //   db($args);
    // }
    // if(isset($args['filter_ids']) && $args['filter_ids']){
    //   $query = 'query{
    //     get_diamond_by_id(diamond_id:"'.str_replace(array('DIAMOND/'), array(''), $args['filter_ids']).'"){id,price,diamond{video,image,certificate{id,certNumber,carats,cut,clarity,polish,symmetry,color,shape,image,video,lab,pdfUrl,length,width,depth}}}
    //   }';
    // }
    // db($args);
    // if(isset($_GET['test'])){
    //   db($args);db($query);
    // }
    
    // $query = 'query{
    //   diamonds_by_query(query:{labgrown:true,shapes:"OVAL"}){total_count,items{id,price,diamond{video,image,certificate{id,certNumber,carats,cut,clarity,polish,symmetry,color,shape,image,video,lab,pdfUrl,length,width,depth}}}},
    //   diamonds_by_query_count(query:{labgrown:true})
    // }';
    // $query = 'query{diamonds_by_query_count(query:{labgrown:true})}';
    
    // $query = 'query{diamonds_by_query(query:{labgrown:true}){total_count,items{id,price,diamond{video,certificate{id,certNumber,carats,cut,clarity,polish,symmetry,color,shape}}}}}';
    $body = ['query' => $query];

    $headers = array(
      'Authorization' => 'Bearer '.$auth_token,
    );
    // if(get_client_ip() == '182.178.244.62'){
    //   db($endpoint);
    //   db($args);
    // }
    $response = $this->wp_remote_post($this->diamond_api_endpoint, $body, $headers);
    // db($auth_token);db($this->diamond_api_endpoint);db(json_encode($body));db($response);exit();

    if(!(!is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) && isset($response['body']))){
      $error_message = 'Sorry, we could not connect with diamonds API';
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
    // db($body['data']['diamonds_by_query']['items']);exit();
    if(!(isset($body['data']) && isset($body['data']['diamonds_by_query']) && isset($body['data']['diamonds_by_query']['items']) && is_array($body['data']['diamonds_by_query']['items']) && count($body['data']['diamonds_by_query']['items']) >= 1 /*&& isset($body['data']['diamonds_by_query_count']) && $body['data']['diamonds_by_query_count'] >= 1 && isset($body['data']['authenticate']['username_and_password']['token']) && $body['data']['authenticate']['username_and_password']['token']*/)){
      $error_message = 'Sorry, we don\'t have any diamonds for your search.';
      return $error_message;
      // return $output_diamonds;
    }
    // db($body['data']['diamonds_by_query']);
    return $body['data'];
  }


  public function get_diamond_by_stock_num($stock_num){

    if($this->nivoda_api_type == 'local'){
      return $this->get_local_diamond_by_stock_num($stock_num);
    }

    // if(isset($this->current_diamond) && $this->current_diamond && isset($this->current_diamond['stock_num']) && $this->current_diamond['stock_num'] == $stock_num)
    //   return $this->current_diamond;

    $endpoint = $this->diamond_api_endpoint;
    // $endpoint = 'http://apiservices.vdbapp.com/v2/diamonds';
    $query = 'query{
      get_diamond_by_id(diamond_id:"'.str_replace(array('DIAMOND/', 'nivoda-'), array('',''), $stock_num).'"){id,price,markup_price,diamond{video,image,certificate{id,certNumber,carats,cut,clarity,polish,symmetry,color,shape,image,video,lab,pdfUrl,length,width,depth}}}
    }';

    $body = ['query' => $query];

    $headers = array(
      'Authorization' => 'Bearer '.$this->get_auth_token(),
    );

    $response = $this->wp_remote_post($this->diamond_api_endpoint, $body, $headers);
    

    if(!(!is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) && isset($response['body']))){
      $error_message = 'Sorry, we could not connect with diamonds API';
      return $error_message;
    }

    $body = wp_remote_retrieve_body($response);

    if(empty($body)){
      $error_message = 'Sorry, we don\'t have any diamonds for your search.';
      return $error_message;
    }
    $body = @json_decode($body, true);
    if(!(isset($body['data']) && isset($body['data']['get_diamond_by_id']) && is_array($body['data']['get_diamond_by_id']) &&  count($body['data']['get_diamond_by_id']) >= 1)){
      $error_message = 'Sorry, we don\'t have any diamonds for your search.';
      return $error_message;
      // return $output_diamonds;
    }
    $diamond = $this->convert_nivoda_to_vdb($body['data']['get_diamond_by_id']);
    // if(isset($_GET['tests'])){
    //   db($body['data']['get_diamond_by_id']);
    // }
    // $this->current_diamond = $diamond;
    // db($body['data']['diamonds_by_query']);
    return $diamond;

    
    // return $response;
  }

  /******************************************/
  /***** get_shape_types **********/
  /******************************************/
  public function get_shape_types($shape) {
    $shape = strtoupper($shape);
    $output = array();
    foreach($this->get_shapes_list() as $key=>$single_shape){
      if($shape == $single_shape)
        $output[] = $key;
    }
    return $output;
  }

  public function get_colorsS_list (){
    return array(
      "D",
      "E",
      'F',
      'G',
      'H',
      'I',
      'J',
      'K',
      'L',
      'M',
      'N',
      'NO',
      'O',
      'OP',
      'PR',
      'P',
      'Q',
      'QR',
      'R',
      'S',
      'SZ',
      'ST',
      'T',
      'U',
      'UV',
      'V',
      'W',
      'WX',
      'X',
      'Y',
      'YZ',
      'Z',
      'FANCY',
    );
  }
  public function get_clarity_list (){
    return array(
      'FL',
      'IF',
      'VVS1',
      'VVS2',
      'VS1',
      'VS2',
      'SI1',
      'SI2',
      'SI3',
      'I1',
      'I2',
      'I3',
    );
  }
  public function get_shapes_list (){
    return array(
      'ROUND' => 'ROUND',
      'OCTAGONAL' => 'OCTAGONAL',
      'ASSCHER' => 'ASSCHER',
      'ROUND MODIFIED BRILLIANT' => 'ROUND',
      'OTHER' => 'OTHER',
      'EMERALD' => 'EMERALD',
      'PENTAGONAL' => 'PENTAGONAL',
      'RECTANGULAR' => 'RECTANGULAR',
      'BRIOLETTE' => 'BRIOLETTE',
      'PEAR MODIFIED BRILLIANT' => 'PEAR',
      'OLD EUROPEAN' => 'OLD EUROPEAN',
      'SQUARE' => 'SQUARE',
      'PEAR' => 'PEAR',
      'CUSHION B' => 'CUSHION',
      'KITE' => 'KITE',
      'EUROPEAN' => 'EUROPEAN',
      'HEXAGONAL' => 'HEXAGONAL',
      'BULLET' => 'BULLET',
      'RECTANGLE' => 'RECTANGLE',
      'TRAPEZOID' => 'TRAPEZOID',
      'HALFMOON' => 'HALFMOON',
      'SHIELD' => 'SHIELD',
      'OVAL MIXED CUT' => 'OVAL',
      'OVAL' => 'OVAL',
      'TRAPEZE' => 'TRAPEZE',
      'BAGUETTE' => 'BAGUETTE',
      'CUSHION MODIFIED' => 'CUSHION',
      'CUSHION BRILLIANT' => 'CUSHION',
      'RADIANT' => 'RADIANT',
      'FAN' => 'FAN',
      'TETRAGONAL' => 'TETRAGONAL',
      'TAPERED BAGUETTE' => 'TAPERED BAGUETTE',
      'CUSHION' => 'CUSHION',
      'SQUARE EMERALD' => 'EMERALD',
      'HEART' => 'HEART',
      'ASCHER' => 'ASCHER',
      'HALF MOON' => 'HALF MOON',
      'PRAD' => 'PRAD',
      'LOZENGE' => 'LOZENGE',
      'PRINCESS' => 'PRINCESS',
      'HEPTAGONAL' => 'HEPTAGONAL',
      'TRILLIANT' => 'TRILLIANT',
      'ROSE' => 'ROSE',
      'SQUARE RADIANT' => 'SQUARE',
      'FLANDERS' => 'FLANDERS',
      'OLD MINER' => 'OLD MINER',
      'MARQUISE' => 'MARQUISE',
      'NONAGONAL' => 'NONAGONAL',
      'EUROPEAN CUT' => 'EUROPEAN CUT',
      'TRIANGULAR' => 'TRIANGULAR',
    );
  }
}