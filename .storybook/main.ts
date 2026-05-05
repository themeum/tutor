import type { StorybookConfig } from 'storybook-react-rsbuild';

const config: StorybookConfig = {
  stories: ['../stories/**/*.mdx', '../stories/**/*.stories.@(js|jsx|mjs|ts|tsx)'],
  addons: ['@storybook/addon-docs', '@storybook/addon-a11y', '@storybook/addon-themes'],
  framework: {
    name: 'storybook-react-rsbuild',
    options: {},
  },
  rsbuildFinal: async (rsbuildConfig, { configType }) => {
    // Ensure SWC loader uses the Emotion plugin, matching your main Rspack config
    const isDevelopment = configType === 'DEVELOPMENT';
    rsbuildConfig.dev = {
      ...rsbuildConfig.dev,
      // Prevent sporadic "factory is undefined" errors in Storybook dev
      // caused by lazy-compiled module registration timing.
      lazyCompilation: false,
    };

    rsbuildConfig.tools = rsbuildConfig.tools || {};
    rsbuildConfig.tools.bundlerChain = (chain, { rspack }) => {
      chain.module
        .rule('sass-inline')
        .test(/\.s[ac]ss$/i)
        .resourceQuery(/inline/)
        .use('style-loader')
        .loader('style-loader')
        .end()
        .use('css-loader')
        .loader('css-loader')
        .end()
        .use('sass-loader')
        .loader('sass-loader')
        .options({
          implementation: 'sass',
          sassOptions: {
            silenceDeprecations: ['abs-percent', 'color-functions', 'global-builtin', 'import', 'legacy-js-api'],
          },
        });

      chain.plugin('extra-define').use(rspack.DefinePlugin, [
        {
          __TUTOR_TEXT_DOMAIN__: {
            NODE_ENV: JSON.stringify(process.env.TEXT_DOMAIN || 'tutor'),
          },
        },
      ]);
    };
    rsbuildConfig.tools.swc = {
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
    };

    return rsbuildConfig;
  },
};

export default config;
