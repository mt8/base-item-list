<?php
/**
 * Tests for mt8\BaseItemList\Admin\View.
 *
 * @package mt8\BaseItemList\Tests
 */

namespace mt8\BaseItemList\Tests\Unit;

use Brain\Monkey\Functions;
use mt8\BaseItemList\Admin\Admin;
use mt8\BaseItemList\Admin\View;
use mt8\BaseItemList\Tests\TestCase;

class ViewTest extends TestCase {

	public function test_filter_setting_blanks_missing_or_empty_required_fields(): void {
		$result = View::filter_setting(
			array(
				'client_id'     => 'has-value',
				'client_secret' => '   ', // whitespace-only → blanked.
				// callback_url missing → blanked.
				'shop_url'      => '',
				'extra'         => 'preserved', // non-required key untouched.
			)
		);

		$this->assertSame( 'has-value', $result['client_id'] );
		$this->assertSame( '', $result['client_secret'] );
		$this->assertSame( '', $result['callback_url'] );
		$this->assertSame( '', $result['shop_url'] );
		$this->assertSame( 'preserved', $result['extra'] );
	}

	public function test_filter_setting_returns_input_unchanged_when_all_required_fields_present(): void {
		$input = array(
			'client_id'     => 'a',
			'client_secret' => 'b',
			'callback_url'  => 'c',
			'shop_url'      => 'd',
		);

		$this->assertSame( $input, View::filter_setting( $input ) );
	}

	public function test_register_setting_fields_registers_section_and_all_fields(): void {
		$key     = Admin::OPTIONS_KEY;
		$section = $key . '_section';

		Functions\expect( 'register_setting' )
			->once()
			->with( $key . '_group', $key, \Mockery::type( 'array' ) );

		Functions\expect( 'add_settings_section' )
			->once()
			->with( $section, '設定', \Mockery::type( 'array' ), $key );

		Functions\expect( 'add_settings_field' )
			->times( 5 )
			->with(
				\Mockery::anyOf( 'client_id', 'client_secret', 'callback_url', 'shop_url', 'use_default_css' ),
				\Mockery::type( 'string' ),
				\Mockery::type( 'array' ),
				$key,
				$section
			);

		View::register_setting_fields();
	}
}
