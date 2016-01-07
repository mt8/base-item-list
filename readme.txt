=== BASE Item List ===
Contributors: mt8.biz
Donate link: http://mt8.biz
Tags: BASE,ec,shortcode
Requires at least: 4.4
Tested up to: 4.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display BASE(https://thebase.in/) Item List by shortcode [BASE_ITEM]

== Description ==

BASE商品情報をリスト表示するショートコード:[BASE_ITEM] を使用可能にします。

= ショートコードパラメータ =

* q: 検索キーワード
* shop_id: ショップID(設定画面で固定化することもできます)
* count: 表示件数(デフォルト:10[件])
* cache: 結果キャッシュ時間(デフォルト:60[秒])
* name: 結果キャッシュ名(デフォルト:base_item_list)※複数箇所にショートコードを設置する場合に使用

= 出力テンプレート =

デフォルトのテンプレートはプラグインディレクトリ内にある「base_items.php」です。

有効化しているテーマフォルダ内にこのファイルをコピーすることでオリジナルの表示をさせることができます。

= 設定 = 

管理画面 -> BASE商品リストより、BASE APIのアプリ情報を設定して下さい。

※ BASE APIは事前に申請が必要です。https://developers.thebase.in/

== Installation ==

1. Upload `base-item-list.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. Latest information

== Frequently Asked Questions ==

Not yet.

== Upgrade Notice ==

= 1.0.0 =
* First release

= 1.0.1 =
* Fix readme.txt

= 1.0.2 =
* Fix bug

== Changelog ==

= 1.0.0 =
* 初版リリース

= 1.0.1 =
* readme.txt修正

= 1.0.2 =
* バグ修正