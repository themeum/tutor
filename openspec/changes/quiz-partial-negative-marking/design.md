# Design: Quiz Partial & Negative Marking, Admin Settings, and Manual Grading (Legacy & v4)

## Architecture Overview

Core submits quiz attempts with baseline calculations, then Pro refines scores via filter hooks when Pro features are enabled. Core handles attempt statuses, UI presentation across both Tutor Legacy and v4 architectures, admin option registration, manual review flows, and question-level feedback.

```
+-------------------------------------------------------------------------------+
| Course Builder (QuizSettings.tsx) & Admin Settings (Grading Tab)               |
| - Admin: 'Grading' tab (Pro active, independent of Gradebook add-on)          |
|   - Automatic Assessment block: Partial marking & Negative marking switches   |
|   - Default negative mark penalty amount and mode                             |
|   - Confirmation modals on turn-off (Negative conditionally checks DB usage)  |
|   - Grandfathering: Turning off affects new quizzes only                      |
| - Builder: QuizSettings.tsx under 'Grading' section:                          |
|   - FormSwitch for Partial marking (visible if Pro active && enabled)         |
|   - FormCheckbox for Negative marking + FormInputWithContent for penalty      |
|   - Negative mark value inherits admin default (overridable in quiz)          |
|   - negative_mark_type locked/read-only in UI, maintained for future typing   |
|   - react-hook-form validation on negative mark value (0-100% or >=0 Pts)     |
+-------------------------------------------------------------------------------+
                                    |
                                    v
+-------------------------------------------------------------------------------+
| Learning Area Quiz Intro: templates/learning-area/quiz/content.php            |
| - Quiz::render_quiz_summary() renders overview parameters table:              |
|   - Partial marking: Enabled (if enabled)                                     |
|   - Negative marking: -{val} for wrong answers (fixed) or {min} – {max}       |
|     (percent with varying question marks, queried via QueryHelper)            |
+-------------------------------------------------------------------------------+
                                    |
                                    v
+-------------------------------------------------------------------------------+
| Core Submit & Snapshot: Quiz.php                                              |
| - Writes attempt row with attempt_info containing settings snapshot           |
| - Writes attempt_answers rows with initial is_correct (0, 1, or null)        |
+-------------------------------------------------------------------------------+
                                    |
                                    v
+-------------------------------------------------------------------------------+
| Pro Filter Pipeline: TUTOR_PRO\Quiz                                           |
| - Hook tutor_filter_quiz_answer_data                                          |
|   - Reads snapshot flags from attempt_info                                    |
|   - Computes proportional earned marks for 6 auto-graded multi-item types      |
|   - Applies negative penalties (fixed or percent) if enabled                  |
|   - Writes achieved_mark, minus_mark, and is_correct = 2 (partial)           |
| - Hook tutor_filter_quiz_total_marks                                          |
|   - Sums achieved marks and clamps total earned marks to max(0.0, total)      |
+-------------------------------------------------------------------------------+
                                    |
                                    v
+-------------------------------------------------------------------------------+
| Legacy & v4 Presentation, Stats & Manual Review Flows                         |
| - Auto-graded: null -> pending, 1 -> correct, 2 -> partial, 0 -> incorrect     |
| - Manual gradeable (open_ended, short_answer):                                |
|   - STRICTLY 'pending' or 'graded' (NEVER correct, incorrect, or partial)     |
|   - Completely unbothered by partial marking and negative marking             |
| - Skipped questions:                                                          |
|   - Hidden from students (maintains existing Tutor behavior)                  |
|   - Visible to instructors with 'Skipped' badge, 0 marks, and no penalty      |
| - Override buttons [✓] / [✕]:                                                 |
|   - REMOVED for Skipped questions and Manually Gradeable questions            |
|   - Kept for Auto-Graded answered questions (adds '(Overrides auto-graded)')  |
| - Dual interface implementations:                                             |
|   - v4 Instructor Dashboard (shared.components): Inline feedback (no modal)   |
|   - Legacy WP-Admin (views/quiz/attempt-details.php): Modal feedback flow     |
|   - Student View: Displays 'Graded' badge & 'Feedback from instructor' box    |
+-------------------------------------------------------------------------------+
```

