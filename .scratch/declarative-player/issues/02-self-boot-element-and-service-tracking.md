Type: task
Status: done
Blocked by: 01

## Question

Self-boot the declared element and move ownership to the element (ADR-0010, grilling
Q6/Q9). Templates are untouched in this ticket — today's markup must run through the
new path via the shim.

- The element creates its handle on connect (`createTutorPlayer` over its store),
  exposes it as `element.player`, and registers with the service.
- Service tracks instances: `getActive()` = most recent live instance; dispose clears
  the active slot; `on()` late-binds when a player appears (research F1). Public shape
  frozen (`create`/`getActive`/`on`/proxies).
- `create()` becomes an idempotent "ensure a handle for legacy markup": detect →
  replace with the declarative tree → element self-boots → return `element.player`
  (used by the shim and the Alpine component).
- Adapter loading: subtree tag scan for `youtube-video`/`vimeo-video` → lazy import.
  Keep the define-player-before-adapters order (ADR-0010 hard contract).
- The element removes a `controls` attribute if present (no-op today; the stage-2
  templates keep `controls` as the no-JS fallback).
- Keep wiring injectable (fake store) so happy-dom tests stay meaningful; extend
  `PlayerService.test.ts`; add element-boot tests. Decide the element ↔ service
  plumbing (wired callback vs DOM event) — avoid the `elements.ts` ↔ `PlayerService.ts`
  import cycle.

Verify: unit tests + `tsc` + build green; smoke learning-area lesson, legacy lesson,
course intro, Alpine path — behavior identical to pre-change.

## Outcome

Done. Plumbing decision: **wired callback** — `elements.ts` exposes
`setPlayerElementHooks({ onMount, onDispose })`, wired by the engine entry to
`playerServiceMeta.instance.track/untrack`; no `elements ↔ PlayerService` import cycle.

- `TutorVideoPlayerElement` (subclass of the vendor `PlayerElement`) boots in
  `connectedCallback`: sets the default `content-title`, drops the no-JS `controls`
  fallback, resolves the provider from its subtree (`resolveProvider`: embed tag scan,
  then `providerFromUrl` on the native source), lazy-loads the adapter for yt/vimeo, and
  creates the handle as `element.player`. `destroyCallback` releases it too, so the
  vendor's two-frame auto-destroy after removal cannot leave a dead handle active.
- Service: `track`/`untrack` (dispose clears the active slot), `on()` late-binds to the
  first player that appears, `create()` = detect → build tree → replace → return
  `element.player` (idempotent ensure). Dependencies reduced to injectable
  `createElement`.
- Adapter loading moved out of `createPlayerElement`; it now always appends the media
  immediately (the deferred-append dance is gone) and the element owns the import.
  Define-player-before-adapters order preserved (hard contract).
- `legacy.ts` subscribes through `service.on` and retries on `PLAYER_ENGINE_READY` when
  the engine is absent (F1).

Tests: 46 in the player suite (boot/dispose/reconnect/destroy, provider resolution,
late-bind, untrack, `onDispose`); full suite 66 green. `tsc`, eslint, production build
green. Browser smoke owed in 04.
