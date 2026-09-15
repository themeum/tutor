## Context

See proposal.md for motivation. Core submit in `tutor/classes/Quiz.php` still writes all-or-nothing `achieved_mark` and `is_correct` `0`/`1` (`null` for manual review), then Pro and H5P already rewrite rows through `tutor_filter_quiz_answer_data` and `tutor_filter_quiz_total_marks`. The attempt-answers table already has `minus_mark` (always `0` today) and a nullable `is_correct` tinyint. Quiz options are snapshotted into `attempt_info` at attempt start. PHP treats `2` as truthy, so today's `(bool) $is_correct` / `! empty( $is_correct )` consumers would show a partial row as fully correct.

## Goals / Non-Goals

**Goals:**

- Keep core submit all-or-nothing; Pro rewrites marks and status after that line.
- Encode partial on the existing attempt `is_correct` column (`2`) with no migration.
- Route every attempt-row status read through an explicit `1` / `2` / `0` / `null` compare.
- Snapshot-safe flags: grade from quiz options in `attempt_info`, never live quiz meta or per-question scoring keys.
- Rename the `Gradebook` settings menu to `Grading`, decouple it from the Gradebook add-on (registering whenever Tutor Pro is active), and render an `Automatic Assessment` settings block below the Gradebook settings block when the add-on is enabled (or standalone when disabled).
- Implement quiz settings UI directly in `QuizSettings.tsx` gated by Tutor Pro plugin and admin settings checks, with `react-hook-form` validation on `negative_mark_value`.

**Non-Goals:**

- Rewriting the core grading loop or adding a new status column.
- Per-question partial or negative marking overrides.
- Partial credit for true/false, single-select MC, essays, H5P, or modern Pro types.
- Recalculating historical attempts or allowing a negative quiz total.
- Instructor partial-credit slider.
- Injecting partial/negative quiz settings via injection field slots (`Curriculum.Quiz.bottom_of_settings`).

## Decisions

### Decision: Persist partial on attempt `is_correct = 2` only when quiz partial marking is on

Reuse `{prefix}tutor_quiz_attempt_answers.is_correct` instead of inferring partial from `achieved_mark` or adding a column. Write `2` only when snapshotted quiz `enable_partial_marking` is on and the item-level result is mixed. When quiz partial marking is off, mixed answers stay `0` (Incorrect) with zero marks. When quiz partial marking is on, status must still survive a zero score after negative marking.

Alternatives considered:

- Always store `2` for mixed item results even when partial marking is off — rejected; instructors expect Incorrect when the partial-credit switch is off.
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

### Decision: Admin Grading settings & Automatic Assessment block

Update Tutor Settings to provide a centralized `Grading` menu tab:

- Rename the settings tab from `Gradebook` to `Grading` (`slug: grading`, `label: Grading`).
- Register the tab whenever Tutor Pro is active, without requiring the Gradebook add-on to be enabled.
- Block structure:
  - If the `Gradebook` add-on is enabled, its settings block (`Settings` with letter grades, GPA scale) renders first.
  - An `Automatic Assessment` block (`slug: automatic_assessment`, `label: Automatic Assessment`) renders below the Gradebook settings block.
  - If the `Gradebook` add-on is disabled, the `Grading` tab displays only the `Automatic Assessment` block.
- Fields in `Automatic Assessment`:
  - `enable_quiz_partial_marking`: `toggle_switch`, default `'off'`, label "Partial marking", description "Award credit for correct sub-answers on multi-part questions", with tooltip.
  - `enable_quiz_negative_marking`: `toggle_switch`, default `'off'`, label "Negative marking", description "Deducts points for each wrong answer once enabled", with tooltip.
- Both option keys are registered in Tutor Pro and localized into `Course.php` (`$required_options`) so `tutorConfig.settings` exposes them in the course builder.

### Decision: Quiz-level settings only; percent or fixed negative marks

Store scoring keys on `tutor_quiz_option` via `tutor_quiz_default_settings`:

- `enable_partial_marking`, `enable_negative_marking`
- `negative_mark_type` (`percent` | `fixed`)
- `negative_mark_value` (percentage between `0` and `100` when type is `percent`; absolute marks $\ge 0$ when type is `fixed`)

Do not add question_settings scoring keys or content-bank scoring UI.

