# Deliver the player engine as a standard IIFE entry

**Status**: accepted (supersedes ADR-0002)

## Context

ADR-0002 built the player as a separate ES-module rspack config on a vendor-docs claim
that non-module (IIFE) output fails with `StoreError: NO_TARGET`. The split carried real
cost:

- One build cannot mix `output.module`, so the engine needed its own config, output
  rules, and clean handling.
- Both configs emitted same-named lazy chunks into `js/lazy-chunks/`: the classic JSONP
  copy clobbered the ESM copy and broke yt/vimeo adapter loading (ticket 04). The
  workaround namespaced the engine's chunks under `js/lazy-chunks/player/` and kept the
  IIFE config's `output.clean` away from both paths.
- `Assets.php` needed a `script_loader_tag` filter to force `type="module"` — the
  supported WordPress floor (5.3) predates `wp_enqueue_script_module`.

The premise did not survive contact: the vendor packages contain no IIFE/non-module
statement (`StoreError: NO_TARGET` is a store-target state error in `@videojs/store`,
not a module-format error), and the IIFE bundles already evaluated the vendor
`createPlayer()` at module scope on every page without issue.

## Decision

The engine is a standard `jsEntries` entry (`assets/core/ts/services/player/index.ts`),
built as IIFE like every other Tutor script and enqueued as a classic footer script. The
`script_loader_tag` filter, the separate module config, its namespaced chunk path, and
the clean keep-entries are deleted.

## Considered Options

- **Keep the module delivery.** Rejected: the extra config and workarounds bought
  nothing — the stated blocker is empirically false for our bundled output.
- **Fold into `jsEntries` but keep `type="module"`.** Rejected: the IIFE runtime derives
  its chunk base from `document.currentScript`, which is null under `type="module"`; the
  runtime falls back to the last http(s) script on the page (Query Monitor on lesson
  pages, MathJax on course pages) and the adapter chunk 404s (`ERR_BLOCKED_BY_ORB`,
  `ChunkLoadError`). The fold and the classic enqueue are a pair.

## Consequences

- One rspack config; adapter chunks return to the standard
  `js/lazy-chunks/[name].js?ver=…` path; the clean keep-list shrinks back to
  `assets/core/`.
- The engine is no longer deferred. On course pages it is enqueued before `tutor-core`
  (learning-area pages: after), so registration falls back to the imported
  `ComponentRegistry` instance when the bridge is absent; either path exposes the
  service on `window.TutorCore.player`, and every consumer reads it from there or waits
  for `PLAYER_ENGINE_READY`.
- Verified live (course page + a real html5 lesson): classic script tag, engine boots,
  adapter chunk 200 from the correct URL, `<youtube-video>` defines, the lesson mounts
  `video-skin`, plays, and resumes from the saved position; no `StoreError: NO_TARGET`,
  no console errors.
- The `?ver` is unchanged, so browsers holding the module-era file need a hard reload —
  local-only while 4.2.0 is unreleased.
