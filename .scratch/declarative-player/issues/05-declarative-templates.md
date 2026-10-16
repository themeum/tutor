Type: task
Status: open
Blocked by: 04

## Question

Stage 2: flip the four templates to declarative markup (ADR-0010).

- `templates/single/video/{html5,external_url}.php`: emit
  `<tutor-video-player><video-skin><video poster playsinline controls><source …></video></video-skin></tutor-video-player>`;
  drop `class="tutorPlayer"`; keep the `.tutor-video-player` wrapper, spinner and
  tracking input; `controls` stays for no-JS/script-failure playback (the element
  removes it on connect); do not set `content-title` (the element owns the default).
- `youtube.php` / `vimeo.php`: in the `disable_default_player_*` opt-in branch emit
  `<youtube-video src="…">` / `<vimeo-video src="…">` with the raw user URL — no
  canonicalization (adapters accept watch/share/embed forms). The non-opt-in branch is
  untouched.
- `tutor_single_lesson_video` still fires exactly where it fires today; the markup
  change goes in the release notes.
- Re-run the ticket-04 ladder. Confirm the shim still mounts legacy markup (simulate an
  old-shape element) and that stage 2 is reversible by reverting the four templates.
- Retarget the learning-area controller
  (`assets/src/js/frontend/learning-area/lesson/player.ts`): it queries
  `.tutor-lesson-video-wrapper .tutorPlayer` and calls `TutorCore.player.create(target)`.
  Once templates declare `<tutor-video-player>`, that selector matches nothing and the
  controller never starts (no sync, no auto-next, no fullscreen class, spinner not
  removed). Point it at the declared element (`create()` is idempotent) and refresh its
  stale "deferred ES module" comment (ADR-0011: classic script).
