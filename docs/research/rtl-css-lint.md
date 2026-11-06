# Linting RTL-unfriendly CSS (research)

**Date:** 2026-10-20 · **Scope:** linting CSS/SCSS and CSS-in-JS against physical (direction-dependent) properties in
this plugin. **Method:** primary sources plus local runs of stylelint 16.26.1/17.16.0, eslint 9.39.5 and the plugins
below. _(verified locally)_ means a test output, not documentation.

## 1. What this repo does today (measured in-tree)

- There was no stylelint at the start of this research (linting was ESLint 9 flat config `eslint.config.mjs` + lint-staged,
  TS/TSX only) and no `rtlcss` anywhere. During write-up a stylelint 17 built-ins config (`stylelint.config.mjs`, SCSS-only:
  `assets/core/scss` + `assets/src/scss/frontend`) was added in parallel, so the "no stylelint" statements below describe
  the state the research started from.
- **v2 SCSS:** rspack entry `'tutor-rtl-scss': './v2-library/src/scss/main.rtl.scss'` (`rspack.config.mjs:325`)
  compiles with `$is-rtl: true` (`$left`/`$right` swaps, `@if ($is_rtl)` overrides); `classes/Assets.php:446`
  enqueues `assets/css/tutor-rtl.min.css` only when `is_rtl()`.
- **Emotion (v3):** runtime flipping in `assets/src/js/v3/shared/components/RTLProvider.tsx` via `@emotion/cache` +
  `stylis-plugin-rtl` (CSSJanus-based). `assets/core/scss/mixins/_rtl.scss` has logical mixins but is imported nowhere.
