const path = require( 'path' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const CssMinimizerPlugin = require( 'css-minimizer-webpack-plugin' );
const { CleanWebpackPlugin } = require( 'clean-webpack-plugin' );

// JS Directory path.
const JS_DIR = path.resolve( __dirname, 'assets/react/src/js' );
const BUILD_DIR = path.resolve( __dirname, 'assets/js' );

const reactRoot = path.resolve(__dirname, 'assets/editor/src/js');
const nodeModules = path.resolve(__dirname, 'node_modules');

module.exports = ( env, options ) => {

	const mode = options.mode || 'development';
	
	const config = {
		mode,
		module: {
			rules: [
				{
					test: /\.js$/,
					include: [ JS_DIR ],
					exclude: /node_modules/,
					use: 'babel-loader'
				}
			]
		},
		plugins: [
			new CleanWebpackPlugin( {
				cleanStaleWebpackAssets: ( mode === 'production' ) // Automatically remove all unused webpack assets on rebuild, when set to true in production.
			} )
		],
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
							sourceMap: false
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
		entry: {
			'tutor'				: './assets/react/tutor.js',
			'tutor-front'		: './assets/react/tutor-front.js',
			'tutor-admin'		: './assets/react/tutor-admin.js',
			'tutor-setup'		: './assets/react/tutor-setup.js',
			'mce-button'		: './assets/react/mce-button.js',
			'gutenberg_blocks'	: './assets/react/gutenberg_blocks.js',
			'Chart.bundle.min'	: './assets/react/Chart.bundle.min.js',
		},
        resolve: {
            modules: [reactRoot, nodeModules],
			extensions: ['*', '.js', '.jsx']
        },
		output: {
			path: path.resolve( __dirname, BUILD_DIR ),
			filename: `[name].js`,
		},
	});

	return [ configEditor ];
}