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

### Second pass (2026-10-17) — flips + visible window

Temporary site settings were flipped and reverted; final values verified:
`learning_mode=modern`, `course_completion_process=flexible`,
`control_video_lesson_completion=false`; user preferences untouched (`auto_play_next` was
already on). No enrollment was created — `do_enroll()` refuses the trashed course and
user 1 was already enrolled in published `#30666`.

Passed:

- Auto-next: lesson 72099 (html5, `#30666`) seeked to `duration-2` and played to the end;
  the tab navigated to `…/quizzes/q1/` = that lesson's `next_id` (72431). `ended` →
  `#autoloadContent()` → `autoload_next_course_content` → `openUrl` end-to-end.
- Strict mode: lesson 72795 (`course-v3`: enrolled, not completed) with
  `course_completion_process=strict` + `control_video_lesson_completion=true`:
  `PlayerData` carried `strict:true, control:true, enrolled:true, completed:false`;
  `seek(120)` was clamped back and the toast “Forward seeking is disabled” appeared. The
  handle's `on()` delivered `play`/`timeupdate` events throughout.
- Legacy lesson (`learning_mode=legacy`, same lesson): `.tutor-course-topic-single-body`
  and `.tutor-video-player-wrapper` render, modern `.tutor-lesson-video-wrapper` does not;
  element boots, player is created, media loads (8.1 s), console clean; the video played
  to the end and the page advanced to the next content (assignment 72797). In legacy mode
  the learning-area controller's `.tutor-lesson-video-wrapper .tutorPlayer` selector
  matches nothing, so the player is created by the legacy path through the shim — the shim
  working as designed.
- `content-title` stays empty outside fullscreen (element default), as specified. The
  fullscreen wiring (`is-fullscreen` class, title set/clear, empty-title case, Vimeo exit
  resync) has unit coverage in `lesson/player.test.ts`.

Notes:

- `window.TutorLessonPlayer.on()` (legacy shim) only carries `enterfullscreen` /
  `exitfullscreen` by design; `play`/`pause`/`ended`/`timeupdate` come from the handle
  (`TutorCore.player.create(…)`) the controller uses. Do not test event flow via the shim.

Remaining — manual pass only (cannot be driven from the harness browser):

- Fullscreen at all: `document.documentElement.requestFullscreen()` never settles (stays
  pending) even after a trusted keypress grants transient activation. The skin's
  fullscreen button, the real `fullscreenchange` path, the Vimeo exit resync and Pro Take
  Note fullscreen need a human run in a real browser.
- YouTube playback: adapter mounts and resolves (`embed/YwKRJMzvQnU`, 61 s) but
  programmatic play against the cross-origin iframe does not start — env limitation.
- Mobile Vimeo mute: no device/UA emulation available.

No test data (unchanged): external provider, course intro, Alpine path.

Test side effects (acceptable on this QA site): watch-time syncs from the playback runs on
lessons 72099/72795 and the vimeo lesson; no completion states changed.

Stage 2 landed in [05](05-declarative-templates.md) and re-ran this ladder against the
declarative markup (html5/vimeo/youtube mounts, playback + auto-next, strict clamp, legacy
lesson, shim back-compat). The manual items above stay open for both stages.
