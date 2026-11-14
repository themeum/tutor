# Expose playback through a TutorCore `player` service

**Status**: accepted (supersedes ADR-0005)

## Context

Lesson playback is currently spread across three unrelated entry points that each
construct Plyr by hand: the Learning Area `LessonPlayer` class, the Legacy Lesson Page
jQuery `videoPlayer` object, and the Alpine `player` component. They share behaviour by
copy-paste, and the only cross-plugin contract is the `window.TutorLessonPlayer` global,
which is a raw Plyr instance exposed as a global variable. Pro's lesson notes reach into
that raw instance (`currentTime` get/set, `play()`, `on('enterfullscreen')`).

This is a full migration, so we can replace the ad-hoc surface with a designed one rather
than re-skin the global.

## Decision

Add a **`player` service to TutorCore** (`window.TutorCore.player`), following the
existing `ServiceMeta` pattern used by `modal`, `toast`, `form`, etc. It is the single
owner of the Video.js engine and exposes a small, typed, framework-free API:

```ts
type PlayerProvider = 'html5' | 'external' | 'youtube' | 'vimeo' | 'embedded' | 'shortcode';
type PlayerEvent = 'ready' | 'play' | 'pause' | 'ended' | 'timeupdate' | 'fullscreenchange';

interface TutorPlayer {
  readonly provider: PlayerProvider;
  readonly element: HTMLElement;
  play(): Promise<void>;
  pause(): void;
  seek(time: number): void;
  getCurrentTime(): number;
  getDuration(): number;
  isPlaying(): boolean;
  getVolume(): number;
  setVolume(value: number): void;
  isMuted(): boolean;
  setMuted(muted: boolean): void;
  on(event: PlayerEvent, handler: (payload: PlayerEventPayload) => void): () => void;
  off(event: PlayerEvent, handler: (payload: PlayerEventPayload) => void): void;
  dispose(): void;
}

interface PlayerService {
  create(target: HTMLElement, options?: PlayerOptions): TutorPlayer; // builds the Video.js tree
  getActive(): TutorPlayer | null; // the most recently created, still-alive player
  play(): void;
  pause(): void;
  seek(time: number): void;
  getCurrentTime(): number;
  getDuration(): number;
  on(event: PlayerEvent, handler: (payload: PlayerEventPayload) => void): () => void;
}
```

Lesson-specific behaviour (Strict Mode seek guard, completion percentage, Watch Progress
Sync, resume from Best Watch Time, auto-next, mobile Vimeo mute, YouTube poster handling)
is **not** part of the service. It becomes a separate `LessonPlaybackController` that
subscribes to a `TutorPlayer` handle, so the engine stays generic (usable for the course
intro video too) and the lesson rules stay in one readable place.

`window.TutorLessonPlayer` is kept for one deprecation cycle as a thin adapter over
`TutorCore.player.getActive()` that preserves the Plyr-shaped surface Pro uses today
(`currentTime` get/set, `play()`, `on('enterfullscreen'|'exitfullscreen')` with
`srcElement`). It logs a deprecation warning once.

### Location and the ESM constraint

`@videojs/*` is ESM-only; loading its output from a non-module (IIFE) script fails at
runtime with `StoreError: NO_TARGET`. That constrains the **entry graph**, not the folder:
a module may live anywhere as long as the only entry that reaches it emits ES modules.
`assets/core/ts` is IIFE only because `assets/core/ts/index.ts` is the `tutor-core` entry
and no entry sets `output.module` — not because of where files sit.

So the engine is co-located with its peers, not parked in a frontend feature folder:

- `assets/core/ts/services/player/` — `PlayerService.ts` (imports `@videojs/*` and the
  packaged skin), `Player.ts`, `providers.ts`, `compat.ts`, and `index.ts` as the **ES
  module rspack entry** that assigns `window.TutorCore.player`.
- `assets/core/ts/types/player.ts` — the types only (no runtime imports), importable from
  the IIFE bundles that consume a handle.
- `assets/core/ts/index.ts` must never import `services/player`.

The one build change this forces: rspack's production build currently returns a single
config, and one config cannot mix `output.module: true` (the player entry) with the IIFE
entries — so production returns an array `[iifeConfig, playerModuleConfig]` (dev already
emits per-entry configs and just special-cases the player entry by name).

## Considered Options

- **Keep the Plyr-shaped global (ADR-0005).** Rejected: freezes a third-party API shape
  as our public contract and keeps the three entry points duplicated.
- **Expose the raw Video.js store.** Rejected: leaks engine-specific structure and
  still needs a wrapper for `getCurrentTime`/`seek`.
- **Drop the global with no deprecation period.** Rejected: Pro and third-party themes
  would break silently.

## Consequences

- A new `@Core/ts/services/player/` module (service + handle) reached only from its own
  ES-module entry; the `TutorCore` type and `window` declaration gain a `player` member.
- The three init paths collapse to: resolve target → `TutorCore.player.create()` →
  attach the relevant controller.
- `tutor-pro`'s lesson notes are migrated from `window.TutorLessonPlayer` to
  `window.TutorCore.player` as part of this migration (full migration, not shimmed).
- The fullscreen payload carries the fullscreen `element` explicitly, replacing Plyr's
  `event.srcElement` convention.
- `tutor-player-ready` / `tutorLessonPlayerReady` stay as-is for one deprecation cycle,
  then are retired in favour of subscription via `TutorCore.player.on('ready', …)`.

## Amendment (2026-10-17)

- Delivery changed: the engine is a standard IIFE `jsEntries` entry (`tutor-player.js`)
  enqueued as a classic script — ADR-0011 supersedes the ES-module entry and the second
  rspack config described above.
- The shipped service surface is smaller than sketched: `isPlaying()`, `off()`, and the
  service-level `play()`/`pause()`/`getDuration()` are not implemented; `readonly active`
  shipped as `getActive()`, the accessor Pro consumes.
- `tutor-player-ready` was removed with the dormant Alpine player component (nothing
  dispatched or listened to it); `tutorLessonPlayerReady` and the
  `window.TutorLessonPlayer` shim remain until ticket 06.
- The registry is a window-level singleton, so the engine's service registration lands in
  the core bundle's registry regardless of script order.
