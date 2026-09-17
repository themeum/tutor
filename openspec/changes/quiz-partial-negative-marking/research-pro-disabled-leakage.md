# Research: Decoupling Partial & Negative Marking via Hooks (Free / Pro Architecture)

## Executive Summary

Partial and negative marking are Tutor Pro features. In keeping with Tutor LMS architectural standards, **Tutor Free core must NOT contain Pro-specific logic, Pro-specific math, Pro-specific translation strings, or hardcoded Pro checks**.

Free core acts as an extensible foundation that provides extension points (**actions and filters**). Tutor Pro connects to these hooks to inject Pro features (partial status, `{N}/{M} correct` badge, penalty delta display, and partial summary stats). When Tutor Pro is inactive or disabled, the hooks simply do not fire, ensuring Free behaves strictly as a binary grading system with zero Pro artifacts.

**Data Persistence Policy**: Data already saved to the database (`achieved_mark`, `earned_marks`, `earned_percentage`, etc.) is **not recalculated or altered** when displayed. The system preserves historical score data as recorded in the DB, while strictly removing Pro UI representations (badges, breakdown cards, penalty notices, delta calculations) when Pro is disabled.

---

## Architectural Audit: Current Leaks in Free vs. Hook-Based Architecture

| Feature Area                                 | Current Free Implementation (Leakage)                                                                                                                                                               | Proposed Decoupled Hook-Based Solution                                                                                                                                                                                                                                                                                                                                                                                                         |
| :------------------------------------------- | :-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | :--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Answer Status**                            | `QuizModel::get_attempt_answer_status()` directly checks `ATTEMPT_ANSWER_PARTIAL` (`2`) and returns `'partial'`.                                                                                    | Free evaluates standard binary statuses (`correct`, `incorrect`, `pending`, `graded`, `skipped`) and calls `apply_filters( 'tutor_quiz_attempt_answer_status', $status, $attempt_answer )`. Pro hooks this filter to return `'partial'` when `is_correct === 2`. When Pro is disabled, Free naturally returns `'incorrect'` for non-correct answers.                                                                                           |
| **`N/M Correct` Badge & Calculation**        | `QuizModel.php` in Free contains `get_attempt_answer_correct_counts()` (~115 lines parsing question types and counting user matches) and formats `{N}/{M} correct` in `get_attempt_answer_badge()`. | Delete `get_attempt_answer_correct_counts()` from Free. Move it to `TutorPro\QuizGrader`. Free's `get_attempt_answer_badge()` defines standard binary badges (`Correct`, `Incorrect`, `Pending`, `Graded`, `Skipped`) and applies `apply_filters( 'tutor_quiz_attempt_answer_badge', $badge, $attempt_answer )`. Pro hooks this filter, computes counts, and injects `{N}/{M} correct` or `Partially correct` under `'tutor-pro'` text domain. |
| **Summary Stat Card**                        | `templates/shared/components/quiz/attempt-details/summary.php` hardcodes `<div class="tutor-quiz-result-static-item partial">` showing `%d partially correct`.                                      | Remove the `partial` div from Free entirely. Between `correct` and `incorrect`, Free exposes `do_action( 'tutor_quiz_attempt_summary_statics_after_correct', $attempt_data, $answers )`. Pro hooks this action, verifies `enable_partial_marking == '1'`, and renders the partial stat item. When Pro is disabled, Free renders only Correct, Incorrect, and Total.                                                                            |
| **Result Column Penalty Delta**              | `views/quiz/attempt-details.php` hardcodes `$earned_val = round( $achieved_val + $minus_val, 2 )` and renders `<div class="tutor-quiz-result-delta">(+{earned}) -{penalty}</div>`.                  | Remove delta calculations and markup from Free. Free fires `do_action( 'tutor_quiz_attempt_details_result_badge_after', $answer, $answer_status, $attempt_id )`. Pro hooks this action to render the penalty delta when negative marking applies.                                                                                                                                                                                              |
| **Answer Counts**                            | `QuizModel::get_attempt_answer_counts()` hardcodes `'partial' => 0`.                                                                                                                                | Free initializes `$counts` with only `'correct'` and `'incorrect'`. Applies `apply_filters( 'tutor_quiz_attempt_answer_counts', $counts, $answers )`. Pro hooks this filter to add `'partial'` count.                                                                                                                                                                                                                                          |
| **Scores & Saved Marks**                     | Question scores and attempt earned marks are displayed from the DB.                                                                                                                                 | Stored database values (`achieved_mark`, `earned_marks`, `earned_percentage`) are displayed as recorded in the DB without recalculation. When Pro is disabled, Pro visual breakdowns and penalty deltas are omitted, but historical DB scores are preserved.                                                                                                                                                                                   |
| **`has_partial_or_negative_marking` Helper** | `QuizModel::has_partial_or_negative_marking()` directly inspects serialized flags in Free core.                                                                                                     | Free implements `return (bool) apply_filters( 'tutor_quiz_has_partial_or_negative_marking', false, $attempt_info );`. Pro hooks this filter and delegates to `TutorPro\QuizGrader::has_partial_or_negative_marking()`.                                                                                                                                                                                                                         |