## Key Decisions

### Decision: Four-state `is_correct` for auto-graded questions

We store `2` in `tutor_quiz_attempt_answers.is_correct` for partially correct auto-graded answers. Option banks continue using only `0` and `1`. No schema migration is required.

### Decision: Remove `Partially correct` badge and show `N/M correct` badge in question header

- For partially graded questions (`is_correct = 2`), the static `Partially correct` badge is completely removed.
- Instead, the question header in both v4 (`question-header.php`) and Legacy (`views/quiz/attempt-details.php`) displays an **`{N}/{M} correct`** badge with warning styling (`Badge::WARNING` / `label-warning`).
- `N` is the number of correct answers/items given by the user, and `M` is the total number of correct answers/items possible for that question:
  - **Matching / Image matching / Ordering**: $N$ = correctly placed items, $M$ = total items.
  - **Fill-in-the-blank**: $N$ = correctly matched blanks, $M$ = total blanks.
  - **Image answering**: $N$ = correctly answered image labels, $M$ = total images.
  - **Multiple choice (multi-correct)**: $N$ = correct options selected by user, $M$ = total correct options.
- Attempt summary stats (e.g. `X partially correct` count) and sidebar question navigator dots continue to represent partial status, but the per-question header badge specifically uses `{N}/{M} correct`.

### Decision: Manual grading status is strictly `pending` or `graded`

- Manually gradeable questions (`open_ended` and `short_answer`) are **never** categorized as `correct`, `incorrect`, or `partial`.
- Their status lifecycle is strictly:
  - **`pending`** (rendered as `Pending` / `Pending Review`) before manual scoring.
  - **`graded`** (rendered as `Graded` with label `Score: {achieved_mark}/{question_mark}`) once scored.
- **Unbothered by partial and negative marking**:
  - Quiz-level partial marking toggle and negative marking settings have zero impact on manual questions.
  - Attempt-level stats (e.g. counts of Correct, Incorrect, Partial answers) strictly exclude `open_ended` and `short_answer` questions from those buckets.
- Numeric marks input: `[ obtained_mark ] / {question_mark}` (e.g., `[ 3.0 ] / 5`), accepting values from `0` to `question_mark`.
- Recalculates total attempt `earned_marks` by delta: `attempt.earned_marks + (new_mark - previous_mark)`.

### Decision: Skipped questions stay hidden from students

- In student-facing views, skipped questions are not displayed, maintaining existing Tutor LMS behavior.
- In instructor views (both Legacy WP-Admin table and v4 Instructor Dashboard), skipped questions are visible with a `Skipped` badge.
- Skipped questions earn `0.00` points and **never receive negative marking penalties**.
- **Manual override buttons `[✓]` and `[✕]` are removed** for skipped questions.

### Decision: Dual implementation across Legacy and v4

1. **v4 Instructor Dashboard (`templates/shared/components/quiz/attempt-details/*`)**:
   - Card layout with Alpine.js form bindings.
   - Initial state: `[ —— ] / 5` with `💬 Add Feedback` button (shown when no feedback exists). When feedback exists, `👁 Show Feedback` button is shown instead.
   - Clicking `Add Feedback` or `Show Feedback` expands an inline feedback panel titled **"Write feedback"** containing a `<textarea name="question_feedback[{attempt_answer_id}]">` and `Cancel` and `Save` buttons.
   - The panel-level **`Save` button is client-side only**: it commits the draft textarea content to Alpine state and collapses the panel. **No API call is fired at this point.**
   - The panel-level **`Cancel` button** reverts the draft to the last committed state and collapses the panel, discarding any unsaved edits.
   - In "Show Feedback" mode (when existing feedback is loaded from the server), a **`Delete` button** (red) is also shown inside the panel. Clicking `Delete` clears the feedback field to an empty string in Alpine state — no API call at that point. **No confirmation dialog** is shown.
   - **All data (obtained marks + feedback) is sent in a single API call** when the instructor clicks the page-level form `Submit` button. There is no per-question or per-feedback AJAX call in v4.
   - The server-side AJAX handler for the submit reads feedback as a POST array via `Input::post('question_feedback', [], Input::TYPE_ARRAY)` (keyed by `attempt_answer_id`), and reads marks via `Input::post('manual_marks', [], Input::TYPE_ARRAY)` (keyed by `question_id`).
