Type: task
Status: open
Blocked by: 09

## Question

Runs in `../tutor-pro`. Migrate `assets/src/js/notes/learning-area-notes.ts` from
`window.TutorLessonPlayer` to `TutorCore.player` — `getCurrentTime()` / `seek()` instead of
the raw `currentTime` property. Separate commit. Run Pro's build.
