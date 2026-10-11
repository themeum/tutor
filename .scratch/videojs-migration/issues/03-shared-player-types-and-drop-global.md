Type: task
Status: open
Blocked by: —

## Question

Add `assets/core/ts/types/player.ts` — `TutorPlayer`, `PlayerProvider`, `PlayerEvent`,
`PlayerEventPayload`, `PlayerService`, `PlayerOptions`; types only, no runtime imports — and
wire the `TutorCore` / `window` declarations.

Also drop `global: true` from the Alpine `player` component meta
(`assets/core/ts/components/player.ts`), so `TutorCore.player` is free for the service (Q7),
and confirm `x-data="tutorPlayer"` still registers via
`ComponentRegistry.initWithAlpine`.
