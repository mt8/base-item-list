<?php
/*
	Plugin Name: BASE Item List
	Plugin URI: https://github.com/mt8/base-item-list
	Description: Display BASE(https://thebase.in/) Item List by shortcode [BASE_ITEM]
	Author: mt8.biz, shimakyohsuke
	Version: 1.1.8
	Author URI: https://mt8.biz
	Domain Path: /languages
	Text Domain: base-item-list
*/
if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/includes/class-BaseItemList.php';
require_once __DIR__ . '/includes/class-BaseItemListAdmin.php';

$bil = new Base_Item_List();
$bil->register_hooks();
