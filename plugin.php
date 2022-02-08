<?php
/*
	Plugin Name: BASE Item List
	Plugin URI: https://github.com/mt8/base-item-list
	Description: Display BASE(https://thebase.in/) Item List by shortcode [BASE_ITEM]
	Author: mt8.biz, shimakyohsuke
	Version: 1.2.4
	Author URI: https://mt8.biz
	Domain Path: /languages
	Text Domain: base-item-list
*/
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', function() {
	$admin_view = new \mt8\BaseItemList\Admin\View();
	add_menu_page(
		'BASE Item List',
		'BASE Item List<span class="awaiting-mod"> !</span>' ,
		'manage_options',
		'base_item_list',
		array( $admin_view, 'option_page' ),
		'dashicons-cart'
	);
});

// v2
require_once __DIR__ . '/vendor/autoload.php';
$bilo_v2 = new \mt8\BaseItemList\Core();
$bilo_v2->register_hooks();

// v1
require_once __DIR__ . '/includes/v1/class-BaseItemList.php';
require_once __DIR__ . '/includes/v1/class-BaseItemListAdmin.php';
$bilo_v1 = new Base_Item_List_V1();
$bilo_v1->register_hooks();
