// Jest config — extends @wordpress/scripts' default config to wire in
// @testing-library/jest-dom matchers via setupFilesAfterEnv.

const defaultConfig = require( '@wordpress/scripts/config/jest-unit.config' );

module.exports = {
	...defaultConfig,
	setupFilesAfterEnv: [
		...( defaultConfig.setupFilesAfterEnv || [] ),
		'<rootDir>/jest.setup.js',
	],
};
