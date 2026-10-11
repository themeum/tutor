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

## Not yet specified

<!-- in-scope fog; graduates as the frontier advances -->

- Removing the deprecated `window.TutorLessonPlayer` adapter and `tutorLessonPlayerReady`
  after one cycle — revisit once the migration ships.
- Whether any YouTube/Vimeo behavior gap uncovered by the Phase 0 spike needs its own
  ticket.
- SCSS cleanup depth once the Plyr controls are gone.

## Out of scope

<!-- ruled past the destination; never graduates -->

- Bringing `embedded` and `shortcode` sources into the engine (Q3): they never used Plyr.
- Taking over the native iframe path of YouTube/Vimeo when `disable_default_player_*` is
  off (Q4): preserve current behavior.
- Release/versioning coordination between the two plugins.
- Caption/storyboard authoring (ADR-0006) and note markers on the progress bar.
