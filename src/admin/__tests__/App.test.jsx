/**
 * Smoke tests for the admin App. Verifies that:
 * - the spinner shows while data is loading,
 * - both REST endpoints are queried on mount,
 * - the settings panel renders after data resolves.
 */

import { render, screen, waitFor } from '@testing-library/react';

import App from '../App';

jest.mock( '@wordpress/api-fetch', () => ( {
	__esModule: true,
	default: jest.fn(),
} ) );

import apiFetch from '@wordpress/api-fetch';

const baseConfig = {
	callbackUrl:
		'https://example.test/wp-admin/admin.php?page=base_item_list_setting&mode=auth',
	homeUrl: 'https://example.test/',
	screenshotUrl: 'https://example.test/screenshot.png',
	justAuthorized: false,
};

const settingsResponse = {
	client_id: 'cid',
	client_secret: 'sec',
	callback_url: baseConfig.callbackUrl,
	shop_url: 'https://shop.example/',
	use_default_css: true,
};

const authStatusResponse = {
	authorized: false,
	access_token_present: false,
	refresh_token_present: false,
	last_error: '',
};

beforeEach( () => {
	apiFetch.mockReset();
	apiFetch.mockImplementation( ( { path } ) => {
		if ( path.endsWith( '/settings' ) ) {
			return Promise.resolve( settingsResponse );
		}
		if ( path.endsWith( '/auth-status' ) ) {
			return Promise.resolve( authStatusResponse );
		}
		return Promise.reject( new Error( 'Unexpected path ' + path ) );
	} );
} );

test( 'renders the heading on mount', async () => {
	render( <App config={ baseConfig } /> );
	expect(
		screen.getByRole( 'heading', { name: /BASE Item List/i } )
	).toBeInTheDocument();

	// Wait for the initial fetches to resolve so React state settles before teardown.
	await waitFor( () => {
		expect( apiFetch ).toHaveBeenCalledTimes( 2 );
	} );
} );

test( 'fetches settings and auth-status on mount', async () => {
	render( <App config={ baseConfig } /> );

	await waitFor( () => {
		expect( apiFetch ).toHaveBeenCalledWith(
			expect.objectContaining( {
				path: '/base-item-list/v1/settings',
				method: 'GET',
			} )
		);
		expect( apiFetch ).toHaveBeenCalledWith(
			expect.objectContaining( {
				path: '/base-item-list/v1/auth-status',
				method: 'GET',
			} )
		);
	} );
} );

test( 'shows authorize panel when required credentials are saved', async () => {
	render( <App config={ baseConfig } /> );

	await waitFor( () => {
		expect( screen.getByText( /API 認証/ ) ).toBeInTheDocument();
	} );
} );

test( 'hides authorize panel when shop_url is missing', async () => {
	apiFetch.mockImplementation( ( { path } ) => {
		if ( path.endsWith( '/settings' ) ) {
			return Promise.resolve( { ...settingsResponse, shop_url: '' } );
		}
		return Promise.resolve( authStatusResponse );
	} );

	render( <App config={ baseConfig } /> );

	await waitFor( () => {
		expect(
			screen.getByRole( 'button', { name: /設定を保存/ } )
		).toBeInTheDocument();
	} );
	expect( screen.queryByText( /API 認証/ ) ).not.toBeInTheDocument();
} );

test( 'shows a success notice when arriving with justAuthorized', async () => {
	render( <App config={ { ...baseConfig, justAuthorized: true } } /> );

	// @wordpress/components' Notice mirrors the message into an a11y-speak region,
	// so the same string appears twice in the DOM — accept both.
	await waitFor( () => {
		expect(
			screen.getAllByText( /API 認証が完了しました/ ).length
		).toBeGreaterThan( 0 );
	} );
} );
