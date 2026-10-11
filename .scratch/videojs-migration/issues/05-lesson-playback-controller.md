Type: task
Status: open
Blocked by: 03

## Question

Rewrite `assets/src/js/frontend/learning-area/lesson/player.ts` as a `LessonPlaybackController`
over a `TutorPlayer` handle — Strict Mode seek guard, completion percentage, Watch Progress
Sync, resume from Best Watch Time, auto-next (`autoload_next_course_content`), mobile Vimeo
mute, YouTube poster handling — plus `initializeLessonPlayer()`. The engine stays generic;
the lesson rules live here.

Unit-test with a fake handle: Strict Mode blocks forward seek (calls `seek` back + warns),
completion math, sync cadence, resume, auto-next.
