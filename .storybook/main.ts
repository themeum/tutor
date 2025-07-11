import path from 'path';
import type { StorybookConfig } from 'storybook-react-rsbuild';

const config: StorybookConfig = {
  stories: ['../stories/**/*.mdx', '../stories/**/*.stories.@(js|jsx|mjs|ts|tsx)'],
  addons: ['@storybook/addon-docs', '@storybook/addon-onboarding', '@storybook/addon-a11y'],
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

    // Add aliases if needed
    rsbuildConfig.resolve = rsbuildConfig.resolve || {};
    rsbuildConfig.resolve.alias = {
      ...rsbuildConfig.resolve.alias,
      '@TutorShared': path.resolve(__dirname, '../assets/react/v3/shared'),
      // Add other aliases as needed
    };

    return rsbuildConfig;
  },
};

export default config;
