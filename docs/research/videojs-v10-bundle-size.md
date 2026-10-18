# Bundle-size research: Video.js 10 (`@videojs/html` 10.0.1)

- **Question:** our lesson player migrated from Plyr to `@videojs/html` 10.0.1; the engine entry (`assets/core/ts/services/player/index.ts`) builds to **572,107 B raw / 128,029 B gzip** (`docs/videojs-migration-report.md:42`), while the vendor blog claims the default HTML video player is **97.4 kB min / 25.1 kB gzip**. What explains the difference, and what does the installed package actually ship (features preset, dev vs default builds, skin CSS, docs)?
- **Method:** read-only inspection of the installed packages (`node_modules/@videojs/html`, `node_modules/.pnpm/@videojs+{core,html}@10.0.1*/...`), their shipped `docs/`, the built Tutor artifact `assets/js/tutor-player.js`, and primary web sources (videojs.org blog/changelog/llms indexes, npm tarballs of the March beta). All bundle measurements are **reproducible local measurements** with `esbuild 0.27.0 --bundle --format=esm --platform=browser --minify --splitting` (default export conditions) and `gzip -9`; the beta comparison re-runs the identical command against the published `10.0.0-beta.2` tarballs (npm). Vendor claims are quoted as claims, not verified stats.
- **Fixed point:** branch `feat/videojs-migration`, `@videojs/*` pinned `10.0.1`; built artifact `assets/js/tutor-player.js` (572,107 B / 127,470 B with local `gzip -9`; the report recorded 128,029 B).
- **Note:** 10.1.0 exists (2026-10-12, `https://videojs.org/changelog/10.1.0.md`); everything below is 10.0.1 as installed.

## 1. Answers at a glance

| #   | Question                                 | Answer (short)                                                                                                                                                         |
| --- | ---------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | `videoFeatures` contents + subsetting API | 15 features (listed in §2). Documented API: `createPlayer({ features: [...] })` with individual named exports, the `features.*` namespace, or a preset bundle.           |
| 2   | Why 572 kB/128 gz vs the vendor's 97.4/25.1 | Unit mismatch + a real vendor-side change: the 97.4/25.1 row **reproduces only for the March beta** (measured beta.2: 98,158 B / 25,240 B gz). 10.0.1 GA is ~3.5× that (measured 340,164 B / 78,574 B gz) because GA added features/UI (settings menus, radio groups, hotkeys, i18n). Plus Tutor's rspack minimizer runs `compress: false`, so the raw number isn't a "minified" number. Production resolves `dist/default` (verified). |
| 3   | Ship HTML skin CSS separately              | Yes — `@videojs/html/video/skin.css` is a real export (`dist/default/define/video/skin.css`, 99,141 B / 10,471 gz). First-party doc exists (`bundlers.md`, `cdn.md`).        |
| 4   | First-party number for a full composition   | Only the **beta-era** table (“Video Player [HTML] 97.4 / 25.1”). No 10.0.1-era full-composition number is stated in primary sources.                                     |
| 5   | Docs that discuss size/tree-shaking         | `features`, `presets`, `why-videojs`, `bundlers` guides + the beta/GA blog posts (§6). No dedicated bundle-size page as of 2026-10-18.                                     |

## 2. `videoFeatures` in 10.0.1 and the subsetting API (Q1)

**Preset source** — `node_modules/.pnpm/@videojs+core@10.0.1_@videojs+element@10.0.1_react@18.3.1/node_modules/@videojs/core/dist/dev/dom/store/features/presets.js:18` (identical list in `dist/default`):

```js
const videoFeatures = [
  playbackFeature, playbackRateFeature, qualityFeature, audioTrackFeature,
  volumeFeature, timeFeature, sourceFeature, bufferFeature, fullscreenFeature,
  pipFeature, remotePlaybackFeature, controlsFeature, textTrackFeature,
  errorFeature, metadataFeature
];
```

