const path = require( 'path' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const CssMinimizerPlugin = require( 'css-minimizer-webpack-plugin' );

module.exports = ( env, options ) => {

	const mode = options.mode || 'development';
	
	const config = {
		mode,
		module: {
			rules: [
				{
					test: /\.js$/,
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
		output: {
			path: path.resolve( __dirname, path.resolve( __dirname, 'assets/js' ) ),
			filename: `[name].js`,
		},
	});

	return [ configEditor ];
};
