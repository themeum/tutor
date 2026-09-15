## Why

Tutor LMS grades every auto-graded quiz question as all-or-nothing, so a student who matches some items and misses others gets zero and is shown as incorrect. Instructors need Pro-only partial credit and optional negative marking at quiz level. When quiz partial marking is on, the attempt UI must show a persisted **Partially correct** status that is not inferred from marks (negative marking can floor a partial answer to 0). When quiz partial marking is off, mixed answers stay Incorrect with zero marks.

## What Changes

- Persist a four-state attempt-answer status on the existing `{prefix}tutor_quiz_attempt_answers.is_correct` column: `1` fully correct, `2` partially correct (only when quiz partial marking is on), `0` incorrect, `null` pending. No new column.
- Treat `2` as **Partially correct** in core attempt results (header, sidebar, summary counts, legacy badges). Do not treat a truthy `2` as fully correct.
- Add Pro-only scoring for six multi-item auto-graded types: multiple choice (multi-correct), matching, image matching, ordering, fill-in-the-blank, and image answering.
- Apply optional quiz-level negative marking only when the question was answered and is not fully correct, using either a percent of the question mark or a fixed mark value. Floor `achieved_mark` and quiz `earned_marks` at 0.
- Add quiz-level controls in the Pro course builder only (no per-question scoring overrides, no content-bank scoring controls). Read flags from the attempt `attempt_info` snapshot, not live quiz meta.
- Keep instructor review binary (`1` or `0`). Review mark math MUST still adjust `earned_marks` when the previous status is `2`.
- Free Tutor stays all-or-nothing for new attempts. Stored `2` values still display as Partially correct if Pro is later deactivated.

## Capabilities

### New Capabilities

- `quiz-attempt-answer-status`: Four-state attempt-answer status (`correct` / `partial` / `incorrect` / `pending`) and how results UI and stats count those states.
- `quiz-partial-negative-scoring`: Pro-only partial and negative mark formulas, supported question types, skip/blank rules, and interaction with core all-or-nothing submit.
- `quiz-partial-negative-settings`: Quiz-level defaults (including percent vs fixed negative marks), who can see the controls, and how settings persist through save, REST, and import/export.

### Modified Capabilities

- None. This project has no archived main specs yet.

## Impact

- **Tutor core:** `QuizModel` status helpers and attempt-row consumers (`get_attempt_answer_status`, `format_quiz_attempts`, attempt-details templates/views, instructor review mark math). Option-bank `is_correct` is unchanged.
- **Tutor Pro:** New grader hooked to `tutor_filter_quiz_answer_data` and `tutor_filter_quiz_total_marks`; course-builder quiz settings slot; quiz option persistence; quiz import/export.
- **Not in v1:** question-level scoring overrides, true/false, single-select MC, essays, H5P, puzzle/pin/draw/scale/graph, per-option weights, negative quiz totals, historical recalculation, core submit-loop rewrite, partial-review slider.
- **Compatibility:** Defaults off. Old attempt rows stay `0`/`1`/`null`. No schema migration.
