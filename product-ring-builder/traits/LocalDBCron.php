<?php
namespace OTW\WooRingBuilder\Traits;

if ( ! defined( 'ABSPATH' ) )	exit;

trait LocalDBCron{

  /******************************************/
  /***** LocalDBCron_init function start from here *********/
  /******************************************/
  public function LocalDBCron_init(){

    if ( isset($_GET['create_custom_table'])) {
      $this->create_custom_table();
    }

    
    if(!$this->nivoda_diamonds)
      $this->nivoda_diamonds = \OTW\WooRingBuilder\Classes\NivodaGetDiamonds::instance();
    if(!$this->diamonds)
      $this->diamonds = \OTW\WooRingBuilder\Classes\Diamonds::instance();
    
      

    add_action('wp_footer', function(){
      if ( isset($_GET['nivoda_start_cron_event'])) {
        $this->StartCronEvent();
        // db($this->prefix);db($this->nivoda_diamonds);exit();
        // $this->StartCronEvent();
        // $files_list = $this->get_option('import_nivoda_csv_files');
        // $current_import_file = $this->get_option('current_import_file');
        // db($files_list);db($current_import_file);exit();
        // $this->nivoda_start_cron_event();
      }
      if ( isset($_GET['every_ten_minute_cron'])) {
        $this->every_ten_minute_cron();
      }
      
    });

    if(isset($_GET['run_csv_import'])){
      // $this->run_csv_import();
      $last_started_date = $this->get_option('last_nivoda_update_key');

      if($last_started_date)
        echo wp_date('Y-m-d H:i:s', $last_started_date);
        // echo date('Y-m-d H:i:s', $last_started_date);

      echo '<br />';
      echo wp_date('Y-m-d H:i:s');
      db($this->get_option('current_import_file'));
      // exit();
    }
    
      
    if ( isset($_GET['nivoda_single_cron_event'])) {
    }
    if ( isset($_GET['delete_old_nivoda_diamonds'])) {
      $this->delete_old_nivoda_diamonds();
    }

    // add weekly schedule to wp cron
		add_filter( 'cron_schedules', array($this , 'AddWeeklyCron' ));

    //Weekly schedule hook
    add_action($this->prefix.'_every_ten_minute', array($this , 'every_ten_minute_cron'));
    add_action($this->prefix.'_nivoda_copy_import_files', array($this , 'nivoda_copy_import_files'));
		add_action($this->prefix.'_nivoda_cron_event', array($this , 'nivoda_start_cron_event'));
    add_action($this->prefix.'_nivoda_single_cron_event', array($this , 'nivoda_single_cron_event_csv'));


    

    add_action('wp_ajax_check_nivoda_cron_status', [$this, 'check_nivoda_cron_status']);
    add_action( 'wp_ajax_nopriv_check_nivoda_cron_status', [$this, 'check_nivoda_cron_status']);
    $files_list = $this->get_option('import_nivoda_csv_files');
    if(($files_list && is_array($files_list) && count($files_list) >= 1)){
      add_action('wp_footer', array($this , 'nivoda_wp_footer'));
    }
    
    
    // wp_clear_scheduled_hook($this->prefix.'_nivoda_cron_event');
    // return false;

    

    if (! wp_next_scheduled ( $this->prefix.'_nivoda_single_cron_event' )){
      $files_list = $this->get_option('import_nivoda_csv_files');
    
      if(($files_list && is_array($files_list) && count($files_list) >= 1)){
        wp_schedule_single_event( wp_date('U') + 1, $this->prefix.'_nivoda_single_cron_event');
      }
    }
      

	}

  /******************************************/
	/***** nivoda_single_cron_event **********/
	/******************************************/
  public function nivoda_single_cron_event_csv() {
    $this->run_csv_import();
  }

