Type: task
Status: done
Blocked by: 04

## Question

Add the deprecated `window.TutorLessonPlayer` adapter over
`TutorCore.player.getActive()`: `currentTime` get/set (routes to `seek`), `play()`, and
`on('enterfullscreen'|'exitfullscreen')` re-emitted with `srcElement`. Keep the
`tutorLessonPlayerReady` event. Log a deprecation warning once.

This is the compat surface `tutor-pro` depends on, so it unblocks tickets 10–12. Unit-test
the `currentTime` mapping and the fullscreen re-emission.

## Outcome

Added `assets/core/ts/services/player/legacy.ts` — a `LegacyTutorLessonPlayer` adapter over
`window.TutorCore.player.getActive()`, resolved lazily per call (so lesson navigation that swaps the
active player keeps working). `currentTime` get/set maps to `getCurrentTime()`/`seek()`; `play()`
forwards; `on('enterfullscreen'|'exitfullscreen')` subscribes once to the active player's
`fullscreenchange` and re-emits each handler with `{ srcElement }`. Warns once (module-scoped).
`window.TutorLessonPlayer` is now the adapter (was the raw handle) at both assignment sites
(learning-area `notifyReady`, legacy env `notifyReady`); the `tutorLessonPlayerReady` dispatch and
its conditional legacy guard are unchanged. `declaration.d.ts` retyped to `LegacyTutorLessonPlayer`.
3 unit tests (currentTime get/set + play, fullscreen re-emission, missing-player guard); 43 total.
`tsc`/`eslint`/`rspack` green.
