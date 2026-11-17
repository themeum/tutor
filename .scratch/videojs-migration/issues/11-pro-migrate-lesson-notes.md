Type: task
Status: open
Blocked by: 09

## Question

Runs in `../tutor-pro`. Migrate `assets/src/js/notes/lesson-notes.js`: use
`TutorCore.player.on('fullscreenchange', ({ element }) => …)` and
`getCurrentTime()` / `seek()` instead of the `window.TutorLessonPlayer` global, and update
`handleTutorLessonPlayerReady` accordingly. Separate commit. Run Pro's build.