  /******************************************/
	/***** AddWeeklyCron **********/
	/******************************************/
  public function AddWeeklyCron( $schedules ) {

    if(!isset($schedules['every_four_hour'])){
      // add a 'weekly' schedule to the existing set
      $schedules['every_four_hour'] = array(
        'interval' => 60 * 60 * 4, # 604,800, seconds in a week
        //'interval' => 30,
        'display' => __('Every 4 hour')
      );
    }

    if(!isset($schedules['every_ten_minute'])){
      $schedules['every_ten_minute'] = array(
        'interval' => 60 * 10,
        'display' => __('Every 10 minute')
      );
    }

    if(!isset($schedules['every_thirty_minute'])){
      $schedules['every_thirty_minute'] = array(
        'interval' => 60 * 30,
        'display' => __('Every 30 minute')
      );
    }
		

// db($schedules);exit();
		return $schedules;
	}

  /******************************************/
  /***** start cron event **********/
  /******************************************/
  public function StartCronEvent(){

    $recurrence = $this->get_option('recurrence');
    // $recurrence = 'twicedaily';
    $recurrence = 'every_four_hour';
    
    if(!$recurrence)
      $recurrence = 'hourly';
      
    if($recurrence == "oneoff" && wp_next_scheduled ( $this->prefix.'_nivoda_cron_event' )){
      wp_clear_scheduled_hook($this->prefix.'_nivoda_cron_event');
    }elseif (! wp_next_scheduled ( $this->prefix.'_nivoda_cron_event' )) {
      wp_schedule_event(wp_date('U')+1, $recurrence, $this->prefix.'_nivoda_cron_event');
    }elseif (wp_next_scheduled ( $this->prefix.'_nivoda_cron_event' )) {
      wp_clear_scheduled_hook($this->prefix.'_nivoda_cron_event');
      wp_schedule_event(wp_date('U')+1, $recurrence, $this->prefix.'_nivoda_cron_event');
    }

    $recurrence = 'every_ten_minute';
    if($recurrence == "oneoff" && wp_next_scheduled ( $this->prefix.'_every_ten_minute' )){
      wp_clear_scheduled_hook($this->prefix.'_every_ten_minute');
    }elseif (! wp_next_scheduled ( $this->prefix.'_every_ten_minute' )) {
      wp_schedule_event(wp_date('U')+1, $recurrence, $this->prefix.'_every_ten_minute');
    }elseif (wp_next_scheduled ( $this->prefix.'_every_ten_minute' )) {
      wp_clear_scheduled_hook($this->prefix.'_every_ten_minute');
      wp_schedule_event(wp_date('U')+1, $recurrence, $this->prefix.'_every_ten_minute');
    }
    // db($recurrence);exit();
	}// StartCronEvent

  public function every_ten_minute_cron(){

    $files_list = $this->get_option('import_nivoda_csv_files');
    
    if($files_list && is_array($files_list) && count($files_list) >= 1)
      return false;

    $file_system = \OTW\GeneralWooRingBuilder\FileSystem::instance();
    $abs_path = ABSPATH.'nivoda/';
    $dir_files = $file_system->scandir($abs_path);
    if($dir_files && is_array($dir_files) && count($dir_files) >= 1){
      // $file_system->unlink($this->current_import_log_path);
      // $this->add_logs_to_file('New import event started.', $this->current_import_log_path);
      $files_list = array();
      foreach($dir_files as $single_file){
        if(isset($single_file['lastmodunix']) && $single_file['lastmodunix'] && isset($single_file['type']) && $single_file['type'] == 'f' && isset($single_file['name']) /*&& substr($single_file['name'], -4) == '.csv'*/ && ($single_file['name'] == 'labgrown.csv' || $single_file['name'] == 'natural_diamonds.csv')){
          $db_lastmodunix = (int) $this->get_option($single_file['name'].'lastmodunix');
          if($db_lastmodunix != $single_file['lastmodunix']){
            $this->update_option($single_file['name'].'lastmodunix', $single_file['lastmodunix']);
            wp_schedule_single_event( wp_date('U') + 60, $this->prefix.'_nivoda_copy_import_files');
            // $rtval = copy( $single_file['absolute_path'], $abs_path.'import/'.$single_file['name'] );
          }
        }
      }
    }
  }