UI: Direct implementation in `QuizSettings.tsx` (not via injection field slots). Gate the settings UI behind Tutor Pro and individual admin settings:

1. `enable_partial_marking` toggle is displayed when `isTutorPro && tutorConfig.settings?.enable_quiz_partial_marking === 'on'`.
2. `enable_negative_marking` toggle, type switch, and `negative_mark_value` field are displayed when `isTutorPro && tutorConfig.settings?.enable_quiz_negative_marking === 'on'`.

Validation in Quiz settings:

- In `QuizSettings.tsx`, enforce client-side form validation via `react-hook-form` `rules` on `negative_mark_value`:
  - When `negative_mark_type === 'percent'`: validate `value >= 0 && value <= 100`, showing an error message (e.g., "Percentage must be between 0 and 100") and blocking form submission if violated.
  - When `negative_mark_type === 'fixed'`: validate `value >= 0`, showing an error message (e.g., "Negative mark value must be greater than or equal to 0") and blocking form submission if violated.
  - Inputs also declare `min={0}`, `max={type === 'percent' ? 100 : undefined}`, and `step="any"` for standard input behavior.
  - Note: Admin-side toggles are boolean switches and do not require range validation.

Because inputs are native form controls in `QuizSettings.tsx`, they bind directly to `quiz_option.*` using `useFormContext<QuizForm>()`, natively serializing into standard quiz option payloads without requiring injection slot merges.

Penalty: when answered and not fully correct:

- If `negative_mark_type` is `percent`: `minus_mark = (negative_mark_value / 100) * question_mark`
- If `negative_mark_type` is `fixed`: `minus_mark = negative_mark_value`
  Then `achieved_mark = max(0, marks - minus_mark)`.

Alternatives considered:

- Per-question inherit/on/off for partial and negative — rejected; instructors want a single quiz policy.
- Percent-only negative marks — rejected; instructors also need a solid mark deduction.
- Separate `negative_mark_percent` and `negative_mark_value` keys — rejected; a single `negative_mark_value` interpreted according to `negative_mark_type` is simpler, avoids redundant keys, and maps directly to a single UI input with dynamic unit label.
- Injecting settings via `Curriculum.Quiz.bottom_of_settings` slot — rejected; direct implementation in `QuizSettings.tsx` with Tutor Pro and admin settings checks avoids slot registration and payload merging fragility.

### Decision: Instructor review stays binary

Review still writes `is_correct` `1` or `0` and full or zero marks. Fix `apply_quiz_answer_review()` so a previous `2` still adjusts `earned_marks` (today only `0`/`null` add marks when marking correct, and only `1` subtracts when marking incorrect). Use the previous `achieved_mark` as the delta, not a hardcoded full question mark, so partial → incorrect subtracts what was actually awarded.

## Risks / Trade-offs

- [Truthy `is_correct = 2`] → Audit every attempt-row consumer; leave option-bank reads alone. Mitigation: route UI through `get_attempt_answer_status()` and use `=== 1` / `=== 2`.
- [Filter collision with H5P and modern Pro types] → Gate the new grader by the six v1 types only.
- [Admin setting not localized in builder] → Ensure `enable_quiz_partial_marking` and `enable_quiz_negative_marking` are included in `Course::localize_course_builder_data` so `tutorConfig.settings` exposes them to `QuizSettings.tsx`.
- [Grading settings tab without Gradebook addon] → Decouple tab creation from Gradebook add-on init and conditionally render Gradebook block only when the add-on is active.
- [Guessing on multi-correct MC] → Moodle-style wrong-option penalty.
- [Pass/fail “correct count”] → Count `is_correct === 1` only; add a separate partial count.
- [Decimals] → Round `achieved_mark` and `minus_mark` to 2 places to match `decimal(8,2)`.

## Migration Plan

- No schema migration. Defaults off keep existing quizzes all-or-nothing.
- Existing installations with Gradebook add-on continue to see their GPA/letter grade settings under the renamed `Grading` tab.
- Deploy core status/display first so stored `2` never renders as fully correct, then Pro grader and settings.
- Rollback: deactivate Pro to stop new partial scoring; core still displays any stored `2`.
- Do not backfill historical rows.

## Open Questions

None. Scope, formulas, storage, and v1 exclusions are fixed by the approved feasibility plan.
