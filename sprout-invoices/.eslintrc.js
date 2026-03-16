module.exports = {
	env: {
		browser: true,
		es2021: true,
		commonjs: true,
		jquery: true
	},
	extends: [
		'eslint:recommended',
		'plugin:@wordpress/eslint-plugin/recommended'
	],
	overrides: [
	],
	parserOptions: {
		ecmaVersion: 'latest',
		sourceType: 'module'
	},
	plugins: [
		'vue'
	],
	rules: {
		'space-in-parens': [ 'error', 'always' ],
		'wrap-iife': [ 2, 'any' ],
		// Allow async-await
		'generator-star-spacing': 0,
		'padded-blocks': [ 'error', { blocks: 'always' } ],
		'comma-dangle': [ 'error', 'never' ]
	}
};