  public function nivoda_copy_import_files(){
    $file_system = \OTW\GeneralWooRingBuilder\FileSystem::instance();
    $abs_path = ABSPATH.'nivoda/';
    $dir_files = $file_system->scandir($abs_path);
    if($dir_files && is_array($dir_files) && count($dir_files) >= 1){
      // $file_system->unlink($this->current_import_log_path);
      // $this->add_logs_to_file('New import event started.', $this->current_import_log_path);
      $files_list = array();
      foreach($dir_files as $single_file){
        if(isset($single_file['lastmodunix']) && $single_file['lastmodunix'] && isset($single_file['type']) && $single_file['type'] == 'f' && isset($single_file['name']) /*&& substr($single_file['name'], -4) == '.csv'*/ && ($single_file['name'] == 'labgrown.csv' || $single_file['name'] == 'natural_diamonds.csv')){
          $db_lastmodunix = (int) $this->get_option($single_file['name'].'lastmodunix');
          if($db_lastmodunix == $single_file['lastmodunix']){
            $rtval = copy( $single_file['absolute_path'], $abs_path.'import/'.$single_file['name'] );
            wp_delete_file($single_file['absolute_path'].'.bk');
            $rtval = copy( $single_file['absolute_path'], $single_file['absolute_path'].'.bk' );
            wp_delete_file($single_file['absolute_path']);
          }
        }
      }
    }
  }

  /******************************************/
	/***** DoThisWeekly **********/
	/******************************************/
  public function nivoda_start_cron_event() {
    
    // update_option('otw_nivoda_cron_last_update_key', wp_date('U'));
    $this->update_option('current_import_file', array());
    $this->update_option('import_nivoda_csv_files', array());
    

    // $_GET['get_new_nivoda_auth_token'] = 'yes';
    //   $this->nivoda_diamonds->get_auth_token();

    // $this->delete_old_nivoda_diamonds();
    $this->get_diamonds_from_csv();
    // $this->get_diamonds_from_live_api();
    
    
	}

  public function get_diamonds_from_csv(){

    
    // $this->update_option('last_import_event_run', wp_date('d-M-Y h:i:s'));
    $file_system = \OTW\GeneralWooRingBuilder\FileSystem::instance();
    $abs_path = ABSPATH.'nivoda/import/';
    $dir_files = $file_system->scandir($abs_path);
    if($dir_files && is_array($dir_files) && count($dir_files) >= 1){
      // $file_system->unlink($this->current_import_log_path);
      // $this->add_logs_to_file('New import event started.', $this->current_import_log_path);
      $files_list = array();
      foreach($dir_files as $single_file){
        if(isset($single_file['type']) && $single_file['type'] == 'f' && isset($single_file['name']) /*&& substr($single_file['name'], -4) == '.csv'*/ && ($single_file['name'] == 'labgrown.csv' || $single_file['name'] == 'natural_diamonds.csv')){
          $files_list[$single_file['name']] = $single_file;
        }
      }
    }
    if(isset($files_list) && is_array($files_list) && $files_list && count($files_list) >= 1){
      $this->update_option('last_nivoda_update_key', wp_date('U'));
      $this->update_option('import_nivoda_csv_files', $files_list);
      // $this->add_file_to_import_que($files_list);
    }

  }

  public function add_file_to_import_que($files_list){
    $first_file = reset($files_list);
      $listWorksheetInfo = $this->listWorksheetInfo($first_file['absolute_path']);
      if($listWorksheetInfo && isset($listWorksheetInfo['totalRows']) && $listWorksheetInfo['totalRows'] >= 1){
        $first_file['rows'] = $listWorksheetInfo['totalRows'];
        $first_file['rows_imported'] = 0;
        $this->update_option('current_import_file', $first_file);
        // $this->add_logs_to_file('started importing file: '.$first_file['absolute_path'], $this->current_import_log_path);
        // db($this->current_import_log_path);
        // db('started importing file: '.$first_file['absolute_path']);exit();
      }else{
        $this->remove_file_from_import_que($first_file);
      }
  }


  public function remove_file_from_import_que($current_file){
    $files_list = $this->get_option('import_nivoda_csv_files');
    if(!($files_list && is_array($files_list) && count($files_list) >= 1)){
      return false;
    }
    if(isset($files_list[$current_file['name']])){
      unset($files_list[$current_file['name']]);
      $this->update_option('current_import_file', array());
      $this->update_option('import_nivoda_csv_files', $files_list);
    }

    if($files_list && is_array($files_list) && count($files_list) >= 1){
      $this->add_file_to_import_que($files_list);
    }
  }


