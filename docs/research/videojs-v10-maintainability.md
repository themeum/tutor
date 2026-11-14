# Maintainability research: the lesson player after the Video.js 10 migration

- **Question:** the Plyr → Video.js 10 migration is implemented (`docs/videojs-migration-report.md`). What should be done to the player code so it stays maintainable and scalable — lifecycle, contracts, provider handling, skin patching, delivery, bundle, process?
- **Method:** read-only review of the engine (`assets/core/ts/services/player/`), its callers (lesson controller, Alpine component, legacy init), the PHP enqueue path (`classes/Assets.php`), the pinned package sources and version-exact docs in `node_modules` (`@videojs/html|store|core|media|vimeo-video@10.0.1`), and primary web sources (WordPress core dev notes, GitHub releases). No code changed. The sibling `tutor-pro` checkout was read only to check the cross-plugin contract.
- **Fixed point:** branch `feat/videojs-migration` @ `937cb9928`; `@videojs/*` pinned `10.0.1` (`package.json:112-114`).
- **Hypotheses tested:** H1 provider extensibility · H2 skin patching · H3 service lifecycle / multi-player · H4 event bridge · H5 bundle size · H6 stale Vimeo workaround · H7 WordPress module delivery · H8 process gaps. Refutations are results.

## 1. Ranked findings

| #   | Area                                                                              | H       | Verdict             | Suggested change                                                            | Impact      | Effort |
| --- | --------------------------------------------------------------------------------- | ------- | ------------------- | --------------------------------------------------------------------------- | ----------- | ------ |
| 1   | Service lifetime: disposed player stays “active”; late subscribers dropped        | H3      | confirmed           | clear `#active` on dispose; fix late subscription in service/legacy shim    | medium-high | small  |
| 2   | No JS/TS CI gate; no dependency automation                                        | H8      | confirmed           | one workflow (`tsc --noEmit`, `test:unit`); grouped `@videojs/*` update PRs | high        | small  |
| 3   | Event bridge coalesces batched store snapshots; ordering not specified            | H4      | confirmed           | document the contract; add a coalescing unit test                           | medium      | small  |
| 4   | Typed contracts drift silently (`PlayerStore`, ADR-0007 text)                     | H3-adj. | confirmed           | compile-time conformance assertion; fix `error` typing; ADR note            | medium      | small  |
| 5   | Provider detection/parsing hand-rolled; no custom-media extension point in 10.0.1 | H1      | partly refuted      | pass `src` through; keep/right-size detection table                         | medium      | small  |
| 6   | Skin CSS patch mostly duplicates upstream; touches a private static               | H2      | mostly redundant    | narrow to the cast-`unavailable` case; drop the vendor `insertRule`         | medium      | small  |
| 7   | WordPress module delivery is at its ceiling for the WP 5.3 floor                  | H7      | right call          | keep; record the revisit trigger; optionally lint-enforce ADR-0002          | low         | tiny   |
| 8   | Vimeo fullscreen workaround is still required                                     | H6      | refuted (not stale) | keep; `shortcut:` comment naming the vendor root cause; watch releases      | none now    | tiny   |
| 9   | Bundle size verified; levers are upstream features/skin                           | H5      | verified            | treat as a budget; no Tutor-side optimization; keep adapters deferred       | low         | none   |

## 2. Findings

### F1 — A disposed player stays “active”, and late subscriptions are dropped (H3)

**Verdict:** confirmed — the only defect-grade finding.

Evidence: the service holds one slot (`PlayerService.ts:28`), overwritten by every `create()` (`:59`), never cleared — `dispose()` (`Player.ts:151-161`) destroys the store and removes the element with no notification back, so `getActive()` keeps returning a dead handle. The repo’s only dispose caller is the Alpine component (`components/player.ts:81`). `PlayerService.on()` binds to whatever is active _at call time_ and returns `noop` otherwise (`PlayerService.ts:76-78`); `legacy.ts:29-44` retries subscription only on the next `.on()` call, so Pro-style early `window.TutorLessonPlayer.on('enterfullscreen')` never attaches when no player exists yet.

