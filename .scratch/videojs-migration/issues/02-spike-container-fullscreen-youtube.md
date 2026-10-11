Type: task
Status: open
Blocked by: 01

## Question

Build a YouTube lesson with the full `videoFeatures` bundle on the packaged Default skin
(`@videojs/html/video/skin`) and enter fullscreen, then assert and record:

1. `document.fullscreenElement` is the player **container**, not the `<iframe>`.
2. YouTube's chrome does **not** appear.

This is the ADR-0009 gate. If chrome appears, escalate per ADR-0009 — first omit the
fullscreen feature (ADR-0008), then vendor the skin source (ADR-0003) — and record which
fallback the map must adopt so ticket 04 can be adjusted.