  public function run_csv_import(){
    $files_list = $this->get_option('import_nivoda_csv_files');
    
    if(!($files_list && is_array($files_list) && count($files_list) >= 1)){
      //$this->delete_old_nivoda_diamonds();
      return false;
    }

    $current_file = $this->get_option('current_import_file');
    
    if(!$current_file){
      $this->add_file_to_import_que($files_list);
      return false;
    }

    if($current_file && isset($current_file['rows']) && isset($current_file['rows_imported']) && $current_file['rows_imported'] < $current_file['rows']){
      
      if(!file_exists($current_file['absolute_path'])){
        $this->remove_file_from_import_que($current_file);
        return false;
      }

      $fileHandle = fopen($current_file['absolute_path'], 'r');
      
      if(!$fileHandle || !flock($fileHandle, LOCK_EX)){
        fclose($fileHandle);
        return false;
      }
        
      

      if(isset($current_file['last_position']))
        fseek($fileHandle, $current_file['last_position']);

      $maxLines = 2000;
      while ($maxLines > 0 && $columns = fgetcsv($fileHandle)) {
        $maxLines--;
        
        if(!isset($current_file['headers'])){
          $current_file['headers'] = $columns;
          $current_file['last_position'] = ftell($fileHandle);
          $current_file['rows_imported']++;          
          $this->update_option('current_import_file', $current_file);
          continue;
        }
        
        if(count($current_file['headers']) == count($columns)){
          $db_diamond = array_combine($current_file['headers'], $columns);
          $this->update_insert_new_csv_diamond($db_diamond);
        }
        
        

        $current_file['last_position'] = ftell($fileHandle);
        $current_file['rows_imported']++;
        $this->update_option('current_import_file', $current_file);

      }
      // db($current_file);
      fclose($fileHandle);
    }
    // db($this->get_option('current_import_file'));
    if($current_file && isset($current_file['rows']) && isset($current_file['rows_imported']) && $current_file['rows_imported'] >= $current_file['rows']){

      $diamond_type = 'lab';
      if($current_file['name'] == 'natural_diamonds.csv')
        $diamond_type = 'natural';

      $this->delete_old_nivoda_diamonds(' AND d_type = "'.$diamond_type.'"');
      wp_delete_file($current_file['absolute_path']);
      $this->remove_file_from_import_que($current_file);
      // $this->add_logs_to_file('finished importing file: '.$current_file['absolute_path'], $this->current_import_log_path);
    }
  }

