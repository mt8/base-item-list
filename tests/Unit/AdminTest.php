<?php
/**
 * Tests for mt8\BaseItemList\Admin\Admin.
 *
 * @package mt8\BaseItemList\Tests
 */

namespace mt8\BaseItemList\Tests\Unit;

use Brain\Monkey\Functions;
use mt8\BaseItemList\Admin\Admin;
use mt8\BaseItemList\Tests\TestCase;

class AdminTest extends TestCase {

	public function test_option_returns_stored_value_for_known_key(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'client_id'     => 'the-client',
				'client_secret' => 'shh',
				'callback_url'  => 'https://example.test/cb',
				'shop_url'      => 'https://shop.example/',
			)
		);

		$this->assertSame( 'the-client', Admin::option( 'client_id' ) );
		$this->assertSame( 'https://shop.example/', Admin::option( 'shop_url' ) );
	}

	public function test_option_returns_empty_string_for_unknown_key(): void {
		Functions\when( 'get_option' )->justReturn( array( 'client_id' => 'x' ) );

		$this->assertSame( '', Admin::option( 'no_such_key' ) );
	}

	public function test_option_returns_empty_string_when_option_is_not_an_array(): void {
		Functions\when( 'get_option' )->justReturn( false );

		$this->assertSame( '', Admin::option( 'client_id' ) );
	}

	public function test_saved_options_returns_true_when_all_required_fields_are_present(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'client_id'     => 'a',
				'client_secret' => 'b',
				'callback_url'  => 'c',
				'shop_url'      => 'd',
			)
		);

		$this->assertTrue( ( new Admin() )->saved_options() );
	}

	public function test_saved_options_returns_false_when_any_required_field_is_empty(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'client_id'     => 'a',
				'client_secret' => '',
				'callback_url'  => 'c',
				'shop_url'      => 'd',
			)
		);

		$this->assertFalse( ( new Admin() )->saved_options() );
	}

	public function test_saved_options_returns_false_when_required_field_is_missing(): void {
		Functions\when( 'get_option' )->justReturn(
			array(
				'client_id'     => 'a',
				'client_secret' => 'b',
				// callback_url missing.
				'shop_url'      => 'd',
			)
		);

		$this->assertFalse( ( new Admin() )->saved_options() );
	}

	public function test_admin_init_skips_when_request_is_not_for_settings_page(): void {
		global $pagenow;
		$pagenow = 'plugins.php';
		unset( $_GET['page'] );

		// No auth flow should run; the test passes as long as admin_init returns without errors.
		( new Admin() )->admin_init();
		$this->assertTrue( true );
	}

	public function test_admin_menu_adds_submenu_page(): void {
		Functions\expect( 'add_submenu_page' )
			->once()
			->with(
				'base_item_list',
				'API設定',
				'API設定',
				'manage_options',
				'base_item_list_setting',
				\Mockery::type( 'array' )
			);

		( new Admin() )->admin_menu();
	}

	public function test_admin_enqueue_scripts_skips_for_unrelated_admin_pages(): void {
		Functions\expect( 'wp_enqueue_script' )->never();
		Functions\expect( 'wp_enqueue_style' )->never();
		Functions\expect( 'wp_localize_script' )->never();

		( new Admin() )->admin_enqueue_scripts( 'edit.php' );
		$this->assertTrue( true );
	}
}
