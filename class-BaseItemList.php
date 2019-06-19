<?php

class Base_Item_List {
		
	private $admin;

	public function __construct() {

		$this->admin = New Base_Item_List_Admin();
		
	}

	public function register_hooks() {

		add_action( 'plugins_loaded', array( &$this, 'plugins_loaded' ) );
		add_action( 'admin_init', array( &$this->admin, 'admin_init' ) );
		add_action( 'admin_menu', array( &$this->admin, 'admin_menu' ) );
		add_shortcode('BASE_ITEM', array( &$this, 'add_shortcode' ) );
	}

	public function plugins_loaded() {
		load_plugin_textdomain(
			Base_Item_List_Admin::TEXT_DOMAIN,
			false,
			dirname( plugin_basename( __FILE__ ) ).'/languages'
		 );
	}
	
	public function add_shortcode( $atts ) {
		
		//setup parameter
		extract( shortcode_atts( 
			array(
				'q'          => '*',
				'shop_id'    => '',
				'count'      => 10,
				'cache'      => 60,
				'name'       => 'base_item_list',
			), $atts ) );

		$client_id = $this->admin->option( 'client_id' );
		$client_secret = $this->admin->option( 'client_secret' );
		$q = urlencode($q);
		if ( '' === $shop_id ) {
			$shop_id = $this->admin->option( 'shop_id' );
		}
		if ( 0 >= (int)$count || (int)$count > 50  ) {
			$count = 10;
		}
		$size = $count;
		
		//call API if no cache
		$json = get_transient( md5( $name ) );
		if ( ! $json ) {
			$json = $this->request_api( compact( 'client_id', 'client_secret', 'q', 'shop_id', 'size' ) );
			if ( $cache > 0 ) {
				set_transient( md5( $name ), $json, $cache );
			}
		}
		
		//print items
		if ( count( $json->items ) < (int)$count ) {
			$count = count( $json->items );
		}
		return $this->item_list( array_slice( $json->items, 0, (int)$count ) );

	}
	
	public function request_api( $args ) {
		
		$endpoint = 'https://api.thebase.in/1/search';
		$query = build_query( $args );
		$response = wp_remote_get( $endpoint . '?' . $query );
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return null;
		}
		return json_decode( wp_remote_retrieve_body( $response ) );
		
	}
	
	public function item_list( $items ) {
		
		//set globals
		$GLOBALS[ 'base_items' ] = $items;
		
		 ob_start();
		if ( is_file( get_stylesheet_directory() . '/base_items.php' ) ) {
			//load base_items.php in your theme.
			get_template_part( 'base_items' );
		} else {
			//load base_items.php in this plugin.
			include plugin_dir_path( __FILE__ ) . '/base_items.php';
		}
		return ob_get_clean();
		
	}

}