**Suggested change:** clear `#active` when the active handle disposes (callback into the service or an identity check in a `dispose` wrapper); make service `on()` late-bind to the next created player (or document the no-op and fix `legacy.subscribe()` to re-run on engine ready / each create). Extend `PlayerService.test.ts`.
**Impact / effort:** medium-high (stale Pro seek targets; silently lost handlers) / small.

### F2 — The engine has no CI gate and no dependency automation (H8)

**Verdict:** confirmed.

Evidence: `.github/workflows/` = `php-compatibility`, `phpstan`, `staging-build-and-deploy`, `wpcs` — no JS/TS job. No dependabot/renovate config. Dependencies are exact pins (`package.json:112-114`), so nothing tracks upstream. The migration’s safety net — 54 Vitest tests, `tsc --noEmit` (report §3.3) — runs only by hand; the Cypress spec targets a remote `baseUrl` (`cypress/e2e/frontend/lesson-video-player.cy.ts`) and cannot gate in CI.

**Suggested change:** add one workflow that runs `pnpm install --frozen-lockfile`, `pnpm exec tsc --noEmit`, `pnpm test:unit` on PRs touching `assets/`; add grouped `@videojs/*` update PRs so the young 10.x line is pullable with review.
**Impact / effort:** high (every future player change is unguarded today) / small.

### F3 — The event bridge diffs microtask-batched snapshots; transitions can collapse (H4)

**Verdict:** confirmed — by vendor design, not a bug, but the contract is unwritten.

Evidence: `@videojs/store@10.0.1` notifies through a microtask flush and the callback takes no arguments (`dist/dev/core/state.d.ts` — `type StateChange = () => void`; `subscribe` options are only `{ signal }`), so `Player.ts:55-99` sees one snapshot per flush. Mutations made in the same task (e.g. programmatic `seek()` + `play()`, or a burst in one handler) produce only the final delta: no intermediate `play`/`pause`, no per-change ordering.

**Suggested change:** state the contract at `Player.ts:70` (“events describe state at microtask boundaries, not every store mutation”) and pin it with one unit test. If a caller ever needs every transition, subscribe to the store’s slice/controller mechanism instead of widening this bridge.
**Impact / effort:** medium (subtle auto-next/resume timing bugs later) / small.

### F4 — Typed contracts drift silently: structural `PlayerStore` and ADR-0007 text (H3-adjacent)

**Verdict:** confirmed — drift exists today.

Evidence: `PlayerStore` (`Player.ts:9-30`) is deliberately structural for fake stores, but nothing checks the real store conforms: `createPlayerElement` casts `as VideoPlayerElement` (`elements.ts:146-147`) whose hand-written `store: PlayerStore` (`:35-37`) is never compared to the package. Two declared shapes are already wrong versus 10.0.1: package `error` is `ErrorLike` (`{ code, message }`, `@videojs/media` `dist/dev/core/types.d.ts:124-133`), not `Error`; package `target.media` is the capability interface `Media`, not `Element` (`@videojs/core` `dist/dev/dom/player.d.ts`). And ADR-0007’s sample lists `'embedded'|'shortcode'` providers and `isPlaying()`/`off()`, while `types/player.ts:1,22-42` ships four providers and neither method.

**Suggested change:** keep the structural type (fakes depend on it) and add a compile-time conformance assertion where the real store meets it — e.g. an `InstanceType<typeof PlayerElement>['store'] extends …` check in `elements.ts`, excluding the two verified, intentional differences (`target.media`, `error`), each with a one-line comment. A type-only package reference is free at runtime: `import type` is erased (precedent `legacy.ts:1`; `isolatedModules: true` in `tsconfig.json`) — but it is not a drop-in replacement for those two members. Either fix `error` to `ErrorLike` in the bridge payloads or normalize to `Error` at `Player.ts:94-96`. Add a one-line note to ADR-0007.
**Impact / effort:** medium (upgrade safety; Pro relies on the documented surface) / small.

### F5 — Provider handling is hand-rolled; 10.0.1 has no custom-media extension point (H1)

