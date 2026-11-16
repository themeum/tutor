import type { Config } from 'stylelint';

// Direction-neutral properties that stay physical in LTR and RTL.
const directionNeutralProperties =
  /^(top|bottom|width|height|min-(width|height)|max-(width|height)|contain-intrinsic-(width|height)|margin-(top|bottom)|padding-(top|bottom)|border-(top|bottom)(-(width|style|color))?|overflow-[xy]|overscroll-behavior-[xy]|scroll-(margin|padding)-(top|bottom))$/;

const config: Config = {
  customSyntax: 'postcss-scss',
  languageOptions: {
    directionality: { block: 'top-to-bottom', inline: 'left-to-right' },
  },
  rules: {},
  overrides: [
    {
      files: ['assets/core/scss/**/*.scss', 'assets/src/scss/frontend/**/*.scss'],
      extends: ['stylelint-config-standard-scss'],
      rules: {
        'property-no-vendor-prefix': null,
        'selector-class-pattern': null,
        'scss/at-mixin-argumentless-call-parentheses': 'always',
        'selector-not-notation': null,
        'media-feature-range-notation': 'prefix',
        'property-layout-mappings': ['flow-relative', { ignoreProperties: [directionNeutralProperties] }],
        'value-keyword-layout-mappings': ['flow-relative', { ignoreProperties: ['resize'] }],
      },
    },
  ],
};

export default config;
