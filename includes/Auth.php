<?php

namespace mt8\BaseItemList;

use mt8\BaseItemList\Admin\Admin;

class Auth {

	const BASE_API_AUTH_URL = 'https://api.thebase.in/1/oauth/authorize';
	const BASE_API_TOKEN_URL = 'https://api.thebase.in/1/oauth/token';

	const ACCESS_TOKEN_TRANSIENT_KEY = 'base-item-list-access-token';
	const REFRESH_TOKEN_OPTION_KEY = 'base-item-list-refresh-token';

	public function authorize() {

		if ( '1' === filter_input( INPUT_GET, 'force' ) ) {
			delete_transient( self::ACCESS_TOKEN_TRANSIENT_KEY );
			delete_option( self::REFRESH_TOKEN_OPTION_KEY );
		}

		$code = $this->get_auth_code();
		$this->get_access_token( $code );

		$admin_url = add_query_arg(
			array(
				'page'   => 'base_item_list_setting',
				'status' => 'authorized',
			),
			admin_url( '/admin.php' )
		);
		wp_safe_redirect( $admin_url, 301 );
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

		$client_id = Admin::option( 'client_id' );
		$callback_url = Admin::option( 'callback_url' );

		$auth_url = add_query_arg(
			array(
				'response_type' => 'code',
				'client_id'     => $client_id,
				'redirect_uri'  => urlencode( $callback_url ),
				'scope'         => 'read_items',
				'state'         => $state,
			),
			self::BASE_API_AUTH_URL
		);

		add_filter( 'allowed_redirect_hosts', function ( $allowed ) {
			$allowed[] = parse_url( self::BASE_API_AUTH_URL, PHP_URL_HOST );
			return $allowed;
		});

		wp_safe_redirect( $auth_url, 301 );
		exit;
	}

	public function get_access_token( $code = '', $use_cache = true ) {

		if ( $use_cache ) {
			$token = get_transient( self::ACCESS_TOKEN_TRANSIENT_KEY );
			if ( ! empty( $token ) ) {
				return $token;
			}
		}

		$client_id = Admin::option( 'client_id' );
		$client_secret = Admin::option( 'client_secret' );
		$callback_url = Admin::option( 'callback_url' );

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

	public function authorized() {
		return ( true !== empty( $this->get_access_token( '', false ) ) );
	}

}
