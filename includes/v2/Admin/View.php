<?php

namespace mt8\BaseItemList\Admin;

use mt8\BaseItemList\Core;
use mt8\BaseItemList\Auth;

class View {

	public static function register_setting_fields() {

		$key     = Admin::OPTIONS_KEY;
		$group   = $key . '_group';
		$section = $key . '_section'; 
		register_setting( $group, $key, array( View::class, 'filter_setting' ) );

		add_settings_section(
			$section,
			'settings',
			array( View::class, 'settings_section' ), $key
		);

		add_settings_field(
			'client_id'
			,'client_id',
			array( View::class, 'field_client_id' ), 
			$key,
			$section
		);

		add_settings_field(
			'client_secret'
			,'client_secret',
			array( View::class, 'field_client_secret' ), 
			$key,
			$section
		);

		add_settings_field(
			'callback_url'
			,'コールバックURL',
			array( View::class, 'field_callback_url' ), 
			$key,
			$section
		);

		add_settings_field(
			'use_default_css'
			,'プラグインCSSを使用する',
			array( View::class, 'field_use_default_css' ), 
			$key,
			$section
		);
	}

	public static function filter_setting( $input ) {
		foreach ( array_keys( Admin::OPTIONS_DEFUALT ) as $option_key ) {
			if ( ! isset( $input[ $option_key ] ) || empty( trim( $input[ $option_key ] ) ) ) {
				$input[ $option_key ] = '';
			}
		}
		return $input;
	}	

	public static function option_page() {
		$admin = new Admin();
	?>
	<div class="wrap">

		<div class="error">
			<p>開発中の機能です。公開サイトでは使用しないでください。</p>
		</div>

		<h2>BASE商品リスト API設定(BETA)</h2>
		<form method="POST" action="options.php">
			<?php do_settings_sections( Admin::OPTIONS_KEY ); ?>
			<?php settings_fields( Admin::OPTIONS_KEY . '_group' ); ?>			
			<?php submit_button(); ?>
		</form>
		<hr />

		<?php if ( $admin->saved_options() ) :
			$callbak_url = add_query_arg( array( 'force' => '1' ), Admin::option( 'callback_url' ) );
		?>
		<h2>API認証</h2>
		<a class="button button-primary" href="<?php echo esc_url( $callbak_url ) ?>">認証する</a>
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
				<td><?php echo esc_html( get_transient( Auth::ACCESS_TOKEN_TRANSIENT_KEY ) ); ?></td>
				<td><?php echo esc_html( get_option( Auth::REFRESH_TOKEN_OPTION_KEY ) ); ?></td>
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
		<hr />

		<?php $last_error = get_option( Core::LAST_ERROR_OPTION_KEY ); if ( ! empty( $last_error ) ) : ?>
		<h2>エラーログ</h2>
		<code><?php echo esc_html( $last_error ) ?></code>
		<?php endif ; ?>
	</div>		
	<?php
	}

	public static function settings_section() {
		if ( 'true' === filter_input( INPUT_GET, 'settings-updated' ) ) { ?>
		<div class="updated"><p>設定を保存しました。</p></div>
		<?php
		}
		if ( 'authorized' === filter_input( INPUT_GET, 'status' ) ) { ?>
		<div class="updated"><p>API認証が完了しました。</p></div>
		<?php
		}
	}

	public static function field_client_id() { ?>
		<input 
			type="text" 
			id="client_id" 
			name="<?php echo Admin::OPTIONS_KEY ?>[client_id]" 
			class="regular-text" 
			value="<?php echo esc_attr( Admin::option( 'client_id' ) ) ?>" 
		/>
	<?php
	}
	
	public static function field_client_secret() { ?>
		<input 
			type="text" 
			id="client_secret" 
			name="<?php echo Admin::OPTIONS_KEY ?>[client_secret]" 
			class="regular-text" 
			value="<?php echo esc_attr( Admin::option( 'client_secret' ) ) ?>" 
		/>
	<?php
	}
	
	public static function field_callback_url() {

		$auth_url = add_query_arg(
			array(
				'page' => 'base_item_list_setting',
				'mode' => 'auth',
			),
			admin_url( '/admin.php' )
		);

		?>
		<input 
			type="text" 
			readonly id="callback_url" 
			name="<?php echo Admin::OPTIONS_KEY ?>[callback_url]" 
			class="regular-text" 
			value="<?php echo esc_url( $auth_url ); ?>" 
		/>
	<?php
	}
	
	public static function field_use_default_css() { ?>
		<input 
			type="checkbox" 
			id="use_default_css" 
			name="<?php echo Admin::OPTIONS_KEY ?>[use_default_css]" 
			value="1" 
			<?php checked( Admin::option( 'use_default_css' ), 1 ); ?>
		/>
	<?php
	}

}
