Type: task
Status: done
Blocked by: 04, 05

## Question

Replace the legacy `videoPlayer` / `new Plyr` block in `assets/src/js/front/tutor-front.js`
with `TutorCore.player.create()` + the controller. This path also serves the course-intro
video on non-learning-area pages, so it must keep working there. Preserve `is-fullscreen`
toggling and the `tutorLessonPlayerReady` dispatch (the legacy path sets
`window.TutorLessonPlayer` only inside `.tutor-course-topic-single-body
.tutor-video-player-wrapper` today).

## Outcome

Replaced the ~250-line `videoPlayer`/`new Plyr` block with `TutorCore.player.create()` + the shared
`LessonPlaybackController` (imported from `@LearningArea/lesson/player`), driven by a legacy
environment adapter. Preserved legacy behaviour: `proActive` from `_tutorobject.tutor_pro_url`;
`sync_video_playback` via `$.post` + nonce and skipped when `!is_enrolled`; auto-next gated on
`video_data.autoload_next_course_content`; strict-mode warning via `tutor_toast`; the conditional
`tutorLessonPlayerReady` dispatch + `window.TutorLessonPlayer` (only inside
`.tutor-course-topic-single-body .tutor-video-player-wrapper`). `is-fullscreen` + loading-spinner
come from the controller; the poster hack is gone. Waits for `PLAYER_ENGINE_READY` (jQuery-ready
runs before the deferred engine module).

No PHP change needed: the engine is already enqueued on `is_single_course(true)` in
`frontend_scripts()` (`classes/Assets.php:340`), which is exactly the course-intro page.

`tsc`/`vitest` (40) / `rspack --mode=production` green (`tutor-front.js` bundles the controller).
