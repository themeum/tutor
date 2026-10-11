Type: task
Status: done
Blocked by: 01

## Question

Build a YouTube lesson with the full `videoFeatures` bundle on the packaged Default skin
(`@videojs/html/video/skin`) and enter fullscreen, then assert and record:

1. `document.fullscreenElement` is the player **container**, not the `<iframe>`.
2. YouTube's chrome does **not** appear.

This is the ADR-0009 gate. If chrome appears, escalate per ADR-0009 — first omit the
fullscreen feature (ADR-0008), then vendor the skin source (ADR-0003) — and record which
fallback the map must adopt so ticket 04 can be adjusted.

## Outcome

**done — ADR-0009 holds.** No fallback (ADR-0008/0003) needed.

- Assertion #1 PASS: the packaged skin's fullscreen feature calls `requestFullscreen()` on
  `<media-container>`; the `<iframe>` is never the target (verified via the button and the
  `f` shortcut).
- Assertion #2 PASS: in real fullscreen only the player container expands; YouTube's own
  fullscreen chrome does not appear.

Spike fixture was throwaway (`spike-videojs/`, uncommitted, deleted).

Finding handed to ticket 04: the packaged skin sizes only native `<video>`/`<audio>`
(`skin.css` `.media-container > slot::slotted(video)`); embed media (`youtube-video`,
`vimeo-video`) is slotted light DOM that the skin never sizes, so its `<iframe>` stays at
300×150. The engine must set `display:block; width:100%; height:100%` on those elements.
