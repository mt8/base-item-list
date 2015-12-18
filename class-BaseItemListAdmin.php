<?php

class Base_Item_List_Admin {
	
	const TEXT_DOMAIN = 'base-item-list';
	const OPTIONS_KEY = 'base-item-list';

	private $options_default = array(
		'client_id'     => '',
		'client_secret' => '',
		'shop_id'       => '',
	);

	public function admin_init() {

		$key     = self::OPTIONS_KEY;
		$group   = $key . '_group';
		$section = $key . '_section'; 
		register_setting( $group, $key, array( &$this, 'register_setting' ) );
		add_settings_section( $section, __( 'settings', self::TEXT_DOMAIN ), array( &$this, 'add_settings_section' ), $key );
		add_settings_field( 'client_id'    , __( 'client_id', self::TEXT_DOMAIN ), array( &$this, 'add_settings_field_client_id' ), $key, $section );
		add_settings_field( 'client_secret', __( 'client_secret', self::TEXT_DOMAIN ) , array( &$this, 'add_settings_field_client_secret' ), $key, $section );
		add_settings_field( 'shop_id'      , __( 'shop_id', self::TEXT_DOMAIN ), array( &$this, 'add_settings_field_shop_id' ), $key, $section );
	}

	public function add_settings_section() {
	}

	public function register_setting( $input ) {
		if ( ! isset( $input['client_id'] ) || trim( $input['client_id'] ) == '' )
			$input['client_id'] = '';
		if ( ! isset( $input['client_secret'] ) || trim( $input['client_secret'] ) == '' )
			$input['client_secret'] = '';
		if ( ! isset( $input['shop_id'] ) || trim( $input['shop_id'] ) == '' )
			$input['shop_id'] = '';
		return $input;
	}

	public function add_settings_field_client_id() {
	?>
	<input type="text" id="client_id" name="<?php echo self::OPTIONS_KEY ?>[client_id]" class="regular-text" value="<?php echo esc_attr( $this->option( 'client_id' ) ) ?>" />
	<?php
	}

	public function add_settings_field_client_secret() {
	?>
	<input type="text" id="client_secret" name="<?php echo self::OPTIONS_KEY ?>[client_secret]" class="regular-text" value="<?php echo esc_attr( $this->option( 'client_secret' ) ) ?>" />
	<?php
	}

	public function add_settings_field_shop_id() {
	?>
	<input type="text" id="app_url" name="<?php echo self::OPTIONS_KEY ?>[shop_id]" class="regular-text" value="<?php echo esc_attr( $this->option( 'shop_id' ) ) ?>" />
	<?php
	}

	public function admin_menu() {
		add_menu_page( 'base_item_list', __('BASE Item List', self::TEXT_DOMAIN) , 'manage_options', __FILE__, array( &$this, 'add_options_page' ), 'dashicons-cart' );
	}

	public function add_options_page() {
	?>
	<div class="wrap">
		<h2><?php _e( 'Input API settings from BASE API application info.', self::TEXT_DOMAIN ) ?></h2>
		<form method="POST" action="options.php">
			<?php do_settings_sections( self::OPTIONS_KEY ); ?>
			<?php settings_fields( self::OPTIONS_KEY . '_group' ); ?>			
			<?php submit_button(); ?>
		</form>		
	</div>
	<?php
	}

	public function option( $key) {
		$option = get_option( self::OPTIONS_KEY, $this->options_default );
		if ( is_array( $option ) && array_key_exists( $key, $option ) ) {
			return $option[$key];
		} else {
			return '';
		}
	}
		
}