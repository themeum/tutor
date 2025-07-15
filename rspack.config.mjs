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
    module: {
      rules: [
        {
          test: /\.s[ac]ss$/i,
          use: [
            rspack.CssExtractRspackPlugin.loader,
            'css-loader',
            {
              loader: 'sass-loader',
              options: {
                implementation: 'sass',
                sassOptions: {
                  outputStyle: isDevelopment ? 'expanded' : 'compressed',
                  silenceDeprecations: [
                    'abs-percent',
                    'color-functions',
                    'global-builtin',
                    'import',
                    'legacy-js-api',
                    'mixed-decls',
                  ],
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
          test: /\.(js|ts|tsx)$/,
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
        filename: 'css/[name].min.css',
        chunkFilename: 'css/[name].min.css',
      }),
      new rspack.ProvidePlugin({
        React: 'react',
      }),
      new rspack.DefinePlugin({
        'process.env.MAKE_POT': JSON.stringify(!!isMakePot),
        'process.env.NODE_ENV': JSON.stringify(mode),
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
      usedExports: false,
      providedExports: false,
      optimizationBailout: false,
      children: false,
      entrypoints: true,
      assets: isDevelopment,
    },
    ignoreWarnings: [/CROSS-CHUNKS-PACKAGE/, /asset size limit/, /entrypoint size limit/],
  };

  if ('production' === mode) {
    baseConfig.optimization = {
      minimize: true,
      splitChunks: false,
      minimizer: [
        new rspack.SwcJsMinimizerRspackPlugin({
          extractComments: false,
          minimizerOptions: {
            compress: false,
            mangle: true,
          },
        }),
        new rspack.LightningCssMinimizerRspackPlugin(),
      ],
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

const getReactEntries = () => ({
  tutor: './assets/react/v2/common.js',
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
});

const getScssEntries = () => ({
  'tutor-front': './assets/scss/front/index.scss',
  'tutor-admin': './assets/scss/admin-dashboard/index.scss',
  'tutor-setup': './assets/scss/admin-dashboard/tutor-setup.scss',
  tutor: './v2-library/src/scss/main.scss',
  'tutor-rtl': './v2-library/src/scss/main.rtl.scss',
  'tutor-icon': './v2-library/fonts/tutor-icon/tutor-icon.scss',
  'tutor-frontend-dashboard': './assets/scss/frontend-dashboard/index.scss',
  'tutor-template-import': './assets/scss/admin-dashboard/template-import.scss',
});

const createResolveAliases = () => ({
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
});

const createChunkFilename = (pathData) => {
  if (pathData.chunk.name?.startsWith('icon-')) {
    const iconName = pathData.chunk.name.replace(/^icon-/, '');
    return `js/icons/${iconName}.js?ver=${version}`;
  }
  return `js/lazy-chunks/[name].js?ver=${version}`;
};

const isScssEntry = (entryPath) => {
  return /\.css$/i.test(entryPath) || /\.s[ac]ss$/i.test(entryPath);
};

export default (env, options) => {
  const baseConfig = createConfig(env, options);
  const reactEntries = getReactEntries();
  const scssEntries = getScssEntries();
  const resolveAliases = createResolveAliases();
  const isDevelopment = options.mode === 'development';

  const allEntries = { ...reactEntries, ...scssEntries };

  if (isDevelopment) {
    return Object.entries(allEntries).map(([entryKey, entryPath]) => ({
      ...baseConfig,
      name: entryKey,
      entry: {
        [entryKey]: entryPath,
      },
      output: {
        path: path.resolve('./assets'),
        filename: (pathData) => {
          const entryName = pathData.chunk.name;
          const originalEntryPath = allEntries[entryName];

          if (isScssEntry(originalEntryPath)) {
            return `[name].min.css`; // CSS files handled by CssExtractRspackPlugin
          }
          return `js/${entryName}.js`; // JavaScript files go to js/ directory
        },
        chunkFilename: createChunkFilename,
        clean: false,
      },
      resolve: {
        extensions: ['.js', '.jsx', '.ts', '.tsx', '.scss', '.css'],
        fallback: {
          fs: false,
          path: false,
          os: false,
        },
        alias: resolveAliases,
      },
    }));
  }

  return {
    ...baseConfig,
    entry: allEntries,
    output: {
      path: path.resolve('./assets'),
      filename: (pathData) => {
        const entryName = pathData.chunk.name;
        const originalEntryPath = allEntries[entryName];

        if (isScssEntry(originalEntryPath)) {
          return `css/${entryName}.min.css`;
        }
        return `js/${entryName}.js`;
      },
      chunkFilename: createChunkFilename,
      clean: true,
    },
    resolve: {
      extensions: ['.js', '.jsx', '.ts', '.tsx', '.scss', '.css'],
      fallback: {
        fs: false,
        path: false,
        os: false,
      },
      alias: resolveAliases,
    },
  };
};
