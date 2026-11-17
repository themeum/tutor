Type: task
Status: done
Blocked by: 04

## Question

Rewrite the Alpine `player` component (`assets/core/ts/components/player.ts`) to call
`TutorCore.player.create()` instead of `new window.Plyr`. Keep the `tutor-player-ready`
dispatch (`TUTOR_CUSTOM_EVENTS.TUTOR_PLAYER_READY`) and update its `detail` shape to carry
the `TutorPlayer` handle. The component is consumed as `x-data="tutorPlayer"`.

## Outcome

`assets/core/ts/components/player.ts` now creates the handle via `window.TutorCore.player.create()`
and exposes it as `player: TutorPlayer | null` (was `plyr: Plyr | null`). `tutor-player-ready` now
carries `detail: { player, component }`. Kept the mobile-Vimeo mute/unmute and `is-fullscreen`
behaviour (now from `provider` + the `fullscreenchange` event on the host element); dropped the
YouTube-iframe `tabindex` hack (the engine owns the iframe). Added `destroy()` to unsubscribe and
`dispose()`. Not consumed by any template in this repo — dormant/public API only.
`tsc`/`eslint`/`vitest` (40)/`rspack` green.
