module.exports = {
	root: true,
	extends: [ require.resolve( '@wordpress/scripts/config/.eslintrc.js' ) ],
	env: {
		browser: true,
		jest: true,
	},
};
