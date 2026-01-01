import { RsdoctorRspackPlugin } from '@rsdoctor/rspack-plugin';
import { rspack } from '@rspack/core';
import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';
import { fileURLToPath } from 'node:url';
import nodeExternals from 'webpack-node-externals';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

let version = '';

try {
  const data = fs.readFileSync('tutor.php', 'utf8');
  version = data.match(/Version:\s*([\d.]+(?:-[a-zA-Z0-9]+)?)/i)?.[1] || '';
} catch (err) {
  // eslint-disable-next-line no-console
  console.log(`Error reading version from tutor.php: ${err}`);
}

const createSwcLoaderOptions = (isDevelopment) => ({
  jsc: {
    parser: {
      syntax: 'typescript',
      tsx: true,
      decorators: false,
    },
    externalHelpers: true,
    transform: {
      react: {
        runtime: 'automatic',
        development: isDevelopment,
        refresh: false,
        importSource: '@emotion/react',
      },
    },
    experimental: {
      plugins: [
        [
          '@swc/plugin-emotion',
          {
            sourceMap: isDevelopment,
            autoLabel: 'dev-only',
            labelFormat: '[local]',
            cssPropOptimization: true,
          },
        ],
      ],
    },
  },
  env: {
    targets: 'Chrome >= 58, Firefox >= 54, Safari >= 10.1, Edge >= 16',
  },
});

const createConfig = (env, options) => {
  const mode = options.mode || 'development';
  const isDevelopment = mode === 'development';
  const isMakePot = env?.['make-pot'];

  const baseConfig = {
    mode,
    cache: false,
    module: {
      rules: [
        {
          test: /\.s[ac]ss$/i,
          use: [
            rspack.CssExtractRspackPlugin.loader,
            {
              loader: 'css-loader',
              options: {
                url: {
                  filter: (url) => {
                    return /\.(woff2?|woff|ttf|otf|eot)(\?.*)?$/i.test(url);
                  },
                },
              },
            },
            {
              loader: 'sass-loader',
              options: {
                implementation: 'sass',
                sassOptions: {
                  outputStyle: isDevelopment ? 'expanded' : 'compressed',
                  silenceDeprecations: ['abs-percent', 'color-functions', 'global-builtin', 'import', 'legacy-js-api'],
                },
              },
            },
          ],
        },
        {
          test: /\.css$/i,
          exclude: /\.s[ac]ss$/i,
          use: ['style-loader', 'css-loader'],
        },
        {
          test: /\.(js|ts|jsx|tsx)$/,
          exclude: [/node_modules/, /\.cy\.(ts|tsx)$/],
          use: [
            {
              loader: 'builtin:swc-loader',
              options: createSwcLoaderOptions(isDevelopment),
            },
          ],
        },
        {
          test: /\.(png|jp(e*)g|gif|webp)$/,
          type: 'asset/resource',
          generator: {
            filename: 'js/images/[name]-[hash:8][ext]',
          },
        },
        {
          test: /\.(woff2?|woff|ttf|otf|eot)$/,
          type: 'asset/resource',
          generator: {
            filename: 'fonts/[name][ext]',
          },
        },
        {
          test: /\.svg$/i,
          issuer: /\.[jt]sx?$/,
          use: ['@svgr/webpack'],
        },
      ],
    },
    plugins: [
      new rspack.CssExtractRspackPlugin({
        filename: (pathData) => {
          const entryName = pathData.chunk.name.replace('-scss', '');
          return `css/${entryName}.min.css`;
        },
        chunkFilename: (pathData) => {
          const entryName = pathData.chunk.name.replace('-scss', '');
          return `css/lazy-chunks/${entryName}.min.css`;
        },
      }),
      new rspack.ProvidePlugin({
        React: 'react',
      }),
      new rspack.DefinePlugin({
        'process.env.MAKE_POT': JSON.stringify(!!isMakePot),
        'process.env.NODE_ENV': JSON.stringify(mode),
        __TUTOR_TEXT_DOMAIN__: JSON.stringify(process.env.TEXT_DOMAIN || 'tutor'),
      }),
      process.env.RSDOCTOR && new RsdoctorRspackPlugin({}),
    ].filter(Boolean),
    externals: {
      react: 'React',
      'react-dom': 'ReactDOM',
      '@wordpress/i18n': 'wp.i18n',
    },
    devtool: isDevelopment ? 'source-map' : false,
    stats: {
      preset: 'errors-warnings',
      colors: true,
      errorDetails: true,
      modules: false,
      chunks: false,
      chunkModules: false,
      reasons: false,
      children: false,
      entrypoints: true,
      assets: isDevelopment,
    },
    ignoreWarnings: [/CROSS-CHUNKS-PACKAGE/, /asset size limit/, /entrypoint size limit/],
    performance: {
      hints: false,
    },
    output: {
      path: path.resolve('./assets'),
      filename: createOutputFileName,
      chunkFilename: createChunkFilename,
    },
    resolve: {
      extensions: ['.js', '.jsx', '.ts', '.tsx', '.scss', '.css'],
      fallback: {
        fs: false,
        path: false,
        os: false,
        url: false,
      },
      alias: resolveAliases,
    },
  };

  if ('production' === mode) {
    baseConfig.optimization = {
      splitChunks: false,
      minimize: true,
      minimizer: [
        new rspack.SwcJsMinimizerRspackPlugin({
          extractComments: false,
          minimizerOptions: {
            compress: false,
            ecma: 2015,
            mangle: {
              reserved: ['__'],
            },
            format: {
              comments: /translators/i,
            },
          },
        }),
        new rspack.LightningCssMinimizerRspackPlugin(),
      ],
    };
    baseConfig.output.clean = {
      keep: (assetPath) => {
        const keepDirectories = [
          'assets/fonts/',
          'assets/icons/',
          'assets/images/',
          'assets/lib/',
          'assets/src/',
          'assets/json/',
          'assets/core/',
        ];
        return keepDirectories.some((dir) => assetPath.includes(dir));
      },
    };
  }

  if (isMakePot) {
    baseConfig.externals = [nodeExternals()];
    baseConfig.optimization = {
      minimize: false,
    };
  }

  return baseConfig;
};

