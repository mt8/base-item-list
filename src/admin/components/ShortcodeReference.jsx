import { Card, CardBody, CardHeader } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const PARAMETERS = [
	{
		name: 'q',
		desc: __( '検索キーワード', 'base-item-list' ),
		default: __( 'なし', 'base-item-list' ),
	},
	{
		name: 'order',
		desc: __(
			'並び替え項目。list_order、modified のいずれか',
			'base-item-list'
		),
		default: __( 'キーワードマッチ度順', 'base-item-list' ),
	},
	{
		name: 'sort',
		desc: __( '並び順。asc か desc のいずれか', 'base-item-list' ),
		default: 'desc',
	},
	{
		name: 'limit',
		desc: __( '表示する商品数（MAX: 100）', 'base-item-list' ),
		default: '10',
	},
	{
		name: 'cache',
		desc: __( 'API の結果をキャッシュする時間（秒）', 'base-item-list' ),
		default: '60',
	},
	{
		name: 'name',
		desc: __(
			'複数エリアに設置する場合に指定。この名前をキーにキャッシュが作成されます。',
			'base-item-list'
		),
		default: 'cache',
	},
];

export default function ShortcodeReference() {
	return (
		<Card>
			<CardHeader>
				<h2 style={ { margin: 0 } }>
					{ __( 'ショートコード', 'base-item-list' ) }
				</h2>
			</CardHeader>
			<CardBody>
				<p>
					<code>[BASE_ITEM]</code>
				</p>

				<h3 style={ { marginTop: 24 } }>
					{ __( 'パラメータ一覧', 'base-item-list' ) }
				</h3>
				<table className="widefat fixed" cellSpacing="0">
					<thead>
						<tr>
							<th>{ __( 'パラメータ名', 'base-item-list' ) }</th>
							<th>{ __( '機能', 'base-item-list' ) }</th>
							<th>{ __( '初期値', 'base-item-list' ) }</th>
						</tr>
					</thead>
					<tbody>
						{ PARAMETERS.map( ( param ) => (
							<tr key={ param.name }>
								<td>{ param.name }</td>
								<td>{ param.desc }</td>
								<td>{ param.default }</td>
							</tr>
						) ) }
					</tbody>
				</table>

				<h3 style={ { marginTop: 24 } }>
					{ __( 'パラメータ例', 'base-item-list' ) }
				</h3>
				<p>
					{ __(
						'1.「Tシャツ」の検索結果を 4 件表示する',
						'base-item-list'
					) }
				</p>
				<p>
					<code>
						[BASE_ITEM q=&quot;Tシャツ&quot; limit=&quot;4&quot;]
					</code>
				</p>
				<p>
					{ __(
						'2.「Tシャツ」の検索結果をサイドバーに 1 件表示する',
						'base-item-list'
					) }
				</p>
				<p>
					<code>
						[BASE_ITEM q=&quot;Tシャツ&quot; limit=&quot;1&quot;
						name=&quot;side&quot;]
					</code>
				</p>
			</CardBody>
		</Card>
	);
}
