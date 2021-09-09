const path = require( 'path' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const CssMinimizerPlugin = require( 'css-minimizer-webpack-plugin' );
const { CleanWebpackPlugin } = require( 'clean-webpack-plugin' );

// JS Directory path.
const JS_DIR = path.resolve( __dirname, 'assets/react' );
const BUILD_DIR = path.resolve( __dirname, 'assets/js' );

const reactRoot = path.resolve( __dirname, 'assets/react' );
const nodeModules = path.resolve( __dirname, 'node_modules' );

module.exports = ( env, options ) => {

	const mode = options.mode || 'development';
	const extPrefix = ( 'production' === mode ) ? '.min' : '';

	const config = {
		mode,
		module: {
			rules: [
				{
					test: /\.js$/,
					include: [ JS_DIR ],
					exclude: /node_modules/,
					use: 'babel-loader',
				}
			],
		},
		devtool: 'source-map',
	};

	if ( 'production' === mode ) {
		config.devtool = false;
		config.optimization = {
			minimize: true,
			minimizer: [
				new TerserPlugin({
					terserOptions: {},
					minify: ( file ) => {
						const uglifyJsOptions = {
							sourceMap: true,
						};
						return require( 'uglify-js' ).minify( file, uglifyJsOptions );
					},
				}),
				new CssMinimizerPlugin(),
			],
		};
	}

	var configEditor = Object.assign({}, config, {
		name: 'configEditor',
		entry: {
			'tutor-front'		    : './assets/react/front/tutor-front.js',
			'tutor-admin'		    : './assets/react/admin/tutor-admin.js',
			'tutor-course-builder'	: './assets/react/course-builder/index.js',
			'tutor-setup'		    : './assets/react/admin/tutor-setup.js',
        },
        resolve: {
            modules: [ reactRoot, nodeModules ],
        },
		output: {
			path: path.resolve( __dirname, BUILD_DIR ),
			filename: `[name].js`,
		},
	});

	return [ configEditor ];
};
