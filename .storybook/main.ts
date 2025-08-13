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

    rsbuildConfig.tools = rsbuildConfig.tools || {};
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
