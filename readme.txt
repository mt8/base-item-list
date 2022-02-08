=== BASE Item List ===
Contributors: mt8.biz, shimakyohsuke
Donate link: https://mt8.biz
Tags: BASE,ec,shortcode
Requires at least: 4.4
Tested up to: 5.9
Stable tag: 1.2.4
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
* sort: 並び順。item_id、price、stock、order_count、modifiedのascまたはdesc (例: order_count desc,item_id asc) (デフォルト: BASEのおすすめ順)

**※shop_idについて**

shop_idの設定ミスに対するお問い合わせが増えています。
以下を参考にして下さい。

ドメインがthebase.inの場合はサブドメインの部分
    例）mt8.thebase.in -> mt8

ドメインがthebase.in以外の場合はドットをハイフンに変えたもの
    例）mt8.theshop.jp -> mt8-theshop-jp

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

= 1.0.3 =
* Tested to: 4.8
* Update Readme

= 1.0.4 =
* Tested to: 5.2
* Fix bug
* Update setting page

= 1.1.1 =
* add default css

= 1.1.2 =
* Tested to: 5.6

= 1.1.3 =
* Added sort parameter
* Output error log when API call fails
* Tested to: 5.7

= 1.1.4 =
* Added list_order

= 1.1.5 =
* CD/CI by GitHub Actions

= 1.1.6 =
* remove unuse files

= 1.1.7 =
* remove unuse files again

= 1.1.8 =
* code refactor

= 1.2 =
* Release BETA for new API

= 1.2.2 =
* Update BETA

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