const path = require('node:path');
const TerserPlugin = require('terser-webpack-plugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
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
		externals: {
			react: 'React',
			'react-dom': 'ReactDOM',
			'@wordpress/i18n': 'wp.i18n',
		},
		devtool: 'source-map',
	};

	if ('production' === mode) {
		config.devtool = false;
		config.optimization = {
			splitChunks: {
				cacheGroups: {
					vendors: {
						test: /[\\/]node_modules[\\/]/,
						name: 'tutor-vendors.min',
						chunks: 'all',
					},
					shared: {
						test: /[\\/]assets[\\/]react[\\/]v3[\\/]shared[\\/]/,
						name: 'tutor-shared.min',
						chunks: 'all',
					},
				},
			},
			minimize: true,
			minimizer: [
				new TerserPlugin({
					parallel: true,
					terserOptions: {
						compress: false,
						ecma: 6,
						mangle: true,
					},
					extractComments: false,
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
				'tutor-tax-settings.min': './assets/react/v3/entries/tax-settings/index.tsx',
				'tutor-coupon.min': './assets/react/v3/entries/coupon-details/index.tsx',
			},
			clean: true,
		},
		{
			dest_path: '../tutor-pro/addons/enrollments/assets/js',
			src_files: {
				'manual-enrollment.min': './assets/react/v3/entries/pro/manual-enrollment/index.tsx',
			},
			clean: false,
		},
	];

	const configEditors = [];
	for (let i = 0; i < react_blueprints.length; i++) {
		const { src_files, dest_path, clean } = react_blueprints[i];

		configEditors.push(
			Object.assign({}, config, {
				name: 'configEditor',
				entry: src_files,
				output: {
					path: path.resolve(dest_path),
					filename: '[name].js',
					clean: clean,
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
						'@Images': path.resolve(__dirname, './assets/react/v3/public/images'),
						'@Controls': path.resolve(__dirname, './assets/react/v3/shared/controls/'),
						'@CourseBuilderComponents': path.resolve(__dirname, './assets/react/v3/entries/course-builder/components/'),
						'@CourseBuilderServices': path.resolve(__dirname, './assets/react/v3/entries/course-builder/services/'),
						'@CourseBuilderConfig': path.resolve(__dirname, './assets/react/v3/entries/course-builder/config/'),
						'@CourseBuilderPages': path.resolve(__dirname, './assets/react/v3/entries/course-builder/pages/'),
						'@CourseBuilderUtils': path.resolve(__dirname, './assets/react/v3/entries/course-builder/utils/'),
						'@CourseBuilderContexts': path.resolve(__dirname, './assets/react/v3/entries/course-builder/contexts/'),
						'@OrderComponents': path.resolve(__dirname, './assets/react/v3/entries/order-details/components/'),
						'@OrderServices': path.resolve(__dirname, './assets/react/v3/entries/order-details/services/'),
						'@OrderAtoms': path.resolve(__dirname, './assets/react/v3/entries/order-details/atoms/'),
						'@OrderContexts': path.resolve(__dirname, './assets/react/v3/entries/order-details/contexts/'),
						'@CouponComponents': path.resolve(__dirname, './assets/react/v3/entries/coupon-details/components/'),
						'@CouponServices': path.resolve(__dirname, './assets/react/v3/entries/coupon-details/services/'),
						'@EnrollmentComponents': path.resolve(__dirname, './assets/react/v3/entries/pro/manual-enrollment/components/'),
						'@EnrollmentServices': path.resolve(__dirname, './assets/react/v3/entries/pro/manual-enrollment/services/'),
					},
				},
			}),
		);
	}

	return [...configEditors];
};
