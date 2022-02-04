<?php

class Base_Item_List_V2 {
		
	public $admin;

	public function __construct() {

		$this->admin = New Base_Item_List_Admin_V2();
		
	}

	public function register_hooks() {

		add_action( 'admin_menu', array( $this->admin, 'admin_menu' ) );

	}

}
