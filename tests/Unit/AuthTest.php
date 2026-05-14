<?php
/**
 * Tests for mt8\BaseItemList\Auth.
 *
 * @package mt8\BaseItemList\Tests
 */

namespace mt8\BaseItemList\Tests\Unit;

use Brain\Monkey\Functions;
use mt8\BaseItemList\Admin\Admin;
use mt8\BaseItemList\Auth;
use mt8\BaseItemList\Tests\TestCase;

class AuthTest extends TestCase {

	protected function set_up() {
		parent::set_up();

		Functions\when( 'get_option' )->alias(
			static function ( $key, $default = false ) {
				if ( Admin::OPTIONS_KEY === $key ) {
					return array(
						'client_id'     => 'cid',
						'client_secret' => 'sec',
						'callback_url'  => 'https://cb.example/',
					);
				}
				if ( Auth::REFRESH_TOKEN_OPTION_KEY === $key ) {
					return '';
				}
				return $default;
			}
		);
	}

	/**
	 * Stubs the two write paths so individual tests don't have to repeat them.
	 *
	 * Call this in tests where set_transient / update_option args are NOT being verified.
	 * In tests that verify args via Functions\expect(), don't call this — `when()` would
	 * shadow the expectation in Brain Monkey.
	 */
	private function ignore_token_writes(): void {
		Functions\when( 'set_transient' )->justReturn( true );
		Functions\when( 'update_option' )->justReturn( true );
	}

	public function test_get_access_token_returns_cached_token_when_available(): void {
		Functions\when( 'get_transient' )->alias(
			static function ( $key ) {
				return Auth::ACCESS_TOKEN_TRANSIENT_KEY === $key ? 'cached-token' : false;
			}
		);
		Functions\expect( 'wp_remote_post' )->never();

		$this->assertSame( 'cached-token', ( new Auth() )->get_access_token() );
	}

	public function test_get_access_token_requests_token_with_authorization_code_when_no_refresh_token(): void {
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\expect( 'wp_remote_post' )
			->once()
			->with(
				Auth::BASE_API_TOKEN_URL,
				\Mockery::on(
					static function ( $args ) {
						return 'authorization_code' === $args['body']['grant_type']
							&& 'cid' === $args['body']['client_id']
							&& 'sec' === $args['body']['client_secret']
							&& 'auth-code' === $args['body']['code']
							&& 'https://cb.example/' === $args['body']['redirect_uri'];
					}
				)
			)
			->andReturn( array( 'body' => '{"access_token":"new-tok","refresh_token":"rt","expires_in":3600}' ) );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 200 );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn( '{"access_token":"new-tok","refresh_token":"rt","expires_in":3600}' );
		Functions\expect( 'set_transient' )
			->once()
			->with( Auth::ACCESS_TOKEN_TRANSIENT_KEY, 'new-tok', 3600 );
		Functions\expect( 'update_option' )
			->once()
			->with( Auth::REFRESH_TOKEN_OPTION_KEY, 'rt' );

		$this->assertSame( 'new-tok', ( new Auth() )->get_access_token( 'auth-code' ) );
	}

	public function test_get_access_token_uses_refresh_grant_when_refresh_token_exists(): void {
		$this->ignore_token_writes();
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'get_option' )->alias(
			static function ( $key, $default = false ) {
				if ( Admin::OPTIONS_KEY === $key ) {
					return array(
						'client_id'     => 'cid',
						'client_secret' => 'sec',
						'callback_url'  => 'https://cb.example/',
					);
				}
				if ( Auth::REFRESH_TOKEN_OPTION_KEY === $key ) {
					return 'existing-refresh';
				}
				return $default;
			}
		);
		Functions\expect( 'wp_remote_post' )
			->once()
			->with(
				Auth::BASE_API_TOKEN_URL,
				\Mockery::on(
					static function ( $args ) {
						return 'refresh_token' === $args['body']['grant_type']
							&& 'existing-refresh' === $args['body']['refresh_token'];
					}
				)
			)
			->andReturn( array( 'body' => '{"access_token":"refreshed","refresh_token":"new-refresh","expires_in":60}' ) );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 200 );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn( '{"access_token":"refreshed","refresh_token":"new-refresh","expires_in":60}' );

		$this->assertSame( 'refreshed', ( new Auth() )->get_access_token() );
	}

	public function test_get_access_token_returns_null_on_non_200_response(): void {
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'wp_remote_post' )->justReturn( array() );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 400 );

		$this->assertNull( ( new Auth() )->get_access_token( 'bad-code' ) );
	}

	public function test_get_access_token_bypasses_cache_when_use_cache_is_false(): void {
		$this->ignore_token_writes();
		// Even when the cache is populated, $use_cache=false should re-negotiate.
		Functions\when( 'get_transient' )->justReturn( 'old-cached' );
		Functions\expect( 'wp_remote_post' )
			->once()
			->andReturn( array() );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 200 );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn( '{"access_token":"renewed","refresh_token":"r","expires_in":1}' );

		$this->assertSame( 'renewed', ( new Auth() )->get_access_token( '', false ) );
	}

	public function test_authorized_returns_true_when_token_obtainable(): void {
		$this->ignore_token_writes();
		// $use_cache=false path: authorized() asks for a fresh token.
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'wp_remote_post' )->justReturn( array() );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 200 );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn( '{"access_token":"t","refresh_token":"r","expires_in":1}' );

		$this->assertTrue( ( new Auth() )->authorized() );
	}

	public function test_authorized_returns_false_when_token_unobtainable(): void {
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'wp_remote_post' )->justReturn( array() );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 401 );

		$this->assertFalse( ( new Auth() )->authorized() );
	}
}
