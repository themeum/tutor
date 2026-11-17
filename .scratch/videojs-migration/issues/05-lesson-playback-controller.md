Type: task
Status: done
Blocked by: 03

## Question

Rewrite `assets/src/js/frontend/learning-area/lesson/player.ts` as a `LessonPlaybackController`
over a `TutorPlayer` handle — Strict Mode seek guard, completion percentage, Watch Progress
Sync, resume from Best Watch Time, auto-next (`autoload_next_course_content`), mobile Vimeo
mute, YouTube poster handling — plus `initializeLessonPlayer()`. The engine stays generic;
the lesson rules live here.

Unit-test with a fake handle: Strict Mode blocks forward seek (calls `seek` back + warns),
completion math, sync cadence, resume, auto-next.

## Outcome

`LessonPlaybackController(player, data, environment)` in the same file, driven entirely by the
`TutorPlayer` handle (`on`/`seek`/`getCurrentTime`/`getDuration`/`setMuted`/`provider`); the
engine stays generic. `initializeLessonPlayer()` reads the target + `#tutor_video_tracking_information`,
calls `TutorCore.player.create(target)` and starts the controller. `PlayerData`/`getPercentage`/
`readPlayerData` exported for tests. 14 Vitest tests (39 total) cover strict seek, tolerance,
sync cadence + pro-inactive, resume, completion enable/disable, mobile Vimeo, auto-next.

Deltas from the ticket wording (behaviour preserved over literal wording):

- **Strict Mode** — the handle has no cancelable pre-seek event, so the guard runs on
  `timeupdate`: a forward jump beyond the max watched time (`+1s` tolerance) calls `seek` back
  and warns. (Confirmed with the user.)
- **Poster handling** — dropped: the packaged skin owns the poster. (Confirmed with the user.)
- **Auto-next** — the old controller actually gated on `tutorConfig.user_preferences.auto_play_next`
  (not `autoload_next_course_content`, which the field declares but never read). Preserved the
  real behaviour; `LessonEnvironment.autoPlayNext` mirrors it.
- Recorder also synced on `pause` and on `ready`; both preserved. YouTube iframe `tabindex`/`aria-hidden`
  hack dropped (the media adapter owns the iframe now).

`tsc`/`eslint`/`vitest` (39) / `rspack --mode=production` all green. `window.TutorLessonPlayer` is
now typed `TutorPlayer`; the deprecated global + `tutorLessonPlayerReady` event are still emitted for
back-compat (retirement is a later ticket).
