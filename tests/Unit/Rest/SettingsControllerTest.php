<?php
/**
 * Tests for mt8\BaseItemList\Rest\SettingsController.
 *
 * @package mt8\BaseItemList\Tests
 */

namespace mt8\BaseItemList\Tests\Unit\Rest;

use Brain\Monkey\Functions;
use mt8\BaseItemList\Admin\Admin;
use mt8\BaseItemList\Auth;
use mt8\BaseItemList\Core;
use mt8\BaseItemList\Rest\SettingsController;
use mt8\BaseItemList\Tests\TestCase;
use WP_REST_Request;

class SettingsControllerTest extends TestCase {

	protected function set_up() {
		parent::set_up();

		Functions\when( 'esc_url_raw' )->alias(
			static function ( $url ) {
				return is_string( $url ) ? trim( $url ) : '';
			}
		);
		Functions\when( 'rest_ensure_response' )->alias(
			static function ( $data ) {
				return new \WP_REST_Response( $data, 200 );
			}
		);
		Functions\when( 'add_query_arg' )->alias(
			static function ( array $args, string $url ) {
				return $url . '?' . http_build_query( $args );
			}
		);
		Functions\when( 'wp_generate_password' )->justReturn( 'random12chars' );
	}

	public function test_register_routes_registers_three_route_paths(): void {
		Functions\expect( 'register_rest_route' )
			->times( 3 )
			->with(
				SettingsController::REST_NAMESPACE,
				\Mockery::anyOf( '/settings', '/auth-status', '/auth/start' ),
				\Mockery::type( 'array' )
			)
			->andReturn( true );

		( new SettingsController() )->register_routes();
	}

	public function test_permission_callback_requires_manage_options(): void {
		Functions\expect( 'current_user_can' )->once()->with( 'manage_options' )->andReturn( true );
		$this->assertTrue( ( new SettingsController() )->permission_callback() );

		Functions\expect( 'current_user_can' )->once()->with( 'manage_options' )->andReturn( false );
		$this->assertFalse( ( new SettingsController() )->permission_callback() );
	}

