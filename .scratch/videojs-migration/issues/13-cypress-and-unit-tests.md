Type: task
Status: open
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
