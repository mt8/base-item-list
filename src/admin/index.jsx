/**
 * BASE Item List — admin entry point.
 *
 * Mounts the React-based settings UI into the placeholder div rendered by
 * mt8\BaseItemList\Admin\View::option_page. PHP injects runtime config via
 * window.basItemListAdmin (see Admin::admin_enqueue_scripts).
 */

import { createRoot } from '@wordpress/element';

import App from './App';

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'bil-admin-root' );
	if ( ! container ) {
		return;
	}

	const config = window.basItemListAdmin ?? {
		callbackUrl: '',
		homeUrl: '',
		screenshotUrl: '',
		justAuthorized: false,
	};

	createRoot( container ).render( <App config={ config } /> );
} );
