<?php

class Base_Item_List_Admin_V2 {

	const TEXT_DOMAIN = 'base-item-list';
	const OPTIONS_KEY = 'base-item-list-v2';

	private $options_default = array(
		'client_id'     => '',
		'client_secret' => '',
		'callback_url'  => '',
	);

	public function admin_init() {

		$key     = self::OPTIONS_KEY;
		$group   = $key . '_group';
		$section = $key . '_section'; 
		register_setting( $group, $key, array( $this, 'register_setting' ) );
		add_settings_section( $section, __( 'settings', self::TEXT_DOMAIN ), array( $this, 'add_settings_section' ), $key );
		add_settings_field( 'client_id'       , __( 'client_id', self::TEXT_DOMAIN ), array( $this, 'add_settings_field_client_id' ), $key, $section );
		add_settings_field( 'client_secret'   , __( 'client_secret', self::TEXT_DOMAIN ) , array( $this, 'add_settings_field_client_secret' ), $key, $section );
		add_settings_field( 'callback_url'         , __( 'コールバックURL', self::TEXT_DOMAIN ), array( $this, 'add_settings_field_callback_url' ), $key, $section );
		add_settings_field( 'use_default_css' , 'プラグインCSSを使用する', array( $this, 'add_settings_field_use_default_css' ), $key, $section );
	}

	public function add_settings_section() {
		if ( 'true' === filter_input( INPUT_GET, 'settings-updated' ) ) {
			?>
			<div class="updated">
				<p>設定を保存しました。</p>
			</div>
			<?php
		}
	}	

	public function register_setting( $input ) {
		if ( ! isset( $input['client_id'] ) || trim( $input['client_id'] ) == '' )
			$input['client_id'] = '';
		if ( ! isset( $input['client_secret'] ) || trim( $input['client_secret'] ) == '' )
			$input['client_secret'] = '';
		if ( ! isset( $input['callback_url'] ) || trim( $input['callback_url'] ) == '' )
			$input['callback_url'] = '';
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

	public function add_settings_field_callback_url() {
	?>
	<input type="text" readonly id="callback_url" name="<?php echo self::OPTIONS_KEY ?>[callback_url]" class="regular-text" value="<?php echo esc_url( home_url( '/bil/auth' ) ); ?>" />
	<?php
	}

	public function add_settings_field_use_default_css() {
		?>
		<input type="checkbox" id="use_default_css" name="<?php echo self::OPTIONS_KEY ?>[use_default_css]" value="1" <?php checked( $this->option( 'use_default_css' ), 1 ); ?>/>
		<?php
	}
	
	public function admin_menu() {
		//add_menu_page( 'BASE Item List', 'BASE Item List' , 'manage_options', 'base_item_list', array( $this, 'add_options_page' ), 'dashicons-cart' );
		add_submenu_page( 'base_item_list', 'API設定(BETA)', 'API設定(BETA)', 'manage_options', 'base_item_list_v2', array( $this, 'add_options_page' ) );		
	}

	public function add_options_page() {
		?>
		<div class="wrap">

			<div class="error">
				<p>開発中の機能です。公開サイトでは使用しないでください。</p>
			</div>
	
			<h2>BASE商品リスト API設定(BETA)</h2>
			<form method="POST" action="options.php">
				<?php do_settings_sections( self::OPTIONS_KEY ); ?>
				<?php settings_fields( self::OPTIONS_KEY . '_group' ); ?>			
				<?php submit_button(); ?>
			</form>
			<hr />

			<?php if ( $this->saved_options() ) : ?>
				<h2>API認証</h2>
				<a target="_blank" class="button button-primary" href="<?php echo esc_url( home_url( '/bil/auth?force=1' ) ) ?>">認証する</a>
				<h3>トークン</h3>
				<table class="widefat fixed" cellspacing="0">
				<thead>
					<tr>
						<th>access token</th>
						<th>refresh token</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php echo esc_html( get_transient( Base_Item_List_Auth::ACCESS_TOKEN_TRANSIENT_KEY ) ); ?></td>
						<td><?php echo esc_html( get_option( Base_Item_List_Auth::REFRESH_TOKEN_OPTION_KEY ) ); ?></td>
					</tr>
				</tbody>
				</table>
				<hr />
			<?php endif; ?>

			<h2>ショートコード</h2>
			<code>[BASE_ITEM_V2]</code>
			<hr />
			<h2>パラメータ一覧</h2>
			<table class="widefat fixed" cellspacing="0">
			<thead>
				<tr>
					<th>パラメータ名</th>
					<th>機能</th>
					<th>初期値</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>q</td>
					<td>検索キーワード</td>
					<td>なし</td>
				</tr>
				<tr>
					<td>order</td>
					<td>並び替え項目。list_order、modifiedのいずれか</td>
					<td>キーワードマッチ度順</td>
				</tr>
				<tr>
					<td>sort</td>
					<td>並び順。asc か desc のいずれか</td>
					<td>desc</td>
				</tr>
				<tr>
					<td>limit</td>
					<td>表示する商品数。 (MAX: 100)</td>
					<td>10</td>
				</tr>
				<tr>
					<td>cache</td>
					<td>APIの結果をキャッシュする時間（秒）です。</td>
					<td>60</td>
				</tr>
				<tr>
					<td>name</td>
					<td>複数エリアに設置する場合に指定します。この名前をキーにキャッシュが作成されます。</td>
					<td>base_item_list_v2</td>
				</tr>
			</tbody>
		</table>
		<hr />
		<h2>パラメータ例</h2>
	
		<p>1.「Tシャツ」の検索結果を4件表示する</p>
		<code>[BASE_ITEM_V2 q="Tシャツ" count="4"]</code>
	
		<p>2.「Tシャツ」の検索結果をサイドバーに1件表示する</p>
		<code>[BASE_ITEM_V2 q="Tシャツ" count="1" name="side"]</code>
	
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

	public function saved_options() {
		$options = get_option( self::OPTIONS_KEY, $this->options_default );
		if ( ! is_array( $options ) || empty( $options ) ) {
			return false;
		}
		foreach ( array_keys( $this->options_default ) as $key ) {
			if ( ! array_key_exists( $key, $options ) ) {
				return false;
			}
			if ( empty( $options[ $key ] ) ) {
				return false;
			}
		}
		return true;
	}
}