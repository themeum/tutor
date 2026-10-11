Type: task
Status: done
Blocked by: —

## Question

Add `assets/core/ts/types/player.ts` — `TutorPlayer`, `PlayerProvider`, `PlayerEvent`,
`PlayerEventPayload`, `PlayerService`, `PlayerOptions`; types only, no runtime imports — and
wire the `TutorCore` / `window` declarations.

Also drop `global: true` from the Alpine `player` component meta
(`assets/core/ts/components/player.ts`), so `TutorCore.player` is free for the service (Q7),
and confirm `x-data="tutorPlayer"` still registers via
`ComponentRegistry.initWithAlpine`.

## Outcome

done.

- `assets/core/ts/types/player.ts` added (types only, no runtime imports): `PlayerProvider`,
  `PlayerEvent`, `PlayerEventPayload`, `TutorPlayer`, `PlayerOptions`, `PlayerService`.
- `TutorCore.player: PlayerService` wired in `assets/core/ts/types/index.ts`; the
  `declaration.d.ts` `window.TutorCore` declaration inherits it.
- Dropped `global: true` from `playerMeta` (`components/player.ts`). `global` only fed
  `Registry.exposeToWindow`; `x-data` registration happens in `initWithAlpine` via
  `Alpine.data('tutor' + Name)`, which is untouched — so `x-data="tutorPlayer"` still
  registers, and `TutorCore.player` is now free for the ES-module service entry (ticket 04).

Verified: `tsc --noEmit` clean, `eslint` clean, `rspack --mode=production` green
(`js/tutor-player.js` emitted).