  public function update_insert_new_csv_diamond($db_diamond){
    $diamond = array();
    // db($db_diamond);
    if(
      !isset($db_diamond['markup_price']) || empty($db_diamond['markup_price']) || 
      !isset($db_diamond['price']) || empty($db_diamond['price']) || 
      !isset($db_diamond['carats']) || empty($db_diamond['carats']) || 
      !isset($db_diamond['stock_id']) || empty($db_diamond['stock_id']) || 
      !isset($db_diamond['shape']) || empty($db_diamond['shape']) || 
      !isset($db_diamond['video']) || empty($db_diamond['video']) || 
      !isset($db_diamond['image']) || empty($db_diamond['image']) || 
      !isset($db_diamond['col']) || empty($db_diamond['col']) /*|| 
      !isset($db_diamond['clar']) || empty($db_diamond['clar']) || 
      !isset($db_diamond['symm']) || empty($db_diamond['symm']) || 
      !isset($db_diamond['length']) || empty($db_diamond['length']) || 
      !isset($db_diamond['width']) || empty($db_diamond['width']) || 
      !isset($db_diamond['lab']) || empty($db_diamond['lab'])*/
      ){
      return false;
    }

    if(!isset($db_diamond['col']) || empty($db_diamond['col']))
      $db_diamond['col'] = '';
    if(!isset($db_diamond['clar']) || empty($db_diamond['clar']))
      $db_diamond['clar'] = '';
    if(!isset($db_diamond['symm']) || empty($db_diamond['symm']))
      $db_diamond['symm'] = '';
    if(!isset($db_diamond['length']) || empty($db_diamond['length']))
      $db_diamond['length'] = '';
    if(!isset($db_diamond['width']) || empty($db_diamond['width']))
      $db_diamond['width'] = '';
    if(!isset($db_diamond['lab']) || empty($db_diamond['lab']))
      $db_diamond['lab'] = '';
    if(!isset($db_diamond['video']) || empty($db_diamond['video']))
      $db_diamond['video'] = '';
    if(!isset($db_diamond['image']) || empty($db_diamond['image']))
      $db_diamond['image'] = 'https://wordpress-1167849-4081671.cloudwaysapps.com/wp-content/uploads/2023/10/cat_halo-300.webp';


    // db('yes');
    $diamond['id'] = $db_diamond['stock_id'];
    $diamond['upload'] = 'csv';
    $diamond['markup_price'] = $db_diamond['markup_price'];
    $diamond['price'] = $db_diamond['price'];
    $diamond['diamond']['certificate']['video'] = $db_diamond['video'];
    $diamond['diamond']['certificate']['image'] = $db_diamond['image'];
    $diamond['diamond']['certificate']['carats'] = $db_diamond['carats'];
    $diamond['diamond']['certificate']['shape'] = $db_diamond['shape'];
    $diamond['diamond']['certificate']['color'] = $db_diamond['col'];
    $diamond['diamond']['certificate']['clarity'] = $db_diamond['clar'];
    $diamond['diamond']['certificate']['symmetry'] = $db_diamond['symm'];
    $diamond['diamond']['certificate']['length'] = $db_diamond['length'];
    $diamond['diamond']['certificate']['width'] = $db_diamond['width'];
    $diamond['diamond']['certificate']['lab'] = $db_diamond['lab'];
    if(isset($db_diamond['pdf']) && $db_diamond['pdf'])
      $diamond['diamond']['certificate']['pdfUrl'] = $db_diamond['pdf'];
    
    

    if(!$this->nivoda_diamonds)
      $this->nivoda_diamonds = \OTW\WooRingBuilder\Classes\NivodaGetDiamonds::instance();
    if(!$this->diamonds)
      $this->diamonds = \OTW\WooRingBuilder\Classes\Diamonds::instance();

    $formated_diamond = $this->nivoda_diamonds->convert_nivoda_to_vdb($diamond);
    if(isset($db_diamond['lg']) && $db_diamond['lg'])
      $formated_diamond['lg'] = $db_diamond['lg'];

    // if($this->diamonds->exclude_diamond($formated_diamond))
    //   return false;

    $this->insert_new_diamond($formated_diamond, array('new_diamond_key' => $this->get_option('last_nivoda_update_key')));
  }

  /**
   * Return worksheet info (Name, Last Column Letter, Last Column Index, Total Rows, Total Columns)
   *
   * @param     string         $pFilename
   * @throws    PHPExcel_Reader_Exception
   */
  public function listWorksheetInfo($pFilename)
  {
    $fileHandle = fopen($pFilename, 'r');
    if(!$fileHandle)
      return false;

    $worksheetInfo = array();
    $worksheetInfo['worksheetName'] = 'Worksheet';
    $worksheetInfo['lastColumnLetter'] = 'A';
    $worksheetInfo['lastColumnIndex'] = 0;
    $worksheetInfo['totalRows'] = 0;
    $worksheetInfo['totalColumns'] = 0;
    while (($rowData = fgetcsv($fileHandle, 0)) !== false) {
      $worksheetInfo['totalRows']++;
      $worksheetInfo['lastColumnIndex'] = max($worksheetInfo['lastColumnIndex'], count($rowData) - 1);
    }
    // $worksheetInfo[0]['lastColumnLetter'] = PHPExcel_Cell::stringFromColumnIndex($worksheetInfo[0]['lastColumnIndex']);
    $worksheetInfo['totalColumns'] = $worksheetInfo['lastColumnIndex'] + 1;
    fclose($fileHandle);

    return $worksheetInfo;
  }


