## 1. Core attempt-answer status

- [ ] 1.1 Add `QuizModel` constants for attempt-answer `is_correct` (`0` / `1` / `2`) and verify they are the only values used for attempt rows, not option-bank rows
- [ ] 1.2 Update `get_attempt_answer_status()` to return `pending` / `correct` / `partial` / `incorrect` by checking `null`, then `1`, then `2`, and verify a fixture with `is_correct = 2` returns `partial` (not `correct`)
- [ ] 1.3 Audit every attempt-row truthy `is_correct` check (`format_quiz_attempts`, summary, attempt-table, attempt-details, question header, questions sidebar, Pro `set_custom_question_answer_status`) and verify none treat `2` as fully correct
- [ ] 1.4 Count `1`, `2`, and `0` separately in summary and `format_quiz_attempts`, and verify a mixed attempt reports correct `1`, partial `1`, incorrect `1`
- [ ] 1.5 Show a Partially correct badge and sidebar `partial` state in attempt details (including legacy badges and a read-only review badge), and verify `is_correct = 2` is not collapsed into correct or incorrect
- [ ] 1.6 Fix `apply_quiz_answer_review()` so a previous `2` still adjusts `earned_marks` when flipping to correct or incorrect, using the previous `achieved_mark` as the delta, and verify both review directions

## 2. Pro grader and unit tests

- [ ] 2.1 Add a Pro `QuizGrader` helper that resolves quiz flags from `attempt_info` only (`enable_partial_marking`, `enable_negative_marking`, `negative_mark_type`, `negative_mark_value`), and verify a later live-meta edit does not change snapshotted flags
- [ ] 2.2 Implement matching, image matching, and ordering proportional marks and item-level status, and verify with quiz partial marking on that `2` correct of `4` items on a 4-point question yields `2.00` and `is_correct = 2`
- [ ] 2.3 Implement fill-in-the-blank (case-insensitive) and image-answering proportional marks, and verify with quiz partial marking on that mixed blanks/labels keep `is_correct = 2` even when net marks later floor to `0`
- [ ] 2.4 Implement Moodle-style multi-correct multiple choice (`max(0, correct/total_correct - incorrect/total_incorrect)`), skip the penalty when `total_incorrect` is `0`, and verify select-all cannot full-score
- [ ] 2.5 Implement negative marking for both `percent` and `fixed` types (`minus_mark` only when answered and not fully correct; `achieved_mark = max(0, marks - minus_mark)` using `negative_mark_value`), and verify skip/blank rows have `minus_mark = 0`
- [ ] 2.6 Add PHPUnit coverage for all six v1 types including partial-on `is_correct = 2` with `achieved_mark = 0`, and partial-off mixed answers as `is_correct = 0` with `achieved_mark = 0`, and verify the Pro test suite passes those cases

## 3. Wire Pro submit filters

- [ ] 3.1 Hook `tutor_filter_quiz_answer_data` in `TUTOR_PRO\Quiz` to rewrite `achieved_mark`, `minus_mark`, and `is_correct` for the six types only, and verify H5P / puzzle / pin / draw / scale / graph callbacks stay exclusive
- [ ] 3.2 Hook `tutor_filter_quiz_total_marks` so a previously not-fully-correct row adds the new `achieved_mark` and a fully correct row is not double-counted, and verify a mixed quiz total matches the sum of rewritten marks
- [ ] 3.3 Confirm core `Quiz.php` submit still writes all-or-nothing before the filters, and verify no new insert keys or schema changes were added

## 4. Admin Grading settings & builder settings persistence

- [ ] 4.1 Add quiz defaults via `tutor_quiz_default_settings` (`enable_partial_marking`, `enable_negative_marking`, `negative_mark_type` percent|fixed, `negative_mark_value` ≥ 0, all default off/percent/0) and verify a new quiz without the keys grades all-or-nothing
- [ ] 4.2 Rename `Gradebook` settings menu to `Grading`, register it whenever Tutor Pro is active (not bound to the Gradebook add-on), and render `Automatic Assessment` settings block with `enable_quiz_partial_marking` and `enable_quiz_negative_marking` toggles below Gradebook settings (or standalone if Gradebook is disabled)
- [ ] 4.3 Localize `enable_quiz_partial_marking` and `enable_quiz_negative_marking` in `Course.php` (`$required_options`) so `tutorConfig.settings` exposes them to the course builder
- [ ] 4.4 Persist quiz keys on save (Pro merge on `tutor_quiz_settings_updated` or direct quiz option handling), and verify builder save/reload keeps percent and fixed modes with `negative_mark_value`
- [ ] 4.5 Confirm the quiz scoring keys are included in the attempt `attempt_info` snapshot, and verify the grader reads that snapshot rather than live quiz meta
- [ ] 4.6 Confirm no question_settings scoring keys are added, and verify question save/reload does not introduce partial or negative overrides

## 5. Builder UI & form validation

- [ ] 5.1 Implement quiz-level controls directly in `QuizSettings.tsx` (partial toggle, negative toggle, type percent|fixed switch, and `negative_mark_value` input with dynamic % or mark unit label) without using injection field slots
- [ ] 5.2 Add react-hook-form validation rules to `negative_mark_value` in `QuizSettings.tsx` (validating range 0–100 for percent mode and ≥ 0 for fixed mode with descriptive error messages, blocking form submission on invalid values)
- [ ] 5.3 Gate controls independently: display partial marking toggle only when `isTutorPro && tutorConfig.settings?.enable_quiz_partial_marking === 'on'`; display negative marking controls only when `isTutorPro && tutorConfig.settings?.enable_quiz_negative_marking === 'on'`
- [ ] 5.4 Verify controls are completely hidden when Tutor Pro is not active, and individual controls hide when their respective admin setting is off
- [ ] 5.5 Confirm question sidebar and content-bank question editors have no scoring override controls
- [ ] 5.6 Document the formulas in UI tooltips, and verify the copy matches the scoring spec

## 6. Pro REST and import/export

- [ ] 6.1 Expose the quiz scoring keys on Pro-backed quiz option read/write paths when Pro is active, and verify a round-trip preserves percent and fixed modes with `negative_mark_value`
- [ ] 6.2 Carry quiz scoring keys through quiz import/export, and verify an exported quiz with fixed negative marks imports with the same values

## 7. QA and compatibility

- [ ] 7.1 Manual QA: Pro on/off, Gradebook addon on/off (Grading menu displays correctly in both states), negative mark input validation (validating 0–100 for percent and ≥ 0 for fixed in Quiz settings), retry, reveal mode, matching/ordering/MC/FITB, skipped vs partial vs incorrect, percent vs fixed negative, instructor review from a `2` row, H5P unchanged — and verify each case against the three specs
- [ ] 7.2 Run PHPCS on touched PHP in tutor and tutor-pro and verify zero WordPress-standard errors
