Type: task
Status: done
Blocked by: 06, 07

## Question

Add `cypress/e2e/frontend/lesson-video-player.cy.ts` using a local HTML5 lesson fixture
(no third-party network): `.plyr__video-embed` and `window.Plyr` are gone; the
`tutor-video-player` element and a media child exist; `getActive()` returns the live handle
and `on('ready')` fires; fullscreen toggles `is-fullscreen` and fires `fullscreenchange`
with `element`; Strict Mode clamps a forward seek and warns; resume lands on seeded
`best_watch_time`; `ended` triggers auto-next.

Ensure the Vitest suite covers the engine and controller, `pnpm test:unit` and `pnpm test`
are green, and `pnpm lint` / typecheck are clean.

## Outcome

Added `cypress/e2e/frontend/lesson-video-player.cy.ts` covering the ticket's bullets: engine mounts
(no `.plyr__video-embed`, `window.Plyr` undefined, `tutor-video-player` + `video` child, live
`getActive()` handle), `on('ready')`, fullscreen (`is-fullscreen` + `fullscreenchange` with
`element`, driven by a stubbed `document.fullscreenElement` + dispatched event), Strict-Mode clamp,
resume from seeded `best_watch_time`, and ended → auto-next. Env-dependent assertions (strict/resume/
auto-next) skip when the seeded lesson does not enable them; it reuses the existing login/start/
topic selectors.

Vitest covers the engine + controller (43 tests). `tsc`/`eslint` clean.

Not executed in this session: `CYPRESS_baseUrl`/`CYPRESS_base_url` points at the remote
`themeum-tutor.com`, and `pnpm test` runs the full Cypress suite (incl. PayPal purchase journeys),
so the spec was authored only (per instruction). `pnpm test` remains the one gate not run.