  public function get_diamonds_from_live_api(){
    $page_size = 50;
    $nivoda_cron_status = array('new_diamond_key' => $this->get_option('last_nivoda_update_key'), 'page_size' => $page_size, 'page_number' => 1);
    $query_response_all_data = $this->nivoda_diamonds->get_diamonds(array());
    if(!($query_response_all_data && is_array($query_response_all_data) && count($query_response_all_data) >= 1 && isset($query_response_all_data['diamonds_by_query']) && isset($query_response_all_data['diamonds_by_query']['items']) && is_array($query_response_all_data['diamonds_by_query']['items']) && count($query_response_all_data['diamonds_by_query']['items']) >= 1)){
      $_GET['get_new_nivoda_auth_token'] = 'yes';
        $this->nivoda_diamonds->get_auth_token();
      $query_response_all_data = $this->nivoda_diamonds->get_diamonds(array());
    }
    if($query_response_all_data && is_array($query_response_all_data) && count($query_response_all_data) >= 1 && isset($query_response_all_data['diamonds_by_query']) && isset($query_response_all_data['diamonds_by_query']['items']) && is_array($query_response_all_data['diamonds_by_query']['items']) && count($query_response_all_data['diamonds_by_query']['items']) >= 1){
      $nivoda_cron_status['diamonds_by_query_count'] = $query_response_all_data['diamonds_by_query_count'];
      $nivoda_cron_status['total_pages'] = (int) ceil($query_response_all_data['diamonds_by_query_count'] / $page_size);
      // wp_schedule_single_event( wp_date('U') + 1, $this->prefix.'_nivoda_single_cron_event');
    }
    update_option('otw_nivoda_cron_status', $nivoda_cron_status);
    // update_option('otw_nivoda_cron_last_update_key', $nivoda_cron_status['new_diamond_key']);
    // db(get_option('otw_nivoda_cron_status'));
  }

  public function delete_old_nivoda_diamonds($where = ''){
    // $last_update_key = get_option('otw_nivoda_cron_last_update_key');
    // db($last_update_key);exit();
    // $last_update_key = '1711982419';
    $last_update_key = $this->get_option('last_nivoda_update_key');
    if($last_update_key){
      global $wpdb;
      $query = "DELETE FROM ".$wpdb->prefix."otw_diamonds WHERE last_update_key != '".$last_update_key."'";
      $query .= $where;
      $wpdb->query($query);
    }  
  }

  /******************************************/
	/***** nivoda_single_cron_event **********/
	/******************************************/
  public function nivoda_single_cron_event() {
    $nivoda_cron_status = get_option('otw_nivoda_cron_status');
    // $nivoda_cron_counter = get_option('otw_nivoda_cron_counter');
    if($nivoda_cron_status && isset($nivoda_cron_status['diamonds_by_query_count']) && $nivoda_cron_status['diamonds_by_query_count'] >= 1 && isset($nivoda_cron_status['total_pages']) && $nivoda_cron_status['total_pages'] >= $nivoda_cron_status['page_number']){
      // wp_schedule_single_event( wp_date('U') + 1, $this->prefix.'_nivoda_single_cron_event');

      $args = array('page_number' => $nivoda_cron_status['page_number'], 'page_number_nivoda' => $nivoda_cron_status['page_number'], 'page_size' => $nivoda_cron_status['page_size'], 'diamonds_by_query_count' => 'no');
      $query_response_all_data = $this->nivoda_diamonds->get_diamonds($args);
      if($query_response_all_data && is_array($query_response_all_data) && count($query_response_all_data) >= 1 && isset($query_response_all_data['diamonds_by_query']) && isset($query_response_all_data['diamonds_by_query']['items']) && is_array($query_response_all_data['diamonds_by_query']['items']) && count($query_response_all_data['diamonds_by_query']['items']) >= 1){
        
        

        $query_response = $query_response_all_data['diamonds_by_query']['items'];
        $counter = 1;
        foreach($query_response as $diamond){
          
          $formated_diamond = $this->nivoda_diamonds->convert_nivoda_to_vdb($diamond);
          
          
          if(!(isset($nivoda_cron_counter) && $nivoda_cron_counter)){
            $nivoda_cron_counter = 0;
          }
          if($counter > $nivoda_cron_counter){
            $this->insert_new_diamond($formated_diamond, $nivoda_cron_status);
          }
          
          // update_option('otw_nivoda_cron_status', $nivoda_cron_status);
          // update_option('otw_nivoda_cron_counter', $counter);
          $counter++;
        }

        $nivoda_cron_status['page_counter'] = 0;
        $nivoda_cron_status['page_number'] = (int) $nivoda_cron_status['page_number'] + 1;
        update_option('otw_nivoda_cron_status', $nivoda_cron_status);
        // db($nivoda_cron_status);exit();

      }else if($nivoda_cron_status && isset($nivoda_cron_status['diamonds_by_query_count']) && $nivoda_cron_status['diamonds_by_query_count'] >= 1 && isset($nivoda_cron_status['total_pages']) && $nivoda_cron_status['page_number'] >= $nivoda_cron_status['total_pages']){
        update_option('otw_nivoda_cron_status', array());
      }
      
    }
    // db(get_option('otw_nivoda_cron_status'));
  }

