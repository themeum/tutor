Type: task
Status: done
Blocked by: —

## Question

Reserve the player's space before upgrade (ADR-0010, grilling Q10) in
`assets/src/scss/frontend/components/_videojs-player.scss`:

- `tutor-video-player { display: block; width: 100%; }` + wrapper aspect-ratio so an
  unupgraded element occupies the same box as the skinned player (no CLS).
- Keep the spinner visible pre-upgrade (it is removed on ready).
- Size the pre-upgrade slotted media (light-DOM `<video>` with `controls`) to fill the
  reserved box.
- No `:not(:defined)` hiding.

Inert until ticket 05 ships; harmless to land early with stage 1.

Verify: throttle JS / block the player script — the reserved box renders with the spinner, no
layout shift when the engine upgrades; fullscreen + embed sizing unchanged.

## Outcome

Done. Added a standalone `tutor-video-player { display: block; width: 100%;
aspect-ratio: 16/9; overflow: hidden; }` rule plus pre-upgrade `video` sizing in
`assets/src/scss/frontend/components/_videojs-player.scss`. The `.tutor-video-player`
wrapper already reserved the box and keeps the spinner visible pre-upgrade; no
`:not(:defined)` hiding. Inert until 05 ships. Browser smoke owed in 04.
