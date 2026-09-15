## 1. Core attempt-answer status

- [ ] 1.1 Add `QuizModel` constants for attempt-answer `is_correct` (`0` / `1` / `2`) and verify they are the only values used for attempt rows, not option-bank rows
- [ ] 1.2 Update `get_attempt_answer_status()` to return `pending` / `correct` / `partial` / `incorrect` by checking `null`, then `1`, then `2`, and verify a fixture with `is_correct = 2` returns `partial` (not `correct`)
- [ ] 1.3 Audit every attempt-row truthy `is_correct` check (`format_quiz_attempts`, summary, attempt-table, attempt-details, question header, questions sidebar, Pro `set_custom_question_answer_status`) and verify none treat `2` as fully correct
- [ ] 1.4 Count `1`, `2`, and `0` separately in summary and `format_quiz_attempts`, and verify a mixed attempt reports correct `1`, partial `1`, incorrect `1`
- [ ] 1.5 Show a Partially correct badge and sidebar `partial` state in attempt details (including legacy badges and a read-only review badge), and verify `is_correct = 2` is not collapsed into correct or incorrect
- [ ] 1.6 Fix `apply_quiz_answer_review()` so a previous `2` still adjusts `earned_marks` when flipping to correct or incorrect, using the previous `achieved_mark` as the delta, and verify both review directions

## 2. Pro grader and unit tests

- [ ] 2.1 Add a Pro `QuizGrader` helper that resolves effective flags from `attempt_info` plus question `inherit` / `on` / `off`, and verify a later live-meta edit does not change snapshotted flags
- [ ] 2.2 Implement matching, image matching, and ordering proportional marks and item-level status, and verify `2` correct of `4` items on a 4-point question yields `2.00` and `is_correct = 2`
- [ ] 2.3 Implement fill-in-the-blank (case-insensitive) and image-answering proportional marks, and verify mixed blanks/labels produce partial status even when net marks later floor to `0`
- [ ] 2.4 Implement Moodle-style multi-correct multiple choice (`max(0, correct/total_correct - incorrect/total_incorrect)`), skip the penalty when `total_incorrect` is `0`, and verify select-all cannot full-score
- [ ] 2.5 Implement negative marking (`minus_mark` only when answered and not fully correct; `achieved_mark = max(0, marks - minus_mark)`), and verify skip/blank rows have `minus_mark = 0`
- [ ] 2.6 Add PHPUnit coverage for all six v1 types including `is_correct = 2` with `achieved_mark = 0`, and verify the Pro test suite passes those cases

## 3. Wire Pro submit filters

- [ ] 3.1 Hook `tutor_filter_quiz_answer_data` in `TUTOR_PRO\Quiz` to rewrite `achieved_mark`, `minus_mark`, and `is_correct` for the six types only, and verify H5P / puzzle / pin / draw / scale / graph callbacks stay exclusive
- [ ] 3.2 Hook `tutor_filter_quiz_total_marks` so a previously not-fully-correct row adds the new `achieved_mark` and a fully correct row is not double-counted, and verify a mixed quiz total matches the sum of rewritten marks
- [ ] 3.3 Confirm core `Quiz.php` submit still writes all-or-nothing before the filters, and verify no new insert keys or schema changes were added

## 4. Builder settings persistence

- [ ] 4.1 Add quiz defaults via `tutor_quiz_default_settings` (`enable_partial_marking`, `enable_negative_marking`, `negative_mark_percent` 0–100, all default off) and verify a new quiz without the keys grades all-or-nothing
- [ ] 4.2 Add question defaults via `tutor_question_default_settings` (`inherit` / `on` / `off` plus optional percent) and persist them through `tutor_quiz_question_data`, and verify a saved override reloads
- [ ] 4.3 Persist quiz keys on save (Pro merge on `tutor_quiz_settings_updated`, or a core `tutor_quiz_option_data` / payload passthrough only if keys are dropped), and verify builder save/reload keeps the values
- [ ] 4.4 Confirm the six keys are included in the attempt `attempt_info` snapshot, and verify the grader reads that snapshot rather than live quiz meta

## 5. Builder and content-bank UI

- [ ] 5.1 Inject quiz-level controls into `Curriculum.Quiz.bottom_of_settings` behind Pro, and verify free Tutor does not show them
- [ ] 5.2 Inject question inherit/on/off controls into `Curriculum.Quiz.bottom_of_question_sidebar` for the six v1 types only, and verify they are hidden for true/false, single-select MC, essays, and modern Pro types
- [ ] 5.3 Mirror the question controls in the Pro content-bank question editor, and verify a matching question shows inherit/on/off there
- [ ] 5.4 Document the formulas in UI tooltips, and verify the copy matches the scoring spec

## 6. Pro REST and import/export

- [ ] 6.1 Extend Pro REST quiz-question read/write with the new keys when Pro is active, and verify a GET/PUT round-trip preserves them
- [ ] 6.2 Carry quiz and question scoring keys through quiz import/export, and verify an exported quiz with overrides imports with the same values

## 7. QA and compatibility

- [ ] 7.1 Manual QA: Pro on/off, retry, reveal mode, matching/ordering/MC/FITB, skipped vs partial vs incorrect, instructor review from a `2` row, H5P unchanged — and verify each case against the three specs
- [ ] 7.2 Run PHPCS on touched PHP in tutor and tutor-pro and verify zero WordPress-standard errors