15 features. Same list in the shipped docs: `node_modules/@videojs/html/docs/guides/presets.md:33` (`@videojs/html/video` — “**Feature bundle:** `videoFeatures` …”) and `docs/reference/api/html-create-player.md:178`.

**Subsetting API** (docs shipped in the package, mirrored on videojs.org):

- `createPlayer({ features: [...] })` — `docs/reference/api/html-create-player.md:12` (“It accepts a `features` array…”); three overloads (Video/Audio/Generic) at `:170–226`.
- Individual features as named exports — `docs/guides/features.md:33`: “If you're not using presets, you can create a player that has individual features.” then `import { controlsFeature, createPlayer, playbackFeature, … }`.
- The `features.*` namespace — exported from `@videojs/core/dom` (`dist/dev/dom/store/features/index.d.ts`, `feature_parts.js` `__exportAll`); the beta blog uses `features: [features.playback]` (`https://videojs.org/blog/videojs-v10-beta-hello-world-again`).
- Extending a preset bundle — `docs/guides/features.md:47–55`: `features: [...backgroundFeatures, playbackFeature]`.
- Tree-shaking claim, vendor: “With v10 if you don't import a component, it doesn't exist in your bundle” (beta post) and “what you don't import doesn't ship” (`docs/guides/why-videojs.md:65`).

**Local measurement (esbuild 0.27, minified ESM, code splitting, default conditions):**

| Entry (installed 10.0.1)                             | raw       | gzip      |
| ---------------------------------------------------- | --------- | --------- |
| `createPlayer({ features: [playbackFeature] })`      | 32,094    | 11,101    |
| `createPlayer({ features: videoFeatures })` (no skin) | 50,633    | 16,592    |
| same but `features: [features.playback]` (namespace) | 53,805    | 17,533    |

`features.playback` pulled **all 20** feature modules (esbuild metafile showed `audio-track.js … volume.js`), while the named `playbackFeature` pulled only `playback.js` — the namespace object (`__exportAll`) defeats tree-shaking. The HTML docs’ example uses named exports; the React blog example uses the namespace. No first-party statement about this difference was found → the namespace cost is a local measurement, not a documented fact.

## 3. Attribution for our 572,107 B / 128,029 B build (Q2)

### 3.1 `dist/dev` vs `dist/default`, and what production resolves

