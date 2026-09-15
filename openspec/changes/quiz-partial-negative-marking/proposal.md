# Change: Quiz Partial and Negative Marking

## Why

Tutor LMS auto-grades quizzes on an all-or-nothing basis: multi-item questions award zero if a single item is incorrect, and wrong answers carry no penalty. Real-world assessments require rewarding partial understanding on multi-item questions and penalizing guessing with negative marks.

In addition, open-ended and short answer questions require manual grading with numeric marks and qualitative feedback rather than binary all-or-nothing overrides. These questions strictly carry `pending` or `graded` statuses (never `correct`, `incorrect`, or `partial`) and are unaffected by partial or negative marking settings. Skipped questions must never incur penalties, remain completely hidden from students as in existing Tutor behavior, display a `Skipped` badge for instructors only, and must not allow manual override buttons. Both Tutor Legacy and v4 interfaces must support these workflows.

Finally, students taking quizzes need clear upfront visibility in the course player (learning area quiz intro) into whether partial marking is enabled and how negative marking penalties will be deducted before starting an attempt.

This change introduces:

1. Four-state correctness tracking (`pending`, `correct`, `partial`, `incorrect`) for auto-graded questions.
2. Pro-backed proportional scoring on six auto-graded multi-item question types.
3. Pro-backed negative marking deducting points for incorrect answers without driving final quiz scores below zero.
4. Independent admin controls in a dedicated `Grading` tab with confirmation modals, default negative marking penalty inheritance, and quiz grandfathering.
5. Manual grading across Tutor Legacy and v4 for `Open-Ended` and `Short Answer` questions with numeric marks and question feedback, using strictly `pending` and `graded` statuses.
6. Removal of binary override buttons (`[✓]` / `[✕]`) for skipped and manually gradeable questions, with skipped questions kept hidden from students.
7. Quiz intro parameter table rows for Partial marking and Negative marking in the Learning Area (`content.php`).

## What Changes

- **Attempt answer correctness**: Core stores `is_correct` as `1` (correct), `2` (partial), `0` (incorrect), or `null` (pending review). Option banks stay strictly `0` or `1`.
- **Display states & Skipped questions**:
  - Auto-graded answers map to `correct`, `partial`, `incorrect`.
  - Skipped questions remain **hidden from students** (preserving existing Tutor LMS behavior). For instructors, skipped questions display a `Skipped` badge with 0 marks, no negative penalty, and no override buttons.
- **Pro auto-graded scoring**: Six question types support partial scoring: matching, image matching, ordering, fill-in-the-blank, image answering, and multiple choice with multiple correct answers.
- **Pro negative marking**: Instructors configure negative marking at the quiz level (percent or fixed deduction per wrong item or question). Final quiz marks floor at `0.00`.
- **Learning Area Quiz Overview (`content.php` & `Quiz::render_quiz_summary`)**:
  - When `enable_partial_marking` is on, renders `Partial marking` parameter row with value `Enabled`.
  - When `enable_negative_marking` is on, renders `Negative marking` parameter row:
    - If negative mark type is `fixed`: displays `-{value} for wrong answers` (e.g. `-0.10 for wrong answers`).
    - If negative mark type is `percent`: calculates penalty per question using `QueryHelper`. If question penalties vary, displays range `{min} – {max}` (e.g. `0.05 – 0.25`); if uniform, displays `-{value} for wrong answers`.
  - Rows are omitted if their respective features are disabled.
