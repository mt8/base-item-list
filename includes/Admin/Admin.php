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
		// Hidden submenu (parent_slug = '') keeps the URL ?page=base_item_list_setting
		// reachable for OAuth callback compatibility — existing BASE Developers
		// registrations pointed at this URL — while not appearing as a duplicate
		// sidebar entry alongside the auto-created "BASE Item List" submenu.
		$hookname = add_submenu_page(
			'',
			'API設定',
			'API設定',
			'manage_options',
			'base_item_list_setting',
			array( View::class, 'option_page' )
		);

		// Hidden pages have no entry in $submenu[<parent>], so WP can't resolve a
		// title via get_admin_page_title() and admin-header.php ends up calling
		// strip_tags( null ) — a PHP 8.1+ deprecation that breaks the subsequent
		// setcookie() calls (headers already sent). Seed $title here, which fires
		// just before admin-header.php is included.
		if ( $hookname ) {
			add_action(
				"load-{$hookname}",
				static function () {
					// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Intentionally seeding WP's $title global for the hidden admin page.
					$GLOBALS['title'] = 'BASE Item List 設定';
				}
			);
		}
	}

	public function admin_enqueue_scripts( $hook_suffix ) {

		// Match either of the plugin's admin pages by looking for the slug in
		// the hook suffix. WP composes hooks as `toplevel_page_<slug>` for the
		// top-level page and `<sanitized-parent>_page_<slug>` (or
		// `admin_page_<slug>` for hidden pages) for submenus, so a substring
		// match avoids depending on those naming details.
		if ( false === strpos( (string) $hook_suffix, 'base_item_list' ) ) {
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
