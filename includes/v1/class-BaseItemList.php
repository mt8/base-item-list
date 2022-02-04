<?php

class Base_Item_List_V1 {
		
	private $admin;

	public function __construct() {

		$this->admin = New Base_Item_List_Admin_V1();
		
	}

	public function register_hooks() {

		add_action( 'plugins_loaded',     array( $this, 'plugins_loaded' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

		add_action( 'admin_init', array( $this->admin, 'admin_init' ) );
		add_action( 'admin_menu', array( $this->admin, 'admin_menu' ) );

		add_shortcode('BASE_ITEM', array( $this, 'add_shortcode' ) );
	}

	public function plugins_loaded() {
		load_plugin_textdomain(
			Base_Item_List_Admin_V1::TEXT_DOMAIN,
			false,
			dirname( plugin_basename( __FILE__ ) ).'/languages'
		 );
	}

	public function wp_enqueue_scripts() {
		if ( '1' == $this->admin->option( 'use_default_css' ) ) {
			wp_enqueue_style( 'base-item-list', plugins_url( '/assets/css/base-item-list.css', dirname( dirname(__FILE__) ) ) );
		}
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
				'sort'       => '',
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
			$json = $this->request_api( compact( 'client_id', 'client_secret', 'q', 'shop_id', 'size', 'sort' ) );
			if ( is_null( $json ) ) {
				return '';
			}
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
		$query = build_query( apply_filters( 'base_item_list_api_args', $args ) );
		$response = wp_remote_get( $endpoint . '?' . $query );
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			error_log( '==========BASE Item List API　Error==========' );
			error_log( 'Request: ' .  $endpoint . '?' . $query );
			error_log( 'Response Code: ' . wp_remote_retrieve_response_code( $response ) );
			error_log( 'Response Message: ' . wp_remote_retrieve_response_message( $response ) );
			return null;
		}
		$json = json_decode( wp_remote_retrieve_body( $response ) );

		if ( array_key_exists( 'sort' , $args ) ) {
			$sorts =  explode( ',', $args['sort'] );
			foreach ( $sorts as $sort ) {
				$conditions = explode( ' ', $sort );
				if ( 2 !== count( $conditions ) ) {
					continue;
				}
				if ( 'list_order' !== $conditions[0] ) {
					continue;
				}
				if ( ! in_array( $conditions[1], array( 'asc', 'desc' ) ) ) {
					continue;
				}
				if ( 'asc' === $conditions[1] ) {
					usort($json->items, function ($a, $b) {
						if ($a->list_order == $b->list_order) return 0;
						return ($a->list_order < $b->list_order) ? -1 : 1;
					});
				}
				if ( 'desc' === $conditions[1] ) {
					usort($json->items, function ($a, $b) {
						if ($a->list_order == $b->list_order) return 0;
						return ($a->list_order > $b->list_order) ? -1 : 1;
					});
				}
			}
		}
		return $json;
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
			include dirname( dirname(__DIR__) ) . '/template/base_items.php';
		}
		return ob_get_clean();

	}

}