  public function insert_new_diamond($formated_diamond, $nivoda_cron_status){
    global $wpdb;

    $d_status = 1;
    if($this->diamonds->exclude_diamond($formated_diamond))
      $d_status = 0;
    
      
    $data = array(
      'api' => '1', 
      'stock_num' => $formated_diamond['stock_num'],
      'd_type' => 'lab',
      'price' => $formated_diamond['total_sales_price'],
      'base_price' => $formated_diamond['base_sales_price'],
      'carat_size' => $formated_diamond['size'],
      'shape' => $formated_diamond['shape'],
      'shape_api' => $formated_diamond['shape_api'],
      'color' => $formated_diamond['color'],
      'clarity' => $formated_diamond['clarity'],
      'symmetry' => $formated_diamond['symmetry'],
      'meas_length' => $formated_diamond['meas_length'],
      'meas_width' => $formated_diamond['meas_width'],
      'meas_ratio' => $formated_diamond['meas_ratio'],
      'lab' => $formated_diamond['lab'],
      'cert_url' => $formated_diamond['cert_url'],
      'video_url' => $formated_diamond['video_url'],
      'image_url' => $formated_diamond['image_url'],
      'd_status' => $d_status,
      'last_update_key' => $nivoda_cron_status['new_diamond_key'],
    );
    if(isset($formated_diamond['lg']) && $formated_diamond['lg'])
      $data['d_type'] = $formated_diamond['lg'];

    $format = array(
      "%s",
      "%s",
      "%s",
      "%d",
      "%d",
      "%s",
      "%s",
      "%s",
      "%s",
      "%s",
      "%s",
      "%s",
      "%s",
      "%s",
      "%s",
      "%s",
      "%s",
      "%s",
      '%d',
      "%s",
    );
    
    $inserted = $wpdb->replace($wpdb->prefix.'otw_diamonds', $data, $format);
    // db($data);db($inserted );exit();
  }

  /******************************************/
  /***** create_custom_table function **********/
  /******************************************/
  public function create_custom_table(){
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'otw_diamonds';

    // Check if the table exists and if it needs to be updated
    $current_version = '1.1'; // Update this with your current version
    $table_version = $this->get_option( 'db_version' );


    if ( ($table_version !== $current_version) || isset($_GET['create_custom_table'])) {

      if(isset($_GET['create_custom_table'])){
        $sql = "DROP TABLE IF EXISTS $table_name"; 
        $wpdb->query ($sql);
      }
      

      $charset_collate = $wpdb->get_charset_collate();
      /*$sql = "CREATE TABLE $table_name (
          ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          api varchar(255) NULL,
          stock_num varchar(255) NULL,
          d_type varchar(255) NULL,
          price bigint(20) unsigned NULL,
          base_price bigint(20) unsigned NULL,
          carat_size FLOAT unsigned NULL,
          shape varchar(255) NULL,
          shape_api varchar(255) NULL,
          color varchar(255) NULL,
          clarity varchar(255) NULL,
          symmetry varchar(255) NULL,
          meas_length FLOAT unsigned NULL,
          meas_width FLOAT unsigned NULL,
          meas_ratio FLOAT unsigned NULL,
          lab varchar(255) NULL,
          cert_url TINYTEXT NULL,
          video_url TINYTEXT NULL,
          image_url TINYTEXT NULL,
          d_status tinyint(1) DEFAULT 1 NULL,
          last_update_key varchar(255) NULL,
          created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
		      updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
		      INDEX (api),
          INDEX (d_type),
          INDEX (price),
          INDEX (carat_size),
          INDEX (shape),
          INDEX (color),
          INDEX (clarity),
          INDEX (last_update_key),
          PRIMARY KEY  (id)
      ) $charset_collate;";*/
//stock_num varchar(255) UNIQUE NULL,
      $sql = "CREATE TABLE $table_name (
        api varchar(255) NULL,
        stock_num varchar(255) UNIQUE NULL,
        d_type varchar(255) NULL,
        price bigint(20) unsigned NULL,
        base_price bigint(20) unsigned NULL,
        carat_size FLOAT NULL,
        shape varchar(255) NULL,
        shape_api varchar(255) NULL,
        color varchar(255) NULL,
        clarity varchar(255) NULL,
        symmetry varchar(255) NULL,
        meas_length FLOAT NULL,
        meas_width FLOAT NULL,
        meas_ratio FLOAT NULL,
        lab varchar(255) NULL,
        cert_url TINYTEXT NULL,
        video_url TINYTEXT NULL,
        image_url TINYTEXT NULL,
        d_status tinyint(1) DEFAULT 1 NULL,
        last_update_key varchar(255) NULL
      ) $charset_collate;";

      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );

