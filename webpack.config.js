const path = require('path');
const TerserPlugin = require('terser-webpack-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const webpack = require('webpack');
// const UglifyJsPlugin = require('uglifyjs-webpack-plugin');

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
    var minimizer = !env.build
      ? new TerserPlugin({
          terserOptions: {},
          minify: file => {
            const uglifyJsOptions = {
              sourceMap: true,
            };
            return require('uglify-js').minify(file, uglifyJsOptions);
          },
        })
      : new TerserPlugin({
          terserOptions: {},
          minify: file => {
            const uglifyJsOptions = {
              sourceMap: false,
            };
            return require('uglify-js').minify(file, uglifyJsOptions);
          },
        });

    config.devtool = false;
    config.optimization = {
      // minimize: true,
      // minimizer: [minimizer, new CssMinimizerPlugin()],
      minimizer: [
        // we specify a custom UglifyJsPlugin here to get source maps in production
        new TerserPlugin({
          // cache: true,
          parallel: true,
          terserOptions: {
            compress: false,
            ecma: 6,
            mangle: true,
          },
        }),
        // new UglifyJsPlugin({
        //   cache: true,
        //   parallel: true,
        //   uglifyOptions: {
        //     compress: false,
        //     ecma: 6,
        //     mangle: true,
        //   },
        // sourceMap: true,
        // }),
        new CssMinimizerPlugin(),
      ],
    };
  }

  var react_blueprints = [
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
      },
      clean: true
    },
    {
      dest_path: './v2-library/bundle',
      src_files: {
        'main.min': './v2-library/_src/js/main.js',
      },
    },
    {
      dest_path: './.docz/static/v2-library/bundle',
      src_files: {
        'main.min': './v2-library/_src/js/main.js',
      },
    },
  ];

  var configEditors = [];
  for (let i = 0; i < react_blueprints.length; i++) {
    let { src_files, dest_path, clean } = react_blueprints[i];

    configEditors.push(
      Object.assign({}, config, {
        name: 'configEditor',
        entry: src_files,
        output: {
          path: path.resolve(dest_path),
          filename: `[name].js`,
          clean: clean
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
          },
        },
      })
    );
  }

  return [...configEditors];
};
