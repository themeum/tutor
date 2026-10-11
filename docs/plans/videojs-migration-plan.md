# Plan: migrate lesson playback from Plyr to Video.js 10

Status: approved — decisions locked (see below).

## Goal

Replace Plyr with Video.js 10 (`@videojs/html`) for Lesson Videos, on every init path,
without regressing Strict Mode, resume-from-Best-Watch-Time, Watch Progress Sync,
auto-next, mobile Vimeo mute, YouTube poster handling, or the `is-fullscreen` wrapper
class — while keeping fullscreen available for every Provider, YouTube included (ADR-0009).

Do it as a **full migration**: one designed API on TutorCore, one engine, one controller,
no duplicated players, and Pro migrated onto the new API.

## Scope

- Providers: HTML5/self-hosted, external URL, YouTube, Vimeo, embedded iframe, shortcode.
- Paths: Learning Area, Legacy Lesson Page, Alpine `player` component.
- Plugins: `tutor` (free) owns the engine; `tutor-pro` lesson notes are migrated too.

## Non-goals

- Caption or Scrub Preview authoring UI, storage, or generation (ADR-0006).
- Note markers on the progress bar (explicitly dropped).
- Redesigning the lesson completion flow.

## Architecture

Two layers, split along the ESM boundary:

1. **Engine — `TutorCore.player` service** (ADR-0007). Owns Video.js, builds the
   custom-element tree from the existing `.tutorPlayer` markup (ADR-0004), and returns a
   generic typed `TutorPlayer` handle. Provider-agnostic; usable for the course intro
   video too. Fullscreen is enabled for all Providers and targets the container (ADR-0009).
2. **Controller — `LessonPlaybackController`.** Subscribes to a `TutorPlayer` handle and
   applies lesson rules: Strict Mode seek guard, completion percentage, Watch Progress
   Sync, resume from Best Watch Time, auto-next, mobile Vimeo mute, YouTube poster.

`@videojs/*` is ESM-only (ADR-0002): loading its output from an IIFE script fails at
runtime (`NO_TARGET`). That constrains the **entry graph**, not the folder, so the engine
is co-located with the other TutorCore services and reached only from its own ES entry:

- The **engine implementation** (imports `@videojs/*` + the packaged skin) lives in
  `assets/core/ts/services/player/`, whose `index.ts` is the ES-module rspack entry and
  assigns `window.TutorCore.player`. `assets/core/ts/index.ts` must never import it.
- The **types** (`TutorPlayer`, `PlayerProvider`, `PlayerEvent`, `PlayerService`) live in
  `assets/core/ts/types/player.ts` — types only, no runtime imports, safe for the IIFE
  bundles that consume them.

`window.TutorLessonPlayer` is a deprecated adapter over `TutorCore.player.getActive()`
(ADR-0007, one deprecation cycle).

## File layout

### Engine (ES-module entry under core services) — new

| File                                                 | Purpose                                                                                                                                                                                                      |
| ---------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `assets/core/ts/services/player/index.ts`            | ES-module rspack entry: imports `@videojs/html/video/player` + `@videojs/html/video/skin` (packaged Default skin) + media packages, registers the player element classes, assigns `window.TutorCore.player`. |
| `assets/core/ts/services/player/elements.ts`         | Single `createPlayer` wrapper: `tutor-video-player` with the full `videoFeatures` bundle for every Provider (ADR-0009).                                                                                      |
| `assets/core/ts/services/player/PlayerService.ts`    | Implements `PlayerService`: `create`, `getActive`, and active-player proxies.                                                                                                                                |
| `assets/core/ts/services/player/Player.ts`           | `TutorPlayer` handle over the Video.js store (play/seek/volume/events/fullscreen).                                                                                                                           |
| `assets/core/ts/services/player/providers.ts`        | Pure Provider/source detection from markup (unit-tested).                                                                                                                                                    |
| `assets/core/ts/services/player/compat.ts`           | Deprecated `window.TutorLessonPlayer` adapter + `tutorLessonPlayerReady`.                                                                                                                                    |
| `assets/core/ts/services/player/__tests__/*.test.ts` | Unit tests for providers/handle/service/compat.                                                                                                                                                              |

> Vendored skin source (`components.json` + `assets/core/ts/services/player/skin/**`) is
> added **only** if the ADR-0009 spike fails and we fall back through ADR-0008 to ADR-0003.

### Shared types — new

