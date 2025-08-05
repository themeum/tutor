import pluginJs from '@eslint/js';
import wordpressEslintPlugin from '@wordpress/eslint-plugin';
import eslintConfigPrettier from 'eslint-config-prettier';
import eslintPluginPrettierRecommended from 'eslint-plugin-prettier/recommended';
import pluginReact from 'eslint-plugin-react';
import reactHooks from 'eslint-plugin-react-hooks';
import storybook from 'eslint-plugin-storybook';
import globals from 'globals';
import tseslint from 'typescript-eslint';

/** @type {import('eslint').Linter.Config[]} */
export default [
  { ignores: ['**/*.js'] },
  { files: ['**/*.{js,mjs,cjs,ts,jsx,tsx}'] },
  { languageOptions: { globals: globals.browser } },
  pluginJs.configs.recommended,
  ...tseslint.configs.recommended,
  pluginReact.configs.flat.recommended,
  eslintPluginPrettierRecommended,
  eslintConfigPrettier,
  reactHooks.configs['recommended-latest'],
  ...storybook.configs['flat/recommended'],
  {
    plugins: {
      '@wordpress': wordpressEslintPlugin,
    },
    rules: {
      '@wordpress/i18n-text-domain': [
        'error',
        {
          allowedTextDomain: ['tutor'],
        },
      ],
      'react/no-unknown-property': ['error', { ignore: ['css'] }],
      'react/react-in-jsx-scope': 'off',
      'react/display-name': 'off',
      'no-console': 'error',
      '@typescript-eslint/consistent-type-imports': [
        'error',
        {
          fixStyle: 'inline-type-imports',
          prefer: 'type-imports',
        },
      ],
      '@typescript-eslint/ban-ts-comment': 'off',
    },
    settings: {
      react: {
        version: 'detect',
      },
    },
  },
];
