<?php
/**
 * Tests for mt8\BaseItemList\Core.
 *
 * @package mt8\BaseItemList\Tests
 */

namespace mt8\BaseItemList\Tests\Unit;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use mt8\BaseItemList\Admin\Admin;
use mt8\BaseItemList\Auth;
use mt8\BaseItemList\Core;
use mt8\BaseItemList\Tests\TestCase;

class CoreTest extends TestCase {

	protected function set_up() {
		parent::set_up();

		Functions\when( 'shortcode_atts' )->alias(
			static function ( $defaults, $atts ) {
				$atts = is_array( $atts ) ? $atts : array();
				return array_merge( $defaults, $atts );
			}
		);
		Functions\when( 'plugins_url' )->alias(
			static function ( $path, $base ) {
				return 'https://example.test' . $path;
			}
		);
		Functions\when( 'get_stylesheet_directory' )->justReturn( '/tmp/does-not-exist' );
	}

	public function test_register_hooks_registers_expected_actions_and_shortcode(): void {
		Actions\expectAdded( 'admin_init' )->once();
		Actions\expectAdded( 'admin_menu' )->once();
		Actions\expectAdded( 'admin_enqueue_scripts' )->once();
		Actions\expectAdded( 'wp_enqueue_scripts' )->once();
		Actions\expectAdded( 'rest_api_init' )->once();

		Functions\expect( 'add_shortcode' )
			->once()
			->with( 'BASE_ITEM', \Mockery::type( 'array' ) );

		( new Core() )->register_hooks();
	}

	public function test_add_shortcode_returns_rendered_items_from_cache(): void {
		$cached = (object) array(
			'items' => array(
				(object) array(
					'item_id'  => 1,
					'title'    => 'cached title',
					'img1_300' => 'https://img.example/1.jpg',
				),
			),
		);
		Functions\when( 'get_transient' )->justReturn( $cached );
		Functions\when( 'set_transient' )->justReturn( true );
		Functions\when( 'get_option' )->alias(
			static function ( $key, $default = false ) {
				if ( Admin::OPTIONS_KEY === $key ) {
					return array( 'shop_url' => 'https://shop.example/' );
				}
				return $default;
			}
		);

		$core   = $this->partial_core_without_request_api();
		$output = $core->add_shortcode( array() );

		$this->assertStringContainsString( 'cached title', $output );
		$this->assertStringContainsString( 'https://shop.example/items/1', $output );
		$this->assertStringContainsString( 'https://img.example/1.jpg', $output );
	}

	public function test_add_shortcode_calls_api_when_cache_miss_and_caches_result(): void {
		$response = (object) array(
			'items' => array(
				(object) array(
					'item_id'  => 42,
					'title'    => 'fresh',
					'img1_300' => 'https://img.example/42.jpg',
				),
			),
		);

		Functions\when( 'get_transient' )->justReturn( false );
		Functions\expect( 'set_transient' )
			->once()
			->with( \Mockery::pattern( '/^base-item-list-[a-f0-9]{32}$/' ), $response, 60 )
			->andReturn( true );
		Functions\when( 'get_option' )->justReturn( array( 'shop_url' => 'https://shop.example' ) );

		$core = \Mockery::mock( Core::class )->makePartial();
		$core->shouldReceive( 'request_api' )
			->once()
			->with(
				\Mockery::on(
					static function ( $args ) {
						return is_array( $args )
							&& '*' === $args['q']
							&& 'title,detail,categories' === $args['fields']
							&& '' === $args['order']
							&& 'desc' === $args['sort']
							&& 10 === $args['limit'];
					}
				)
			)
			->andReturn( $response );

		$output = $core->add_shortcode( array() );

		$this->assertStringContainsString( 'fresh', $output );
	}

	public function test_add_shortcode_returns_empty_string_when_api_returns_null(): void {
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'get_option' )->justReturn( array( 'shop_url' => '' ) );

		$core = \Mockery::mock( Core::class )->makePartial();
		$core->shouldReceive( 'request_api' )->once()->andReturn( null );

