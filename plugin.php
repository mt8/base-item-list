<?php
/*
	Plugin Name: BASE Item List
	Plugin URI: https://github.com/mt8/base-item-list
	Description: Display BASE(https://thebase.in/) Item List by shortcode [BASE_ITEM]
	Author: mt8.biz, shimakyohsuke
	Version: 1.1.7
	Author URI: https://mt8.biz
	Domain Path: /languages
	Text Domain: base-item-list
*/
if ( ! defined( 'ABSPATH' ) ) exit;

// v1
require_once __DIR__ . '/includes/v1/class-BaseItemList.php';
require_once __DIR__ . '/includes/v1/class-BaseItemListAdmin.php';
$bilo_v1 = new Base_Item_List_V1();
$bilo_v1->register_hooks();

// v2
require_once __DIR__ . '/includes/v2/class-BaseItemList.php';
require_once __DIR__ . '/includes/v2/class-BaseItemListAdmin.php';
$bilo_v2 = new Base_Item_List_V2();
$bilo_v2->register_hooks();
