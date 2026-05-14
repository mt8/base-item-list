import {
	Button,
	Flex,
	FlexItem,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const REQUIRED_FIELDS = [ 'client_id', 'client_secret', 'shop_url' ];

export default function SettingsPanel( {
	initialSettings,
	callbackUrl,
	onSave,
	saving,
} ) {
	const [ values, setValues ] = useState( initialSettings );

	useEffect( () => {
		setValues( initialSettings );
	}, [ initialSettings ] );

	const updateField = ( key ) => ( value ) =>
		setValues( ( prev ) => ( { ...prev, [ key ]: value } ) );

	const handleSubmit = ( event ) => {
		event.preventDefault();
		onSave( { ...values, callback_url: callbackUrl } );
	};

	const handleCopy = () => {
		if ( window.navigator?.clipboard ) {
			window.navigator.clipboard.writeText( callbackUrl );
		}
	};

	return (
		<form onSubmit={ handleSubmit }>
			<TextControl
				label={ __( 'client_id', 'base-item-list' ) }
				value={ values.client_id }
				onChange={ updateField( 'client_id' ) }
				autoComplete="off"
				__nextHasNoMarginBottom
				__next40pxDefaultSize
			/>
			<TextControl
				label={ __( 'client_secret', 'base-item-list' ) }
				value={ values.client_secret }
				onChange={ updateField( 'client_secret' ) }
				type="password"
				autoComplete="off"
				__nextHasNoMarginBottom
				__next40pxDefaultSize
			/>
			<div style={ { marginTop: 16, marginBottom: 16 } }>
				<div style={ { fontWeight: 600 } }>
					{ __( 'コールバック URL', 'base-item-list' ) }
				</div>
				<div style={ { color: '#757575', fontSize: 12 } }>
					{ __(
						'BASE Developers のアプリ設定にこの URL を貼り付けてください。',
						'base-item-list'
					) }
				</div>
				<Flex align="center" gap={ 2 } style={ { marginTop: 4 } }>
					<FlexItem>
						<code>{ callbackUrl }</code>
					</FlexItem>
					<FlexItem>
						<Button
							variant="secondary"
							onClick={ handleCopy }
							size="small"
						>
							{ __( 'コピー', 'base-item-list' ) }
						</Button>
					</FlexItem>
				</Flex>
			</div>
			<TextControl
				label={ __( 'ショップ URL', 'base-item-list' ) }
				value={ values.shop_url }
				onChange={ updateField( 'shop_url' ) }
				type="url"
				__nextHasNoMarginBottom
				__next40pxDefaultSize
			/>
			<div style={ { marginTop: 16 } }>
				<ToggleControl
					label={ __(
						'プラグイン同梱の CSS を使用する',
						'base-item-list'
					) }
					checked={ !! values.use_default_css }
					onChange={ updateField( 'use_default_css' ) }
					__nextHasNoMarginBottom
				/>
			</div>
			<div style={ { marginTop: 16 } }>
				<Button
					type="submit"
					variant="primary"
					isBusy={ saving }
					disabled={ saving }
				>
					{ __( '設定を保存', 'base-item-list' ) }
				</Button>
			</div>
		</form>
	);
}

export function hasRequiredCredentials( settings ) {
	if ( ! settings ) {
		return false;
	}
	return REQUIRED_FIELDS.every( ( key ) => {
		const value = settings[ key ];
		return typeof value === 'string' && value.trim() !== '';
	} );
}
