<?php

namespace mt8\BaseItemList;

use mt8\BaseItemList\Admin\Admin;
use mt8\BaseItemList\Admin\View;

class Core {
		
	const BASE_API_ITEMS_URL = 'https://api.thebase.in/1/items/search';
	const LAST_ERROR_OPTION_KEY = 'base-item-list-last-error';

	public function register_hooks() {

		$admin = New Admin();
		$auth = new Auth();

		add_action( 'admin_menu', array( $admin, 'admin_menu' ) );
		add_action( 'admin_init', array( View::class, 'register_setting_fields' ) );
		
		add_action( 'init', array( $auth, 'init' ) );
		add_action( 'template_redirect', array( $auth, 'template_redirect' ) );

		add_shortcode('BASE_ITEM_V2', array( $this, 'add_shortcode' ) );

	}

	public function add_shortcode( $atts ) {

		//setup parameter
		extract( shortcode_atts( 
			array(
				'q'     => '*',
				'order' => '',
				'sort'  => 'desc',
				'limit' => 10,
				'cache' => 60,
				'name'  => 'base_item_list',
			), $atts ) );

		//call API if no cache
		$json = get_transient( md5( $name ) );
		if ( ! $json ) {
			$json = $this->request_api( compact( 'q', 'order', 'sort', 'limit' ) );
			if ( is_null( $json ) ) {
				return '';
			}
			if ( $cache > 0 ) {
				set_transient( md5( $name ), $json, $cache );
			}
		}

		//print items
		if ( count( $json->items ) < (int)$limit ) {
			$limit = count( $json->items );
		}
		return $this->item_list( array_slice( $json->items, 0, (int)$limit ) );

	}
	
	public function request_api( $args ) {

		$auth = new Auth();

		$token = $auth->get_access_token();
		if ( empty( $token ) ) {
			error_log( '==========BASE Item List API Error==========' );
			error_log( 'アクセストークンが取得できません。認証してください。' );
			update_option( self::LAST_ERROR_OPTION_KEY, 'アクセストークンが取得できません。認証してください。', false );
			return null;
		}

		$args = array(
			'headers'     => array(
				'Authorization' => 'Bearer ' . $token,
			),
			'body' => array(
				'q'     => $args['q'],
				'order' => $args['order'],
				'sort'  => $args['sort'],
				'limit' => $args['limit'],
			)
		);

		$response = wp_remote_get( self::BASE_API_ITEMS_URL, $args );

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			error_log( '==========BASE Item List API Error==========' );
			error_log( 'Request Params:   ' . var_export( $args, true ) );
			error_log( 'Response Code:    ' . wp_remote_retrieve_response_code( $response ) );
			error_log( 'Response Message: ' . wp_remote_retrieve_response_message( $response ) );
			update_option(
				self::LAST_ERROR_OPTION_KEY, var_export( $args, true ) . PHP_EOL .
				'(' . wp_remote_retrieve_response_code( $response ) . ')' . 
				wp_remote_retrieve_response_message( $response ) , false );
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
			include dirname( dirname(__DIR__) ) . '/template/v2/base_items.php';
		}
		return ob_get_clean();

	}

}
