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
			if ( PHP_SESSION_NONE === session_status() ) {
				session_start();
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

	const ADMIN_HOOKS = array(
		'toplevel_page_base_item_list',
		'base_item_list_page_base_item_list_setting',
	);

	public function admin_enqueue_scripts( $hook_suffix ) {

		if ( ! in_array( $hook_suffix, self::ADMIN_HOOKS, true ) ) {
			return;
		}

		$plugin_dir = dirname( __DIR__, 2 );
		$plugin_url = plugin_dir_url( $plugin_dir . '/plugin.php' );
		$asset_file = $plugin_dir . '/build/admin/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = include $asset_file;

		wp_enqueue_script(
			'base-item-list-admin',
			$plugin_url . 'build/admin/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_enqueue_style( 'wp-components' );

		$callback_url = add_query_arg(
			array(
				'page' => 'base_item_list_setting',
				'mode' => 'auth',
			),
			admin_url( '/admin.php' )
		);

		wp_localize_script(
			'base-item-list-admin',
			'basItemListAdmin',
			array(
				'callbackUrl'    => $callback_url,
				'homeUrl'        => home_url( '/' ),
				'screenshotUrl'  => $plugin_url . 'assets/images/api-apply.png',
				'justAuthorized' => 'authorized' === filter_input( INPUT_GET, 'status' ),
			)
		);

		wp_set_script_translations( 'base-item-list-admin', 'base-item-list' );
	}

	public static function option( $key ) {
		$option = get_option( self::OPTIONS_KEY, self::OPTIONS_DEFUALT );
		if ( is_array( $option ) && array_key_exists( $key, $option ) ) {
			return $option[ $key ];
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
