import { Card, CardBody, CardHeader } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function HelpPanel( { homeUrl, screenshotUrl } ) {
	return (
		<Card>
			<CardHeader>
				<h2 style={ { margin: 0 } }>
					{ __( '設定ヘルプ', 'base-item-list' ) }
				</h2>
			</CardHeader>
			<CardBody>
				<div style={ { display: 'flex', gap: 24, flexWrap: 'wrap' } }>
					<div style={ { flex: '1 1 55%', minWidth: 280 } }>
						<ol>
							<li>
								<a
									target="_blank"
									rel="noopener noreferrer"
									href="https://developers.thebase.in/"
								>
									BASE Developers
								</a>
								{ __(
									'にアカウント登録し、ログインします。',
									'base-item-list'
								) }
							</li>
							<li>
								<a
									target="_blank"
									rel="noopener noreferrer"
									href="https://developers.thebase.in/apps"
								>
									{ __(
										'アプリケーション',
										'base-item-list'
									) }
								</a>
								{ __( 'ページから', 'base-item-list' ) }
								<a
									target="_blank"
									rel="noopener noreferrer"
									href="https://developers.thebase.in/apply"
								>
									{ __( '新規作成', 'base-item-list' ) }
								</a>
								{ __( 'を開きます。', 'base-item-list' ) }
							</li>
							<li>
								{ __(
									'次のように入力し「申請する」をクリックします。',
									'base-item-list'
								) }
								<ul>
									<li>
										{ __(
											'アプリ名: 「WordPress」など、わかりやすい内容を入力します。',
											'base-item-list'
										) }
									</li>
									<li>
										{ __(
											'アプリの説明: 当プラグインを使用する旨などを入力します。',
											'base-item-list'
										) }
									</li>
									<li>
										{ __(
											'アプリ URL: WordPress の URL を入力します。',
											'base-item-list'
										) }{ ' ' }
										<code>{ homeUrl }</code>
									</li>
									<li>
										{ __(
											'コールバック URL: このページの設定欄に表示されているコールバック URL をコピー＆ペーストします。',
											'base-item-list'
										) }
									</li>
									<li>
										<strong>
											{ __(
												'利用制限: 「商品情報を見る」を必ずチェック',
												'base-item-list'
											) }
										</strong>
										{ __(
											'してください。',
											'base-item-list'
										) }
									</li>
								</ul>
							</li>
							<li>
								{ __(
									'申請後、BASE より承認のメールが届きます（数日かかることがあります）。',
									'base-item-list'
								) }
							</li>
							<li>
								{ __(
									'アプリケーションページに記載されている client_id・client_secret をこのページに入力して保存します。',
									'base-item-list'
								) }
							</li>
							<li>
								{ __(
									'設定を保存後に表示される「認証する」をクリックして認証します。',
									'base-item-list'
								) }
							</li>
							<li>
								{ __(
									'認証が正しく完了すると、ショートコードが使用可能になります。',
									'base-item-list'
								) }
							</li>
						</ol>
						<p>
							<strong>
								{ __(
									'※認証でエラーになる場合は、3 の設定を見直してください。',
									'base-item-list'
								) }
							</strong>
						</p>
					</div>
					{ screenshotUrl && (
						<div style={ { flex: '1 1 35%', minWidth: 240 } }>
							<p>
								{ __(
									'※クリックで拡大します',
									'base-item-list'
								) }
							</p>
							<a
								target="_blank"
								rel="noopener noreferrer"
								href={ screenshotUrl }
							>
								<img
									style={ { width: '100%', height: 'auto' } }
									src={ screenshotUrl }
									alt={ __(
										'BASE Developers アプリ申請画面のスクリーンショット',
										'base-item-list'
									) }
								/>
							</a>
						</div>
					) }
				</div>
			</CardBody>
		</Card>
	);
}
