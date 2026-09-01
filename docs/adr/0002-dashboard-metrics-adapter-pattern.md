# ADR 0002: Separating Domain Metric Adaptation from Dashboard Presentation using the Adapter Pattern

**Status:** Accepted  
**Date:** 2026-09-01  
**Context:** Tutor LMS Core (`classes/InstructorMetricsAdapter.php`, `classes/DashboardSectionManager.php`) & Tutor Pro  
**Deciders:** Core Engineering Team

---

## 1. Context and Problem Statement

Previously, dashboard metric queries were placed in `InstructorMetricsService.php`, while AJAX dispatch and template loading lived in `DashboardSectionManager.php`. This created a split-knowledge problem where every dashboard section required modifying two separate classes with a tightly coupled 1:1 dependency.

Moreover, underlying database queries produced heterogeneous data types (`stdClass` from `WithdrawModel`, nested arrays from `Analytics`, raw post objects from `CourseModel`, and mixed types from `tutor_utils()`). Consumers lacked a normalized data schema and a clean mechanism to choose between **pure domain data** (arrays, raw values) and **rendered HTML markup**.

---

## 2. Decision

We will decouple data adaptation from presentation using the **Adapter Pattern**:

1. **`InstructorMetricsAdapter` (Data Adapter)**:
   - Queries and adapts disparate models (`WithdrawModel`, `CourseModel`, `Analytics`, `tutor_utils()`) into normalized, standardized schema contracts (`stat_cards`, `overview_chart`, `course_completion_distribution`, `top_performing_courses`, `upcoming_tasks`, `recent_reviews`).
   - 100% presentation-agnostic (0% HTML) and reusable across REST APIs, CLI commands, mobile endpoints, and CSV exports.

2. **`DashboardSectionManager` (Controller & View Renderer)**:
   - Manages section metadata and AJAX lazyloading lifecycle (`wp_ajax_tutor_get_dashboard_section`).
   - Exposes dual public APIs:
     - `DashboardSectionManager::get_section_data( $id, $params )`: Directly calls `InstructorMetricsAdapter` to return normalized data.
     - `DashboardSectionManager::get_section_html( $id, $params )`: Consumes the adapted data and renders the template partial.
     - `DashboardSectionManager::render_section( $id, $params )`: Combines data and HTML for the full AJAX payload.

---

## 3. Consequences

### Positive:

- **Strict Single Responsibility**: `InstructorMetricsAdapter` changes only when underlying database models change; `DashboardSectionManager` changes only when dashboard views/AJAX change.
- **Consumer Flexibility**: Callers can request pure domain data or rendered HTML on demand.
- **Simplicity**: Direct, straightforward method calls without complex strategy wrappers.
