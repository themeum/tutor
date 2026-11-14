Type: task
Status: done
Blocked by: 13, 14

## Question

Remove Plyr: the `tutor-plyr` enqueues at both sites in `classes/Assets.php`, the vendored
`assets/lib/plyr/*` files, Plyr-only rules in
`assets/src/scss/frontend/components/_player.scss`, and the now-unused Plyr type declaration.
Confirm no `cdn.plyr.io` request remains at runtime. Re-run the full ladder (`build-dev`,
`pnpm test`, `pnpm test:unit`, lint/typecheck).

## Outcome

Removed both `tutor-plyr` style/script enqueues in `classes/Assets.php` (kept the Video.js engine
enqueue at both sites); deleted `assets/lib/plyr/{plyr.css,plyr.min.js,plyr.polyfilled.min.js}`,
the Plyr type declaration `assets/core/ts/plyr.d.ts`, and the now-dead Plyr helpers
`assets/src/js/frontend/types/index.ts` (no importers); dropped the Plyr-only SCSS from
`_player.scss` and course-spotlight `_players.scss`; removed the dangling `@FrontendTypes` alias
from `rspack.config.mjs` + `tsconfig.json`.

The `.plyr__video-embed` class in the YouTube/Vimeo templates and the `providers.test.ts` fixture is
**kept** — it is the engine's provider-detection hook (ADR-0004), not a Plyr runtime dependency.
No static `tutor-plyr` enqueue, `assets/lib/plyr` load, or `cdn.plyr.io` reference remains
(verified by repo scan). Runtime network check not possible here (no browser run).

`tsc`/`eslint`/`vitest` (43)/`rspack --mode=production` green (`tutor-front.min.css` shrank). `pnpm test`
not run (remote site, as for ticket 13). Ticket 14 (manual matrix) skipped per instruction.