**Verdict:** the “adopt an upstream extension point” premise is **refuted** for 10.0.1; the duplication it left behind is confirmed.

Evidence: no public bring-your-own-media API — upstream’s `createMediaElement` PR (#2603) is still open (checked 2026-10-15), `CustomMediaConstructor` is only the internal base of shipped adapters (`@videojs/html` `dist/dev/media/youtube-video/adapter.d.ts`), and the “build your own” guide covers UI components only. Meanwhile `providers.ts:19-66` re-parses URL forms the adapters already accept: the `youtube-video` docs say watch/share/embed/Shorts/live/nocookie URLs all work, `vimeo-video` accepts watch/embed/event URLs, and `resolveAdapterType`’s docs state it “doesn’t rewrite the source … every source it recognizes … is one the matching media component accepts as its `src`” (`docs/reference/api/resolve-adapter-type.md`).

**Suggested change:** pass `source.src` through in `buildMediaSource` (`elements.ts:114-127`) instead of rebuilding canonical URLs (the rebuild also drops `t`/`list`/extra params); keep a local parser only for detection, or use `resolveAdapterType` (re-exported from `@videojs/html`) for the youtube/vimeo branch. Document that adding a provider is a two-file edit (union member + deferred loader). Re-run the provider tests after.
**Impact / effort:** medium (deletes parsing we don’t need to maintain) / small.

### F6 — The skin patch mostly duplicates 10.0.1; one rule is real (H2)

**Verdict:** mostly redundant — the cast-`unavailable` case is the only contentful button rule.

Evidence: button cores already set `hidden`: PiP hidden when not actionable (`@videojs/core` `dist/dev/core/ui/pip-button/core.js:51`), AirPlay hidden unless available (`airplay-button/core.js:54`), cast hidden only when _unsupported_ (`cast-button/core.js:52`), with Chrome availability derived from `remotePlaybackAvailability` (`:47`). The default skin hides `[hidden]` (`@videojs/html` `dist/default/define/video/skin.css`, `[hidden][hidden]` rule; no `[data-availability]` rules at all). So Tutor’s per-button selectors only add behavior for cast when _unavailable_ — visible-but-disabled upstream. Separately, `patchVideoSkinStyles` (`elements.ts:78-105`) mutates the vendor class static `VideoSkinElement.styles` via `insertRule`, an API the docs never mention; docs say skin internals are private and may change (`docs/reference/components/video-skin.md:45`). The same CSS is already applied per instance by `applyUnsupportedControlsPatch` (`:55-76`).

**Suggested change:** narrow the per-instance patch to `media-cast-button[data-availability="unavailable"]` (plus `[data-hidden]` if still used; grep shows nothing sets it today) and drop `patchVideoSkinStyles` — or, if kept, add a `shortcut:` comment naming the private dependency and version. Verify controls in Chrome/Safari after.
**Impact / effort:** medium (smaller upgrade surface; one private API touch removed) / small.

### F7 — WordPress module delivery is already right for the WP 5.3 floor (H7)

**Verdict:** confirmed as the right call; nothing to change until the floor moves.

Evidence: `readme.txt:5` — “Requires at least: 5.3”, so `wp_enqueue_script_module` (WP 6.5.0+) is not available. Today `Assets.php:70` + `mark_player_script_as_module` (`:944-951`) add `type="module"` for the `tutor-player` handle only, while boot ordering relies on the IIFE bundles loading before the deferred module (report §5.2). Upstream confirms the constraint: module IDs cannot depend on classic script handles, and importing modules before the import map breaks it (make.wordpress.org/core, 6.5 script-modules dev note).

**Suggested change:** keep as-is; record the revisit trigger in ADR-0002 (WP floor ≥ 6.5 **and** the caller handshake expressible as module dependencies) instead of building a dual path now. The “no IIFE bundle may import the engine” rule is convention-only — one `no-restricted-imports` entry would enforce it.
**Impact / effort:** low now (avoids rework) / tiny.

### F8 — The Vimeo fullscreen workaround is still required; there is nothing newer (H6)

**Verdict:** **refuted** — the hypothesis that the workaround is stale does not hold. No release after 10.0.1 exists (GitHub releases, checked 2026-10-15; 10.0.1 released 02 Oct 2026), and the root cause persists in it.

Evidence: core’s fullscreen feature reads `media.isFullscreen ?? false` (`@videojs/core` `dist/dev/dom/presentation/fullscreen.js:27`) and only re-syncs on document `fullscreenchange`/`webkitfullscreenchange` (`dist/dev/dom/store/features/fullscreen.js:26-27`), while the Vimeo adapter sets its flag synchronously in request/exit (`@videojs/vimeo-video` `dist/dev/adapter.js:333-344`) and again from the SDK echo (`:487`) — the desync the lesson controller corrects at `assets/src/js/frontend/learning-area/lesson/player.ts:185-203` (`#resyncFullscreen`).

**Suggested change:** keep the workaround; add a `shortcut:`-style comment at `#resyncFullscreen` naming the vendor files and the fix condition (“remove when the adapter’s fullscreen flag settles before the store reads it”), and watch upstream releases/issues.
**Impact / effort:** none now (prevents a wrong removal) / tiny.

### F9 — Bundle size is verified; the levers are upstream features and skin (H5)

**Verdict:** verified — no Tutor-side action.

Evidence: report §3.1 (560,603 B raw / 124,290 B gzip, measured 2026-10-13); provider adapters are deferred (`elements.ts:14-17`); the eager cost is the 15-feature `videoFeatures` preset plus `@videojs/html/video/skin` (`elements.ts:2,39`), which upstream documents as coupled (skins rely on the feature bundle). An `RSDoctor` measurement script already exists (`package.json:12`, `build-analysis`).

**Suggested change:** treat the number as a budget rather than an optimization backlog; if it ever must shrink, it is an upstream feature/skin spike measured with RSDoctor, not Tutor code. Route any new media import through `DEFERRED_MEDIA_LOADERS` — that is the one rule that keeps the HTML5 path lean.
**Impact / effort:** low (guidance only) / none.

## 3. Don’t touch

- **Migration decisions stay decided:** ADR-0006 (captions/storyboards are capability-only), Q3 (`embedded`/`shortcode` out of the engine), Q4 (native-iframe YouTube/Vimeo paths out) — do not reopen these as part of maintenance work.
- **Do not vendor the skin** (ADR-0003 fallback) or change the container-first fullscreen approach (ADR-0009) to fix a control symptom; patch narrowly (F6) or wait for upstream.
- **Do not remove the Vimeo fullscreen resync** (F8) until an upstream release fixes the adapter’s flag timing.
- **Do not build a provider/plugin abstraction** (F5) ahead of upstream’s `createMediaElement` (#2603); the current two-file edit is cheaper than a speculative layer.
- **Do not switch delivery to `wp_enqueue_script_module`** while the WP floor is 5.3 (F7).
- **Do not replace the structural `PlayerStore` wholesale** with the package type (F4): `target.media` and `error` differ deliberately; assert conformance instead.
- **Do not trade skin or features for bundle size** (F9).

## 4. Open questions

1. **Shim removal:** the report commits to removing `window.TutorLessonPlayer` “after one release cycle”. Pro still reads it (`../tutor-pro` lesson-notes seek paths and the `getActive()` fullscreen fallback). What is the actual Pro ship date that starts the clock?
2. **Upstream #2603 (`createMediaElement`):** if it merges, does it formalize a public custom-media path that replaces Tutor’s `createMediaElement`/detection split (F5)?
3. **Multi-player pages:** the service is single-slot by design (F1). Does any page mount two players (course intro + lesson)? If yes, multi-player needs an explicit policy, not just the `#active` fix.
4. **Progress-sync cadence:** is microtask-boundary `timeupdate` sufficient for Watch Progress Sync on embed providers whose events arrive in bursts (F3)? Cheap to re-check if save frequency ever changes.
5. **CI E2E:** can the Cypress suite get a local `baseUrl` so it becomes a real gate (F2), or is remote-only acceptable?