		$this->assertSame( '', $core->add_shortcode( array() ) );
	}

	public function test_add_shortcode_falls_back_to_defaults_for_invalid_parameters(): void {
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'get_option' )->justReturn( array( 'shop_url' => '' ) );
		Functions\when( 'set_transient' )->justReturn( true );

		$core = \Mockery::mock( Core::class )->makePartial();
		$core->shouldReceive( 'request_api' )
			->once()
			->with(
				\Mockery::on(
					static function ( $args ) {
						return 'title,detail,categories' === $args['fields'] // invalid field reset.
							&& '' === $args['order'] // invalid order reset.
							&& 'desc' === $args['sort'] // invalid sort reset.
							&& 10 === $args['limit']; // out-of-range limit reset.
					}
				)
			)
			->andReturn( (object) array( 'items' => array() ) );

		$core->add_shortcode(
			array(
				'fields' => 'evil,detail',
				'order'  => 'bogus',
				'sort'   => 'sideways',
				'limit'  => 9999,
				'cache'  => -1,
			)
		);
	}

	public function test_add_shortcode_resets_non_positive_cache_to_default_and_writes_transient(): void {
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'get_option' )->justReturn( array( 'shop_url' => '' ) );
		// cache=-5 is reset to the 60s default by the validator, so set_transient IS called with 60.
		Functions\expect( 'set_transient' )
			->once()
			->with( \Mockery::type( 'string' ), \Mockery::type( 'object' ), 60 )
			->andReturn( true );

		$core = \Mockery::mock( Core::class )->makePartial();
		$core->shouldReceive( 'request_api' )->andReturn( (object) array( 'items' => array() ) );

		$core->add_shortcode( array( 'cache' => '-5' ) );
	}

	public function test_add_shortcode_returns_empty_and_logs_when_exception_thrown(): void {
		Functions\when( 'get_transient' )->alias(
			static function () {
				throw new \RuntimeException( 'boom' );
			}
		);
		Functions\expect( 'update_option' )
			->once()
			->with( Core::LAST_ERROR_OPTION_KEY, \Mockery::pattern( '/boom/' ), false );

		$core = new Core();
		$this->assertSame( '', $core->add_shortcode( array() ) );
	}

	public function test_request_api_returns_null_when_no_access_token(): void {
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'get_option' )->alias(
			static function ( $key, $default = false ) {
				if ( Auth::REFRESH_TOKEN_OPTION_KEY === $key ) {
					return ''; // no refresh token.
				}
				if ( Admin::OPTIONS_KEY === $key ) {
					return array(
						'client_id'     => 'cid',
						'client_secret' => 'sec',
						'callback_url'  => 'https://cb',
					);
				}
				return $default;
			}
		);
		// No refresh token + no code: wp_remote_post returns error (we make it 401).
		Functions\when( 'wp_remote_post' )->justReturn( array() );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 401 );
		Functions\expect( 'update_option' )
			->once()
			->with( Core::LAST_ERROR_OPTION_KEY, \Mockery::type( 'string' ), false );

		$core   = new Core();
		$result = $core->request_api(
			array(
				'q'      => '*',
				'fields' => 'title',
				'order'  => '',
				'sort'   => 'desc',
				'limit'  => 10,
			)
		);

		$this->assertNull( $result );
	}

	public function test_request_api_returns_decoded_json_on_success(): void {
		// Cached access token shortcut: no token negotiation needed.
		Functions\when( 'get_transient' )->alias(
			static function ( $key ) {
				return Auth::ACCESS_TOKEN_TRANSIENT_KEY === $key ? 'cached-token' : false;
			}
		);

		Functions\expect( 'wp_remote_get' )
			->once()
			->with(
				Core::BASE_API_ITEMS_URL,
				\Mockery::on(
					static function ( $args ) {
						return isset( $args['headers']['Authorization'] )
							&& 'Bearer cached-token' === $args['headers']['Authorization']
							&& 'kw' === $args['body']['q']
							&& 5 === $args['body']['limit'];
					}
				)
			)
			->andReturn( array( 'body' => '{"items":[{"item_id":7,"title":"a"}]}' ) );

		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 200 );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn( '{"items":[{"item_id":7,"title":"a"}]}' );

		$core   = new Core();
		$result = $core->request_api(
			array(
				'q'      => 'kw',
				'fields' => 'title',
				'order'  => 'list_order',
				'sort'   => 'asc',
				'limit'  => 5,
			)
		);

		$this->assertIsObject( $result );
		$this->assertSame( 7, $result->items[0]->item_id );
	}

	public function test_request_api_returns_null_and_logs_on_non_200(): void {
		Functions\when( 'get_transient' )->alias(
			static function ( $key ) {
				return Auth::ACCESS_TOKEN_TRANSIENT_KEY === $key ? 'tok' : false;
			}
		);
		Functions\when( 'wp_remote_get' )->justReturn( array() );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 500 );
		Functions\when( 'wp_remote_retrieve_response_message' )->justReturn( 'Server Error' );
		Functions\expect( 'update_option' )
			->once()
			->with( Core::LAST_ERROR_OPTION_KEY, \Mockery::type( 'string' ), false );

		$core = new Core();
		$this->assertNull(
			$core->request_api(
				array(
					'q'      => '*',
					'fields' => 'title',
					'order'  => '',
					'sort'   => 'desc',
					'limit'  => 10,
				)
			)
		);
	}

	public function test_item_list_appends_shop_url_to_each_item(): void {
		Functions\when( 'get_option' )->justReturn( array( 'shop_url' => 'https://shop.example/' ) );

		$items = array(
			(object) array(
				'item_id'  => 1,
				'title'    => 't1',
				'img1_300' => 'https://i/1.jpg',
			),
			(object) array(
				'item_id'  => 2,
				'title'    => 't2',
				'img1_300' => 'https://i/2.jpg',
			),
		);

		$core   = new Core();
		$output = $core->item_list( $items );

		$this->assertStringContainsString( 'https://shop.example/items/1', $output );
		$this->assertStringContainsString( 'https://shop.example/items/2', $output );
		$this->assertStringContainsString( 't1', $output );
		$this->assertStringContainsString( 't2', $output );
	}

	public function test_wp_enqueue_scripts_enqueues_default_style(): void {
		Functions\when( 'get_option' )->justReturn( array( 'use_default_css' => '1' ) );
		Functions\expect( 'wp_enqueue_style' )
			->once()
			->with( 'base-item-list', \Mockery::pattern( '#/assets/css/base-item-list\.css$#' ) );

		( new Core() )->wp_enqueue_scripts();
	}

	/**
	 * Build a partial Core mock whose request_api is never expected; useful when the test
	 * exercises the cache-hit branch only.
	 */
	private function partial_core_without_request_api(): Core {
		$core = \Mockery::mock( Core::class )->makePartial();
		$core->shouldNotReceive( 'request_api' );
		return $core;
	}
}
