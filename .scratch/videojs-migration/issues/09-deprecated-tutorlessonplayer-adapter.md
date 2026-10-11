Type: task
Status: open
Blocked by: 04

## Question

Add the deprecated `window.TutorLessonPlayer` adapter over
`TutorCore.player.getActive()`: `currentTime` get/set (routes to `seek`), `play()`, and
`on('enterfullscreen'|'exitfullscreen')` re-emitted with `srcElement`. Keep the
`tutorLessonPlayerReady` event. Log a deprecation warning once.

This is the compat surface `tutor-pro` depends on, so it unblocks tickets 10–12. Unit-test
the `currentTime` mapping and the fullscreen re-emission.