| File                             | Purpose                                                                                                                     |
| -------------------------------- | --------------------------------------------------------------------------------------------------------------------------- |
| `assets/core/ts/types/player.ts` | `TutorPlayer`, `PlayerProvider`, `PlayerEvent`, `PlayerEventPayload`, `PlayerService`, `PlayerOptions`. No runtime imports. |

### Controller + entry points — changed

| File                                                                | Change                                                                                                                                     |
| ------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| `assets/src/js/frontend/learning-area/lesson/player.ts`             | Rewrite as `LessonPlaybackController` (rules) + `initializeLessonPlayer()` (calls `TutorCore.player.create`).                              |
| `assets/src/js/front/tutor-front.js`                                | Replace the jQuery `videoPlayer`/`new Plyr` block with `TutorCore.player.create()` + controller wiring.                                    |
| `assets/core/ts/components/player.ts`                               | Alpine component calls `TutorCore.player.create()`; keep `tutor-player-ready`.                                                             |
| `assets/core/ts/index.ts`                                           | Register nothing new at runtime; typing only (engine self-registers).                                                                      |
| `assets/core/ts/types/index.ts` + `assets/core/ts/declaration.d.ts` | Add `player: PlayerService`; retype `TutorLessonPlayer` as deprecated.                                                                     |
| `assets/src/js/frontend/learning-area/index.ts`                     | No engine import (this is an IIFE bundle); lesson init reads `window.TutorCore.player` at runtime. The engine is enqueued by `Assets.php`. |

### Pro — changed (full migration)

| File                                                   | Change                                                                                                                  |
| ------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------- |
| `tutor-pro/assets/src/js/notes/learning-area-notes.ts` | `TutorCore.player.getCurrentTime()/seek()` instead of `window.TutorLessonPlayer.currentTime`.                           |
| `tutor-pro/assets/src/js/notes/lesson-notes.js`        | `TutorCore.player.on('fullscreenchange', ({ element }) => …)` and `getCurrentTime()/seek()`; drop the raw-global reads. |

### Build + assets — changed

