const path = require( 'path' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const CssMinimizerPlugin = require( 'css-minimizer-webpack-plugin' );

module.exports = ( env, options ) => {

	if(env=='docz') {
		return {
			mode: 'development',
			devtool: 'source-map',
			entry: [
			'./v2-library/_src/js/main.js',
			'./v2-library/_src/scss/main.scss'
			],
			output: {
				path: path.resolve(__dirname+'/v2-library', 'bundle'),
				filename: "[name].min.js",
			},
			module: {
			rules: [
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
			new MiniCssExtractPlugin({
				filename: '[name].min.css'
			}),
			],
			optimization: {
			minimize: true,
			minimizer: [
				new TerserPlugin({
				terserOptions: {},
				minify: (file) => {
					const uglifyJsOptions = {
					sourceMap: false
					};
					return require("uglify-js").minify(file, uglifyJsOptions);
				},
				}),
				new CssMinimizerPlugin()
			],
			}
		}
	}

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
							sourceMap: false,
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
			'tutor-admin'		    : './assets/react/admin-dashboard/tutor-admin.js',
			'tutor-course-builder'	: './assets/react/course-builder/index.js',
			'tutor-setup'		    : './assets/react/admin-dashboard/tutor-setup.js',
        },
		output: {
			path: path.resolve( __dirname, path.resolve( __dirname, 'assets/js' ) ),
			filename: `[name].js`,
		},
	});

	return [ configEditor ];
};