2. **Legacy WP-Admin (`views/quiz/attempt-details.php`)**:
   - Table-based row review with `Manual Review` column.
   - Shows `[ obtained_mark ] / {question_mark}` and `Add Feedback` / `Show Feedback`.
   - Clicking `Add Feedback` opens an AJAX modal dialog: "Write feedback" with close 'X', textarea, `Cancel` and `Save`.
   - Clicking `Show Feedback` opens \"Edit feedback\" modal with `Delete`, `Cancel`, and `Save`.
   - Clicking `Delete` opens the confirmation modal: _\"Are you sure you want to delete this feedback?\"_ with `[No, keep it]` and `[Yes, delete]`.
3. **Student View (Legacy & v4)**:
   - Displays `Graded` badge with `Score: X/Y`.
   - Displays a dedicated callout section: **\"Feedback from instructor\"** beneath the student's submitted answer.
   - Completely omits skipped questions.

### Decision: Question-level feedback storage

- Per-question instructor feedback is stored in `attempt_info['question_feedback'][$attempt_answer_id]`.
- Backwards compatible, requires no table alterations, and is automatically preserved with quiz attempt exports and backups.

### Decision: Auto-graded question override buttons

- Auto-graded answered questions retain `[✓]` and `[✕]` manual override buttons.
- When an auto-graded question has been overridden by an instructor, the UI displays `(Overrides the auto-graded result)` beneath the buttons.
- Manually gradeable questions (`open_ended`, `short_answer`) and skipped questions do NOT show `[✓]` / `[✕]` buttons.

### Decision: Admin `Grading` settings tab & decoupling from Gradebook

- The settings menu is renamed to `Grading` (`slug: grading`, `label: Grading`).
- Registers whenever Tutor Pro is active, without requiring the Gradebook add-on.
- When Gradebook add-on is enabled, its configuration block appears first; the `Automatic Assessment` block appears below it. When Gradebook is disabled, only `Automatic Assessment` appears.
- Confirmation modals on turn-off:
  - **Partial marking**: Informs instructor that existing quizzes continue working as configured while new quizzes won't have partial marking.
  - **Negative marking**: Queries DB for any customized negative marking values. If found, shows modal; if none found, turns off immediately.
- **Grandfathering**: Quizzes with `enable_partial_marking == 1` continue scoring partially and keep the toggle active in the Course Builder even when the admin toggle is OFF.

### Decision: Learning Area Quiz Summary Parameters Table (`content.php` & `Quiz::render_quiz_summary`)

- In `templates/learning-area/quiz/content.php`, pass `$quiz_id` to `Quiz::render_quiz_summary()`.
- **Decoupled Architecture & Filter Injection**:
  - Tutor Free core's `Quiz::render_quiz_summary()` applies the `tutor_quiz_summary_parameters` filter on `$quiz_summary` before rendering the table:
    `$quiz_summary = apply_filters( 'tutor_quiz_summary_parameters', $quiz_summary, $quiz_id );`
  - Tutor Pro injects standard table rows directly into `$quiz_summary`.
  - Tutor Pro (`tutor-pro/classes/Quiz.php`) hooks into `tutor_quiz_summary_parameters` via `add_filter( 'tutor_quiz_summary_parameters', array( $this, 'add_quiz_summary_parameters' ), 10, 2 );`.
  - All Pro-specific logic (checking `tutor_utils()->get_quiz_option()`, localized strings in `'tutor-pro'`, and negative penalty calculations) lives entirely in Tutor Pro.
