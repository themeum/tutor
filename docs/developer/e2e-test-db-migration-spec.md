# Spec: E2E test-database migration

Status: implemented
Source architecture: PR #3118 (`feat/integrate-unit-e2e-tests`, merged at `0f4af643d`)
Companion plan: `docs/developer/e2e-test-db-migration.md`

## Problem Statement

The E2E suite still depends on a disposable wp-env site. Running it locally means starting Docker, and the merged test-database architecture is only half-wired:

- there are two Playwright configs, and the one introduced by the PR points at a single smoke spec that imports a function which no longer exists;
- the test-environment header reaches browser requests but not the global setup or the per-role fixtures, so login state is created against the live database;
- factories still shell out to wp-env, so there is no way to create fixtures on a local test database;
- the test database seeds three roles with the wrong password, while the suite expects five roles and a different password;
- Pro and WooCommerce cannot activate because the active-plugin list is hardcoded to Tutor;
- PHPUnit and Playwright share one test database, so a unit run can erase E2E fixtures;
- the reset guard treats any database whose name contains `test` as disposable, including a live database with that name;
- CI still provisions wp-env and the PR adds no replacement provisioning.

The team has decided to adopt the test-database architecture. The migration must preserve all 45 specs and 98 tests, the five-role suite, Pro coverage, and the zero-retry policy.

## Solution

Consolidate on one Playwright config that lives with the E2E suite and one test-environment selector. Every browser context sends the test header, factories run through WP-CLI with the test environment, and a single shared seeder creates the five roles with the suite password.

The installer activates Tutor, Pro, and WooCommerce from an explicit list, gives PHPUnit its own database, and refuses to touch the live database. Destructive commands require an explicit database name and hard guards, and the installer supports dry-run and uninstall. CI moves to the same test-database path with a MySQL service and a test-environment web server. wp-env is removed only after CI is green.

## User Stories

1. As a maintainer, I want to run the full E2E suite locally without Docker, so that I can test changes without starting wp-env.
2. As a maintainer, I want one Playwright config, so that I do not have to remember which config to use.
3. As a maintainer, I want the Playwright config to live with the E2E suite, so that the suite's paths, projects, and setup stay in one place.
4. As a maintainer, I want all existing specs to run unchanged, so that the migration does not reduce coverage.
5. As a maintainer, I want zero retries, so that flaky tests are fixed rather than hidden.
6. As a test author, I want every browser context to send the test-environment header, so that all page requests hit the test database.
7. As a test author, I want the global setup and role fixtures to send the header, so that stored login state is created in the test database.
8. As a test author, I want factories to run through WP-CLI with the test environment, so that fixtures are created in the test database without wp-env.
9. As a test author, I want factory results written to a predictable directory, so that the TypeScript helper can read them reliably.
10. As a test author, I want five seeded roles, so that admin, instructor, student, student-two, and manager journeys can run.
11. As a test author, I want the seeded password to match the suite default, so that logins work without per-test overrides.
12. As a Pro developer, I want Pro activated in the test database, so that Pro specs run against real Pro tables and capabilities.
13. As a Pro developer, I want Pro factories and seeders to stay in the Pro plugin, so that the free plugin does not ship Pro fixtures.
14. As a WooCommerce developer, I want WooCommerce activated when it is present, so that checkout and order specs have the tables and roles they need.
15. As a maintainer, I want PHPUnit on its own database, so that running unit and E2E tests concurrently does not destroy fixtures.
16. As a maintainer, I want the unit suite to pass on its separate database, so that the migration does not affect unit coverage.
17. As a new contributor, I want a dry-run installer, so that I can see what will change before it touches my site.
18. As a new contributor, I want an explicit apply flag, so that setup never changes my site by accident.
19. As a new contributor, I want an uninstall path, so that I can restore my configuration and remove test artifacts.
20. As a security reviewer, I want the installer and reset command to refuse the live database, so that no test command can erase site data.
21. As a security reviewer, I want an explicit test database name, so that a naming convention cannot silently select a production database.
22. As a security reviewer, I want the header and cookie honored only on approved hosts, so that a public host cannot be routed into the test database.
23. As a maintainer, I want test uploads in a separate directory, so that E2E uploads do not pollute the live media library.
24. As a maintainer, I want the test HTTP stub and mail log to load only in test requests, so that the live site's outbound HTTP and mail are untouched.
25. As a CI engineer, I want CI to use the same test-database path as local, so that local and CI behavior match.
26. As a CI engineer, I want a MySQL service and a deterministic WordPress install, so that CI no longer depends on wp-env.
27. As a CI engineer, I want the web server to run with the test environment and multiple workers, so that cron loopbacks do not block the suite.
28. As a CI engineer, I want reports and traces uploaded, so that failures are diagnosable.
29. As a maintainer, I want one environment file, so that PHP and Playwright read the same configuration.
30. As a maintainer, I want a preflight check, so that invalid database names stop the run before any test starts.
31. As a maintainer, I want the installer to be idempotent, so that rerunning setup does not duplicate configuration or corrupt backups.
32. As a maintainer, I want the reset to be idempotent, so that a failed run can be retried from a clean state.
33. As a maintainer, I want Pro specs to remain in the Pro project, so that the free repository stays independent.
34. As a maintainer, I want the broken PR smoke spec removed, so that the suite starts clean.
35. As a maintainer, I want the E2E documentation updated, so that the team knows the new commands and layout.
36. As a release manager, I want CI green on the test-database path before wp-env is removed, so that the branch never loses coverage mid-migration.

## Implementation Decisions