- **Manual grading for Open-Ended & Short Answer (Legacy and v4)**:
  - Strict statuses: strictly **`pending`** (before review) and **`graded`** (after review). Never marked as `correct`, `incorrect`, or `partial`.
  - Unbothered by partial or negative marking settings.
  - Replaces binary `[✓]` / `[✕]` buttons with a numeric obtained marks input (`[ obtained_mark ] / {question_mark}`).
  - Provides question feedback authoring across two interfaces:
    - **v4 Instructor Dashboard**: Inline feedback expansion (Add feedback $\to$ Save $\to$ Show feedback $\to$ Edit / Delete directly without a confirmation dialog because review is saved in one go).
    - **Legacy WP-Admin**: Modal-based feedback flow in the table view (Add Feedback modal $\to$ Show Feedback modal $\to$ Delete confirmation modal: _\"Are you sure you want to delete this feedback?\"_).
    - **Student view**: Displays `Graded` badge with `Score: X/Y` and a _\"Feedback from instructor\"_ callout box beneath the student's submitted response.
- **Restricted manual override buttons**:
  - Binary `[✓]` / `[✕]` buttons are **removed** for skipped questions and for manually gradeable questions (`open_ended`, `short_answer`).
  - Auto-graded questions retain `[✓]` / `[✕]` buttons, displaying `(Overrides the auto-graded result)` when overridden.
- **Admin Grading settings & Value Inheritance**:
  - The `Gradebook` menu in Tutor admin settings is renamed to `Grading` and registers whenever Tutor Pro is active (independent of the Gradebook add-on).
  - Contains an `Automatic Assessment` block with `enable_quiz_partial_marking` and `enable_quiz_negative_marking` (with default penalty value and Pts/% dropdown).
  - Quizzes inherit the default negative mark penalty value from admin settings, which can be overridden at the quiz level.
  - Includes confirmation modals on turn-off:
    - Partial marking turn-off confirms that existing quizzes continue working as configured.
    - Negative marking turn-off conditionally confirms if any quiz has customized negative marking values.
- **Quiz grandfathering**: Turning off partial marking in Admin Settings affects only new quizzes; existing quizzes with partial marking enabled continue to score partially and show the setting in the Course Builder.
- **Course Builder UI & validation**:
  - `QuizSettings.tsx` exposes `Partial marking` (`FormSwitch`) and `Negative marking` (`FormCheckbox`) under the `Grading` section.
  - Uses `FormInputWithContent.tsx` for negative mark value input with unit label (`Pts` or `%`).
  - While users currently cannot change the negative value type in `QuizSettings.tsx` (locked/fixed as Pts/fixed), the form structure retains the `negative_mark_type` field for future extensibility.
  - `react-hook-form` validation rules validate range (0–100 for percent, $\ge 0$ for fixed).
- **Architecture & Conventions**:
  - Use `Tutor\Helpers\QueryHelper` for database queries where possible.
  - Use `Tutor\Traits\JsonResponse` trait for JSON responses.

## Capabilities

### New Capabilities

- `quiz-attempt-answer-status`: Attempt-answer four-state correctness for auto-graded questions, `pending`/`graded` status for manual questions, and instructor-only Skipped badging.
- `quiz-partial-negative-scoring`: Pro-backed grader for partial credit across six question types and negative penalties with floor at zero.
- `quiz-partial-negative-settings`: Admin Grading settings, confirmation modals, default penalty settings inheritance, quiz grandfathering, Course Builder `QuizSettings.tsx` controls and validation with `FormInputWithContent`, and Learning Area quiz overview parameter rows.
- `quiz-manual-grading`: Numeric manual grading and per-question feedback flow for `open_ended` and `short_answer` questions across Legacy and v4, removal of override buttons for skipped/manual questions, and student feedback display.

## Non-Goals

- Per-question partial/negative scoring overrides in question editors.
- Negative marking driving final quiz marks below zero.
- Student-facing Skipped questions (kept hidden from students as currently).
- Arbitrary partial scoring on single-choice, true/false, or H5P questions.

## Impact

- Core: `QuizModel.php`, `Quiz.php`, `templates/learning-area/quiz/content.php`, `attempt-details.php` (both legacy view and v4 shared component), `question-header.php`, `question.php`, `open-ended.php`, and `Course.php`.
- Pro: `Quiz.php`, `QuizGrader.php`, and options filters.
- Frontend: `QuizSettings.tsx`, `quiz.js`, admin CSS/SCSS, and Alpine.js attempt review components.
- Database: No schema migrations. Attempt-row `is_correct` tinyint already supports `2`. Question feedback persists cleanly in `attempt_info`.
