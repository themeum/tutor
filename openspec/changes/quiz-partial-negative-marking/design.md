## Context

See proposal.md for motivation. Core submit in `tutor/classes/Quiz.php` still writes all-or-nothing `achieved_mark` and `is_correct` `0`/`1` (`null` for manual review), then Pro and H5P already rewrite rows through `tutor_filter_quiz_answer_data` and `tutor_filter_quiz_total_marks`. The attempt-answers table already has `minus_mark` (always `0` today) and a nullable `is_correct` tinyint. Quiz options are snapshotted into `attempt_info` at attempt start. PHP treats `2` as truthy, so today's `(bool) $is_correct` / `! empty( $is_correct )` consumers would show a partial row as fully correct.

## Goals / Non-Goals

**Goals:**

- Keep core submit all-or-nothing; Pro rewrites marks and status after that line.
- Encode partial on the existing attempt `is_correct` column (`2`) with no migration.
- Route every attempt-row status read through an explicit `1` / `2` / `0` / `null` compare.
- Snapshot-safe flags: grade from `attempt_info` plus `question_settings`, never live quiz meta.
- Inject builder UI through existing course-builder slots so free `QuizSettings` / `QuestionConditions` stay thin.

**Non-Goals:**

- Rewriting the core grading loop or adding a new status column.
- Partial credit for true/false, single-select MC, essays, H5P, or modern Pro types.
- Recalculating historical attempts or allowing a negative quiz total.
- Instructor partial-credit slider.

## Decisions

### Decision: Persist partial on attempt `is_correct = 2`

Reuse `{prefix}tutor_quiz_attempt_answers.is_correct` instead of inferring partial from `achieved_mark` or adding a column. Status must survive a zero score after negative marking.

Alternatives considered:

- Infer partial from `0 < achieved_mark < question_mark` — rejected; negative marking can floor a partial answer to `0`.
- New `answer_status` column — rejected; the tinyint is already nullable and unused values are available.

Add `QuizModel` constants (`ATTEMPT_ANSWER_INCORRECT = 0`, `ATTEMPT_ANSWER_CORRECT = 1`, `ATTEMPT_ANSWER_PARTIAL = 2`) and change `get_attempt_answer_status()` to return `correct` | `partial` | `incorrect` | `pending`. Do not teach the option bank a `2`.

### Decision: Pro QuizGrader behind existing filters

Put formulas in a dedicated Pro helper (`tutor-pro/includes/QuizGrader.php` or `tutor-pro/classes/QuizGrader.php`) and call it from `TUTOR_PRO\Quiz`. Gate by question type so H5P and draw/pin/puzzle/scale/graph callbacks stay exclusive.

Total-marks rule: core adds full marks only when 100% correct. For a row that was not fully correct before Pro ran, **add** the new `achieved_mark`. For a fully correct row, leave the running total alone.

Alternative considered: move all six types into core submit — rejected; scoring is Pro-only and the filter path already exists.

### Decision: Moodle-style multiple-choice formula

Use `correct_selected / total_correct - incorrect_selected / total_incorrect` so “select all” cannot full-score. Skip the penalty term when `total_incorrect` is `0`.

Alternative considered: `correct_selected / total_options` — rejected; it rewards checking every box.

### Decision: Settings live in existing blobs; UI via Pro slots

Quiz keys on `tutor_quiz_option` via `tutor_quiz_default_settings`. Question keys on `question_settings` via `tutor_question_default_settings` and `tutor_quiz_question_data`. Prefer merging on `tutor_quiz_settings_updated` if slot fields land outside `payload.quiz_option`. Add a one-line core `tutor_quiz_option_data` filter only if that merge cannot see the keys. If `convertQuizFormDataToPayload` still drops unknown keys, add a small core passthrough behind `tutorConfig.tutor_pro_url` (same pattern as `answer_explanation`).

Quiz defaults: `Curriculum.Quiz.bottom_of_settings`. Question overrides: `Curriculum.Quiz.bottom_of_question_sidebar` via `registerContent` (inherit/on/off plus type gating). Mirror question controls in the Pro content bank.

### Decision: Instructor review stays binary

Review still writes `is_correct` `1` or `0` and full or zero marks. Fix `apply_quiz_answer_review()` so a previous `2` still adjusts `earned_marks` (today only `0`/`null` add marks when marking correct, and only `1` subtracts when marking incorrect). Use the previous `achieved_mark` as the delta, not a hardcoded full question mark, so partial → incorrect subtracts what was actually awarded.

## Risks / Trade-offs

- [Truthy `is_correct = 2`] → Audit every attempt-row consumer; leave option-bank reads alone. Mitigation: route UI through `get_attempt_answer_status()` and use `=== 1` / `=== 2`.
- [Filter collision with H5P and modern Pro types] → Gate the new grader by the six v1 types only.
- [Payload whitelist drops slot fields] → Pro merge hook first; core passthrough only if save does not receive the keys.
- [Guessing on multi-correct MC] → Moodle-style wrong-option penalty.
- [Pass/fail “correct count”] → Count `is_correct === 1` only; add a separate partial count.
- [Decimals] → Round `achieved_mark` and `minus_mark` to 2 places to match `decimal(8,2)`.

## Migration Plan

- No schema migration. Defaults off keep existing quizzes all-or-nothing.
- Deploy core status/display first so stored `2` never renders as fully correct, then Pro grader and settings.
- Rollback: deactivate Pro to stop new partial scoring; core still displays any stored `2`.
- Do not backfill historical rows.

## Open Questions

None. Scope, formulas, storage, and v1 exclusions are fixed by the approved feasibility plan.
