<?php

namespace mt8\BaseItemList\Rest;

use mt8\BaseItemList\Admin\Admin;
use mt8\BaseItemList\Auth;
use mt8\BaseItemList\Core;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

class SettingsController {

	const REST_NAMESPACE = 'base-item-list/v1';

	public function register_routes() {

		register_rest_route(
			self::REST_NAMESPACE,
			'/settings',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'permission_callback' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'permission_callback' ),
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/auth-status',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_auth_status' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/auth/start',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'start_auth' ),
				'permission_callback' => array( $this, 'permission_callback' ),
			)
		);
	}

	public function permission_callback() {
		return current_user_can( 'manage_options' );
	}

	public function get_settings( WP_REST_Request $request ) {
		return rest_ensure_response( $this->current_settings() );
	}

	public function update_settings( WP_REST_Request $request ) {
		$params = $request->get_json_params();
		if ( ! is_array( $params ) ) {
			$params = $request->get_params();
		}

		$sanitized = array(
			'client_id'       => isset( $params['client_id'] ) ? sanitize_text_field( (string) $params['client_id'] ) : '',
			'client_secret'   => isset( $params['client_secret'] ) ? sanitize_text_field( (string) $params['client_secret'] ) : '',
			'callback_url'    => isset( $params['callback_url'] ) ? esc_url_raw( (string) $params['callback_url'] ) : '',
			'shop_url'        => isset( $params['shop_url'] ) ? esc_url_raw( (string) $params['shop_url'] ) : '',
			'use_default_css' => ! empty( $params['use_default_css'] ),
		);

		update_option( Admin::OPTIONS_KEY, $sanitized );

		return rest_ensure_response( $this->current_settings() );
	}

	public function get_auth_status( WP_REST_Request $request ) {
		$access_token    = get_transient( Auth::ACCESS_TOKEN_TRANSIENT_KEY );
		$refresh_token   = get_option( Auth::REFRESH_TOKEN_OPTION_KEY );
		$last_error      = get_option( Core::LAST_ERROR_OPTION_KEY );
		$last_error_time = get_option( Core::LAST_ERROR_TIME_OPTION_KEY );

		return rest_ensure_response(
			array(
				'authorized'            => ! empty( $access_token ),
				'access_token_present'  => ! empty( $access_token ),
				'refresh_token_present' => ! empty( $refresh_token ),
				'last_error'            => $last_error ? (string) $last_error : '',
				'last_error_time'       => $last_error_time ? (int) $last_error_time : 0,
			)
		);
	}

	public function start_auth( WP_REST_Request $request ) {
		$client_id    = Admin::option( 'client_id' );
		$callback_url = Admin::option( 'callback_url' );

		if ( empty( $client_id ) || empty( $callback_url ) ) {
			return new WP_Error(
				'base_item_list_missing_credentials',
				__( 'client_id とコールバック URL を先に保存してください。', 'base-item-list' ),
				array( 'status' => 400 )
			);
		}

		$this->ensure_session_started();
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- benign URL-safe state token, not for obfuscation.
		$state                   = base64_encode( wp_generate_password( 12, true, true ) );
		$_SESSION['oauth_state'] = $state;

		$auth_url = add_query_arg(
			array(
				'response_type' => 'code',
				'client_id'     => $client_id,
				'redirect_uri'  => rawurlencode( $callback_url ),
				'scope'         => 'read_items',
				'state'         => $state,
			),
			Auth::BASE_API_AUTH_URL
		);

		return rest_ensure_response( array( 'redirect_url' => $auth_url ) );
	}

	protected function ensure_session_started(): void {
		if ( PHP_SESSION_NONE === session_status() ) {
			session_start();
		}
	}

	private function current_settings(): array {
		$options = get_option( Admin::OPTIONS_KEY, Admin::OPTIONS_DEFUALT );
		if ( ! is_array( $options ) ) {
			$options = Admin::OPTIONS_DEFUALT;
		}

		return array(
			'client_id'       => isset( $options['client_id'] ) ? (string) $options['client_id'] : '',
			'client_secret'   => isset( $options['client_secret'] ) ? (string) $options['client_secret'] : '',
			'callback_url'    => isset( $options['callback_url'] ) ? (string) $options['callback_url'] : '',
			'shop_url'        => isset( $options['shop_url'] ) ? (string) $options['shop_url'] : '',
			'use_default_css' => ! empty( $options['use_default_css'] ),
		);
	}
}
