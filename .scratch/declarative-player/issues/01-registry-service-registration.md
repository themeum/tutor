Type: task
Status: done
Blocked by: —

## Question

Register the player service through `ComponentRegistry` instead of the hand-rolled
`Object.assign` in `assets/core/ts/services/player/index.ts` (ADR-0010, grilling Q8):

- Move `window.TutorComponentRegistry = TutorComponentRegistry` from inside
  `initializePlugin()` to module scope in `assets/core/ts/index.ts`. The deferred engine
  module executes before `initializePlugin` runs, so the window bridge must exist by
  then. Confirm no consumer depends on the registry appearing later — existing
  registrants run on `alpine:init` or later and only guard with `if`.
- Add `playerServiceMeta: ServiceMeta<PlayerService>` next to the service
  (`{ name: 'player', instance }`).
- Engine `index.ts`: register via
  `window.TutorComponentRegistry?.register({ type: 'service', meta: playerServiceMeta })`,
  falling back to the imported registry instance when the bridge is absent; delete the
  `Object.assign` exposure. `window.TutorCore.player` must be set by
  `exposeToWindow` like every other service.
- Add `window.TutorComponentRegistry` typing if missing.

Verify: `tsc` / `eslint` / `rspack --mode=production` green;
`window.TutorComponentRegistry.has({ name: 'player', type: 'service' })` is true after
the engine boots; `TutorCore.player` is set on a lesson page; dashboard +
learning-area smoke (the earlier exposure must be inert).

## Outcome

Done. `window.TutorComponentRegistry = TutorComponentRegistry` moved to module scope in
`assets/core/ts/index.ts`; `playerServiceMeta` added in `PlayerService.ts`; the engine
registers via `window.TutorComponentRegistry ?? imported instance` and the `Object.assign`
exposure is deleted. Typing already existed (`declaration.d.ts:58`).

Verified: player vitest suite, `tsc`, eslint, production build green. Built
`tutor-player.js` carries the `name:"player"` meta; `tutor-core.js` sets the bridge at
module scope. Since the delivery change (ADR-0011) the engine is a classic script too:
registration uses the bridge when `tutor-core` is already loaded (learning-area pages)
and the imported instance when the engine runs first (course pages); either path exposes
`window.TutorCore.player`. Browser smoke in 04.
