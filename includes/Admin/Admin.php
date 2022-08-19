<?php

namespace mt8\BaseItemList\Admin;

use mt8\BaseItemList\Auth;

class Admin {

	const TEXT_DOMAIN = 'base-item-list';
	const OPTIONS_KEY = 'base-item-list-v2';

	const OPTIONS_DEFUALT = array(
		'client_id'     => '',
		'client_secret' => '',
		'callback_url'  => '',
		'shop_url'      => '',
	);

	public function admin_init() {

		$request_to_auth = false;
		global $pagenow;
		if ( isset( $pagenow ) && 'admin.php' === $pagenow && 'base_item_list_setting' === filter_input( INPUT_GET, 'page' ) ) {
			$request_to_auth = true;
		}
		if ( ! $request_to_auth ) {
			return;
		}

		$do_auth = false;

		$nonce_check = (
			! empty( filter_input( INPUT_POST, 'base_item_list_auth' ) )
			&&
			wp_verify_nonce( filter_input( INPUT_POST, 'base_item_list_auth' ), 'base_item_list_auth' )
		);
		if ( $nonce_check ) {
			$do_auth = true;
		}

		$call_back = (
			! empty( filter_input( INPUT_GET, 'state' ) )
			&&
			! empty( filter_input( INPUT_GET, 'code' ) )
		);
		if ( $call_back ) {
			$do_auth = true;
		}

		if ( $do_auth ) {
			if ( PHP_SESSION_ACTIVE !== session_status() ) {
				@session_start();
			}			
			$auth = new Auth();
			$auth->authorize();
		}
	}

	public function admin_menu() {
		add_submenu_page(
			'base_item_list',
			'API設定',
			'API設定',
			'manage_options',
			'base_item_list_setting',
			array( View::class, 'option_page' )
		);		
	}

	public static function option( $key) {
		$option = get_option( self::OPTIONS_KEY, self::OPTIONS_DEFUALT );
		if ( is_array( $option ) && array_key_exists( $key, $option ) ) {
			return $option[$key];
		} else {
			return '';
		}
	}

	public function saved_options() {
		$options = get_option( self::OPTIONS_KEY, self::OPTIONS_DEFUALT );
		if ( ! is_array( $options ) || empty( $options ) ) {
			return false;
		}
		foreach ( array_keys( self::OPTIONS_DEFUALT ) as $key ) {
			if ( ! array_key_exists( $key, $options ) ) {
				return false;
			}
			if ( empty( $options[ $key ] ) ) {
				return false;
			}
		}
		return true;
	}
}
