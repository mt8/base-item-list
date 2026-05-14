<?php

namespace mt8\BaseItemList;

use Exception;
use mt8\BaseItemList\Admin\Admin;
use mt8\BaseItemList\Admin\View;
use mt8\BaseItemList\Rest\SettingsController;

class Core {

	const BASE_API_ITEMS_URL    = 'https://api.thebase.in/1/items/search';
	const LAST_ERROR_OPTION_KEY = 'base-item-list-last-error';

	public function register_hooks() {

		$admin = new Admin();

		add_action( 'admin_init', array( $admin, 'admin_init' ) );
		add_action( 'admin_menu', array( $admin, 'admin_menu' ) );
		add_action( 'admin_init', array( View::class, 'register_setting_fields' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

		add_action(
			'rest_api_init',
			static function () {
				( new SettingsController() )->register_routes();
			}
		);

		add_shortcode( 'BASE_ITEM', array( $this, 'add_shortcode' ) );
	}

	public function add_shortcode( $atts ) {

		try {

			$fields_default = array(
				'title',
				'detail',
				'categories',
			);

			$params = shortcode_atts(
				array(
					'q'      => '*',
					'fields' => implode( ',', $fields_default ),
					'order'  => '',
					'sort'   => 'desc',
					'limit'  => 10,
					'cache'  => 60,
					'name'   => 'cache',
				),
				$atts
			);
			$q      = $params['q'];
			$fields = $params['fields'];
			$order  = $params['order'];
			$sort   = $params['sort'];
			$limit  = $params['limit'];
			$cache  = $params['cache'];
			$name   = $params['name'];

			// Validate parameters; fall back to defaults on bad input.
			$fields_check = explode( ',', $fields );
			foreach ( $fields_check as $field ) {
				if ( ! in_array( $field, $fields_default, true ) ) {
					$fields = implode( ',', $fields_default );
				}
			}
			if ( ! in_array( $order, array( 'list_order', 'modified' ), true ) ) {
				$order = '';
			}
			if ( ! in_array( $sort, array( 'asc', 'desc' ), true ) ) {
				$sort = 'desc';
			}
			if ( 0 >= intval( $limit ) || $limit > 100 ) {
				$limit = 10;
			}
			if ( 0 >= intval( $cache ) ) {
				$cache = 60;
			}

			// call API if no cache
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

			// print items
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
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
			),
			'body'    => array(
				'q'      => $args['q'],
				'fields' => $args['fields'],
				'order'  => $args['order'],
				'sort'   => $args['sort'],
				'limit'  => $args['limit'],
			),
		);

		$response = wp_remote_get( self::BASE_API_ITEMS_URL, $args );

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			error_log( '==========BASE Item List API Error==========' );
			error_log( 'Request Params:   ' . var_export( $args, true ) );
			error_log( 'Response Code:    ' . wp_remote_retrieve_response_code( $response ) );
			error_log( 'Response Message: ' . wp_remote_retrieve_response_message( $response ) );
			update_option(
				self::LAST_ERROR_OPTION_KEY,
				var_export( $args, true ) . PHP_EOL .
				'(' . wp_remote_retrieve_response_code( $response ) . ')' .
				wp_remote_retrieve_response_message( $response ),
				false
			);
			return null;
		}

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	public function item_list( $items ) {

		// set globals
		$GLOBALS['base_items'] = $items;

		foreach ( $items as $index => $item ) {
			$items[ $index ]->shop_url = untrailingslashit( Admin::option( 'shop_url' ) );
		}

		ob_start();
		if ( is_file( get_stylesheet_directory() . '/base_items.php' ) ) {
			// load base_items.php in your theme.
			get_template_part( 'base_items' );
		} else {
			// load base_items.php in this plugin.
			include dirname( __DIR__ ) . '/template/base_items.php';
		}
		return ob_get_clean();
	}

	public function wp_enqueue_scripts() {
		// The default stylesheet is always enqueued; the `use_default_css` option is currently
		// a no-op kept for backwards compatibility with stored settings.
		wp_enqueue_style(
			'base-item-list',
			plugins_url( '/assets/css/base-item-list.css', __DIR__ )
		);
	}
}
