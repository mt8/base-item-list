import { Notice, Panel, PanelBody, Spinner } from '@wordpress/components';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import * as api from './api';
import AuthPanel from './components/AuthPanel';
import ErrorLogPanel from './components/ErrorLogPanel';
import HelpPanel from './components/HelpPanel';
import SettingsPanel, {
	hasRequiredCredentials,
} from './components/SettingsPanel';
import ShortcodeReference from './components/ShortcodeReference';

export default function App( { config } ) {
	const [ settings, setSettings ] = useState( null );
	const [ authStatus, setAuthStatus ] = useState( null );
	const [ notice, setNotice ] = useState( null );
	const [ saving, setSaving ] = useState( false );
	const [ starting, setStarting ] = useState( false );

	useEffect( () => {
		let alive = true;
		Promise.all( [ api.getSettings(), api.getAuthStatus() ] )
			.then( ( [ s, a ] ) => {
				if ( ! alive ) {
					return;
				}
				setSettings( s );
				setAuthStatus( a );
			} )
			.catch( ( err ) => {
				if ( ! alive ) {
					return;
				}
				setNotice( {
					status: 'error',
					message: err.message ?? String( err ),
				} );
			} );

		if ( config.justAuthorized ) {
			setNotice( {
				status: 'success',
				message: __( 'API 認証が完了しました。', 'base-item-list' ),
			} );
		}

		return () => {
			alive = false;
		};
	}, [ config.justAuthorized ] );

	const handleSave = useCallback( async ( payload ) => {
		setSaving( true );
		setNotice( null );
		try {
			const saved = await api.updateSettings( payload );
			setSettings( saved );
			setNotice( {
				status: 'success',
				message: __( '設定を保存しました。', 'base-item-list' ),
			} );
		} catch ( err ) {
			setNotice( {
				status: 'error',
				message: err.message ?? String( err ),
			} );
		} finally {
			setSaving( false );
		}
	}, [] );

	const handleAuthStart = useCallback( async () => {
		setStarting( true );
		setNotice( null );
		try {
			const { redirect_url: redirectUrl } = await api.startAuth();
			window.location.href = redirectUrl;
		} catch ( err ) {
			setStarting( false );
			setNotice( {
				status: 'error',
				message: err.message ?? String( err ),
			} );
		}
	}, [] );

	if ( ! settings || ! authStatus ) {
		return (
			<div className="wrap">
				<h1>{ __( 'BASE Item List 設定', 'base-item-list' ) }</h1>
				<Spinner />
			</div>
		);
	}

	const showAuthPanel = hasRequiredCredentials( settings );

	return (
		<div className="wrap bil-admin">
			<h1>{ __( 'BASE Item List 設定', 'base-item-list' ) }</h1>

			{ notice && (
				<Notice
					status={ notice.status }
					onRemove={ () => setNotice( null ) }
				>
					{ notice.message }
				</Notice>
			) }

			<Panel className="bil-admin-panel" style={ { marginTop: 16 } }>
				<PanelBody title={ __( '設定', 'base-item-list' ) } initialOpen>
					<SettingsPanel
						initialSettings={ settings }
						callbackUrl={ config.callbackUrl }
						onSave={ handleSave }
						saving={ saving }
					/>
				</PanelBody>

				{ showAuthPanel && (
					<PanelBody
						title={ __( 'API 認証', 'base-item-list' ) }
						initialOpen
					>
						<AuthPanel
							authStatus={ authStatus }
							onStart={ handleAuthStart }
							starting={ starting }
						/>
					</PanelBody>
				) }

				<PanelBody
					title={ __( 'ショートコード', 'base-item-list' ) }
					initialOpen
				>
					<ShortcodeReference />
				</PanelBody>

				<PanelBody
					title={ __( '設定ヘルプ', 'base-item-list' ) }
					initialOpen={ false }
				>
					<HelpPanel
						homeUrl={ config.homeUrl }
						screenshotUrl={ config.screenshotUrl }
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'エラーログ', 'base-item-list' ) }
					initialOpen={ false }
				>
					<ErrorLogPanel
						lastError={ authStatus.last_error }
						lastErrorTime={ authStatus.last_error_time }
					/>
				</PanelBody>
			</Panel>
		</div>
	);
}
