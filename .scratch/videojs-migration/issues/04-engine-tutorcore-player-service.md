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

Sizing (found by the ticket-02 spike): the packaged skin sizes only native
`<video>`/`<audio>`; size embed media ourselves so `youtube-video`/`vimeo-video` fill the
container (`display:block; width:100%; height:100%`) — otherwise the `<iframe>` sits at
300×150 in a corner.

YouTube branding (ported from Plyr's `.plyr--youtube iframe` trick): the embedded frame is
exposed as `::part(iframe)`, so hide YouTube's title/overlay with

```scss
video-player video-skin youtube-video::part(iframe) {
  height: 200% !important; /* overrides the shadow-styled iframe */
  top: -50%;
  pointer-events: none;
}
```

The skin's `.media-container` (`overflow: clip`) crops the oversize away. Verified: the
`::part` rule wins and the frame becomes 200% tall at `top:-50%`. Carry over the Plyr
mobile-fullscreen aspect fix too (`.media-container:fullscreen` → `width: 177.778dvh` under
`@media (min-aspect-ratio: 16/9)`), and confirm on a real device.

Unit-test the pure parts (providers, handle event fan-out/clamping, service
active-tracking). `assets/core/ts/index.ts` must never import this module. Outcome:
`TutorCore.player.create(target)` returns a working handle for an HTML5 lesson.
