<?php

namespace mt8\BaseItemList;

use Exception;
use mt8\BaseItemList\Admin\Admin;
use mt8\BaseItemList\Admin\View;

class Core {
		
	const BASE_API_ITEMS_URL = 'https://api.thebase.in/1/items/search';
	const LAST_ERROR_OPTION_KEY = 'base-item-list-last-error';

	public function register_hooks() {

		$admin = New Admin();

		add_action( 'admin_init', array( $admin, 'admin_init' ) );
		add_action( 'admin_menu', array( $admin, 'admin_menu' ) );
		add_action( 'admin_init', array( View::class, 'register_setting_fields' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		
		add_shortcode('BASE_ITEM', array( $this, 'add_shortcode' ) );

	}

	public function add_shortcode( $atts ) {

		try {
			//setup parameter
			extract( shortcode_atts( 
				array(
					'q'     => '*',
					'fields' => 'title,detail,categories',
					'order' => '',
					'sort'  => 'desc',
					'limit' => 10,
					'cache' => 60,
					'name'  => 'cache',
				), $atts ) );

			// check parameter
			if ( ! in_array( $order, array( 'list_order', 'modified' ) ) ) {
				$order = '';
			}
			if ( ! in_array( $sort, array( 'asc', 'desc' ) ) ) {
				$sort = 'desc';
			}
			if ( 0 >= intval( $limit ) || $limit > 100 ) {
				$limit = 10;
			}
			if ( 0 >= intval( $cache ) ) {
				$cache = 60;
			}

			//call API if no cache
			$json = get_transient( 'base-item-list-' . md5( $name ) );
			if ( ! $json ) {
				$json = $this->request_api( compact( 'q', 'fields', 'order', 'sort', 'limit' ) );
				if ( is_null( $json ) ) {
					return '';
				}
				if ( $cache > 0 ) {
					set_transient( 'base-item-list-' . md5( $name ), $json, $cache );
				}
			}

			//print items
			return $this->item_list( $json->items );

		} catch ( Exception $ex ) {
			error_log( '==========BASE Item List API Error==========' );
			error_log( 'エラー:' . $ex->getMessage() );
			update_option( self::LAST_ERROR_OPTION_KEY, 'エラー:' . $ex->getMessage(), false );
			return '';
		}

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
				'fields' => $args['fields'],
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

		foreach ( $items as $index => $item ) {
			$items[$index]->shop_url = untrailingslashit( Admin::option('shop_url') );
		}
		
		 ob_start();
		if ( is_file( get_stylesheet_directory() . '/base_items.php' ) ) {
			//load base_items.php in your theme.
			get_template_part( 'base_items' );
		} else {
			//load base_items.php in this plugin.
			include dirname(__DIR__) . '/template/base_items.php';
		}
		return ob_get_clean();

	}

	public function wp_enqueue_scripts() {
		$admin = New Admin();
		if ( true || '1' == $admin->option( 'use_default_css' ) ) {
			wp_enqueue_style(
				'base-item-list',
				plugins_url( '/assets/css/base-item-list.css', dirname(__FILE__) )
			);
		}
	}

}
