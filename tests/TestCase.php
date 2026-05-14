<?php
/**
 * Base test case wiring Brain Monkey into PHPUnit.
 *
 * @package mt8\BaseItemList\Tests
 */

namespace mt8\BaseItemList\Tests;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Yoast\PHPUnitPolyfills\TestCases\TestCase as PolyfillsTestCase;

abstract class TestCase extends PolyfillsTestCase {

	use MockeryPHPUnitIntegration;

	protected function set_up() {
		parent::set_up();
		Monkey\setUp();
		$this->stub_common_wp_functions();
	}

	protected function tear_down() {
		Monkey\tearDown();
		parent::tear_down();
	}

	/**
	 * Stub WordPress helpers that are pure / commonly used so tests don't repeat themselves.
	 *
	 * Tests can override any of these with Functions\when()/expect() inside the test.
	 */
	private function stub_common_wp_functions(): void {
		Monkey\Functions\stubs(
			array(
				'__'             => null,
				'_e'             => null,
				'esc_html'       => null,
				'esc_attr'       => null,
				'esc_url'        => null,
				'esc_html__'     => null,
				'esc_attr__'     => null,
				'wp_kses_post'   => null,
				'untrailingslashit' => static function ( $string ) {
					return rtrim( (string) $string, '/\\' );
				},
				'sanitize_text_field' => static function ( $string ) {
					return is_string( $string ) ? trim( $string ) : '';
				},
			)
		);
	}
}
