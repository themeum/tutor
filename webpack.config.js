const path = require('node:path');
const TerserPlugin = require('terser-webpack-plugin');
const webpack = require('webpack');

module.exports = (env, options) => {
	const mode = options.mode || 'development';

	const config = {
		mode,
		module: {
			rules: [
				{
					test: /\.css$/i,
					use: ['style-loader', 'css-loader'],
				},
				{
					test: /\.(js|ts|tsx)$/,
					exclude: /node_modules/,
					use: 'babel-loader',
				},
				{
					test: /\.(png|jp(e*)g|gif|webp)$/,
					use: [
						{
							loader: 'file-loader',
							options: {
								name: 'images/[hash]-[name].[ext]',
							},
						},
					],
				},
				{
					test: /\.svg$/i,
					issuer: /\.[jt]sx?$/,
					use: ['@svgr/webpack'],
				},
			],
		},
		plugins: [new webpack.ProvidePlugin({ React: 'react' })],
		devtool: 'source-map',
	};

	if ('production' === mode) {
		config.devtool = false;
		config.optimization = {
			minimize: true,
			minimizer: [
				new TerserPlugin({
					parallel: true,
					terserOptions: {
						compress: false,
						ecma: 6,
						mangle: true,
					},
				}),
			],
		};
	}

	const react_blueprints = [
		{
			dest_path: './assets/js',
			src_files: {
				'tutor-front.min': './assets/react/front/tutor-front.js',
				'tutor-admin.min': './assets/react/admin-dashboard/tutor-admin.js',
				'tutor-course-builder.min': './assets/react/course-builder/index.js',
				'tutor-setup.min': './assets/react/admin-dashboard/tutor-setup.js',
				'tutor.min': './assets/react/v2/common.js',
				'tutor-gutenberg.min': './assets/react/gutenberg/index.js',
				'tutor-course-builder-v3.min': './assets/react/v3/entries/course-builder/index.tsx',
				'tutor-order-details.min': './assets/react/v3/entries/order-details/index.tsx',
				'tutor-coupon.min': './assets/react/v3/entries/coupon-details/index.tsx',
			},
		},
	];

	const configEditors = [];
	for (let i = 0; i < react_blueprints.length; i++) {
		const { src_files, dest_path } = react_blueprints[i];

		configEditors.push(
			Object.assign({}, config, {
				name: 'configEditor',
				entry: src_files,
				output: {
					path: path.resolve(dest_path),
					filename: '[name].js',
					clean: true,
				},
				resolve: {
					extensions: ['.js', '.jsx', '.ts', '.tsx'],
					fallback: {
						fs: false,
						path: false,
						os: false,
					},
					alias: {
						'@Atoms': path.resolve(__dirname, './assets/react/v3/shared/atoms/'),
						'@Molecules': path.resolve(__dirname, './assets/react/v3/shared/molecules/'),
						'@Components': path.resolve(__dirname, './assets/react/v3/shared/components/'),
						'@Config': path.resolve(__dirname, './assets/react/v3/shared/config/'),
						'@Hooks': path.resolve(__dirname, './assets/react/v3/shared/hooks/'),
						'@Services': path.resolve(__dirname, './assets/react/v3/shared/services/'),
						'@Utils': path.resolve(__dirname, './assets/react/v3/shared/utils/'),
						'@Images': path.resolve(__dirname, './assets/images/'),
						'@Controls': path.resolve(__dirname, './assets/react/v3/shared/controls/'),
						'@CourseBuilderComponents': path.resolve(__dirname, './assets/react/v3/entries/course-builder/components/'),
						'@CourseBuilderServices': path.resolve(__dirname, './assets/react/v3/entries/course-builder/services/'),
						'@CourseBuilderConfig': path.resolve(__dirname, './assets/react/v3/entries/course-builder/config/'),
						'@CourseBuilderPages': path.resolve(__dirname, './assets/react/v3/entries/course-builder/pages/'),
						'@CourseBuilderUtils': path.resolve(__dirname, './assets/react/v3/entries/course-builder/utils/'),
						'@CourseBuilderContexts': path.resolve(__dirname, './assets/react/v3/entries/course-builder/contexts/'),
						'@CourseBuilderPublic': path.resolve(__dirname, './assets/react/v3/entries/course-builder/public/'),
						'@OrderComponents': path.resolve(__dirname, './assets/react/v3/entries/order-details/components/'),
						'@OrderServices': path.resolve(__dirname, './assets/react/v3/entries/order-details/services/'),
						'@OrderAtoms': path.resolve(__dirname, './assets/react/v3/entries/order-details/atoms/'),
						'@OrderContexts': path.resolve(__dirname, './assets/react/v3/entries/order-details/contexts/'),
						'@CouponComponents': path.resolve(__dirname, './assets/react/v3/entries/coupon-details/components/'),
						'@CouponServices': path.resolve(__dirname, './assets/react/v3/entries/coupon-details/services/'),
						'@CouponAtoms': path.resolve(__dirname, './assets/react/v3/entries/coupon-details/atoms/'),
						'@CouponContexts': path.resolve(__dirname, './assets/react/v3/entries/coupon-details/contexts/'),
					},
				},
			}),
		);
	}

	return [...configEditors];
};