- Both packages ship two trees. `node_modules/@videojs/html/package.json:44–161` maps export conditions: `"development"` → `dist/dev`, `"default"` → `dist/default` (e.g. `"."` at `:46–50`, `"./video/*"` at `:67–71`). **There is no `"production"` condition.**
- Rspack (this repo's bundler, `rspack.config.mjs`) injects the condition `"development"` or `"production"` depending on `--mode` (`node_modules/@rspack/core/dist/index.js:6097`). Production mode → `"production"` (no match) → falls through to `"default"` → `dist/default`. Rspack also does no JS minification of the raw files themselves; both trees are already-bundled (rolldown) module builds and **the consuming bundler minifies** (no `*.min.js` anywhere in either tree).
- Verified in our artifact: the module-path comments in `assets/js/tutor-player.js` reference `dist/default` and never `dist/dev` (`grep -c "dist/dev"` → 0).
- `dist/dev` is *not* an unminified duplicate: comparing file pairs (JS content, ignoring sourcemap links) 280/289 core files and 505/545 HTML files are byte-identical. Differences:
  - dev files end with `//# sourceMappingURL=<file>.js.map` (default has no maps);
  - core keeps dev-only instrumentation in dev, stubbed in default — e.g. `core/dist/dev/dom/utils/log.js` warns, `core/dist/default/dom/utils/log.js` is `function logMissingFeature(displayName, featureName) {}`; `dom/feature.js` warns on duplicate config keys in dev only;
  - HTML dev keeps the inline skin CSS **unminified**: `dist/dev/define/video/skin2.js` = 103,254 B vs `dist/default/define/video/skin2.js` = 83,886 B (same stylesheet as `skin.css`).
- Measured effect of resolving dev: same composition bundled with `--conditions=development` → 358,597 B / 80,324 B gz vs 340,164 B / 78,574 B gz for `dist/default` (≈ +5 %). So build-variant resolution is **not** the main explanation.

### 3.2 What `dist/default/presets/video` and `dist/default/define/video` contain

`node_modules/@videojs/html/dist/default/presets/video/`:

| File               | What it is                                                        | raw       |
| ------------------ | ----------------------------------------------------------------- | --------- |
| `player.js`        | `createPlayer({ features: videoFeatures })` + `<video-player>`    | 769 B     |
| `skin.js`          | `VideoSkinElement` (`<video-skin>`), calls `createShadowStyle`    | 571 B     |
| `neutral-skin.js`  | Neutral skin class (`<video-neutral-skin>`)                       | 633 B     |
| `compat-skin.js`   | Compat skin class (`<video-compat-skin>`)                         | 623 B     |
| `index.js`         | re-exports all of the above + `videoFeatures`                     | 416 B     |

`node_modules/@videojs/html/dist/default/define/video/` (registration entry points + inlined CSS):

| File                                             | raw     | gzip    |
| ------------------------------------------------ | ------- | ------- |
| `player.js` (registers `<video-player>`)         | 214     | 156     |
| `skin.js` (registers `<video-skin>`)             | 263     | 174     |
| `skin2.js` (default skin CSS as a JS string)     | 83,886  | 9,845   |
| `skin.css` (same CSS, standalone export)         | 99,141  | 10,471  |
| `neutral-skin2.js` / `compat-skin2.js`           | 84,623 / 76,445 | — |
| `ui.js` / `neutral-ui.js`                        | 3,004 / 2,777 | — |

### 3.3 Skin CSS lives inside the JS (constructable stylesheets)

`dist/default/presets/video/skin.js` imports the CSS string from `define/video/skin2.js` and does `this.styles = createShadowStyle(skin_default)` (`@videojs/utils` `dist/dom/shadow-styles.js`). Vendor docs confirm: “Packaged HTML skins inline their CSS into JavaScript” (`docs/guides/browser-support.md:54`); “The skin bundles inline their CSS, so no `<link>` is required” (`docs/guides/cdn.md:81`). In the minified 10.0.1 entry chunk the CSS string is **83,690 B of 340,164 B (24.6 %)**; as a standalone file `skin2.js` gzips to 9,845 B — a quarter of the vendor's claimed 25.1 gz, by itself.

### 3.4 10.0.1 (GA) grew a lot after the March beta — this is the real vendor-side change

Same file (installed) vs the published `10.0.0-beta.2` tarball:

| Evidence                                  | beta (2026-03-10) | 10.0.1 (GA) | Source |
| ----------------------------------------- | ----------------- | ----------- | ------ |
| `videoFeatures` entries                   | **11** (no quality, audioTrack, remotePlayback, metadata) | **15** | beta `@videojs/core` `dist/default/dom/store/features/presets.js` vs installed `…/dist/dev/dom/store/features/presets.js:18` |
| Default-skin UI module imports            | **14** `../ui/*` imports in `define/video/skin.js` | **58** `define/ui/*` imports + 23 icons in `internal/skins/default-video/register.js`, incl. `menu*`, `quality-radio-group`, `playback-rate-radio-group`, `captions-radio-group`, `audio-track-radio-group`, `hotkey`, `gesture` | package files, both versions |
| Post-beta changelog additions             | — | beta.16 hotkeys + gestures · beta.17 `media-gesture` + default-skin bindings · beta.24 menu system + playback-rate menu · beta.25 quality menu, **settings menu**, AirPlay · beta.26 **i18n with 40+ locales** | `https://videojs.org/changelog/llms.txt` |

The beta post itself says settings menus are “still on their way”. Compositions, identical command and tooling:

| Composition (entry: `video/player` + `video/skin`) | raw      | gzip     |
| -------------------------------------------------- | -------- | -------- |
| `@videojs/html` + friends **10.0.0-beta.2**        | 98,158   | 25,240   |
| `@videojs/html` + friends **10.0.1**               | 340,164  | 78,574   |

The beta figure reproduces the blog's table row “Video.js v10 Video Player [HTML] 97.4 / 25.1” within ~1 %; the 10.0.1 figure does not. **So the 25.1 gz claim describes the March beta, not the installed GA package.**

Where the 340,164 B entry chunk goes (10.0.1, esbuild metafile): `@videojs/html` 194,394 B (57.3 %; CSS string 83,690 B, default-video template 17,791 B), `@videojs/core` 113,762 B (33.5 %; UI primitives incl. menu/popover/positioner), `@videojs/utils` 16,654 B, `@videojs/store` 5,245 B, `@lit/context` 3,595 B, `@videojs/element` 3,060 B, `@videojs/media` 2,785 B. Locale packs are **not** in the entry chunk (only `i18n/locales/en.js`; the rest load as separate chunks — verified in our rspack bundle: `i18n/locales/en.js` only, no Greek/other locale strings). Provider adapters are deferred the same way.

### 3.5 Our build, measured like-for-like

- Our real engine entry, fully minified with esbuild (same flags; includes the first-party service layer): **362,775 B raw / 86,985 B gz eager** (entry 330,434 B + one static shared chunk 32,341 B that is shared with the lazily-loaded adapter chunks; locale chunks lazy). That is ~+6.6 % raw over the vendor composition's 340,164 B — the first-party service layer is ~22 kB of TS source (`Player.ts`, `PlayerService.ts`, `elements.ts`, `providers.ts`, `boot.ts`, `index.ts`), and the rest of the delta is layout/tree-shake differences.
- The rspack artifact is 572,107 B raw because `rspack.config.mjs:252` configures the swc minimizer with `compress: false` and comments are retained in the output (413 `//#region` markers, per-module path comments). That raw number is not a “minified kB” number; the gzip delta (127.5–128.0 kB vs 87.0 kB) is the same `compress:false` effect.
- **Conclusion:** the gap vs the vendor claim decomposes as (1) beta→GA library growth, vendor-side: 25,240 → 78,574 B gz for the same composition (+53.3 kB gz; the skin's inlined CSS alone went 14.8 → 83.7 kB raw), and (2) Tutor-side tooling: the rspack artifact is 572,107 B raw / ~127.5 kB gz where a full minify of the identical logical graph is 362,775 B / 86,985 B gz (+47 % gz), and its raw bytes are not “minified kB”. None of it is first-party code bloat.

## 4. Shipping the HTML skin CSS separately (Q3)

- Export exists: `"./video/*.css": "./dist/default/define/video/*.css"` (`node_modules/@videojs/html/package.json:66`); file `dist/default/define/video/skin.css` = 99,141 B / 10,471 gz (also `neutral-skin.css`, `compat-skin.css`).
- First-party docs:
  - `docs/guides/bundlers.md:15` — “Use Vite 4.1 or later; … Earlier versions can't resolve exported stylesheets such as `@videojs/html/video/skin.css`.”
  - `docs/guides/bundlers.md:17` — “Packaged HTML skins import their own styles, so an HTML application using `@videojs/html/video/skin` doesn't need to import `skin.css` separately. The CSS limitation matters only when your application imports that stylesheet directly.”
  - `docs/guides/browser-support.md:54` — packaged HTML skins inline CSS into JS (relevant when a CSP forbids constructable stylesheets or when adding skin source).
  - `docs/guides/cdn.md:81–85` and `docs/guides/self-hosting.md:124–126` — the standalone sheets are for declarative shadow DOM/SSR rendering; linking them can't style inside an existing shadow root.
- Practical read for Tutor: since we register `<video-skin>` from JS, the CSS already ships inside the bundle; importing `skin.css` as well would duplicate it unless we move to a server-rendered/declarative skin.

## 5. First-party numbers for a full composition (Q4)

- The **only** vendor number for `videoFeatures` + default skin is the March beta table: “**Video.js v10** Video Player \[HTML\] | 97.4 | 25.1” (`https://videojs.org/blog/videojs-v10-beta-hello-world-again`, verified; reproduced here for beta.2 at 98,158 B / 25,240 B gz). The same post claims “**88% reduction in default bundle size**” vs v8's default and “the v10 default video player (HTML) is still **66% smaller** than the size of the previous version” with ABR removed — both beta-era claims.
- The GA post (`https://videojs.org/blog/videojs-10.md`, 2026-10-01) claims “Our RC benchmarks showed a 60 % reduction from Video.js v8's default bundle” — a different baseline (v8 default includes ABR), not a 10.0.1 composition number.
- **10.0.1-era number for the full composition: not stated in primary sources.** The measured values in §3 (340,164 B / 78,574 B gz entry chunk) are our local measurements, not vendor claims.

## 6. Where the docs discuss this (Q5)

All verified live 2026-10-18; the same pages ship inside the package under `node_modules/@videojs/html/docs/`.

- Feature composition / subsetting: `https://videojs.org/docs/framework/html/guides/features.md` (package: `docs/guides/features.md`).
- Presets + “skins depend tightly on their feature bundle”: `https://videojs.org/docs/framework/html/guides/presets.md` (`docs/guides/presets.md:51`).
- “Add only what you need” / tree-shaking rationale: `https://videojs.org/docs/framework/html/guides/why-videojs.md` (`docs/guides/why-videojs.md:63–79`).
- Package exports, CSS, bundler requirements: `https://videojs.org/docs/framework/html/guides/bundlers.md`; CDN/self-hosting: `.../guides/cdn.md`, `.../guides/self-hosting.md`.
- `createPlayer` reference: `https://videojs.org/docs/framework/html/reference/api/html-create-player.md`.
- Bundle tables and the “<5 kB gzipped” hello world (React): `https://videojs.org/blog/videojs-v10-beta-hello-world-again` (2026-03-10).
- GA announcement (60 % vs v8 default): `https://videojs.org/blog/videojs-10.md` (2026-10-01).
- Version history used for the beta→GA diff: `https://videojs.org/changelog/llms.txt` (entries beta.16/17/24/25/26).
- **No dedicated bundle-size or tree-shaking page exists** in the docs index (`https://videojs.org/docs/framework/html/llms.txt`, checked 2026-10-18).

## 7. Not stated in primary sources

- No vendor statement of which build variant (`dist/dev` vs `dist/default`) the 97.4/25.1 measurement used, or which bundler/minifier/toolchain produced it (the beta post documents neither).
- No 10.0.1 release note or doc says the default composition is now ~78 kB gz; the growth is documented only as feature/changelog additions (§3.4).
- No first-party statement about the `features.*` namespace vs named-export tree-shaking difference.
- No first-party doc on shipping `@videojs/html/video/skin.css` **for the bundled (non-CDN/SSR) path** beyond “you don't need to import it separately” (`bundlers.md:17`).

## 8. Caveats / open questions

1. All numbers are from `esbuild 0.27.0` + `gzip -9` on one machine; different minifiers/bundlers move the raw size by a few percent, not by the 3× in question. The beta-vs-GA reproduction used npm-published tarballs (`10.0.0-beta.2`), not the exact beta the blog screenshotted (same day, 2026-03-10).
2. Rspack's minimizer settings (`compress: false`) were not re-measured in isolation; the claim “raw number is not minified” rests on the config (`rspack.config.mjs:252`) plus retained comments/region markers in the artifact.
3. If the budget ever needs to come down, the levers are identical to the maintainability study's F9: fewer preset features, a different skin, or shipping the skin CSS separately — all upstream/trade-off choices, not Tutor code. Re-measure with `pnpm build-analysis` (RSDoctor) before/after.
4. 10.1.0 (2026-10-12) adds a Scaffold skin and other changes; if the pin moves, re-run §3.4's measurement, as growth is the trend.
