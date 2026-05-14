/**
 * Thin wrapper around @wordpress/api-fetch for the four REST endpoints exposed
 * by mt8\BaseItemList\Rest\SettingsController.
 *
 * apiFetch automatically attaches the wp-rest nonce from wpApiSettings, so the
 * admin page only needs to call these helpers.
 */

import apiFetch from '@wordpress/api-fetch';

const NAMESPACE = '/base-item-list/v1';

export function getSettings() {
	return apiFetch( { path: `${ NAMESPACE }/settings`, method: 'GET' } );
}

export function updateSettings( settings ) {
	return apiFetch( {
		path: `${ NAMESPACE }/settings`,
		method: 'POST',
		data: settings,
	} );
}

export function getAuthStatus() {
	return apiFetch( { path: `${ NAMESPACE }/auth-status`, method: 'GET' } );
}

export function startAuth() {
	return apiFetch( {
		path: `${ NAMESPACE }/auth/start`,
		method: 'POST',
	} );
}
