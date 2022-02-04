<?php

class Base_Item_List_Admin_V1 {
	
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
		add_settings_field( 'client_id'       , __( 'client_id (検索API用)', self::TEXT_DOMAIN ), array( &$this, 'add_settings_field_client_id' ), $key, $section );
		add_settings_field( 'client_secret'   , __( 'client_secret (検索API用)', self::TEXT_DOMAIN ) , array( &$this, 'add_settings_field_client_secret' ), $key, $section );
		add_settings_field( 'shop_id'         , __( 'shop_id', self::TEXT_DOMAIN ), array( &$this, 'add_settings_field_shop_id' ), $key, $section );
		add_settings_field( 'use_default_css' , 'プラグインCSSを使用する', array( &$this, 'add_settings_field_use_default_css' ), $key, $section );
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
	<br />
	<h3>※ショップIDについて</h3>
	<table class="widefat fixed" cellspacing="0">
		<thead>
			<tr>
				<th>BASE初期ドメイン</th>
				<th>ショップIDに設定する値</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>xxxxxx.thebase.inの場合</td>
				<td>xxxxxx</td>
			</tr>
			<tr>
				<td>xxxxxx.theshop.jpなど、<strong>thebase.in以外</strong>の場合</td>
				<td>xxxxxx-theshop-jp（ドットをハイフン「-」で繋げる）</td>
			</tr>
		</tbody>
	</table>
	<p><a href="https://admin.thebase.in/shop_admin/shop_settings" target="_blank">BASE ショップ情報</a>の、「アプリ掲載」という項目に<strong>shop_id=xxxxxxxxxx</strong>という形式でも記載されているので確認してみてください。</p>

	<?php
	}

	public function add_settings_field_use_default_css() {
	?>
	<input type="checkbox" id="use_default_css" name="<?php echo self::OPTIONS_KEY ?>[use_default_css]" value="1" <?php checked( $this->option( 'use_default_css' ), 1 ); ?>/>
	<?php
	}

	public function admin_menu() {
		add_menu_page( 'BASE Item List', 'BASE Item List' , 'manage_options', 'base_item_list', array( $this, 'add_options_page' ), 'dashicons-cart' );
		add_submenu_page( 'base_item_list', '検索API設定（廃止予定）', '検索API（廃止予定）', 'manage_options', 'settings_v1', array( $this, 'add_options_page' ) );		
	}

	public function add_options_page() {
	?>
	<div class="wrap">

		<div class="error">
			<p>本プラグインで使用している BASE検索APIが<string><a href="https://docs.thebase.in/docs/api/search/" target="_blank">2022年2月21日で新規受付を終了することがアナウンスされています。</a></strong></p>
		</div>

		<h2>BASE商品リスト 設定</h2>
		<form method="POST" action="options.php">
			<?php do_settings_sections( self::OPTIONS_KEY ); ?>
			<?php settings_fields( self::OPTIONS_KEY . '_group' ); ?>			
			<?php submit_button(); ?>
		</form>
		<hr />
		<h2>ショートコード</h2>
		<code>[BASE_ITEM]</code>
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
				<td>shop_id</td>
				<td>別のショップの商品リストを表示する際に使用します</td>
				<td>設定項目で入力した値</td>
			</tr>
			<tr>
				<td>count</td>
				<td>表示する商品数。最大で50件です。</td>
				<td>10</td>
			</tr>
			<tr>
				<td>sort</td>
				<td>並び順。item_id、price、stock、order_count、modifiedのascまたはdesc (例: order_count desc,item_id asc) (デフォルト: BASEのおすすめ順)</td>
				<td></td>
			</tr>
			<tr>
				<td>cache</td>
				<td>APIの結果をキャッシュする時間（秒）です。</td>
				<td>60</td>
			</tr>
			<tr>
				<td>name</td>
				<td>複数エリアに設置する場合に指定します。この名前をキーにキャッシュが作成されます。</td>
				<td>base_item_list</td>
			</tr>
		</tbody>
	</table>
	<hr />
	<h2>パラメータ例</h2>

	<p>1.「Tシャツ」の検索結果を4件表示する</p>
	<code>[BASE_ITEM q="Tシャツ" count="4"]</code>

	<p>2.「Tシャツ」の検索結果をサイドバーに1件表示する</p>
	<code>[BASE_ITEM q="Tシャツ" count="1" name="side"]</code>

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