# Map: migrate lesson playback from Plyr to Video.js 10

## Destination

Both plugins play lesson **and course-intro** video through one Video.js 10 engine exposed
as `TutorCore.player`; Plyr is removed from the free plugin; `tutor-pro` lesson notes are
migrated onto the new API; the automated ladder (Vitest + Cypress + build/typecheck) is
green and the manual provider matrix passes. Ends at _mergeable in both repos_ —
release/versioning mechanics are out of scope.

## Notes

- Detailed route and rationale live in `docs/plans/videojs-migration-plan.md` and
  `docs/adr/0001`–`0009`. Tickets hold the work; the plan holds the shape.
- This effort **executes** (not decisions-only): tickets include `task` slices that perform
  the migration, each sized for one agent session.
- Locked decisions from the charting round:
  - **Q1** full cutover — both live paths (Learning Area, legacy `tutor-front.js`) and the
    course-intro video.
  - **Q2** migrate the dormant Alpine `player` component (used as `x-data="tutorPlayer"`,
    `tutor` prefix added by `ComponentRegistry.initWithAlpine`).
  - **Q3** `embedded` and `shortcode` sources stay out of the engine.
  - **Q4** the native-iframe branch of YouTube/Vimeo (`disable_default_player_*` off) stays
    out of the engine.
  - **Q5** one map; Pro tickets run in the sibling repo `../tutor-pro`.
  - **Q6** the agent runs the automated ladder; one HITL ticket covers the manual matrix;
    the human approves the final merge.
  - **Q7** drop `global: true` on the Alpine component so `TutorCore.player` is free for the
    service.
- Environment: WP minimum is 5.3, so `wp_enqueue_script_module` is unavailable — a
  `script_loader_tag` filter adds `type="module"`.
- Claim a ticket (`Status: claimed`) before working it. Expect other sessions on the edge.

## Decisions so far

<!-- one line per resolved ticket, with a link and a gist -->

- [01 — deps + ESM entry](issues/01-add-videojs-deps-and-esm-entry.md): added `@videojs/html`,
  `@videojs/youtube-video`, `@videojs/vimeo-video` (`10.0.1`) + Vitest; a second rspack config
  with `output.module` emits `assets/js/tutor-player.js` (stub sets `window.TutorCore.player`);
  a `script_loader_tag` filter marks that handle `type="module"`. Verified with `pnpm build-dev`.
- **02 done** — ADR-0009 gate passes: the packaged skin fullscreens `<media-container>`, never
  the `<iframe>`, and YouTube's own fullscreen chrome does not appear in a real fullscreen, so
  ticket 04 keeps the full `videoFeatures` bundle (no ADR-0008/0003 fallback). Spike also found
  that the skin sizes only native `<video>`/`<audio>`; embed media (`youtube-video`/`vimeo-video`)
  must be sized `display:block; width/height:100%` by the engine — folded into ticket 04.
- **03 done** — shared player contract added at `assets/core/ts/types/player.ts`
  (`TutorPlayer`/`PlayerProvider`/`PlayerEvent`/`PlayerEventPayload`/`PlayerOptions`/`PlayerService`)
  and `TutorCore.player` typed as `PlayerService`; dropped `global: true` from the Alpine `player`
  component so `TutorCore.player` is free for the service (Q7) while `x-data="tutorPlayer"` still
  registers. `tsc`/`eslint`/`rspack` all green. Addendum: the first cut used
  `enterfullscreen`/`exitfullscreen` events and property-style accessors; ticket 04 realigned the
  contract to the plan (method accessors, one `fullscreenchange` event with `{ fullscreen, element }`,
  `on()` returns an unsubscribe).
- **04 done** — engine behind `TutorCore.player`: `providers.ts` detects provider/source from the
  `.tutorPlayer` markup, `Player.ts` wraps the video.js store as a `TutorPlayer` handle (event
  fan-out via `store.subscribe`, seek/volume clamping, fullscreenchange, `dispose`), `PlayerService.ts`
  resolves the target, builds the `<tutor-video-player><video-skin>` tree and tracks the active
  player, and `index.ts` imports the skin + media packages, defines the element and assigns
  `window.TutorCore.player`. Embed sizing + YouTube `::part(iframe)` crop live in
  `_videojs-player.scss`. 25 Vitest tests pass; `tsc`/`eslint`/`rspack` green (`js/tutor-player.js`).
- **05 done** — lesson controller (`assets/src/js/frontend/learning-area/lesson/player.ts`) rewritten
  as `LessonPlaybackController` over a `TutorPlayer` handle + `initializeLessonPlayer()`: Strict Mode
  guard (detected on `timeupdate`, seeks back + warns), completion-percentage button, Watch Progress
  Sync (play interval + pause/ready/ended), resume from Best Watch Time, auto-next, mobile Vimeo mute.
  Dropped the old poster hack and YouTube iframe `tabindex` hack (skin/adapter own those); auto-next
  preserved on the real `user_preferences.auto_play_next` gate. 14 new Vitest tests (39 total);
  `tsc`/`eslint`/`rspack` green. `window.TutorLessonPlayer` retyped `TutorPlayer`.

## Not yet specified

<!-- in-scope fog; graduates as the frontier advances -->

- Removing the deprecated `window.TutorLessonPlayer` adapter and `tutorLessonPlayerReady`
  after one cycle — revisit once the migration ships.
- SCSS cleanup depth once the Plyr controls are gone.

## Out of scope

<!-- ruled past the destination; never graduates -->

- Bringing `embedded` and `shortcode` sources into the engine (Q3): they never used Plyr.
- Taking over the native iframe path of YouTube/Vimeo when `disable_default_player_*` is
  off (Q4): preserve current behavior.
- Release/versioning coordination between the two plugins.
- Caption/storyboard authoring (ADR-0006) and note markers on the progress bar.
