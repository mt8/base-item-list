<?php
/*
	Plugin Name: BASE Item List
	Plugin URI: https://github.com/mt8/base-item-list
	Description: Display BASE(https://thebase.in/) Item List by shortcode [BASE_ITEM]
	Author: mt8.biz, shimakyohsuke
	Version: 2.0.3
	Author URI: https://mt8.biz
	Domain Path: /languages
	Text Domain: base-item-list
*/
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', function() {
	$admin_view = new \mt8\BaseItemList\Admin\View();
	add_menu_page(
		'BASE Item List',
		'BASE Item List' ,
		'manage_options',
		'base_item_list',
		array( $admin_view, 'option_page' ),
		'dashicons-cart'
	);
});

require_once __DIR__ . '/includes/Core.php';
require_once __DIR__ . '/includes/Admin/Admin.php';
require_once __DIR__ . '/includes/Admin/View.php';
require_once __DIR__ . '/includes/Auth.php';
$bilo = new \mt8\BaseItemList\Core();
$bilo->register_hooks();
