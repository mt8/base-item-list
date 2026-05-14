import { Button, Card, CardBody, CardHeader } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function AuthPanel( { authStatus, onStart, starting } ) {
	const isAuthorized = !! authStatus?.authorized;

	return (
		<Card>
			<CardHeader>
				<h2 style={ { margin: 0 } }>
					{ isAuthorized
						? __( 'API 認証（認証済）', 'base-item-list' )
						: __( 'API 認証（未認証）', 'base-item-list' ) }
				</h2>
			</CardHeader>
			<CardBody>
				<p>
					{ isAuthorized
						? __(
								'BASE API への認証が完了しています。商品情報の取得が可能です。',
								'base-item-list'
						  )
						: __(
								'BASE Developers でアプリを登録した後、認証を実行してください。',
								'base-item-list'
						  ) }
				</p>
				<Button
					variant="primary"
					onClick={ onStart }
					isBusy={ starting }
					disabled={ starting }
				>
					{ isAuthorized
						? __( '再認証する', 'base-item-list' )
						: __( '認証する', 'base-item-list' ) }
				</Button>

				<table
					className="widefat fixed"
					cellSpacing="0"
					style={ { marginTop: 16 } }
				>
					<thead>
						<tr>
							<th>{ __( 'access token', 'base-item-list' ) }</th>
							<th>{ __( 'refresh token', 'base-item-list' ) }</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								{ authStatus?.access_token_present
									? __( '取得済', 'base-item-list' )
									: '—' }
							</td>
							<td>
								{ authStatus?.refresh_token_present
									? __( '取得済', 'base-item-list' )
									: '—' }
							</td>
						</tr>
					</tbody>
				</table>
			</CardBody>
		</Card>
	);
}
