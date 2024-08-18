<?php
class Ring_Storage {
	
	private $options;
 	/**
	* Instance
	* @access private
	* @static
	*
	*/
	private static $_instance = null;
	private $table_name = null;

	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {

		global $wpdb;
		$this->table_name = $wpdb->prefix . 'gcpb_storage';

		add_action( 'init', [ $this, 'init' ] );
		register_activation_hook( PLUGIN_WITH_CLASSES__FILE__ , [ $this, 'create_gcpb_database_table' ] );
 		
  	}

	public function init() {
		
		if (!isset($_COOKIE['gcpb_user_data'])) {

			if(function_exists('wp_generate_uuid4')) {
 				
 				$uuid36 = wp_generate_uuid4();
				$uuid32 = str_replace( '-', '', $uuid36 ); // a938e855483e48c79b98f41e90511f77
				setcookie('gcpb_user_data', $uuid32, strtotime('+20 day'),  COOKIEPATH, COOKIE_DOMAIN,false, true,);
			}
		}
	}
 	
	public function create_gcpb_database_table() {
		
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

 		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$table_seller_auction_log = $this->table_name;

		/* sell logger */
		$sql = "CREATE TABLE $table_seller_auction_log (
		id BIGINT(9) NOT NULL AUTO_INCREMENT,
		user_id TEXT NOT NULL DEFAULT 0,
		product_id BIGINT(9) NOT NULL,
		variation_id BIGINT(9) NOT NULL,
		created_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id)
		) $charset_collate;";
		dbDelta( $sql );
	}

	
	public function gcpb_insert_product_log( $data ) {
				
		if(!empty($data)) {
 
			// $log_date = date('Y-m-d H:i:s'); 
			// $output_format = 'Y-m-d H:i:s';
			// $local_timestamp = get_date_from_gmt( $log_date, $output_format );
 
			global $wpdb;
			$inserted = $wpdb->insert($this->table_name, $data);

 			return $inserted;
			// echo $wpdb->last_query;
 		}
	}
}
 
Ring_Storage::instance(); ?>