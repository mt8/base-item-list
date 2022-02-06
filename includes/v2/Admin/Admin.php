<?php

namespace mt8\BaseItemList\Admin;

class Admin {

	const TEXT_DOMAIN = 'base-item-list';
	const OPTIONS_KEY = 'base-item-list-v2';

	const OPTIONS_DEFUALT = array(
		'client_id'     => '',
		'client_secret' => '',
		'callback_url'  => '',
	);

	public function admin_menu() {
		//add_menu_page( 'BASE Item List', 'BASE Item List' , 'manage_options', 'base_item_list', array( $this, 'add_options_page' ), 'dashicons-cart' );
		add_submenu_page( 'base_item_list', 'API設定(BETA)', 'API設定(BETA)', 'manage_options', 'base_item_list_v2', array( View::class, 'option_page' ) );		
	}

	public function admin_init() {

		$key     = self::OPTIONS_KEY;
		$group   = $key . '_group';
		$section = $key . '_section'; 
		register_setting( $group, $key, array( $this, 'register_setting' ) );

		add_settings_section( $section, __( 'settings', self::TEXT_DOMAIN ), array( View::class, 'settings_section' ), $key );

		add_settings_field(
			'client_id'
			,'client_id',
			array( View::class, 'field_client_id' ), 
			$key,
			$section
		);

		add_settings_field(
			'client_secret'
			,'client_secret',
			array( View::class, 'field_client_secret' ), 
			$key,
			$section
		);

		add_settings_field(
			'callback_url'
			,'コールバックURL',
			array( View::class, 'field_callback_url' ), 
			$key,
			$section
		);

		add_settings_field(
			'use_default_css'
			,'プラグインCSSを使用する',
			array( View::class, 'field_use_default_css' ), 
			$key,
			$section
		);

	}

	public function register_setting( $input ) {
		foreach ( array_keys( $this->options_default ) as $option_key ) {
			if ( ! isset( $input[ $option_key ] ) || empty( trim( $input[ $option_key ] ) ) ) {
				$input[ $option_key ] = '';
			}
		}
		return $input;
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
