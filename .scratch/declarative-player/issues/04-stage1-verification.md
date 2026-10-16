Type: task
Status: claimed
Blocked by: 01, 02, 03

## Question

Stage-1 gate: prove the invisible refactor changed nothing before templates move.

- `pnpm test:unit`, `tsc --noEmit`, `rspack --mode=production` green.
- Manual smoke against today's markup: learning-area lesson, legacy lesson, course
  intro, Alpine component path, Pro lesson notes + Take Note fullscreen.
- Provider matrix subset: html5, external, youtube opt-in, vimeo opt-in (playback,
  fullscreen, resume, strict mode, auto-next, mobile Vimeo mute).
- Record any behavioral delta. Stage 2 does not start until this passes.

## Progress

Automated half done: full vitest suite 66 green (46 in the player folder), `tsc --noEmit`
clean, eslint clean, production build green.

Delivery changed mid-gate (ADR-0002 superseded by ADR-0011): the engine is a standard
`jsEntries` IIFE entry enqueued as a classic script. The separate module config, the
`type="module"` `script_loader_tag` filter, and the namespaced `js/lazy-chunks/player/`
chunk path are deleted. The collision below was real for the two-config era and its fix
worked, but it is moot now — one config emits the adapter chunks at the standard
`js/lazy-chunks/[name].js?ver=…` path.

History, condensed:

- Both configs emitted same-named lazy chunks into `js/lazy-chunks/` — the IIFE config as
  classic JSONP (its dead copy of `elements.ts` keeps the adapter dynamic imports alive)
  and the player ES-module config as ES-module chunks. The JSONP copy clobbered the ESM
  copy, so the engine's `import()` of `tutor-player-youtube.js` resolved to a namespace
  without `__webpack_ids__` and the runtime threw.
- The earlier `jsEntries` rejection stands as an observation: folding into the IIFE config
  _while still enqueuing as `type="module"`_ breaks adapter loading
  (`document.currentScript` is null under `type="module"`, so the runtime's chunk base
  falls back to the last http(s) script on the page — Query Monitor / MathJax — and the
  chunk 404s). Fold + classic enqueue is the working pair.
- The IIFE bundles still ship a dead copy of `elements.ts` — stage-2 cleanup candidate,
  unrelated to the delivery.

Verified live after the delivery change (course page + a real html5 lesson):

- Classic `<script>` tag (no `type="module"`); engine boots; `TutorCore.player` present.
- yt probe: adapter chunk 200 from the correct plugin URL; `<youtube-video>` defines;
  player created. No `StoreError: NO_TARGET`, no console errors.
- Real lesson: legacy markup replaced by `<tutor-video-player>`, `video-skin` mounted,
  media `readyState 4`, muted playback works and resumes from the saved position. Script
  order: `tutor-core` → `tutor-learning-area` → `tutor-player`.

Manual half pending on this delivery — walk the checklist and close this ticket: four
pages (learning-area lesson, legacy lesson, course intro, Alpine path) + Pro
fullscreen/notes + provider matrix (html5, external, yt, vimeo: playback, fullscreen,
resume, strict mode, auto-next, mobile Vimeo mute).
