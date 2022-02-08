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

	public function admin_head() {
		if ( 'auth' === filter_input( INPUT_GET, 'mode' ) ) {
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