      // Update the table version
      $this->update_option( 'db_version', $current_version );
    }
    

    

    /*if ( $table_version !== $current_version ) {
        // Perform table updates here
        $existing_columns = $wpdb->get_col( "DESC $table_name", 0 );

        if ( ! in_array( 'age', $existing_columns ) ) {
            $sql = "ALTER TABLE $table_name ADD COLUMN age int(3) NOT NULL DEFAULT 0;";
            dbDelta( $sql );
        }

        // Update the table version
        $this->update_option( 'db_version', $current_version );
    }*/

  }

  public function check_nivoda_cron_status(){
    
    /*$nivoda_cron_status = get_option('otw_nivoda_cron_status');
    // db($nivoda_cron_status);exit();
    $this->nivoda_single_cron_event();
    $output = array('status' => 'finish', 'cron_data' => $nivoda_cron_status);
    if($nivoda_cron_status && isset($nivoda_cron_status['diamonds_by_query_count']) && $nivoda_cron_status['diamonds_by_query_count'] >= 1 && isset($nivoda_cron_status['total_pages']) && $nivoda_cron_status['total_pages'] >= $nivoda_cron_status['page_number']){
      $output = array('status' => 'unfinished', 'cron_data' => $nivoda_cron_status);
    }
    wp_send_json_success($output);*/



    $current_import_file = $this->get_option('current_import_file');
    $files_list = $this->get_option('import_nivoda_csv_files');
    if(($files_list && is_array($files_list) && count($files_list) >= 1)){
      $output = array('status' => 'unfinished', 'cron_data' => $current_import_file);
    }else{
      $output = array('status' => 'finish', 'cron_data' => $nivoda_cron_status);
    }

    // $this->run_csv_import();
    
    wp_send_json_success($output);

  }

  public function nivoda_wp_footer(){

    

    ?>
      <script>
        jQuery(document).ready(function(){
          function check_nivoda_cron_status(){
            jQuery.ajax({
              type: "POST",
              url: '<?php echo admin_url('admin-ajax.php'); ?>',
              data: {action: "check_nivoda_cron_status"},
              success: function (response) {
                // console.log(response);
                if(typeof response != 'undefined' && typeof response.success != "undefined" && response.success == true && typeof response.data != "undefined"){
                  if(typeof response.data.status != "undefined"){
                    if(response.data.status == 'finish'){
                      // jQuery('.background_export_event_running').html('Export has been finished.');
                      // console.log(response);
                    }else{
                      setTimeout(function(){
                        check_nivoda_cron_status();
                      }, 1000*5);
                      // console.log(response);
                      // setTimeout(function(){
                      //   check_otw_export_status();
                      // }, 1000*10);
                    }
                  }
                }
              }
            });
          }

          setTimeout(function(){
            check_nivoda_cron_status();
          }, 1000*10);
          
        });
      </script>
    <?php
  }
}