- **One Playwright configuration.** The root configuration and the PR configuration are merged into a single file inside the E2E suite. The root file is deleted. The merged config keeps the existing projects for core, external, and Pro suites, the existing timeouts, reporters, and global setup, and adds environment loading plus the test header.
- **Configuration location changes path resolution.** The suite's test root becomes the config's own directory. The Pro project resolves relative to the config's location, not the plugin root. Report and result directories remain at the plugin root so CI artifact paths and ignore rules do not move.
- **Header injection is centralized.** The Playwright config sets the header for ordinary pages, and the global setup and role fixture constructors set it for every additional browser context. No spec sets it by hand.
- **One environment selector.** The test environment is selected by the request header, the test cookie, or the `WP_TEST_ENV` environment variable. The selector additionally requires an approved host, so a public host cannot opt in with a cookie.
- **Explicit database names.** The test database and the PHPUnit database are named explicitly. The selector no longer derives a name by appending a suffix to the live database name. Missing names fail closed.
- **Database safety contract.** The installer, reset command, and preflight all refuse a target that equals the live database, is empty, or does not match the test naming rule. The preflight runs before Playwright and stops the run on any mismatch.
- **Installer contract.** Dry-run is the default. An explicit apply flag writes. The installer creates the test databases, routes the configuration, installs the test mu-plugins, and records what it changed. An uninstall flag restores the configuration backup, removes the mu-plugin links, and drops only the generated test databases. The backup is created once and reused.
- **Reset contract.** The reset command accepts an explicit target, refuses unsafe targets, drops only the target's tables, installs a fresh WordPress site, applies the plugin list, and calls the shared seeder.
- **Shared seeder contract.** One seeder creates the five roles, pages, settings, manual payment method, and baseline course fixture. The password comes from `E2E_PASSWORD` and defaults to `password`. The seeder invokes the Pro seeder when the Pro plugin is present and stays silent when it is not.
- **Plugin activation contract.** The active-plugin list comes from `WP_TEST_ACTIVE_PLUGINS` when set. Otherwise it is built from the plugins that exist on disk: Tutor always, Pro and WooCommerce when present. Activation hooks run in the test database so their tables and capabilities exist.
- **Test-only mu-plugins.** The HTTP stub and mail log return immediately unless the test environment is active. The installer links them into the site's mu-plugin directory; uninstall removes the links. They are safe to leave installed on the live site because they no-op there.
- **Uploads isolation.** The test environment defines a separate uploads directory so attachments created by tests do not appear in the live media library.
- **Factory transport contract.** The factory helper calls WP-CLI with the test environment and an explicit WordPress root. The runner is selectable so the old wp-env path can stay alive until CI is migrated, then removed. Results are written under the WordPress content directory and read from there.
- **PHPUnit isolation.** PHPUnit uses its own database name. The selector chooses it when the unit-test constant is defined, before falling back to the E2E test database.
- **CI contract.** CI provisions MySQL as a service, downloads WordPress into a known root, checks the plugin out at the path WordPress expects, applies the installer, and serves the site with the test environment enabled and multiple PHP workers. Playwright runs against that server. Cron loopbacks therefore use the test database as well.
- **Retry policy.** Retries remain zero in local and CI. The PR configuration's CI retries are not adopted.
- **No product-code changes.** The migration touches test infrastructure, scripts, configuration, and CI only.

## Testing Decisions

A good test here is a black-box run of an existing entry point: it asserts observable outcomes (tests pass, database contents land in the expected database, the live database is untouched) and never asserts implementation details such as which config file was loaded or which helper function injected a header.

The migration is verified through four seams, in priority order:

1. **Playwright entry point (existing, highest seam).** Running the full E2E command must execute all 45 specs and 98 tests with zero retries. This one seam covers the config merge, header injection, seeding, factory transport, Pro project resolution, and role fixtures.
2. **PHPUnit entry point (existing seam).** Running the unit command must pass all existing PHPUnit files while writing only to the PHPUnit database.
3. **Installer and reset commands (new seam, unavoidable).** Data safety, idempotency, dry-run, and uninstall cannot be proven through Playwright. These are tested by running the commands against a disposable database and asserting the live database is untouched.
4. **CI workflow (existing seam).** A pull-request run must be green on the test-database path with zero retries, and the artifacts must be uploadable.

Prior art is the existing E2E suite, the existing factory helper, and the existing CI workflow. No new test framework is introduced. The new seam is limited to the installer/reset command boundary; it exists because the safety contract is destructive and cannot be exercised through the browser.

Seam acceptance checks:

- the live database contains a marker option that is unchanged after every run;
- checksums of the uploads and plugin directories are unchanged before and after the E2E run;
- the MySQL general log shows no live-database queries during the E2E run;
- the installer is run twice and produces the same state;
- the reset is run twice and produces the same seeded state;
- uninstall restores the configuration backup and removes the generated databases and mu-plugin links.

## Out of Scope

- Moving specs into a new directory.
- Adding retries.
- Copying Pro tests, factories, or seeders into the free plugin.
- Seeding the test database from a live dump.
- Committing the environment file.
- Isolating plugin files: the test site still shares the plugin filesystem, so a plugin update run from the test site would change code the live site serves.
- Full isolation of local cron and inbound webhooks: the header only reaches requests that send it. CI avoids this by running the whole server with the test environment; local outbound calls are stubbed.
- Running Pro tests in CI without a Pro checkout or token.
- Changes to product code or database schema.

## Further Notes

The source architecture is PR #3118. The migration deliberately keeps the existing 98-test suite rather than the PR's three-test smoke suite, keeps the five-role, zero-retry policy, and keeps Pro code in the Pro plugin.

The implementation plan, including phases and file-level changes, is in `docs/developer/e2e-test-db-migration.md`.

This spec is stored locally at the user's request; no remote issue was created. The repository has not been configured for an issue tracker or triage labels, so the `ready-for-agent` status is recorded here rather than applied as a tracker label.