---

## Detailed Hook Specifications

### 1. `tutor_quiz_attempt_answer_status` (Filter)

- **Location**: [`tutor/models/QuizModel.php::get_attempt_answer_status()`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/models/QuizModel.php#L1094)
- **Signature**: `apply_filters( 'tutor_quiz_attempt_answer_status', string $status, object $attempt_answer ): string`
- **Free Default**: Returns `'skipped'`, `'pending'`, `'graded'`, `'correct'`, or `'incorrect'`.
- **Pro Hook**: In `tutor-pro/classes/Quiz.php`, returns `'partial'` if `(int) $attempt_answer->is_correct === QuizModel::ATTEMPT_ANSWER_PARTIAL`.

### 2. `tutor_quiz_attempt_answer_badge` (Filter)

- **Location**: [`tutor/models/QuizModel.php::get_attempt_answer_badge()`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/models/QuizModel.php#L1126)
- **Signature**: `apply_filters( 'tutor_quiz_attempt_answer_badge', array $badge, object|null $attempt_answer ): array`
- **Free Default**: Standard binary badge (`Correct`, `Incorrect`, `Pending`, `Graded`, `Skipped`).
- **Pro Hook**: In `tutor-pro/classes/Quiz.php`, when status is `'partial'`, calls `QuizGrader::get_attempt_answer_correct_counts()` and populates `{N}/{M} correct` label under `'tutor-pro'` domain.

### 3. `tutor_quiz_attempt_answer_counts` (Filter)

- **Location**: [`tutor/models/QuizModel.php::get_attempt_answer_counts()`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/models/QuizModel.php#L1218)
- **Signature**: `apply_filters( 'tutor_quiz_attempt_answer_counts', array $counts, array|null $answers ): array`
- **Free Default**: `['correct' => X, 'incorrect' => Y]`.
- **Pro Hook**: In `tutor-pro/classes/Quiz.php`, adds `'partial' => Z`.

### 4. `tutor_quiz_attempt_summary_statics_after_correct` (Action)

- **Location**: [`tutor/templates/shared/components/quiz/attempt-details/summary.php:183`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/templates/shared/components/quiz/attempt-details/summary.php#L183)
- **Signature**: `do_action( 'tutor_quiz_attempt_summary_statics_after_correct', object $attempt_data, array $answers ): void`
- **Free Default**: Nothing rendered.
- **Pro Hook**: In `tutor-pro/classes/Quiz.php`, if attempt has partial marking enabled, renders `<div class="tutor-quiz-result-static-item partial">`.

### 5. `tutor_quiz_attempt_details_result_badge_after` (Action)

- **Location**: [`tutor/views/quiz/attempt-details.php:816`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/views/quiz/attempt-details.php#L816)
- **Signature**: `do_action( 'tutor_quiz_attempt_details_result_badge_after', object $answer, string $answer_status, int $attempt_id ): void`
- **Free Default**: Nothing rendered.
- **Pro Hook**: In `tutor-pro/classes/Quiz.php`, renders `<div class="tutor-quiz-result-delta">` if penalty `minus_mark > 0`.

### 6. `tutor_quiz_has_partial_or_negative_marking` (Filter)

- **Location**: [`tutor/models/QuizModel.php::has_partial_or_negative_marking()`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/models/QuizModel.php#L2063)
- **Signature**: `apply_filters( 'tutor_quiz_has_partial_or_negative_marking', bool $default, array|string $attempt_info ): bool`
- **Free Default**: `false`.
- **Pro Hook**: In `tutor-pro/classes/Quiz.php`, delegates to `QuizGrader::has_partial_or_negative_marking()`.