- **Partial marking row** (injected by Tutor Pro):
  - Injected when Tutor Pro is active and `enable_partial_marking` is true on the quiz.
  - Icon: `Icon::CHECK_SQUARE`, Column: `__( 'Partial marking', 'tutor-pro' )`, Value: `__( 'Enabled', 'tutor-pro' )`.
- **Negative marking row** (injected by Tutor Pro):
  - Injected when Tutor Pro is active and `enable_negative_marking` is true on the quiz.
  - Icon: `Icon::MINUS_SQUARE`, Column: `__( 'Negative marking', 'tutor-pro' )`.
  - Penalty value calculation (computed within Tutor Pro via `get_negative_marking_summary_label`):
    - If `negative_mark_type === 'fixed'`:
      - Single penalty for all questions: `-{value} for wrong answers` (e.g. `-0.10 for wrong answers`).
    - If `negative_mark_type === 'percent'`:
      - Question mark values can vary. The system queries quiz questions using `QueryHelper::get_all( $wpdb->tutor_quiz_questions, ['quiz_id' => $quiz_id], 'question_id' )`.
      - For each question with mark $M$, calculated penalty is $( \text{negative\_mark\_value} / 100 ) \times M$.
      - If penalties vary ($min \neq max$): display range `{min} – {max}` (e.g. `0.05 – 0.25`).
      - If penalties are uniform ($min = max$): display `-{min} for wrong answers`.
  - Both rows are hidden if their respective features are disabled on the quiz.

### Decision: Builder `QuizSettings.tsx` with `FormInputWithContent` and Admin Default Inheritance

- For negative marking penalty value, `QuizSettings.tsx` uses `FormInputWithContent` with `content={negativeMarkType === 'percent' ? '%' : 'Pts'}` and `contentPosition="right"`.
- When initializing or creating a quiz, `negative_mark_value` inherits the admin default negative mark value (`quiz_negative_mark_amount`). Instructors can override this value in the quiz settings.
- Currently, users cannot change the negative mark type from `QuizSettings.tsx` (locked/fixed), but the underlying form state maintains `negative_mark_type` for future support.

### Decision: Standardization on `QueryHelper` and `JsonResponse`

- All new database queries in Tutor use `Tutor\Helpers\QueryHelper` methods (`get_all`, `get_row`, `get_count`, etc.) instead of raw `$wpdb` calls.
- AJAX endpoints use the `Tutor\Traits\JsonResponse` trait for standardized JSON response output.

### Decision: Attempt Details Question Header Score & Penalty Breakdown (Student vs. Instructor View)

- **Question Header Score (`templates/shared/components/quiz/attempt-details/question-header.php`)**:
  - For all evaluated questions (where status is NOT `pending` and NOT `skipped`):
    - For students, display `Score: {achieved_mark}/{question_mark}` in the top right of the question header card.
    - Strictly omitted for instructors (`! $is_instructor_review`).
- **Student View: Under-Explanation Penalty Notice**:
  - In student attempt review:
  - When `enable_negative_marking` is enabled in `attempt_info` and `minus_mark > 0`, render the deducted mark directly beneath the `Answer Explanation` card in red text (e.g. `-{minus_mark} points` such as `-0.25 points`).
  - Omitted when `minus_mark == 0` or negative marking is disabled.
