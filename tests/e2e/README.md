# Tutor LMS E2E (Playwright)

The browser suite runs against a disposable wp-env site. One run covers the core
plugin and, when `E2E_PRO=1`, the Pro checkout in `../tutor-pro/tests/e2e`.

## Commands

```bash
pnpm e2e            # smoke, fresh database
pnpm e2e:full       # feature specs
pnpm e2e:journey    # serial end-to-end journeys
pnpm e2e:external   # opt-in PayPal/Zoom/Meet specs
pnpm e2e:pro        # core + Pro; requires ../tutor-pro and generates .wp-env.pro.json
pnpm e2e:open       # Playwright UI; run pnpm e2e:env first
pnpm e2e:reset      # reset the database and seed again
pnpm e2e:down       # stop wp-env
```

Override the pinned WordPress/PHP for one run:

```bash
pnpm e2e --wp=7.0 --php=8.2
```

The version is printed and the environment is recreated when it changes.

## Layout

```text
tests/e2e/
  global-setup.ts     # UI login, one storage state per seeded role
  support/            # env, auth, role fixtures, wp-env factories
  smoke/              # @smoke
  feature/            # @feature (new areas go here)
  journey/            # @journey (single serial spec per journey)
  external/           # @external, only loaded with E2E_EXTERNAL=1
bin/e2e/
  seed.php            # users, pages, settings, manual payment
  factories.php       # per-spec courses/lessons/enrollments
  run.sh, seed.sh
```

The seed creates no courses. Each spec creates its own course and lesson through
`createCourse()`; the database is fresh for every run, so no spec deletes shared
fixtures or depends on another file's data.

## Selectors

Use lowercase `data-testid` (Playwright's default `testIdAttribute`). Do not add
new `data-cy`. WordPress core screens have no test ids; use labels, roles, or
their stable IDs there. `getByTestId()` is the default for plugin UI.

## Migration status

The old Cypress specs are frozen as reference while areas are ported. The five
smoke flows are on Playwright. Port feature areas in batches, then remove the
Cypress dependency, `cypress.config.ts`, and `cypress/`. Do not run the old suite
against `.env`; it points at a remote site.

## Flake policy

- Retries are disabled. A failure is a failure, not a prompt to retry.
- Quarantine a flaky test with `test.fixme` and the `@quarantine` tag, keep the
  owner and the failing run link in a comment, and fix or delete it within two
  weeks. Do not add `retries` or `test.describe.configure({ retries })`.
- Suite owner: Tutor LMS platform team (Themeum). Area owners are recorded in
  the pull request that ports each feature area.

## Notes

- The suite never reads `.env`; only `.env.e2e` (optional, see
  `.env.e2e.example`).
- wp-env uses `http://localhost:8888`. The Pro license gate is bypassed on
  localhost/.local.
- `pnpm e2e:pro` mounts `../tutor-pro`; build its Composer/npm assets there
  first, as the core suite does not build the Pro plugin.
- Run `pnpm e2e:build` after changing `data-testid` attributes in TypeScript
  components; wp-env serves the built assets.
- CI should call the same `pnpm e2e` command. Add the GitHub Actions workflow
  only after the smoke suite has passed twice; keep it non-blocking until then.
