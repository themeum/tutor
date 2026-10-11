# Preserve `window.TutorLessonPlayer` and `tutorLessonPlayerReady` as a compatibility shim

**Status**: superseded by ADR-0007

## Context

The Lesson Player is exposed as `window.TutorLessonPlayer` (typed as `Plyr` in
`assets/core/ts/declaration.d.ts`) and announced through the `tutorLessonPlayerReady`
event. Pro, addons, and possibly third-party themes consume these, and they are outside
this repository, so their exact usage is unknown.

## Confirmed consumers

`tutor-pro`'s lesson-notes feature (both the Learning Area and Legacy Lesson Page) is the
known consumer, and it constrains the shim exactly:

- `window.TutorLessonPlayer.currentTime` — read as a number, and **assigned** to seek.
- `window.TutorLessonPlayer.play()`.
- `player.on('enterfullscreen', cb)` / `player.on('exitfullscreen', cb)`, where the
  callback receives an object with an `srcElement` property: Pro appends its "take note"
  button and note form into `e.srcElement` while fullscreen.
- `tutorLessonPlayerReady` with `event.detail.player`.

(Pro also has its own `tutor-pro/plyr.d.ts`, but only the runtime surface above is used.)

## Decision

Keep both the `window.TutorLessonPlayer` global and the `tutorLessonPlayerReady` event.
`window.TutorLessonPlayer` becomes a shim that presents the confirmed Plyr-like surface
(`play`, `pause`, `currentTime` get/set, `volume`, `muted`, `duration`, `ended`,
`paused`, `on`/`off`, `dispose`, and a `provider` field) backed by the Video.js store.
The `enterfullscreen`/`exitfullscreen` callbacks are re-emitted with `srcElement` set to
the element that entered fullscreen.

## Considered Options

- **Expose the Video.js store directly and update consumers.** Rejected: consumers are
  out of repo and cannot be verified here.
- **Drop the global entirely.** Rejected: silently breaks unknown consumers.

## Consequences

- The shim is a maintained compatibility surface; removals need a deprecation cycle.
- `tutorLessonPlayerReady` (and the Alpine `tutor-player-ready`) keep their existing
  names and payload shape as closely as the store allows.
- Fullscreen is available for every Provider (ADR-0009), so the `enterfullscreen` callback
  fires for YouTube too and Pro's in-fullscreen note button keeps working there.
- Setting `currentTime` on the shim must route to `store.seek()`; reading it must read
  from the store so Pro always sees live playhead state.
