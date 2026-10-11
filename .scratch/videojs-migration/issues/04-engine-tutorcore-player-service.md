Type: task
Status: open
Blocked by: 02, 03

## Question

Implement the engine under `assets/core/ts/services/player/`:

- `providers.ts` — pure Provider/source detection from the existing `.tutorPlayer` markup
  (html5 `<video>` + `<source>`, external URL, `.plyr__video-embed` YouTube, Vimeo).
- `Player.ts` — `TutorPlayer` handle over the Video.js store (play/seek/volume/events/
  fullscreen, `dispose`).
- `PlayerService.ts` — `create`, `getActive`, active-player proxies.
- `index.ts` — ES-module entry: imports `@videojs/html/video/player`,
  `@videojs/html/video/skin`, the media packages; registers the `tutor-video-player`
  element class; assigns `window.TutorCore.player`.

Unit-test the pure parts (providers, handle event fan-out/clamping, service
active-tracking). `assets/core/ts/index.ts` must never import this module. Outcome:
`TutorCore.player.create(target)` returns a working handle for an HTML5 lesson.