	public function test_get_settings_returns_current_options_with_defaults(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'client_id'       => 'cid',
				'client_secret'   => 'sec',
				'callback_url'    => 'https://cb.example/',
				'shop_url'        => 'https://shop.example/',
				'use_default_css' => '1',
			)
		);

		$response = ( new SettingsController() )->get_settings( new WP_REST_Request() );

		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertSame( 'cid', $data['client_id'] );
		$this->assertSame( 'sec', $data['client_secret'] );
		$this->assertSame( 'https://cb.example/', $data['callback_url'] );
		$this->assertSame( 'https://shop.example/', $data['shop_url'] );
		$this->assertTrue( $data['use_default_css'] );
	}

	public function test_get_settings_falls_back_when_option_is_not_an_array(): void {
		Functions\when( 'get_option' )->justReturn( false );

		$data = ( new SettingsController() )->get_settings( new WP_REST_Request() )->get_data();

		$this->assertSame( '', $data['client_id'] );
		$this->assertSame( '', $data['shop_url'] );
		$this->assertFalse( $data['use_default_css'] );
	}

	public function test_update_settings_sanitizes_and_persists(): void {
		Functions\when( 'get_option' )->justReturn( array() );
		Functions\expect( 'update_option' )
			->once()
			->with(
				Admin::OPTIONS_KEY,
				\Mockery::on(
					static function ( $payload ) {
						return 'cid' === $payload['client_id']
							&& 'sec' === $payload['client_secret']
							&& 'https://cb' === $payload['callback_url']
							&& 'https://shop' === $payload['shop_url']
							&& true === $payload['use_default_css'];
					}
				)
			)
			->andReturn( true );

		$request = ( new WP_REST_Request() )->set_json_params(
			array(
				'client_id'       => '  cid  ',
				'client_secret'   => 'sec',
				'callback_url'    => 'https://cb',
				'shop_url'        => 'https://shop',
				'use_default_css' => true,
			)
		);

		$response = ( new SettingsController() )->update_settings( $request );
		$this->assertSame( 200, $response->get_status() );
	}

	public function test_update_settings_treats_missing_fields_as_empty(): void {
		Functions\when( 'get_option' )->justReturn( array() );
		Functions\expect( 'update_option' )
			->once()
			->with(
				Admin::OPTIONS_KEY,
				array(
					'client_id'       => '',
					'client_secret'   => '',
					'callback_url'    => '',
					'shop_url'        => '',
					'use_default_css' => false,
				)
			)
			->andReturn( true );

		$request = ( new WP_REST_Request() )->set_json_params( array() );

		( new SettingsController() )->update_settings( $request );
	}

	public function test_update_settings_falls_back_to_request_params_when_json_is_null(): void {
		Functions\when( 'get_option' )->justReturn( array() );
		Functions\expect( 'update_option' )
			->once()
			->with(
				Admin::OPTIONS_KEY,
				\Mockery::on(
					static function ( $payload ) {
						return 'from-form' === $payload['client_id'];
					}
				)
			)
			->andReturn( true );

		$request = ( new WP_REST_Request() )
			->set_json_params( null )
			->set_params( array( 'client_id' => 'from-form' ) );

		( new SettingsController() )->update_settings( $request );
	}

	public function test_get_auth_status_reports_authorized_when_access_token_exists(): void {
		Functions\when( 'get_transient' )->alias(
			static function ( $key ) {
				return Auth::ACCESS_TOKEN_TRANSIENT_KEY === $key ? 'tok' : false;
			}
		);
		Functions\when( 'get_option' )->alias(
			static function ( $key ) {
				if ( Auth::REFRESH_TOKEN_OPTION_KEY === $key ) {
					return 'rt';
				}
				if ( Core::LAST_ERROR_OPTION_KEY === $key ) {
					return 'some error';
				}
				return false;
			}
		);

		$data = ( new SettingsController() )->get_auth_status( new WP_REST_Request() )->get_data();

		$this->assertTrue( $data['authorized'] );
		$this->assertTrue( $data['access_token_present'] );
		$this->assertTrue( $data['refresh_token_present'] );
		$this->assertSame( 'some error', $data['last_error'] );
	}

	public function test_get_auth_status_reports_unauthorized_when_no_token(): void {
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'get_option' )->justReturn( '' );

		$data = ( new SettingsController() )->get_auth_status( new WP_REST_Request() )->get_data();

		$this->assertFalse( $data['authorized'] );
		$this->assertFalse( $data['refresh_token_present'] );
		$this->assertSame( '', $data['last_error'] );
	}

	public function test_start_auth_returns_error_when_credentials_missing(): void {
		Functions\when( 'get_option' )->justReturn( array( 'client_id' => '', 'callback_url' => '' ) );

		$result = ( new SettingsController() )->start_auth( new WP_REST_Request() );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'base_item_list_missing_credentials', $result->get_error_code() );
		$this->assertSame( 400, $result->get_error_data()['status'] );
	}

	public function test_start_auth_returns_redirect_url_with_state_when_credentials_present(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'client_id'    => 'my-client',
				'callback_url' => 'https://example.test/cb',
			)
		);

		$controller = \Mockery::mock( SettingsController::class )
			->shouldAllowMockingProtectedMethods()
			->makePartial();
		$controller->shouldReceive( 'ensure_session_started' )->once();

		$response = $controller->start_auth( new WP_REST_Request() );
		$this->assertSame( 200, $response->get_status() );

		$redirect = $response->get_data()['redirect_url'];
		$this->assertStringStartsWith( Auth::BASE_API_AUTH_URL . '?', $redirect );
		$this->assertStringContainsString( 'client_id=my-client', $redirect );
		$this->assertStringContainsString( 'response_type=code', $redirect );
		$this->assertStringContainsString( 'scope=read_items', $redirect );

		$this->assertNotEmpty( $_SESSION['oauth_state'] ?? '' );
	}
}