- **Instructor View: Under-Explanation Detailed Mark Breakdown Table**:
  - In instructor review (WP-Admin attempt details & v4 instructor dashboard):
  - Rendered beneath the `Answer Explanation` card for all evaluated questions (strictly excluding `pending` review questions) **only when the quiz attempt has Partial Marking (`enable_partial_marking == '1'`) or Negative Marking (`enable_negative_marking == '1'`) enabled**:
    - Row 1: Earned credit:
      - If question status is `correct` or `incorrect`: label is `Earned`, value is `{earned_mark}` (where earned mark before deduction = `achieved_mark + minus_mark`).
      - If question status is `partial`: label is `Partial credit`, value is `{earned_mark}`.
    - Row 2: Penalty deduction (displayed only when `minus_mark > 0`):
      - Label is `Penalty -{minus_mark} deducted` (styled in red), and value is `-{minus_mark}` (styled in red).
    - Row 3: Final question score:
      - Displayed right-aligned: `Score: {achieved_mark}/{question_mark}`.
  - When neither Partial Marking nor Negative Marking is enabled on the quiz attempt, the breakdown table is completely omitted.
- **Question-Level Net Marks vs Quiz Earned Marks**:
  - Individual question `achieved_mark` is calculated as `earned_mark - minus_mark`. It is NOT clamped to `0.00` at the question level when negative marking is applied — it can be negative (e.g., `-0.25`, `-0.08`).
  - Quiz-level cumulative `earned_marks` continues to floor at `0.00` (`max(0.00, sum(achieved_marks))`), ensuring the overall quiz grade never goes below 0%.

### Decision: Admin Dashboard Quiz Attempt Review Table Result Column (`views/quiz/attempt-details.php`)

In the WP-Admin legacy quiz attempt details table, the `Result` column (`<td class="result">`) renders a badge and conditional score/penalty breakdown:

- **`Correct` status**:
  - Badge: `<span class="tutor-badge-label label-success">Correct</span>`
  - Below badge: `<div class="tutor-fs-8 tutor-color-muted tutor-mt-4">Score: {achieved_mark}/{question_mark}</div>`
- **`Partial` status**:
  - Badge: `<span class="tutor-badge-label label-success">{N}/{M} Correct</span>`
  - When `minus_mark > 0`:
    - Line 1: `<div class="tutor-fs-8 tutor-mt-4"><span class="tutor-color-success">(+{earned_mark})</span> <span class="tutor-color-danger">-{minus_mark}</span></div>` (where raw earned credit = `achieved_mark + minus_mark`)
    - Line 2: `<div class="tutor-fs-8 tutor-color-muted tutor-mt-2">Score: {achieved_mark}/{question_mark}</div>`
  - When `minus_mark == 0`:
    - Below badge: `<div class="tutor-fs-8 tutor-color-muted tutor-mt-4">Score: {achieved_mark}/{question_mark}</div>`
- **`Incorrect` status**:
  - Badge: `<span class="tutor-badge-label label-danger">Incorrect</span>`
  - When `minus_mark > 0`:
    - Line 1: `<div class="tutor-fs-8 tutor-mt-4"><span class="tutor-color-success">(+{earned_mark})</span> <span class="tutor-color-danger">-{minus_mark}</span></div>`
    - Line 2: `<div class="tutor-fs-8 tutor-color-muted tutor-mt-2">Score: {achieved_mark}/{question_mark}</div>`
  - When `minus_mark == 0`:
    - Below badge: `<div class="tutor-fs-8 tutor-color-muted tutor-mt-4">Score: {achieved_mark}/{question_mark}</div>`
- **`Pending` status**:
  - Badge: `<span class="tutor-badge-label label-warning">Pending</span>`
  - Omitted from `Result` column; score input lives in the `Manual Review` column (`[ 3.0 ] / 5` with `💬 Add Feedback`).
- **`Skipped` status**:
  - Badge: `<span class="tutor-badge-label label-secondary">Skipped</span>`
  - Omitted from `Result` column; manual review buttons are omitted.
- **`Graded` status**:
  - Badge: `<span class="tutor-badge-label label-success">Graded</span>`
  - Below badge: `<div class="tutor-fs-8 tutor-color-muted tutor-mt-4">Score: {achieved_mark}/{question_mark}</div>`
