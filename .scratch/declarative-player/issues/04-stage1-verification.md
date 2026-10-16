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

### Verified on the new delivery (2026-10-16)

- Lesson pages `html5-4`, `vimeo-4`, `dadas-2` (course
  `1-introduction-to-mindfulness-3-6-19`): classic `tutor-player.js` tag; element booted;
  `TutorCore.player` present; legacy markup replaced; `video-skin` mounted;
  `window.TutorLessonPlayer` set through the controller's `notifyReady`; no console errors.
- Adapter matrix: vimeo opt-in → `vimeo-video` mounts, src resolves to
  `player.vimeo.com/video/336812686?…`, duration 41s; youtube opt-in → `youtube-video`
  mounts, src `youtube.com/embed/YwKRJMzvQnU?…`, duration 61s; html5 → `video`
  (`sample-course-video.mp4`, 10.006s). HTML5 playback + resume were proven earlier on
  the saved-position lesson.

### Still open before this gate closes

- The browser window was hidden for this pass (`document.visibilityState === "hidden"`
  even after `tabs.focus` / `tabs.open({ focus: true })`). Chrome defers programmatic
  playback in hidden tabs, so play-through (`ended`/`timeupdate` chains), fullscreen
  clicks and Pro Take Note must be re-run with the window visible.
- fullscreen + Pro lesson notes/Take Note fullscreen: need real clicks (transient
  activation) in a visible tab.
- auto-next: needs an enrolled user — user 1 is enrolled in no courses here, so
  `autoload_next_course_content` returns an error.
- strict mode: Pro `control_video_lesson_completion` is false and no course runs
  `completion_mode: strict` → no test data (`#isStrict()` also needs `is_enrolled`).
- legacy lesson: needs `learning_mode: legacy` (site-wide) for one pass.
- mobile Vimeo mute: needs a mobile UA/device run (`core.device.isMobileDevice()`).
- external provider: no published lesson has `source: external`.
- course intro: no published course has `_video` meta.
- Alpine component path: dormant/unreferenced (ADR-0010), nothing mounts.