const jsEntries = {
  tutor: './assets/src/js/v2/common.js',
  'tutor-core': './assets/core/ts/index.ts',
  'tutor-front': './assets/src/js/front/tutor-front.js',
  'tutor-admin': './assets/src/js/admin-dashboard/tutor-admin.js',
  'tutor-setup': './assets/src/js/admin-dashboard/tutor-setup.js',
  'tutor-gutenberg': './assets/src/js/gutenberg/index.js',
  'tutor-template-import-script': './assets/src/js/admin-dashboard/template-import-script.js',
  'tutor-course-builder': './assets/src/js/v3/entries/course-builder/index.tsx',
  'tutor-order-details': './assets/src/js/v3/entries/order-details/index.tsx',
  'tutor-coupon': './assets/src/js/v3/entries/coupon-details/index.tsx',
  'tutor-tax-settings': './assets/src/js/v3/entries/tax-settings/index.tsx',
  'tutor-payment-settings': './assets/src/js/v3/entries/payment-settings/index.tsx',
  'tutor-addon-list': './assets/src/js/v3/entries/addon-list/index.tsx',
  'tutor-import-export': './assets/src/js/v3/entries/import-export/index.tsx',
  'tutor-dashboard': './assets/src/js/frontend/dashboard/index.ts',
  'tutor-learning-area': './assets/src/js/frontend/learning-area/index.ts',
};

const scssEntries = {
  'tutor-front-scss': './assets/src/scss/front/index.scss',
  'tutor-admin-scss': './assets/src/scss/admin-dashboard/index.scss',
  'tutor-setup-scss': './assets/src/scss/admin-dashboard/tutor-setup.scss',
  'tutor-scss': './v2-library/src/scss/main.scss',
  'tutor-rtl-scss': './v2-library/src/scss/main.rtl.scss',
  'tutor-icon-scss': './v2-library/tutor-icon/tutor-icon.scss',
  'tutor-frontend-dashboard-scss': './assets/src/scss/frontend-dashboard/index.scss',
  'tutor-template-import-scss': './assets/src/scss/admin-dashboard/template-import.scss',
  'tutor-core-scss': './assets/core/scss/main.scss',
  'tutor-dashboard-scss': './assets/src/scss/frontend/main/dashboard.scss',
  'tutor-learning-area-scss': './assets/src/scss/frontend/main/learning-area.scss',
};

const resolveAliases = {
  '@Core': path.resolve(__dirname, './assets/core'),
  '@TutorShared': path.resolve(__dirname, './assets/src/js/v3/shared'),
  '@SharedImages': path.resolve(__dirname, './assets/src/js/v3/public/images'),
  '@CourseBuilderComponents': path.resolve(__dirname, './assets/src/js/v3/entries/course-builder/components/'),
  '@CourseBuilderServices': path.resolve(__dirname, './assets/src/js/v3/entries/course-builder/services/'),
  '@CourseBuilderConfig': path.resolve(__dirname, './assets/src/js/v3/entries/course-builder/config/'),
  '@CourseBuilderPages': path.resolve(__dirname, './assets/src/js/v3/entries/course-builder/pages/'),
  '@CourseBuilderUtils': path.resolve(__dirname, './assets/src/js/v3/entries/course-builder/utils/'),
  '@CourseBuilderContexts': path.resolve(__dirname, './assets/src/js/v3/entries/course-builder/contexts/'),
  '@OrderDetails': path.resolve(__dirname, './assets/src/js/v3/entries/order-details/'),
  '@CouponDetails': path.resolve(__dirname, './assets/src/js/v3/entries/coupon-details/'),
  '@AddonList': path.resolve(__dirname, './assets/src/js/v3/entries/addon-list/'),
  '@ImportExport': path.resolve(__dirname, './assets/src/js/v3/entries/import-export/'),
  '@FrontendTypes': path.resolve(__dirname, './assets/src/js/frontend/types'),
  '@FrontendServices': path.resolve(__dirname, './assets/src/js/frontend/services'),
  '@FrontendDashboard': path.resolve(__dirname, './assets/src/js/frontend/dashboard'),
  '@LearningArea': path.resolve(__dirname, './assets/src/js/frontend/learning-area'),
};

const isScssEntry = (entry) => {
  return String(entry).includes('-scss');
};

const createOutputFileName = (pathData) => {
  const entryName = pathData.chunk.name;
  if (isScssEntry(entryName)) {
    return 'css/ignore/[name].css.ignore';
  }
  return `js/[name].js`;
};

const createChunkFilename = (entryPath) => {
  if (isScssEntry(entryPath)) {
    return `css/ignore/[name].css.ignore?ver=${version}`;
  }
  return `js/lazy-chunks/[name].js?ver=${version}`;
};

export default (env, options) => {
  const baseConfig = createConfig(env, options);
  const isDevelopment = options.mode === 'development';
  const isMakePot = env?.['make-pot'];

  const allEntries = { ...jsEntries, ...scssEntries };

  if (isDevelopment) {
    return Object.entries(allEntries).map(([entryKey, entryPath]) => ({
      ...baseConfig,
      name: entryKey,
      entry: {
        [entryKey]: entryPath,
      },
    }));
  }

  return {
    ...baseConfig,
    entry: isMakePot ? jsEntries : allEntries,
  };
};
