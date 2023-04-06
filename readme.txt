=== BASE Item List ===
Contributors: mt8.biz, shimakyohsuke, masakawai
Donate link: https://mt8.biz
Tags: BASE,ec,shortcode
Requires at least: 5.9
Tested up to: 6.2
Stable tag: 2.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display BASE(https://thebase.in/) Item List by shortcode [BASE_ITEM]

== Description ==

BASE商品情報をリスト表示するショートコード:[BASE_ITEM] を使用可能にします。

= ショートコードパラメータ =

* q: 検索キーワード
* order: 並び替え項目。list_order、modifiedのいずれか（未指定時: キーワードマッチ度順）
* sort: 並び順。asc か desc のいずれか（未指定時: desc）
* limit: 表示する商品数。 (MAX: 100)（未指定時：10）
* cache: APIの結果をキャッシュする時間（秒）です。（未指定時：60）
* name: 複数エリアに設置する場合に指定します。この名前をキーにキャッシュが作成されます。（未指定時：base_item_list）

= 出力テンプレート =

デフォルトのテンプレートはプラグインディレクトリ内にある「/template/base_items.php」です。

有効化しているテーマフォルダ内にこのファイルをコピーすることでオリジナルの表示をさせることができます。

= 設定 =

管理画面 -> BASE Item Listより、BASE APIのアプリ情報を設定して下さい。

※ BASE APIは事前に申請が必要です。https://developers.thebase.in/

== Installation ==

1. Upload `base-item-list.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

== Frequently Asked Questions ==

Not yet.

== Changelog ==

= 1.0.0 =
* 初版リリース

= 1.0.1 =
* readme.txt修正

= 1.0.2 =
* バグ修正

= 1.0.3 =
* 4.8でのテスト
* shop_idについて記載

= 1.0.4 =
* 5.2でのテスト
* バグ修正
* 管理画面の説明を変更

= 1.1.1 =
* デフォルトCSS機能の実装

= 1.1.2 =
* 5.6での動作確認

= 1.1.3 =
* sortパラメータ追加
* API呼び出し失敗時にエラーログ出力する
* 5.7での動作確認

= 1.1.4 =
* list_orderでのソート追加

= 1.1.5 =
* GitHub Actions でのCD/CI 対応

= 1.1.6 =
* 未使用ファイルを削除

= 1.1.7 =
* 未使用ファイルを削除

= 1.1.8 =
* コード整理

= 1.2 =
* API認証のベータ機能公開

= 1.2.2 =
* ベター機能のアップデート

= 2.0.0 =
* 検索APIの廃止

= 2.0.1 =
* BASE認証時のセキュリティチェック

= 2.0.2 =
* Tested up to 6.2

= 2.0.3 =
* ショートコードパラメータにfields追加：https://docs.thebase.in/docs/api/items/search Props to masakawai