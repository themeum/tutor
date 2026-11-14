# Bundling research: Elementor (`elementor/elementor`) production JS/CSS pipeline

- **Question:** how does the `elementor/elementor` WordPress plugin build, minify and ship its JS/CSS in production, and how does PHP pick between shipped variants? Read as a primary-source comparison for Tutor LMS, whose rspack production build keeps `compress: false` in the shared SWC minimizer and which recently added a scoped esbuild pass for Video.js assets.
- **Method:** downloaded the pinned source tree (`curl -L https://codeload.github.com/elementor/elementor/tar.gz/<sha>`, 54 MB, extracted to a scratch dir outside the tutor repo) and grepped it locally; all citations are permalinks into that tree at the pinned SHA. Release artifact evidence: `https://downloads.wordpress.org/plugin/elementor.zip` (23.6 MB) inspected with `unzip -l` / `unzip -p`. No packages installed, nothing built. Tutor-side facts were read (read-only) from `rspack.config.mjs`, `classes/Utils.php` and `minify-videojs.mjs` in this repo. Date of research: 2026-10-19.
- **Fixed point:** repo `elementor/elementor`, default branch `main`, commit `80706a7a7fed482b8009be1a765dfc7014b94988` (2026-10-19T16:40:25Z, `package.json` version `4.4.0`). Every `path#Lx-Ly` citation below expands to the permalink `https://github.com/elementor/elementor/blob/80706a7a7fed482b8009be1a765dfc7014b94988/<path>#Lx-Ly` (example: [scripts/vite/create-config.mjs#L59-L62](https://github.com/elementor/elementor/blob/80706a7a7fed482b8009be1a765dfc7014b94988/scripts/vite/create-config.mjs#L59-L62)).
- **Artifact note:** the WordPress.org zip checked is the current stable release, `4.3.5` (zip timestamps 2026-10-13), not the pinned commit — so it is used only as evidence of *what ships*, never for source line numbers.
- **Scope note:** "Elementor Core" below means the main plugin build; the `packages/` monorepo (npm-published `@elementor/*` libraries) has its own toolchain and is covered separately in §1/§2.

## 1. Toolchain

**There is no Grunt and no Webpack in the plugin's own build any more.** `scripts/vite/README.md:3-11` states it directly (permalink: `blob/80706a7a…/scripts/vite/README.md#L3-L11`):

> "Elementor Core builds with Vite and Rolldown. This directory is the whole build; there is no Grunt and no Webpack."
> "`webpack` also remains a devDependency, because the plugins published from `packages/packages/tools/` declare it as a peer and their tests compile with it. Nothing in Core's own build uses it."

| Layer | Tool (version) | Source | Builds |
| --- | --- | --- | --- |
| Core JS bundles | `vite-plus` `0.2.6` → Rolldown `1.1.5` (lockfile) | `package.json#L164`, `package-lock.json` (`node_modules/rolldown` 1.1.5) | `assets/js/<entry>[.min].js`, `assets/js/chunks/<name>[.min].js`, `assets/js/packages/<name>/<name>[.min].js` |
| ES5 downlevel (JS only) | Babel (`@babel/*` `^7.26`) via `plugins/babel-legacy.mjs` | `scripts/vite/README.md#L47-L59` | syntax lowering for Backbone/Marionette layers; Rolldown keeps resolution/tree-shaking/externals/minify |
| TS/JSX transform for packages | `esbuild` `0.28.1` | `scripts/vite/plugins/packages-transform.mjs:2`, lockfile | type-strip + classic JSX for package entries |
| CSS | `sass` `1.94.0` → `postcss` `8.5.24` + `autoprefixer` `10.4.22` → `cssnano` `7.1.2` | `scripts/vite/plugins/scss-build.mjs:5-9,17-21` | `assets/css/**.css` + `assets/css/**.min.css` |
| npm packages (`packages/**`) `dist/` | `tsup` `^8.3.5` (esbuild) | `packages/tsup.build.ts`, `packages/packages/libs/editor-ui/package.json` | `dist/index.js` + `dist/index.mjs` published to npm, unrelated to plugin output |
| Monorepo task runner | `turbo` `^2.3.3`, npm workspaces | `package.json#L63-L69`, `turbo.json` | `turbo build` → `packages/*/dist` |

Scripts that drive all of it (`package.json#L10-L17`, `#L39-L46`):

```
start            → composer:no-dev && build:packages && styles && scripts
build            → build:packages && node scripts/vite/build-all.mjs
scripts          → node scripts/vite/build-scripts.mjs && npm run packages:assets
styles           → node scripts/vite/build-styles.mjs
packages:assets  → node scripts/vite/build-packages.mjs
test:qunit       → node scripts/vite/build-scripts.mjs --dev --no-clean qunit && karma start …
```

Entry targets are declared as data, not config: `base` (wp-admin + editor entries, e.g. `editor`, `admin`, `common`, `app`), `frontend` (plus lazily-loaded per-widget chunks) and `qunit` (`scripts/vite/build-scripts.mjs#L31-L35`, `scripts/vite/shared/entries.mjs`). `.grunt-config/webpack.alias.js` survives as the alias source of truth, re-required by the new build and by Jest (`scripts/vite/shared/aliases.mjs:12-18`).

## 2. Production minification

### 2.1 JS

Minification is enabled per output in production only (`scripts/vite/create-config.mjs#L119-L151`):

```js
const MINIFY_OPTIONS = {                                  // :59-62
	mangle: { keepNames: true },
	compress: { keepNames: { function: true, class: true } },
};
…
build: { minify: false, sourcemap: ! isProduction,        // :135-136
	rollupOptions: { output: {
		keepNames: true,
		minify: isProduction ? MINIFY_OPTIONS : false,     // :146
	} } }
```

The comment attached to `MINIFY_OPTIONS` is the same class of runtime-semantics constraint Tutor hits: *"Function and class names must survive minification: module identity, editor event dispatch and `is-instanceof` all branch on `constructor.name` at runtime."* (`create-config.mjs#L55-L62`.) The per-package build repeats the identical options (`scripts/vite/build-packages.mjs#L22-L26`).

Rolldown's own output options define what `minify` does — Rolldown `v1.1.5`, [packages/rolldown/src/options/output-options.ts#L399-L411](https://github.com/rolldown/rolldown/blob/v1.1.5/packages/rolldown/src/options/output-options.ts#L399-L411):

> "Rolldown uses Oxc Minifier under the hood. … `true`: Enable full minification including code compression and dead code elimination; `false`: Disable minification; `'dce-only'`: Only perform dead code elimination without code compression (default); `MinifyOptions`: Fine-grained control…"

Elementor passes the object form, so production JS is compressed and mangled by **Oxc's minifier**, not Terser/SWC/esbuild. `terser` 5.44.1 exists in the lockfile only through `terser-webpack-plugin` (webpack's default), i.e. the retained devDependency, not the shipped build. `@swc/core` appears in `package.json#L89` as a Jest transformer, not as the bundler's minimizer.

Effect size, from the release artifact (4.3.5): `assets/js/frontend.js` 152,738 B → `assets/js/frontend.min.js` 60,022 B; `editor.js` 2,024,823 B → `editor.min.js` 1,198,752 B.

### 2.2 CSS

Sass compiles `expanded` with source maps, then PostCSS prefixes in place, then a second PostCSS pass with `autoprefixer + cssnano` writes a `.min.css` sibling (`scripts/vite/plugins/scss-build.mjs#L111-L156`). The cssnano preset is non-default where it would change semantics (`scss-build.mjs#L17-L21`):

```js
const CSSNANO_PRESET = [ 'default', { reduceIdents: false, zindex: false, calc: false } ];
```

Nested folders get autoprefixing *only* as part of minification, so their unminified output carries no vendor prefixes (`scripts/vite/README.md#L32-L38`). Per-widget stylesheets are generated, minified, and the unminified widget artifacts are then deleted (`scripts/vite/shared/widgets-css.mjs#L49-L61`) — widget CSS ships minified-only, confirmed in the zip (`assets/css/widget-heading.min.css` exists, `widget-heading.css` does not).

### 2.3 Comments, banners, source maps

- No banner/license-header machinery: `scripts/vite/README.md#L206-L209` records that the old Grunt `usebanner` task always wrote to files that were overwritten in the same block, so **"zero shipped `.js` or `.css` files carried it"**, and the new build emits no banner either.
- No comment-preservation option is configured for the JS minifier (contrast with Tutor's `format.comments: /translators/i`).
- Source maps: generated for the **development** variant only (`sourcemap: ! isProduction`, `create-config.mjs#L136`; same at `#L187` and `build-packages.mjs#L79-L89`), and excluded from the distributable by pattern `'!assets/**/*.map'` (`scripts/vite/shared/plugin-files.mjs#L22`). The 4.3.5 zip contains **0** `.map` files.

## 3. How users get the artifacts

**Nothing generated is committed.** `.gitignore#L23-L26` ignores `build/`, `assets/css/`, `assets/js/`; the tree at the pinned SHA contains 894 files under `assets/dev/` (sources) and **0** under `assets/js/`. What *is* committed is vendored third-party code: 31 `.min.js` and 80 `.min.css` under `assets/lib/**` (e.g. `assets/lib/swiper/v8/swiper.min.js`, `assets/lib/animations/**`), plus one example plugin artifact. `assets/js/**/*.asset.php` are build outputs too, not committed.

Pipeline:

1. **Build job** — `.github/workflows/build.yml` runs on pushes to `main` / `release/beta` / `release/stable` / `[0-9].[0-9][0-9]`, on `merge_group`, and as `workflow_call`, and is reused by other workflows (`build.yml#L17-L28`, `#L34-L40`). It installs Node 24.15.0, runs `./.github/workflows/build-plugin` with `BUILD_SCRIPT_PATH: './.github/scripts/build-zip.sh'`, then uploads the `elementor-<version>-<YYYYMMDD.HHMM>` directory as an artifact (`build.yml#L82-L97`).
2. **Zip script** — `.github/scripts/build-zip.sh#L20-L24` is just `npm run build && mv build elementor && zip -r elementor-<version>.zip elementor`.
3. **Plugin tree assembly** — `npm run build` ends with `scripts/vite/assemble-plugin.mjs`, which wipes `build/` and copies the ordered pattern set from `scripts/vite/shared/plugin-files.mjs` (`'**'` then ~50 negations, with explicit re-inclusions for `vendor/` and `core/files/assets/**`). Excluded: `assets/dev/**`, `packages/**`, `tests/**`, `scripts/**`, `node_modules/**`, `*.map`.
4. **Release** — `.github/workflows/latest-release.yml#L88-L99` (automated on PR merge into a release branch) and `.github/workflows/publish-release.yml` (manual / one-click) call the same build action, attach `elementor-*.zip` to a GitHub release, and publish the same tree to WordPress.org SVN (`publish-release.yml#L127-L135`, `bash .github/scripts/publish-to-wordpress-org.sh`).
5. **Both variants ship, built in the same run.** Production and development builds both run unless a flag says otherwise (`scripts/vite/build-scripts.mjs#L168-L174`: `return prodOnly ? [true] : [false, true];`), and the output name adds `.min` only in production (`PRODUCTION_SUFFIX = '.min'`, `scripts/vite/shared/entries.mjs#L3`, applied at `create-config.mjs#L84`).

Artifact evidence (4.3.5 zip, 4,190 files): under `assets/js/` — 156 `*.min.js` and 278 plain `*.js`, i.e. both flavours side by side (`accordion.js` + `accordion.min.js`, `frontend.js` + `frontend.min.js`, `editor.js` + `editor.min.js`, plus 47 entries under `assets/js/chunks/` and the same pairs under `assets/js/packages/<name>/`); 60 `*.asset.php`, 122 `*.strings.js`; under `assets/css/` — 267 `*.min.css` and 133 plain `*.css`.

Also shipped for compatibility: `assets/js/webpack.runtime[.min].js` are now one-comment placeholder files, kept only because the PHP handle `elementor-webpack-runtime` still exists and is depended on (`scripts/vite/build-scripts.mjs#L23-L29`).

## 4. Enqueue logic in PHP

There is no single "asset manager" that owns the switch; the suffix decision is spread across three idiomatic shapes, all keyed on `SCRIPT_DEBUG`:

1. **The helper.** `classes`-equivalent `Elementor\Utils::is_script_debug()` — `includes/utils.php#L152-L154`:
   ```php
   public static function is_script_debug() {
       return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
   }
   ```
   A sibling `Utils::is_elementor_tests()` (`includes/utils.php#L176-L178`, constant `ELEMENTOR_TESTS`) is OR'd in wherever widget/edit-time assets must stay readable under test.

2. **The URL builder on the module base class** — `core/base/module.php#L223-L245`:
   ```php
   final protected function get_assets_url( $file_name, $file_extension, $relative_url = null, $add_min_suffix = 'default' ) {
       static $is_test_mode = null;
       if ( null === $is_test_mode ) {
           $is_test_mode = Utils::is_script_debug() || Utils::is_elementor_tests();
       }
       …
       if ( 'default' === $add_min_suffix ) { $add_min_suffix = ! $is_test_mode; }
       if ( $add_min_suffix ) { $url .= '.min'; }
       return $url . '.' . $file_extension;
   }
   ```
   with `get_js_assets_url()` / `get_css_assets_url()` wrappers (`:259`, `:276`) and a `$add_direction_suffix` flag for the RTL `-rtl.min.css` files. Passing `true` forces minified regardless of debug — that is how widget CSS is always loaded minified (`includes/frontend.php#L564-L580`).

3. **The repeated ternary** — where assets are not built through the base class: `$suffix = Utils::is_script_debug() ? '' : '.min';` appears in `includes/frontend.php#L481` (frontend styles; used for `"frontend{$min_suffix}.css"` at `:556-L561`), `core/admin/admin.php#L121`, `core/editor/editor.php#L501` (theme UI CSS) and `:731` (`min_suffix` injected into `Editor_Loader` config), `includes/preview.php#L260,L318`, `modules/interactions/module.php#L127` and others.

Two supporting pieces:

- **Package bundles get their deps from generated PHP.** `Elementor\Core\Utils\Assets_Config_Provider` `require`s `assets/js/packages/<name>/<name>.asset.php` (a build output returning `['handle' => …, 'deps' => […]]`) and registers `…/<name>{$suffix}.js` with those deps (`core/utils/assets-config-provider.php#L19-L48`, `core/admin/admin.php#L113-L134`, same pattern at `app/app.php#L168`, `core/editor/editor.php#L731-L744`).
- **Translations ignore the suffix** because WordPress resolves translations from the file name: `Assets_Translation_Loader::default_replace_translation()` strips `(.min)?` and rewrites to `<name>.strings.js` (`core/utils/assets-translation-loader.php#L64-L72`; those `.strings.js` files are emitted in production only, `create-config.mjs#L115-L117`).
- **Frontend lazy chunks carry their own flavour switch:** the build defines `__ELEMENTOR_CHUNK_SUFFIX__` as `'.min'` in production and `''` in development, and the runtime loader in `assets/dev/js/frontend/utils/chunk-loader.js` picks `<assets>/js/chunks/<name><suffix>.js` from it (`create-config.mjs#L64-L71`, `scripts/vite/README.md#L73-L80`).

So: both variants are shipped, and `SCRIPT_DEBUG` (or `ELEMENTOR_TESTS`) decides per request which one is registered.

## 5. Dev vs production parity for contributors

- `npm run build` builds **every target twice**, development then production (`build-scripts.mjs#L168-L174`); `--dev`/`--watch` restricts to development, `--prod` to production (`:42-L43`). Because both trees land in the same directories, a contributor's local site has the same file names PHP will ask for either way.
- `npm run start` (= `build:packages && styles && scripts`) therefore produces dev+prod assets without a mode flag; `npm run watch` runs turbo dev + `styles:watch` + `scripts:watch`, and CSS watch mode stops after the Sass pass so dev CSS is unminified and unprefixed (`scripts/vite/README.md#L32-L38`).
- `mode` and `process.env.NODE_ENV` are set explicitly from the target, with a comment explaining why: without it the production `exports` condition leaks into the dev build and development warnings disappear (`create-config.mjs#L119-L125`, `README.md#L174-L180`). Development keeps `sourcemap: true` and `minify: false`.
- `test:qunit` builds only the dev qunit bundles before Karma, which loads plain `assets/js/*.js` names (`karma.conf.js#L29-L44`).
- Build guards run only outside watch mode and fail the build on outputs a human would not notice: unresolved static dynamic imports, a bundle reading a global it publishes itself, an inlined locale map (`build-scripts.mjs#L197-L202`, `scripts/vite/shared/verify-bundles.mjs#L31-L75`). Note `verify-bundles.mjs` scans **only** `*.min.js` — unminified output keeps JSDoc `@typedef` comments that a regex cannot distinguish from real calls (`verify-bundles.mjs#L20-L25`). A full `npm run build` is documented at ~43 s (`README.md#L172`).

## 6. WordPress-specific constraints and decisions visible in code/CI

- **Script-style dual shipping exists purely for `SCRIPT_DEBUG`.** The plugin carries an unminified tree *and* a minified tree per entry, and PHP switches (`§4`). The unminified tree is a real `mode: 'development'` build (dev React runtime, source maps, `jsxDEV` warnings), not a prettified production output — the failure that motivated the explicit `mode` is documented at `scripts/vite/README.md#L174-L180`.
- **Cache busting is the plugin version.** Every registration passes `ELEMENTOR_VERSION` (defined at `elementor.php#L31`, currently `4.4.0`) as `$ver`. Where the asset is a per-site generated file under `wp-content/uploads`, the version parameter is deliberately `null` and the URL instead carries its own `?ver=<generation time>` (`includes/frontend.php#L556-L561` `$has_custom_breakpoints ? null : ELEMENTOR_VERSION`, produced by `Elementor\Core\Files\Base::get_url()` → `add_query_arg( [ 'ver' => $this->get_meta( 'time' ) ] )`, `core/files/base.php#L184-L188`). Custom-breakpoint CSS is also named `custom-<file>` in uploads (`includes/frontend.php#L738-L750`, `#L795-L805`).
- **No source maps in the distributable** (`plugin-files.mjs#L22`), even though dev builds generate them — they are a local-dev aid only.
- **WordPress dependency graph is generated at build time**, via the 60 `.asset.php` files (`§3`, `§4`) rather than hard-coded `deps` arrays for package bundles.
- **Size budgets are enforced in CI, not by convention.** `tests/scripts/checkFileSize.js#L4-L7` fails a workflow if `assets/css/frontend.css` exceeds 65 KB or `assets/css/frontend.min.css` exceeds 55 KB, and the file must exist ("No target files found" is also a failure); it runs from `.github/workflows/css-file-check.yml` against a freshly built artifact. Lighthouse baselines additionally assert `"unused-javascript": [ "error", { "maxLength": 0 } ]` (`tests/lighthouse/baselines/law-firm-home-page.json#L7`), which is the audit the README credits for the decision to split the frontend entry into on-demand chunks (`scripts/vite/README.md#L65-L71`).
- **Cloud/preview builds read the same tree** (`npm run wp-playground:ci` mounts `./build`, `package.json#L55-L56`), so the artifact the CI builds *is* the thing tested.

## 7. Key takeaways vs Tutor

- Elementor **enables** JS compression in production: Rolldown → **Oxc Minifier**, options `mangle.keepNames` + `compress.keepNames` (`scripts/vite/create-config.mjs#L59-L62`). Tutor's rspack production build sets `compress: false` in `SwcJsMinimizerRspackPlugin` (`rspack.config.mjs:254`) and added a scoped esbuild pass (`minify-videojs.mjs`).
- Elementor's difference is per-file, not per-feature: **both** `<entry>.js` (dev build) and `<entry>.min.js` (production) ship in the same zip, and PHP selects with `SCRIPT_DEBUG` via `Utils::is_script_debug()` (`includes/utils.php#L152`) and `Module::get_assets_url()` (`core/base/module.php#L223-L245`).
- Tutor defines `Utils::is_script_debug()` (`classes/Utils.php:6808`) but **never calls it** and has no `.min` suffix logic; one built variant is enqueued unconditionally. Gap: no `SCRIPT_DEBUG` fallback for debugging a shipped bundle.
- Elementor ships **no source maps** and no banner/license comment in the distributable; Tutor's minimizer keeps comments matching `/translators/i` and disables compression entirely.
- Both projects hit the same root constraint: names are load-bearing at runtime — Elementor's comment names `constructor.name` branches (`create-config.mjs#L55-L62`), Tutor reserves `__` and keeps translator comments.
- Elementor's CSS pipeline is separate from its JS pipeline (Sass + PostCSS + cssnano → `.min.css` sibling, `scss-build.mjs#L148-L156`); Tutor uses `LightningCssMinimizerRspackPlugin` inside rspack.
- Elementor enforces budgets in CI (`frontend.css` 65 KB / `frontend.min.css` 55 KB, `tests/scripts/checkFileSize.js#L4-L7`); Tutor has no equivalent size gate found in this pass.
- Elementor builds dev and production in one `npm run build` (`build-scripts.mjs#L168-L174`) rather than gating production output behind a separate command.
- Not checked in this pass (would need a build to answer): Elementor's actual gzip/brotli transfer sizes, and whether Oxc minification is measurably smaller than SWC's for comparable inputs.
