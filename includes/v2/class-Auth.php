<?php

class Base_Item_List_Auth {

	const BASE_API_AUTH_URL = 'https://api.thebase.in/1/oauth/authorize';
	const BASE_API_TOKEN_URL = 'https://api.thebase.in/1/oauth/token';

	const ACCESS_TOKEN_TRANSIENT_KEY = 'base-item-list-access-token';
	const REFRESH_TOKEN_OPTION_KEY = 'base-item-list-refresh-token';

	public function init() {
		add_rewrite_endpoint( 'bil', EP_ROOT );
		if ( PHP_SESSION_ACTIVE !== session_status() ) {
			session_start();
		}
	}

	public function template_redirect() {
		if ( 'auth' === get_query_var( 'bil' ) ) {
			if ( true || current_user_can( 'administrator' ) ) {
				$this->authorize();
			} else {
				wp_safe_redirect( home_url( '/' ), 301 );
				exit;
			}
		}
	}

	public function authorize() {

		if ( '1' === filter_input( INPUT_GET, 'force' ) ) {
			delete_transient( self::ACCESS_TOKEN_TRANSIENT_KEY );
			delete_option( self::REFRESH_TOKEN_OPTION_KEY );
		}

		// 認可コード取得
		$code = $this->get_auth_code();

		// アクセストークン種取得
		$this->get_access_token( $code );

		wp_safe_redirect( admin_url( '/admin.php?page=base_item_list_v2' ), 301 );
		exit;
	}
	
	public function get_auth_code() {
		if ( ! empty( filter_input( INPUT_GET, 'code' ) ) ) {
			if ( $_SESSION['oauth_state'] !== filter_input( INPUT_GET, 'state' ) ) {
				wp_die( 'Bad Request' );
				exit;
			}
			unset( $_SESSION['oauth_state'] );
			return filter_input( INPUT_GET, 'code' );
		}

		$state = base64_encode( wp_generate_password( 12, true ,true ) );
		$_SESSION['oauth_state'] = $state;

		$admin = new Base_Item_List_Admin_V2();
		$client_id = $admin->option( 'client_id' );
		$callback_url = $admin->option( 'callback_url' );

		$auth_url = add_query_arg(
			array(
				'response_type' => 'code',
				'client_id'     => $client_id,
				'redirect_uri'  => $callback_url,
				'scope'         => 'read_items',
				'state'         => $state,
			),
			self::BASE_API_AUTH_URL
		);
		header( "Location:{$auth_url}" );
		exit;
	}

	public function get_access_token( $code ) {

		$token = get_transient( self::ACCESS_TOKEN_TRANSIENT_KEY );
		if ( ! empty( $token ) ) {
			return $token;
		}

		$admin = new Base_Item_List_Admin_V2();
		$client_id = $admin->option( 'client_id' );
		$client_secret = $admin->option( 'client_secret' );
		$callback_url = $admin->option( 'callback_url' );

		$refresh_token = get_option( self::REFRESH_TOKEN_OPTION_KEY );
		if ( empty( $refresh_token ) ) {
			$res = wp_remote_post(
				self::BASE_API_TOKEN_URL,
				array(
					'headers' => array(
						'Content-Type: application/x-www-form-urlencoded',
					),
					'body' => array(
						'grant_type'    => 'authorization_code',
						'client_id'     => $client_id,
						'client_secret' => $client_secret,
						'code'          => $code,
						'redirect_uri'  => $callback_url,
					)
				)
			);	
		} else {
			$res = wp_remote_post(
				self::BASE_API_TOKEN_URL,
				array(
					'headers' => array(
						'Content-Type: application/x-www-form-urlencoded',
					),
					'body' => array(
						'grant_type'    => 'refresh_token',
						'client_id'     => $client_id,
						'client_secret' => $client_secret,
						'refresh_token' => $refresh_token,
						'redirect_uri'  => $callback_url,
					)
				)
			);				
		}

		if ( 200 === wp_remote_retrieve_response_code( $res ) ) {

			$json = json_decode( wp_remote_retrieve_body( $res ) );

			$token = $json->access_token;
			$refresh_token = $json->refresh_token;

			set_transient( self::ACCESS_TOKEN_TRANSIENT_KEY, $token, $json->expires_in );
			update_option( self::REFRESH_TOKEN_OPTION_KEY, $refresh_token );

			return $token;

		} else {
			return null;
		}
	}

}
