<?php
/*
	Plugin Name: BASE Item List
	Plugin URI: https://github.com/mt8/base-item-list
	Description: Display BASE(https://thebase.in/) Item List by shortcode [BASE_ITEM]
	Author: mt8.biz
	Version: 1.0.4
	Author URI: https://mt8.biz
	Domain Path: /languages
	Text Domain: base-item-list
*/
if ( ! defined( 'ABSPATH' ) ) exit;

require_once plugin_dir_path( __FILE__ ) . '/class-BaseItemList.php';
require_once plugin_dir_path( __FILE__ ) . '/class-BaseItemListAdmin.php';

$bil = new Base_Item_List();
$bil->register_hooks();
