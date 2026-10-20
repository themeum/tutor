// Shared lint config for new UI styles.
//
// The scoped trees use stylelint-config-standard-scss (style + correctness)
// plus a guard against physical direction properties, which do not mirror in
// RTL:
//   assets/core/scss          design system
//   assets/src/scss/frontend  dashboard, learning area, kids
//
// Legacy styles are deliberately out of scope: v2-library/src/scss builds a
// separate RTL stylesheet via $is-rtl (main.rtl.scss), and
// assets/src/scss/{front,admin-dashboard,modules} predate the design system.
//
// Intentional physical CSS (physical float utilities, or `left: 50%` with a
// physical translate) gets a one-line disable with a reason:
//   /* stylelint-disable-next-line <rule> -- reason */
//
// Known gap: 4-value shorthands (margin: 0 0 0 10px) are not inspected.

// Block-axis and size properties read the same in LTR and RTL, so they stay
// physical: width/height, top/bottom, and the block border/margin/padding sides.
const directionNeutralProperties =
  /^(top|bottom|width|height|min-(width|height)|max-(width|height)|contain-intrinsic-(width|height)|margin-(top|bottom)|padding-(top|bottom)|border-(top|bottom)(-(width|style|color))?|overflow-[xy]|overscroll-behavior-[xy]|scroll-(margin|padding)-(top|bottom))$/;

/** @type {import('stylelint').Config} */
export default {
  customSyntax: 'postcss-scss',
  languageOptions: {
    directionality: { block: 'top-to-bottom', inline: 'left-to-right' },
  },
  // Stylelint resolves the formatter against the config too; without a top-level
  // rules object it errors before the file-scoped overrides are applied.
  rules: {},
  overrides: [
    {
      files: ['assets/core/scss/**/*.scss', 'assets/src/scss/frontend/**/*.scss'],
      extends: ['stylelint-config-standard-scss'],
      rules: {
        // Browser prefixes still needed for Safari/Firefox support.
        'property-no-vendor-prefix': null,
        // Public and third-party class names (BEM, Plyr, vanilla-calendar) are
        // not ours to rename.
        'selector-class-pattern': null,
        // Keep min-/max-width for browsers without range syntax.
        'media-feature-range-notation': 'prefix',

        'property-layout-mappings': ['flow-relative', { ignoreProperties: [directionNeutralProperties] }],
        // resize does not mirror; block/inline only narrows browser support.
        'value-keyword-layout-mappings': ['flow-relative', { ignoreProperties: ['resize'] }],
      },
    },
  ],
};
