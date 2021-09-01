const path = require( 'path' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const CssMinimizerPlugin = require( 'css-minimizer-webpack-plugin' );
const { CleanWebpackPlugin } = require( 'clean-webpack-plugin' );

// JS Directory path.
const EDITOR_JS_DIR = path.resolve( __dirname, 'assets/editor/src/js' );
const EDITOR_BUILD_DIR = path.resolve( __dirname, 'assets/editor/dist' );
const FRONT_JS_DIR = path.resolve( __dirname, 'assets/front/src/js' );
const FRONT_BUILD_DIR = path.resolve( __dirname, 'assets/front/dist' );

const reactRoot = path.resolve(__dirname, 'assets/editor/src/js');
const nodeModules = path.resolve(__dirname, 'node_modules');

module.exports = ( env, options ) => {

	const mode = options.mode || 'development';
	const extPrefix = ( mode === 'production' ) ? '.min' : '';

	const config = {
		mode,
		module: {
			rules: [
				{
					test: /\.js$/,
					include: [ EDITOR_JS_DIR, FRONT_JS_DIR ],
					exclude: /node_modules/,
					use: 'babel-loader'
				},
				{
					test: /\.(sa|sc|c)ss$/,
					use: [
						MiniCssExtractPlugin.loader,
						'css-loader',
						'sass-loader',
					],
				},
			]
		},
		plugins: [
			new CleanWebpackPlugin( {
				cleanStaleWebpackAssets: ( mode === 'production' ) // Automatically remove all unused webpack assets on rebuild, when set to true in production.
			} ),
			new MiniCssExtractPlugin( {
				filename: `[name]${extPrefix}.css`,
			} ),
		],
		externals: {
			'@wordpress/api-fetch': ['wp', 'apiFetch'],
			'@wordpress/i18n': ['wp', 'i18n'],
		},
		devtool: 'source-map'
	}

	if ( mode === 'production' ) {
		config.devtool = false;
		config.optimization = {
			minimize: true,
			minimizer: [
				new TerserPlugin( {
					terserOptions: {},
					minify: ( file ) => {
						const uglifyJsOptions = {
							sourceMap: true
						};
						return require( 'uglify-js' ).minify( file, uglifyJsOptions );
					},
				} ),
				new CssMinimizerPlugin()
			],
		};
	}

	var configEditor = Object.assign({}, config, {
		name: "configEditor",
		entry: [
			'./assets/editor/src/js/main.js',
			'./assets/editor/src/scss/main.scss'
		],
        resolve: {
            modules: [reactRoot, nodeModules]
        },
		output: {
			path: path.resolve( __dirname, EDITOR_BUILD_DIR ),
			filename: `[name]${extPrefix}.js`,
		},
	});

	var configFront = Object.assign({}, config, {
		name: "configFront",
		entry: [
			'./assets/front/src/js/main.js',
			'./assets/front/src/scss/main.scss'
		],
		output: {
			path: path.resolve( __dirname, FRONT_BUILD_DIR ),
			filename: `[name]${extPrefix}.js`,
		},
	});

	return [ configEditor, configFront ];
}