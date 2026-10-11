Type: task
Status: open
Blocked by: 04, 05

## Question

Wire the Learning Area path to the new engine: resolve the
`.tutor-lesson-video-wrapper .tutorPlayer` target, call `TutorCore.player.create()`, attach
the `LessonPlaybackController`. Preserve the `tutorLessonPlayerReady` dispatch and the
`is-fullscreen` wrapper toggling. `learning-area/index.ts` is an IIFE bundle and must read
`window.TutorCore.player` at runtime, not import the engine.
