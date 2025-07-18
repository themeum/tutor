const path = require('node:path');
const fs = require('fs');
const TerserPlugin = require('terser-webpack-plugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const webpack = require('webpack');
const nodeExternals = require('webpack-node-externals');

let version = "";

try {
    const data = fs.readFileSync('tutor.php', 'utf8');
    version = data.match(/Version:\s*([\d.]+(?:-[a-zA-Z0-9]+)?)/i)?.[1] || '';
} catch (err) {
    console.log(err);
}

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
        plugins: [
            new webpack.ProvidePlugin({ React: 'react' }),
            new webpack.DefinePlugin({
                'process.env.MAKE_POT': JSON.stringify(!!env['make-pot']),
            }),
        ],
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
            splitChunks: false,
            minimize: true,
            minimizer: [
                new TerserPlugin({
                    parallel: true,
                    terserOptions: {
                        compress: false,
                        ecma: 6,
                        mangle: {
                            reserved: ['__'],
                        },
                        format: {
                            comments: /translators:/i,
                        },
                    },
                    extractComments: false,
                }),
            ],
        };
    }

    if (env['make-pot']) {
        config.externals = [nodeExternals()];
        config.optimization = {
            minimize: false,
        };
    }

    const react_blueprints = [
        {
            dest_path: './assets/js',
            src_files: {
                'tutor': './assets/react/v2/common.js',
                'tutor-front': './assets/react/front/tutor-front.js',
                'tutor-admin': './assets/react/admin-dashboard/tutor-admin.js',
                'tutor-setup': './assets/react/admin-dashboard/tutor-setup.js',
                'tutor-gutenberg': './assets/react/gutenberg/index.js',
                'tutor-course-builder': './assets/react/v3/entries/course-builder/index.tsx',
                'tutor-order-details': './assets/react/v3/entries/order-details/index.tsx',
                'tutor-coupon': './assets/react/v3/entries/coupon-details/index.tsx',
                'tutor-tax-settings': './assets/react/v3/entries/tax-settings/index.tsx',
                'tutor-payment-settings': './assets/react/v3/entries/payment-settings/index.tsx',
                'tutor-addon-list': './assets/react/v3/entries/addon-list/index.tsx',
                'tutor-template-import-script': './assets/react/admin-dashboard/template-import-script.js',
                'tutor-import-export': './assets/react/v3/entries/import-export/index.tsx',
            }
        }
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
                    chunkFilename: `lazy-chunks/[name].js?ver=${version}`,
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
                        '@TutorShared': path.resolve(__dirname, './assets/react/v3/shared'),
                        '@SharedImages': path.resolve(__dirname, './assets/react/v3/public/images'),
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
                        '@AddonList': path.resolve(__dirname, './assets/react/v3/entries/addon-list/'),
                        '@ImportExport': path.resolve(__dirname, './assets/react/v3/entries/import-export/'),
                    },
                },
            }),
        );
    }

    return [...configEditors];
};
