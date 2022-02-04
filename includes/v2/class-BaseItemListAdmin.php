<?php

class Base_Item_List_Admin_V2 {

	const TEXT_DOMAIN = 'base-item-list';
	const OPTIONS_KEY = 'base-item-list-v2';

	public function admin_menu() {
		//add_menu_page( 'BASE Item List', 'BASE Item List' , 'manage_options', 'base_item_list', array( $this, 'add_options_page' ), 'dashicons-cart' );
		add_submenu_page( 'base_item_list', 'API設定(BETA)', 'API設定(BETA)', 'manage_options', 'settings_v2', array( $this, 'add_options_page' ) );		
	}

	public function add_options_page() {
		?>
		<div class="wrap">
	
			<h2>BASE商品リスト API設定(BETA)</h2>
			
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