| File                                               | Change                                                                                                                                                                                          |
| -------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `package.json`                                     | Add `@videojs/html`, `@videojs/youtube-video`, `@videojs/vimeo-video` (pinned `10.0.1`); add Vitest.                                                                                            |
| `rspack.config.mjs`                                | Add the player ES-module entry → `assets/core/ts/services/player/index.ts`; production returns `[iifeConfig, playerModuleConfig]` (one config can't mix `output.module` with the IIFE entries). |
| `assets/src/js/frontend/types/index.ts`            | Replace `isYouTubePlyr`/`isVimeoPlyr` with `Provider` helpers over the handle.                                                                                                                  |
| `classes/Assets.php`                               | Stop enqueuing `tutor-plyr`; enqueue the player bundle with `type="module"` via `script_loader_tag`.                                                                                            |
| `assets/src/scss/frontend/components/_player.scss` | Drop Plyr overrides not carried over.                                                                                                                                                           |
| `assets/lib/plyr/*`                                | Delete after verification.                                                                                                                                                                      |

## Phases

0. **Spike + scaffold.** Add the three pinned deps via pnpm; add the ES-module rspack
   config; add the module enqueue in `Assets.php`. Then run the ADR-0009 spike: build a
   YouTube lesson with the full `videoFeatures` bundle on the packaged Default skin and
   press fullscreen, asserting (1) `document.fullscreenElement` is the player **container**,
   not the `<iframe>`, and (2) YouTube's chrome does **not** appear.
   - **Spike passes** → ship the single `tutor-video-player` class; the engine imports the
     packaged `@videojs/html/video/skin`. Fullscreen stays for every Provider.
   - **Spike fails (chrome appears)** → first fall back to ADR-0008: a second YouTube-only
     element class from `videoFeatures` minus `fullscreenFeature`. If that is not enough,
     fall back to ADR-0003 and vendor the skin source:
     ```bash
     pnpm dlx shadcn@latest registry add @videojs=https://shadcn.videojs.org/r/html/\{name\}.json
     pnpm dlx shadcn@latest add @videojs/video --overwrite --yes
     ```
     shadcn needs a `components.json` whose `aliases.components` points at
     `assets/core/ts/services/player` (via a new `@/*` tsconfig path and a matching rspack
     alias) so the skin lands next to the engine.

1. **Shared types.** Write `assets/core/ts/types/player.ts` and wire the `TutorCore` /
   `window` declarations. No runtime.
2. **Engine.** Implement `providers.ts` → `Player.ts` → `PlayerService.ts` → `index.ts`
   against the documented store API; register `window.TutorCore.player`. Unit-test the
   pure parts.
3. **Controller.** Rewrite `LessonPlaybackController` on top of a `TutorPlayer` handle;
   unit-test it with a fake handle.
4. **Paths.** Wire Learning Area, Legacy Lesson Page, and the Alpine component to
   `TutorCore.player.create()` + the controller.
5. **Pro.** Migrate both `tutor-pro` notes files onto `TutorCore.player` as **separate
   commits** (same migration, independently reviewable); run Pro's build.
6. **Cleanup.** Remove the Plyr enqueues and vendored files; delete dead SCSS; drop the
   deprecated global after the cycle (or keep the adapter, per decision).
7. **Verify.** Run the full test ladder below.

## API mapping (for the port)

| Behaviour               | Plyr today                                                   | New API                                                  |
| ----------------------- | ------------------------------------------------------------ | -------------------------------------------------------- | ------- | --------- | -------------------------------------------- |
| play / pause            | `player.play()/pause()`                                      | `handle.play()/pause()`                                  |
| current time / duration | `player.currentTime/duration`                                | `handle.getCurrentTime()/getDuration()`                  |
| resume / seek           | `embed.seekTo` / `media.currentTime =`                       | `handle.seek(t)`                                         |
| mute / volume           | `muted`, `volume`, `mute()/unmute()`                         | `handle.setMuted()/setVolume()`                          |
| fullscreen              | `on('enterfullscreen'/'exitfullscreen')`, `event.srcElement` | `on('fullscreenchange', ({ fullscreen, element }) => …)` |
| ready / events          | `player.on('ready'                                           | 'play'                                                   | 'pause' | 'ended')` | `handle.on(event, cb)` (returns unsubscribe) |
| captions toggle         | `toggleCaptions()`                                           | engine-internal (`store.toggleSubtitles()`)              |
| active player           | `window.TutorLessonPlayer`                                   | `TutorCore.player.getActive()`                           |

Strict Mode seek guard: the controller subscribes to `timeupdate`; when the playhead
advances beyond `maxSeekTime` without playback, it calls `handle.seek(maxSeekTime)`,
toasts, and keeps the button state in sync (Video.js has no cancellable `seek` event).

## Test plan

No player test exists today: Cypress is the only JS runner and there is no unit runner.
The plan is layered so the non-browser logic is covered fast and the browser paths are
covered by E2E plus a manual matrix.

### Layer 1 — Unit (new runner: Vitest)

Pure/headless logic gets unit tests so they fail without a browser:

- `providers.ts`: detection for each markup shape → correct Provider, source URL, poster
  (html5 `<video>` + `<source>`, external URL, `.plyr__video-embed` YouTube, Vimeo,
  embedded iframe, shortcode). Includes negative cases.
- `Player.ts` handle: event fan-out, `seek` clamping, volume/mute round-trips, `dispose`
  unsubscribes — against a fake store.
- `PlayerService.ts`: `create` registers the active player; `getActive` returns the last
  live one; proxies forward to it; `dispose` clears it.
- `LessonPlaybackController`: with a fake `TutorPlayer`, assert Strict Mode blocks forward
  seek (calls `seek` back + warns), completion-percentage math, sync cadence, resume, and
  auto-next (`autoload_next_course_content`).
- `compat.ts`: the deprecated adapter maps `currentTime` get/set, `play()`, and re-emits
  `enterfullscreen`/`exitfullscreen` with `srcElement`.

Add `"test:unit": "vitest run"` and keep `"test": "cypress run"` for E2E.

### Layer 2 — E2E (Cypress, extends existing suite)

New `cypress/e2e/frontend/lesson-video-player.cy.ts`, using an HTML5/self-hosted lesson
fixture (real, local media — no third-party network):

1. Player mounts; `.plyr__video-embed` and raw `window.Plyr` are gone.
2. The `tutor-video-player` element exists; a media child exists.
3. `window.TutorCore.player.getActive()` returns the live handle; `player.on('ready')` fires.
4. Fullscreen: `is-fullscreen` toggles and a `fullscreenchange` payload carries `element`
   (Pro compat), plus the deprecated `TutorLessonPlayer` adapter still works.
5. Strict Mode: forward seek is clamped back and a warning is shown; the Complete button
   stays disabled until the required percentage.
6. Resume: seeded `best_watch_time` resumes at that position.
7. Auto-next: on `ended` with autoload enabled, `autoload_next_course_content` is called.

Pro note-affordance smoke (Learning Area) — a `video-note` can be created and clicking it
seeks via `TutorCore.player`.

Third-party Providers (YouTube/Vimeo) are covered manually, not in CI — their playback is
network-dependent and flaky.

### Layer 3 — Manual matrix (required before merge)

Run on Chrome, Firefox, Safari, and iOS Safari, for each Provider:

| #   | Check                                                               | html5 | external | YouTube | Vimeo | embedded | shortcode |
| --- | ------------------------------------------------------------------- | ----- | -------- | ------- | ----- | -------- | --------- |
| 1   | Plays and seeks                                                     |       |          |         |       |          |           |
| 2   | Fullscreen targets the container, not the iframe; no YouTube chrome |       |          |         |       |          |           |
| 3   | Resume at Best Watch Time                                           |       |          |         |       |          |           |
| 4   | Watch Progress Sync fires every 10s while playing                   |       |          |         |       |          |           |
| 5   | Strict Mode blocks forward seek + gates Complete                    |       |          |         |       |          |           |
| 6   | Auto-next on ended                                                  |       |          |         |       |          |           |
| 7   | Mobile Vimeo starts muted, unmutes on play                          | n/a   | n/a      | n/a     |       | n/a      | n/a       |
| 8   | Embedded Captions appear where the Provider has them                | n/a   | n/a      |         |       | n/a      | n/a       |
| 9   | Scrub Previews appear where a Storyboard Track is present           |       |          |         |       |          |           |
| 10  | Lesson preview (not enrolled) plays without sync                    |       |          |         |       |          |           |
| 11  | Pro lesson notes + in-fullscreen note button                        |       |          |         |       |          |           |

### Layer 4 — Build / regression gates

- `pnpm build-dev` (free) and the `tutor-pro` build both succeed; the player bundle is
  emitted as an ES module (`type="module"` in the enqueued `<script>`), and the existing
  IIFE entries still build.
- Full existing Cypress suite (`pnpm test`) stays green.
- `pnpm lint` / typecheck clean.
- No request to `cdn.plyr.io` remains at runtime.

## Risks

- **Browser floor** (ADR-0001): older-browser and webview regressions. Mitigation: call
  it out in the release notes; detect unsupported and fall back to native controls.
- **Cross-plugin release ordering**: `tutor-pro` must ship the API migration no earlier
  than the free engine. Mitigation: keep the deprecated `TutorLessonPlayer` adapter for
  one cycle so either order works.
- **ESM/IIFE boundary**: the engine must never be imported by an IIFE bundle or the build
  breaks (`NO_TARGET`). Mitigation: shared types only in `assets/core/ts/types/player.ts`.
- **YouTube chrome in container fullscreen** (ADR-0009): it is undocumented whether a
  container-fullscreened YouTube iframe keeps its chrome hidden. Mitigation: the Phase 0
  spike on each YouTube path; if chrome appears, escalate to ADR-0008 (omit the feature)
  then ADR-0003 (vendor the skin).
- **Undocumented feature removal** (only if the ADR-0008 fallback is used): removing a
  feature from under a packaged skin is not a documented path and could shift between
  releases.
- **Skin source drift** (only if the ADR-0003 fallback is used): Shadcn updates overwrite
  local files. Mitigation: commit and review as diffs.

## Rollback

Plyr is left in the tree until Layer 3 passes. If blocked, re-enqueue `tutor-plyr` and
revert the controller/entry changes; the engine and service are additive and inert until
`TutorCore.player.create()` is called.

## Locked decisions

1. **ESM enqueue:** a `script_loader_tag` filter adds `type="module"` (minimum WP is 5.3,
   so `wp_enqueue_script_module` is unavailable).
2. **Rollout:** hard cutover; no feature flag. Plyr stays in-tree only until the manual
   matrix passes.
3. **Unit runner:** add Vitest for Layer 1.
4. **API:** expose playback as `window.TutorCore.player` (ADR-0007); keep
   `window.TutorLessonPlayer` as a deprecated adapter for one cycle.
5. **Pro migration:** same migration, separate commits; `tutor-pro` notes move onto
   `TutorCore.player` and the raw global reads are dropped.
6. **Fullscreen event:** a single `fullscreenchange` event carrying
   `{ fullscreen, element }` (not Plyr's `enterfullscreen`/`exitfullscreen`+`srcElement`).
7. **Skin:** Default, packaged. Fullscreen stays for every Provider and targets the
   container (ADR-0009); the only fallbacks, if the Phase 0 spike shows YouTube chrome, are
   omitting the fullscreen feature (ADR-0008) then vendoring the skin source (ADR-0003).
