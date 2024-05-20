<?php
namespace OTW\GeneralWooRingBuilder\Traits;

if ( ! defined( 'ABSPATH' ) )	exit;

trait AdminNotices{

  public $message = null;
  public $messageClass = 'success';

  /******************************************/
  /***** admin_notice_missing_main_plugin. **********/
  /******************************************/
  public function admin_notices() {
    //Value of $class can be error, success, warning and info
    if($this->message && $this->messageClass){ 
      printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( 'notice notice-'.$this->messageClass.' is-dismissible' ), esc_html( $this->message ) );
    }
  }

}