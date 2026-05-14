/**
 * BASE Item List — admin entry point.
 *
 * Phase 1 stub: confirms the build pipeline is working. The actual React-based
 * settings UI replaces this in Phase 3 (issue #33's follow-up).
 */

import { createRoot } from '@wordpress/element';

function AdminApp() {
	return (
		<div className="bil-admin-app">
			<p>BASE Item List admin (React build pipeline ready).</p>
		</div>
	);
}

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'bil-admin-root' );
	if ( ! container ) {
		return;
	}
	createRoot( container ).render( <AdminApp /> );
} );
