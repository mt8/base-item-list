/**
 * Webpack config for the BASE Item List plugin.
 *
 * Extends @wordpress/scripts' default config and declares a single admin entry
 * that compiles to `build/admin/index.js` + `build/admin/index.asset.php`.
 */

const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		'admin/index': path.resolve( process.cwd(), 'src/admin/index.jsx' ),
	},
};
