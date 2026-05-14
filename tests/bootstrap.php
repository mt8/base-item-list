<?php
/**
 * PHPUnit bootstrap for the BASE Item List plugin.
 *
 * Loads Composer's autoloader so plugin classes are available via PSR-4. WordPress
 * core functions are mocked per-test with Brain Monkey, so a full WordPress install
 * is not required.
 *
 * @package mt8\BaseItemList\Tests
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

require_once __DIR__ . '/Stubs/rest-stubs.php';
require_once __DIR__ . '/TestCase.php';
