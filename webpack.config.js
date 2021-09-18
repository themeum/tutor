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
		var minimizer = env!='build' ? 
			new TerserPlugin({
					terserOptions: {},
					minify: ( file ) => {
						const uglifyJsOptions = {
							sourceMap: true,
						};
						return require( 'uglify-js' ).minify( file, uglifyJsOptions );
					},
				}) :
			new TerserPlugin({
				terserOptions: {},
				minify: ( file ) => {
					const uglifyJsOptions = {
						sourceMap: false,
					};
					return require( 'uglify-js' ).minify( file, uglifyJsOptions );
				},
			});

		config.devtool = false;
		config.optimization = {
			minimize: true,
			minimizer: [
				minimizer,
				new CssMinimizerPlugin(),
			]
		};
	}

	var react_blueprints = [
		{
			dest_path: './assets/js',
			src_files: {
				'tutor-front'		    : './assets/react/front/tutor-front.js',
				'tutor-admin'		    : './assets/react/admin-dashboard/tutor-admin.js',
				'tutor-course-builder'	: './assets/react/course-builder/index.js',
				'tutor-setup'		    : './assets/react/admin-dashboard/tutor-setup.js',
			}
		}
	];

	var configEditors = [];
	for(let i=0; i<react_blueprints.length; i++) {
		let {src_files, dest_path} = react_blueprints[i];

		configEditors.push(Object.assign({}, config, {
			name: 'configEditor',
			entry: src_files,
			output: {
				path: path.resolve(dest_path),
				filename: `[name].js`,
			},
		}))
	}

	return [ ...configEditors ];
};
