<?php
/**
 * Tests that the three OAuth/items endpoint filters override the hardcoded URLs.
 *
 * @package mt8\BaseItemList\Tests
 */

namespace mt8\BaseItemList\Tests\Unit;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use mt8\BaseItemList\Auth;
use mt8\BaseItemList\Core;
use mt8\BaseItemList\Rest\SettingsController;
use mt8\BaseItemList\Tests\TestCase;
use WP_REST_Request;

class FiltersTest extends TestCase {

	public function test_auth_endpoint_returns_constant_when_no_filter(): void {
		$this->assertSame( Auth::BASE_API_AUTH_URL, Auth::auth_endpoint() );
	}

	public function test_auth_endpoint_returns_filtered_value(): void {
		Filters\expectApplied( 'base_item_list_auth_url' )
			->once()
			->with( Auth::BASE_API_AUTH_URL )
			->andReturn( 'https://mock.test/authorize' );

		$this->assertSame( 'https://mock.test/authorize', Auth::auth_endpoint() );
	}

	public function test_token_endpoint_returns_filtered_value(): void {
		Filters\expectApplied( 'base_item_list_token_url' )
			->once()
			->with( Auth::BASE_API_TOKEN_URL )
			->andReturn( 'https://mock.test/token' );

		$this->assertSame( 'https://mock.test/token', Auth::token_endpoint() );
	}

	public function test_items_endpoint_returns_filtered_value(): void {
		Filters\expectApplied( 'base_item_list_items_url' )
			->once()
			->with( Core::BASE_API_ITEMS_URL )
			->andReturn( 'https://mock.test/items' );

		$this->assertSame( 'https://mock.test/items', Core::items_endpoint() );
	}

	public function test_request_api_uses_filtered_items_url(): void {
		Filters\expectApplied( 'base_item_list_items_url' )
			->atLeast()
			->once()
			->andReturn( 'https://mock.test/items' );
		Functions\when( 'get_transient' )->alias(
			static function ( $key ) {
				return Auth::ACCESS_TOKEN_TRANSIENT_KEY === $key ? 'tok' : false;
			}
		);
		Functions\expect( 'wp_remote_get' )
			->once()
			->with( 'https://mock.test/items', \Mockery::type( 'array' ) )
			->andReturn( array( 'body' => '{"items":[]}' ) );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 200 );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn( '{"items":[]}' );

		( new Core() )->request_api(
			array(
				'q'      => '*',
				'fields' => 'title',
				'order'  => '',
				'sort'   => 'desc',
				'limit'  => 10,
			)
		);
	}

	public function test_get_access_token_uses_filtered_token_url(): void {
		Filters\expectApplied( 'base_item_list_token_url' )
			->atLeast()
			->once()
			->andReturn( 'https://mock.test/token' );
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'get_option' )->justReturn( '' );
		Functions\when( 'set_transient' )->justReturn( true );
		Functions\when( 'update_option' )->justReturn( true );
		Functions\expect( 'wp_remote_post' )
			->once()
			->with( 'https://mock.test/token', \Mockery::type( 'array' ) )
			->andReturn( array() );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 200 );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn(
			'{"access_token":"t","refresh_token":"r","expires_in":60}'
		);

		( new Auth() )->get_access_token( 'code' );
	}

	public function test_settings_controller_start_auth_uses_filtered_auth_url(): void {
		Filters\expectApplied( 'base_item_list_auth_url' )
			->atLeast()
			->once()
			->andReturn( 'https://mock.test/authorize' );

		Functions\when( 'get_option' )->justReturn(
			array(
				'client_id'    => 'cid',
				'callback_url' => 'https://cb.example/',
			)
		);
		Functions\when( 'wp_generate_password' )->justReturn( 'state12chars' );
		Functions\when( 'add_query_arg' )->alias(
			static function ( array $args, string $url ) {
				return $url . '?' . http_build_query( $args );
			}
		);
		Functions\when( 'rest_ensure_response' )->alias(
			static function ( $data ) {
				return new \WP_REST_Response( $data, 200 );
			}
		);

		$controller = \Mockery::mock( SettingsController::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();
		$controller->shouldReceive( 'ensure_session_started' )->once();

		$response = $controller->start_auth( new WP_REST_Request() );
		$redirect = $response->get_data()['redirect_url'];

		$this->assertStringStartsWith( 'https://mock.test/authorize?', $redirect );
	}
}
