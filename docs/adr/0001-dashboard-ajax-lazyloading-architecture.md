# ADR 0001: Granular AJAX Lazyloading with Skeletons for Instructor Dashboard & Report Pages

**Status:** Accepted  
**Date:** 2026-08-29  
**Context:** Tutor LMS (`tutor`) & Tutor LMS Pro (`tutor-pro`)  
**Deciders:** Core Engineering Team

---

## 1. Context and Problem Statement

When an instructor visits the Tutor LMS Frontend Dashboard (`/dashboard/`) or the Report/Analytics Overview (`/dashboard/analytics/`), the server synchronously executes up to 11 heavy SQL queries on the main thread inside template files (`templates/dashboard/instructor/home.php` and `tutor-pro/addons/tutor-report/templates/overview.php`). This results in:

- High Time-to-First-Byte (TTFB) blocking initial page render.
- White screens on filter interactions because `DateFilter`, period tabs, and course sort dropdowns execute full page reloads (`window.location.href`).
- Zero visual feedback or skeleton loaders during data computation.
- Violation of Single Responsibility and DRY principles, as template files execute duplicate queries for courses, earnings, student counts, and ratings.

---

## 2. Decision

We will implement a **Granular AJAX Lazyloading Architecture with Reusable Skeletons** across both the Instructor Dashboard Home and the Report/Analytics Overview:

1. **Fast Shell with Skeletons**:
   - The initial HTTP request delivers the layout shell immediately with **zero heavy queries**.
   - Each section renders a designated skeleton wireframe using a new PHP component `Tutor\Components\Skeleton` extending `BaseComponent`.

2. **Granular Asynchronous Section Hydration**:
   - Rather than bundling all sections into one monolithic AJAX request (which causes light queries to wait for the slowest query), each section fetches independently in parallel via `wp_ajax_tutor_get_dashboard_section`.
   - Fast metrics (e.g. Stat Cards / KPI counts) complete in ~30–50ms, replacing their skeletons immediately.
   - Slower analytical queries (timeline charts, ratings) load progressively as they complete.
   - Tutor LMS does not lock persistent PHP sessions during AJAX reads, allowing HTTP/2 multiplexed concurrency with zero server session contention.

3. **HTML Partials with Chart Payloads**:
   - The server endpoint executes the section query and outputs the rendered PHP template partial (`ob_get_clean()`) alongside any structured chart configuration required by Chart.js.
   - This maintains 100% compatibility with Tutor LMS template overrides, WordPress gettext translations (`__()`), and existing hooks without duplicating view logic in JavaScript.
   - Alpine replaces the skeleton and invokes `Alpine.initTree()` on the hydrated DOM tree.

4. **Service Layer for DRY & SOLID Compliance**:
   - Move database aggregation queries out of template files into a centralized `InstructorMetricsService` (and `AnalyticsService` in Tutor Pro).
   - Both Dashboard Home and Report Overview reuse the identical query methods for courses, earnings, student counts, and rating aggregations.

5. **In-Place Reactive Filtering**:
   - Changing `DateFilter` or sorting triggers an Alpine reactive refetch on affected sections with skeleton transitions.
   - The browser URL is synced via `history.pushState` to retain bookmarkable, shareable URLs without reloading the page.

---

## 3. Alternatives Considered

| Alternative                                      | Why Rejected                                                                                                                                                                                            |
| :----------------------------------------------- | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Monolithic Single AJAX Request**               | Light stat cards take ~30ms but would be held hostage waiting for 600ms chart queries, preventing progressive rendering.                                                                                |
| **Pure JSON with Client-side Alpine Templating** | Requires rewriting hundreds of lines of PHP template HTML into Alpine `<template x-for>` and `x-text` directives, breaking WordPress child theme template overrides and complicating i18n translations. |
| **Server-Side Rendering with Full Page Cache**   | Dashboard data is highly personalized, user-role gated, dynamic, and nonces must be fresh. Full page cache is unsuitable.                                                                               |

---

## 4. Consequences

### Positive:

- **Instant TTFB**: Initial HTML response is near-instantaneous (<150ms).
- **Progressive UI**: Fast cards appear immediately; heavy charts stream in cleanly.
- **Zero Layout Shift (CLS)**: Skeletons match the exact pixel geometry of the loaded cards and charts.
- **DRY Codebase**: Unified query services eliminate duplicated SQL queries between Core Dashboard and Pro Report.

### Negative / Trade-offs:

- Requires adding `Tutor\Components\Skeleton` component to Tutor Core.
- Requires safelisting `.tutor-skeleton` classes in `purgecss.config.mjs`.
