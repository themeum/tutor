# Domain Context: Tutor LMS

## Glossary

### Instructor Dashboard

The authenticated frontend portal (`/dashboard/`) where teachers and course creators manage courses, track student engagements, evaluate quiz attempts, and review their teaching metrics.

### Dashboard Home

The landing tab of the Instructor Dashboard (`templates/dashboard/instructor/home.php`), presenting high-level KPI cards, an earnings timeline graph, course completion distribution, top performing courses, upcoming live classes/tasks, and recent student reviews.

### Report / Analytics (`tutor-report`)

The dedicated reporting and intelligence subsystem provided by the Tutor Pro addon (`tutor-pro/addons/tutor-report/`). It operates in two environments:

1. **Frontend Analytics**: Sub-pages in the instructor dashboard (`/dashboard/analytics/`) covering Overview, Courses, Earnings, Statements, Students, and CSV/ZIP Export.
2. **Admin Report**: Backend administration pages (`wp-admin/admin.php?page=tutor-report`) for sitewide LMS reporting.

### Skeleton

A placeholder wireframe UI element with an animated wave/shimmer effect that mirrors the geometry and typography of the content being loaded (e.g. `Tutor\Components\Skeleton`). Skeletons eliminate Cumulative Layout Shift (CLS) and enhance perceived performance.

### Granular Hydration

The asynchronous pattern of fetching each dashboard/report section independently in parallel. Fast-calculating sections (e.g. KPI cards) render immediately (<50ms), while heavier analytical sections (e.g. multi-series timeline charts) stream in progressively, preventing light queries from waiting on slow queries.

### InstructorMetricsAdapter

The domain adapter (`classes/InstructorMetricsAdapter.php`) responsible for querying and adapting disparate underlying models (`WithdrawModel`, `CourseModel`, `Analytics`, `tutor_utils()`) into normalized data structures (KPI cards, comparison percentages, timeline series, and course distribution arrays) independent of any HTML markup.

### DashboardSectionManager

The Section Strategy Context and Registry (`classes/DashboardSectionManager.php`) responsible for registering dashboard sections, managing AJAX lazyload requests (`wp_ajax_tutor_get_dashboard_section`), and executing view rendering. Consumes `InstructorMetricsAdapter` for domain data and exposes dual public APIs (`get_section_data()` and `get_section_html()` / `render_section()`).

### tutorLazySection

The frontend Alpine.js component that controls skeleton states, fetches HTML partials and chart payloads via `window.TutorCore.api.wpPost`, mounts the resulting DOM tree with `Alpine.initTree()`, and listens for global filter events (`tutor:date-changed`, `tutor:sort-changed`) to trigger reactive in-place refreshes.
