Type: task
Status: done
Blocked by: 04

## Question

Stage 2: flip the four templates to declarative markup (ADR-0010).

- `templates/single/video/{html5,external_url}.php`: emit
  `<tutor-video-player><video-skin><video poster playsinline controls><source …></video></video-skin></tutor-video-player>`;
  drop `class="tutorPlayer"`; keep the `.tutor-video-player` wrapper, spinner and
  tracking input; `controls` stays for no-JS/script-failure playback (the element
  removes it on connect); do not set `content-title` (the element owns the default).
- `youtube.php` / `vimeo.php`: in the `disable_default_player_*` opt-in branch emit
  `<youtube-video src="…">` / `<vimeo-video src="…">` with the raw user URL — no
  canonicalization (adapters accept watch/share/embed forms). The non-opt-in branch is
  untouched.
- `tutor_single_lesson_video` still fires exactly where it fires today; the markup
  change goes in the release notes.
- Re-run the ticket-04 ladder. Confirm the shim still mounts legacy markup (simulate an
  old-shape element) and that stage 2 is reversible by reverting the four templates.
- Retarget the learning-area controller
  (`assets/src/js/frontend/learning-area/lesson/player.ts`): it queries
  `.tutor-lesson-video-wrapper .tutorPlayer` and calls `TutorCore.player.create(target)`.
  Once templates declare `<tutor-video-player>`, that selector matches nothing and the
  controller never starts (no sync, no auto-next, no fullscreen class, spinner not
  removed). Point it at the declared element (`create()` is idempotent) and refresh its
  stale "deferred ES module" comment (ADR-0011: classic script).

## Result

The four templates declare `<tutor-video-player><video-skin><media></video-skin>` and both
callers adopt the self-booted element instead of rebuilding it.

- `PlayerService.create()` is idempotent for declarative markup: it adopts
  `element.player` and tracks it; the legacy replacement path (the shim) still mounts
  `.tutorPlayer` markup for third parties.
- `ready` became a latch in `Player.ts`: a subscriber arriving after the first store sync
  still receives it. Declarative markup boots when the engine loads, which can precede the
  lesson controller; without the latch the lesson bootstrap (spinner removal, resume, sync
  timer, `TutorLessonPlayer` global) would silently not run.
- The element applies the unsupported-controls CSS to a declared `video-skin` as well (the
  built path did it inside `createPlayerElement`).
- Retargets: the learning-area controller selects
  `.tutor-lesson-video-wrapper tutor-video-player, … .tutorPlayer`; `tutor-front.js`
  selects `tutor-video-player, .tutorPlayer`. The legacy lesson's caller lives in
  `tutor-front.js` (not in the learning-area bundle) — it was not in the original ticket
  scope but the legacy page needs it, verified below.
- The opt-in branches build URLs from the IDs the templates already parse
  (`watch?v=<id>`, `vimeo.com/<id>[/hash]`) instead of the raw user URL: that is exactly
  what the shim's `buildMediaSource()` produced in stage 1, so the flip stays
  behavior-identical (a raw URL would forward provider query params the old path dropped
  and would mishandle a bare video id).

Verified live on the production build with the flips reverted:

- HTML5 (`course-v3` lesson1): zero `.tutorPlayer`, one host, declared `<source>` kept
  (`controls` removed by the element), `TutorCore.player.getActive() === element.player`,
  spinner removed, `content-title` empty, clean console; the 8.1 s video played to the end
  and the page advanced to the next content (`assignments/heelo/`).
- Vimeo (lesson 72100): `<vimeo-video src="https://vimeo.com/336812686">` → adapter
  resolves `player.vimeo.com/video/336812686…`, 41 s; played to the end and advanced to the
  next quiz.
- YouTube (lesson 72710): `<youtube-video src="https://www.youtube.com/watch?v=I2eHUhu9ut4">`
  → embed resolves, 57 s.
- Strict re-run (lesson 72710, not completed): `PlayerData` carries
  `strict/control/enrolled = true, completed = false`; `seek(60)` clamped to the lesson's
  watched limit with the "Forward seeking is disabled" toast.
- Legacy lesson (`learning_mode=legacy`, flipped and reverted): legacy template markers,
  host inside `.tutor-video-player-wrapper`, player created, spinner removed,
  `activeIsSame`, clean console.
- Shim back-compat: an injected `<video class="tutorPlayer">` was replaced by a booted
  declarative host through `create()` (then disposed cleanly).
- `php -l`, `tsc --noEmit`, eslint, 68 unit tests (2 new), production build — green.

Site options restored: `mode=modern`, `completion=flexible`, `control=false`, provider
opt-ins on, `auto_play_next` on. Remaining manual pass is unchanged from ticket 04 (real
fullscreen + Vimeo exit resync, Pro Take Note in fullscreen, mobile Vimeo mute, YouTube
playback drive).