- Scale (`rg` today): ≈500 matched lines in `.scss` across 79 files; ≈205 in TS/TSX across 89 files under
  `assets/src/js/v3`. CSS-in-JS is Emotion `css` templates (253 files use ``css` ``, 295 files import
  `@emotion/react`; `@emotion/styled` is unused).

## 2. Third-party stylelint rules

| Package / rule                                                | stylelint   | Coverage                   | Autofix             | Verdict           |
| ------------------------------------------------------------- | ----------- | -------------------------- | ------------------- | ----------------- |
| `stylelint-use-logical` 2.1.3 `csstools/use-logical`          | any         | longhands, partial sizes   | **broken**          | avoid             |
| `stylelint-use-logical-spec` 5.0.1 `liberty/use-logical-spec` | `<17`       | longhands + shorthands     | rewrites shorthands | avoid             |
| `stylelint-plugin-logical-css` 2.1.0 `logical-css/*`          | 14–17       | longhands, keywords, units | safe for longhands  | recommended on 16 |
| built-in `*-layout-mappings`                                  | ≥17.8/17.10 | same as above              | safe for longhands  | recommended on 17 |

- **csstools** is "largely in maintenance mode" (https://github.com/csstools/stylelint-use-logical#readme). `--fix`
  emits an invalid `margin: logical 0 0 auto auto` (https://github.com/csstools/stylelint-use-logical/blob/main/index.js#L72);
  issues #31/#32 are open since Nov 2024 (https://github.com/csstools/stylelint-use-logical/issues/31,
  https://github.com/csstools/stylelint-use-logical/issues/32) _(verified locally)_. It never flags
  `margin`/`padding`/`inset` shorthands (https://github.com/csstools/stylelint-use-logical/issues/29) and misses
  top-level declarations in styled templates on 16.26.1 and 17.16.0 _(verified locally)_.
- **use-logical-spec** (https://github.com/Jordan-Hall/stylelint-use-logical-spec) false-positives on safe shorthands
  (`margin: 0 auto`, `padding: 1px 2px`), though its autofix does handle
  `margin: 0 0 0 10px` → `margin-block: 0; margin-inline: 10px 0;` _(verified locally)_. Peer range `>=11 <17` means no
  stylelint 17 (https://www.npmjs.com/package/stylelint-use-logical-spec).
- **yuschick plugin** (https://github.com/yuschick/stylelint-plugin-logical-css): rules `require-logical-properties` /
  `-keywords` / `-units`, each with `fix`/`ignore`/`severity` and a `configs/recommended` preset. It also flags
  `width`/`height`/`min-*`/`max-*` (not RTL bugs), so an `ignore` list is needed; `ignore` takes exact names only
  _(verified locally)_.
- **stylelint built-ins:** `property-layout-mappings` shipped in 17.8.0
  (https://github.com/stylelint/stylelint/releases/tag/17.8.0); `unit-layout-mappings` + `value-keyword-layout-mappings`
  in 17.10.0 (https://github.com/stylelint/stylelint/releases/tag/17.10.0). They require
  `languageOptions.directionality` and support `ignoreProperties`/`ignoreKeywords`/`ignoreUnits`
  (https://stylelint.io/user-guide/rules/property-layout-mappings/); behavior matches yuschick _(verified locally)_.
- **Hand-rolled, no autofix:** `property-disallowed-list`, `declaration-property-value-disallowed-list`,
  `declaration-property-value-allowed-list` (https://stylelint.io/user-guide/rules/property-disallowed-list/ and
  sibling URLs; no "fixable" in their sources at 17.16.0). Stopgap:
  `'declaration-property-value-disallowed-list': { 'text-align': ['left','right'], float: ['left','right'] }`.

## 3. No rule catches asymmetric shorthands

csstools, yuschick and the 17.x built-ins never flag `margin: 0 0 0 10px`, `padding: 1px 2px 3px 4px` or
`inset: 0 0 0 10px` _(verified locally)_. The CSS Logical spec's "logical shorthand keyword" is still a draft
(https://www.w3.org/TR/css-logical-1/#logical-shorthand-keyword); only use-logical-spec rewrites shorthands, with the
false positives above.

## 4. CSS-in-JS support in stylelint 16/17

- Supported via `customSyntax` in config/overrides (https://stylelint.io/user-guide/configure/#customsyntax). 15.0.0
  removed only `processors` (https://stylelint.io/migration-guide/to-15/); `@stylelint/postcss-css-in-js` is
  npm-deprecated (https://www.npmjs.com/package/@stylelint/postcss-css-in-js).
- `postcss-styled-syntax` 0.7.4 is maintained and supports Emotion ``css`...` ``
  (https://github.com/hudochenkov/postcss-styled-syntax); verified on this repo's real templates with both plugins
  _(verified locally)_.
- Object styles (`css={{...}}`, `style={{...}}`) are not CSS text, so stylelint can't parse them; use ESLint. The repo
  has 10 `css={{ }}` and 19 `style={{ }}` usages.

## 5. ESLint alternatives

- No maintained ESLint plugin enforces RTL/logical properties. `eslint-plugin-css` 0.12.0
  (https://github.com/ota-meshi/eslint-plugin-css) parses Emotion objects via `settings.css.target`
  (https://ota-meshi.github.io/eslint-plugin-css/settings/) _(verified locally: it reported an invalid key in
  `styled.div({...})` and `css={{...}}`)_, but has no logical/RTL rule. `@stylexjs/eslint-plugin`'s `preferInline` only
  expands StyleX shorthands
  (https://github.com/facebook/stylex/blob/main/packages/%40stylexjs/eslint-plugin/README.md#stylexvalid-shorthands);
  `eslint-plugin-rtl-friendly` 0.5.1 is Tailwind-class-only (https://www.npmjs.com/package/eslint-plugin-rtl-friendly).
- Working approach: `no-restricted-syntax` (https://eslint.org/docs/latest/rules/no-restricted-syntax) with object-key
  and template-text selectors, verified on `PreviewItem.tsx` in this repo (5 reports) _(verified locally)_:

```js
// eslint.config.mjs, scoped to assets/src/js/v3
'no-restricted-syntax': ['error',
  { selector: 'Property[key.name=/^(margin|padding|border|inset)(Top|Right|Bottom|Left)$/]', message: 'Use a logical CSS property.' },
  { selector: 'TemplateElement[value.raw=/(margin|padding|border|inset)-(left|right)|text-align:\\s*(left|right)|float:\\s*(left|right)/]', message: 'Use a logical CSS property/keyword.' },
],
```

## 6. WordPress ecosystem

- Core generates RTL CSS with `grunt-rtlcss` (https://github.com/WordPress/wordpress-develop/blob/trunk/Gruntfile.js,
  also in its `package.json`); it flips rather than lints, and this plugin doesn't use it.
- The CSS standards still mandate physical TRBL ordering and use "logical ordering" only for grouping
  (https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/, "Property Ordering"); no RTL
  lint rule is prescribed.
- `@wordpress/stylelint-config` 26.1.0 requires stylelint `^17.14.1` + `stylelint-scss ^7.2.0`
  (https://www.npmjs.com/package/@wordpress/stylelint-config) and has zero RTL/logical rules _(verified locally by
  unpacking 26.1.0)_, so adopting it forces stylelint 17.

## 7. Incremental adoption

- Bulk suppressions are the official baseline: `stylelint --suppress` records current problems in a committed
  `stylelint-suppressions.json` (experimental; added in 16.25.0) — https://stylelint.io/user-guide/suppressions/,
  https://github.com/stylelint/stylelint/releases/tag/16.25.0.
- Cheaper first steps: `severity: 'warning'`, add stylelint to lint-staged (currently TS/TSX/PHP/MD only), `--cache`
  in CI, `stylelint-disable-next-line` for one-offs.

## 8. Logical properties: support and gotchas

- Shorthands are newest: `margin-inline`/`padding-inline` Chrome 87, Firefox 66, Safari 14.1; `inset-inline` Safari
  14.1 (https://developer.mozilla.org/en-US/docs/Web/CSS/margin-inline,
  https://developer.mozilla.org/en-US/docs/Web/CSS/inset-inline).
- Longhands are safe: `margin-inline-start`/`padding-inline-start`/`border-inline-start` Chrome 69, Firefox 41,
  Safari 12.1; `border-start-start-radius` Chrome 89, Firefox 66, Safari 15
  (https://developer.mozilla.org/en-US/docs/Web/CSS/border-start-start-radius).
- `text-align: start/end` is ancient; **`float: inline-start|inline-end` is Chrome 118 / Firefox 55 / Safari 15**
  (https://developer.mozilla.org/en-US/docs/Web/CSS/float) — don't autofix `float: left` blindly. Overview:
  https://caniuse.com/css-logical-props.
- Repo gotcha: Emotion styles are already flipped at runtime by `stylis-plugin-rtl`, so physical properties in `css`
  templates render correctly for RTL today. Converting them is still direction-proof but changes the RTL path — verify
  visually in `ar`/`he`/`fa`, not with `--fix`.

## Recommendation

### Option A — stylelint 16 + `stylelint-plugin-logical-css`

Flat config with per-syntax overrides:

```js
// stylelint.config.mjs  (deps: stylelint, stylelint-plugin-logical-css, postcss-scss, postcss-styled-syntax)
export default {
  plugins: ['stylelint-plugin-logical-css'],
  rules: {
    'logical-css/require-logical-properties': [
      true,
      {
        // width/height are not RTL bugs; keep them out of the first pass
        ignore: ['width', 'height', 'min-width', 'max-width', 'min-height', 'max-height'],
      },
    ],
    // float: left -> inline-start needs Safari 15+/Chrome 118+; start as warning
    'logical-css/require-logical-keywords': [true, { severity: 'warning' }],
  },
  overrides: [
    { files: ['**/*.scss'], customSyntax: 'postcss-scss' },
    { files: ['**/*.{js,jsx,ts,tsx}'], customSyntax: 'postcss-styled-syntax' },
  ],
};
```

Add `fix: true` to `require-logical-properties` only after reviewing the diff (longhand fixes are safe; shorthands
are never rewritten).

### Option B — stylelint 17 built-ins, no plugin

`languageOptions.directionality` plus the three built-in `*-layout-mappings` rules with `ignoreProperties` —
preferred if the repo adopts `@wordpress/stylelint-config` (which forces ≥17.14.1).

### Option C — object styles and rollout

Add the `no-restricted-syntax` pair to `eslint.config.mjs`; land the rules with `--suppress` to snapshot current
violations, then let lint-staged/CI prevent growth.

## Uncertainties

- No examined rule catches asymmetric `margin`/`padding`/`inset` shorthands; use-logical-spec does but false-positives
  on symmetric values and doesn't support stylelint 17.
- csstools' autofix is broken (issues #31/#32) and its parser misses top-level declarations in styled templates; don't
  use it even as a stopgap.
- yuschick `ignore` matches exact names only (`inset*` values are rejected as options).
- Converting Emotion styles while `stylis-plugin-rtl` is active is untested end-to-end; both can coexist, but RTL visual
  regression testing is required before autofix.
- `@wordpress/stylelint-config` 26.x cannot be mixed with stylelint 16.
