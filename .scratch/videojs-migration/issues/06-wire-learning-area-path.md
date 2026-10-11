Type: task
Status: done
Blocked by: 04, 05

## Question

Wire the Learning Area path to the new engine: resolve the
`.tutor-lesson-video-wrapper .tutorPlayer` target, call `TutorCore.player.create()`, attach
the `LessonPlaybackController`. Preserve the `tutorLessonPlayerReady` dispatch and the
`is-fullscreen` wrapper toggling. `learning-area/index.ts` is an IIFE bundle and must read
`window.TutorCore.player` at runtime, not import the engine.

## Outcome

`initializeLessonPlayer()` reads `window.TutorCore.player` at runtime (no engine import). Because
`tutor-learning-area.js` (IIFE, footer) executes before `tutor-player.js` (deferred ES module), it
now waits for a new `TUTOR_CUSTOM_EVENTS.PLAYER_ENGINE_READY` (`tutor-player-engine-ready`) event
that the engine dispatches right after assigning `window.TutorCore.player`, then creates the player
and attaches `LessonPlaybackController`. Preserved: `tutorLessonPlayerReady` dispatch (now via the
`LESSON_PLAYER_READY` constant), `window.TutorLessonPlayer`, and `is-fullscreen` now toggles on the
player host element on `fullscreenchange`. `tsc`/`eslint`/`vitest` (40)/`rspack` all green.
