const wordpress = require( '@wordpress/eslint-plugin' );
const pluginVue = require( 'eslint-plugin-vue' );
const globals = require( 'globals' );

module.exports = [
	...wordpress.configs.recommended,
	...pluginVue.configs[ 'flat/essential' ],
	{
		ignores: [
			'**/node_modules/**',
			'**/vendor/**',
			'**/bundles/**/vendor/**',
			'**/bundles/**/sdk/**',
			'**/bundles/**/lib/**',
			'**/resources/**/plugins/**',
			'**/resources/**/vue.js',
			'**/resources/**/vue.min.js',
		],
	},
	{
		languageOptions: {
			ecmaVersion: 'latest',
			sourceType: 'module',
			globals: {
				...globals.browser,
				...globals.jquery,
				...globals.commonjs,
				Vue: 'readonly',
			},
		},
		rules: {
			'space-in-parens': [ 'error', 'always' ],
			'wrap-iife': [ 2, 'any' ],
			// Allow async-await.
			'generator-star-spacing': 0,
			'padded-blocks': [ 'error', { blocks: 'always' } ],
			'comma-dangle': [ 'error', 'never' ],
		},
	},
];