### Post-gate fix: define order (user report, same day)

Symptom after the flip: no control in any player responded (play/mute/fullscreen rendered
normally but were inert) and dev builds logged one `media-* requires … feature` warning per
skin control (~20). Production builds were silent, which is why the gate missed it — it
drove playback through the handle, never through the UI.

Cause: the host class registered in `index.ts` **after** `@videojs/html/video/skin`, so a
declared `<video-skin>` upgraded first. The vendor's controls resolve the player store
context once, when they upgrade (lit `ContextConsumer`), found no provider and stayed
unbound. The vendor's packaged-skin snippet imports the player module before the skin
module for the same reason (`docs/guides/skins.md`).

Fix:

- `boot.ts` (new) sets the service hooks and calls `defineVideoPlayer()`; `index.ts`
  imports it before the skin import, so the host class registers first. The repo's eslint
  import sort keeps side-effect imports in their own group ahead of the skin package
  import, so the order is stable under autofix.
- `elements.ts`: a declared skin has no shadow root while the engine script itself is still
  evaluating, so the unsupported-controls fallback patch now waits one microtask.

Verified on both the watcher dev build and the production build: html5 (lesson1), vimeo
(lesson 72100 → played to the end → `quizzes/prova-nivel-2/`), youtube (72710, `t: 18.7`
after one trusted Enter on Play), legacy shim (injected `.tutorPlayer` → built host bound),
button binding read directly (`mediaState.value` on play/mute/fullscreen), `0` feature
warnings, `content-title` set, tracked by the service, spinner removed. `tsc`, eslint,
vitest and the production build — green.

### Review fixes (vs 4.2.0)

Code review of the branch against 4.2.0, same day:

- `_videojs-player.scss`: dropped the YouTube iframe crop (`height: 200% / top: -50%`). It
  assumed a 16:9 video, so portrait lessons (including the repo's own `portrait-lesson`)
  rendered zoomed 2× with their top and bottom quarters cut. `pointer-events: none` stays,
  so the embed chrome is still unclickable; what returns is YouTube's own title/overlay.
- `Player.ts`: `fullscreenchange` only counts when the fullscreen element contains the
  player or is contained by it, and an exit fires only after our own enter. Another
  element going fullscreen no longer emits `enterfullscreen` (Pro's Take Note) or publishes
  the lesson title to the player.
- Tests: `Player.test.ts` (unrelated fullscreen ignored, a fullscreen ancestor counts) and
  `defineOrder.test.ts` (the host is defined when the skin module evaluates — locks the
  `boot.ts` order the post-gate fix depends on).

### Review round 2

A standards/spec/ponytail/architecture review pass over the branch, all findings applied:

- Engine kept out of both caller bundles: `CONTENT_TITLE_ATTRIBUTE` moved to
  `@Core/ts/constant` (shared values live there, never in the engine module), so
  `tutor-front.js` / `tutor-learning-area.js` dropped ~100 KB raw each.
- `ComponentRegistry` is a window-level singleton, so the core bundle and the engine bundle
  share one registry regardless of script order (the "two registries" outcome ADR-0010
  rejected).
- Engine enqueue (both paths in `Assets.php`) is gated on `has_video_in_single()`; quiz and
  video-less pages no longer download `tutor-player.js`.
- Strict mode counts wall-clock time only while playing: throttled/background tabs stop
  bouncing playback backwards, and a seek performed during a pause is still blocked.
- Legacy pages get the pre-migration `tooltip-wrap` + `tooltip-txt` span when Alpine is
  absent; the Learning Area keeps the Alpine `tutorTooltip` markup.
- `tutor-front.js` warns and continues when the engine cannot read a legacy element instead
  of aborting the rest of the bundle's ready handlers.
- Templates emit the raw user URL again (params and unlisted hashes intact) — the
  canonicalization recorded under Result is reversed per ADR-0010; bare ids were, and
  remain, not rendered by these templates.
- `vimeoVideoId` reads the path-form hash (`player.vimeo.com/video/{id}/{hash}`).
- Dead surface removed: the dormant Alpine player component and its `tutor-player-ready`
  event, the test-only `active` getter, `PlayerEventPayload`, a duplicated `AnyHandler`,
  and the duplicated unsupported-controls CSS; ADR-0007 is amended to the shipped surface.

### At release

Changelog line to add with the next version bump (the repo updates version and readme
together at release):

> Update: The lesson player markup is now declarative (`<tutor-video-player>`); overrides
> of `templates/single/video/*.php` should return that element. Legacy `.tutorPlayer`
> markup keeps working for this cycle.
