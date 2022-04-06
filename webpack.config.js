const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const path = require( 'path' );
const { get } = require( 'lodash' );

const NODE_ENV = process.env.NODE_ENV || 'development';

/**
 * WordPress dependencies
 */
// const CustomTemplatedPathPlugin = require( '@wordpress/custom-templated-path-webpack-plugin' );

const externals = {
	'@wordpress/hooks': { this: [ 'wp', 'hooks' ] },
	'@wordpress/i18n': { this: [ 'wp', 'i18n' ] },
	'@wordpress/api-fetch': { this: [ 'wp', 'apiFetch' ] },
	react: 'React',
	'react-dom': 'ReactDOM',
};

const aliases = {
	'@paddle/api': path.resolve( __dirname, 'assets/js/dev/api' ),
};

/* const adminConfig = {
	mode: NODE_ENV,
	entry: {
		product: './assets/js/dev/product/index.jsx',
	},
	output: {
		filename: './assets/js/admin/[name]/index.js',
		path: __dirname,
		library: [ '[modulename]' ],
		libraryTarget: 'this',
	},
	externals,
	module: {
		rules: [
			{
				parser: {
					amd: false,
				},
			},
			{
				test: /\.(js|jsx)$/,
				exclude: /node_modules/,
				loader: 'babel-loader',
			},
			{ test: /\.md$/, use: 'raw-loader' },
			{
				test: /\.s?css$/,
				use: [
					MiniCssExtractPlugin.loader,
					'css-loader'
				],
			},
			{
				test: /\.(png|jpe?g|gif|svg|eot|ttf|woff|woff2)$/,
				loader: 'url-loader',
			},
		],
	},
	resolve: {
		extensions: [ '*', '.js', '.jsx' ],
		alias: aliases,
	},
	plugins: [
		new MiniCssExtractPlugin( {
			filename: './assets/css/admin/[name]/style.css',
		} ),
	],
}; */

const frontConfig = {
	mode: NODE_ENV,
	entry: {
		'account-subscriptions':
			'./assets/js/dev/account-subscriptions/index.jsx',
	},
	output: {
		filename: './assets/js/[name]/index.js',
		path: __dirname,
		library: [ '[modulename]' ],
		libraryTarget: 'this',
	},
	externals,
	module: {
		rules: [
			{
				parser: {
					amd: false,
				},
			},
			{
				test: /\.(js|jsx)$/,
				exclude: /node_modules/,
				loader: 'babel-loader',
			},
			{ test: /\.md$/, use: 'raw-loader' },
			{
				test: /\.s?css$/,
				use: [
					MiniCssExtractPlugin.loader,
					'css-loader',
					'sass-loader',
				],
			},
			{
				test: /\.(png|jpe?g|gif|svg|eot|ttf|woff|woff2)$/,
				loader: 'url-loader',
			},
		],
	},
	resolve: {
		extensions: [ '*', '.js', '.jsx' ],
		alias: aliases,
	},
	plugins: [
		new MiniCssExtractPlugin( {
			filename: './assets/css/[name]/style.css',
		} ),
	],
};

if ( 'production' !== NODE_ENV ) {
	// adminConfig.devtool = process.env.SOURCEMAP || 'source-map';
	frontConfig.devtool = process.env.SOURCEMAP || 'source-map';
}

module.exports = [ frontConfig ];
