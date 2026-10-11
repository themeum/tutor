Type: task
Status: open
Blocked by: 04, 05

## Question

Replace the legacy `videoPlayer` / `new Plyr` block in `assets/src/js/front/tutor-front.js`
with `TutorCore.player.create()` + the controller. This path also serves the course-intro
video on non-learning-area pages, so it must keep working there. Preserve `is-fullscreen`
toggling and the `tutorLessonPlayerReady` dispatch (the legacy path sets
`window.TutorLessonPlayer` only inside `.tutor-course-topic-single-body
.tutor-video-player-wrapper` today).
