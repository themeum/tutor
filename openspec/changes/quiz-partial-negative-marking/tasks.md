## 1. Core attempt-answer status

- [ ] 1.1 Add `QuizModel` constants for attempt-answer `is_correct` (`0` / `1` / `2`) and verify they are the only values used for attempt rows, not option-bank rows
- [ ] 1.2 Update `get_attempt_answer_status()` to return `pending` / `correct` / `partial` / `incorrect` / `graded` / `skipped`, ensuring `open_ended` and `short_answer` strictly return `pending` (if unreviewed) or `graded` (if reviewed) and are never classified as correct, partial, or incorrect
- [ ] 1.3 Audit every attempt-row truthy `is_correct` check (`format_quiz_attempts`, summary, attempt-table, attempt-details, question header, questions sidebar, Pro `set_custom_question_answer_status`) and verify none treat `2` as fully correct
- [ ] 1.4 Count auto-graded `1`, `2`, and `0` separately in summary and `format_quiz_attempts`, and verify manual questions (`pending` / `graded`) are excluded from auto-graded correct/incorrect counts
- [ ] 1.5 Show a Partially correct badge and sidebar `partial` state in attempt details, and verify `is_correct = 2` is not collapsed into correct or incorrect
- [ ] 1.6 Show `Skipped` badge only for instructors in instructor dashboard and admin attempts overview; keep skipped questions completely hidden from student results views across Legacy and v4

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
- [ ] 4.2 Rename `Gradebook` settings menu to `Grading`, register it whenever Tutor Pro is active (not bound to the Gradebook add-on), and render `Automatic Assessment` settings block with `enable_quiz_partial_marking` and `enable_quiz_negative_marking` (with default penalty value and Pts/% dropdown) below Gradebook settings (or standalone if Gradebook is disabled)
- [ ] 4.3 Implement confirmation modal when turning off `enable_quiz_partial_marking` in Admin Settings explaining that existing quizzes will continue to work as configured
- [ ] 4.4 Implement conditional confirmation modal when turning off `enable_quiz_negative_marking` in Admin Settings, querying whether any quiz has customized negative marking and showing modal only if customizations exist
- [ ] 4.5 Localize `enable_quiz_partial_marking`, `enable_quiz_negative_marking`, and default penalty settings in `Course.php` (`$required_options`) so `tutorConfig.settings` exposes them to the course builder
- [ ] 4.6 Persist quiz keys on save, and verify builder save/reload keeps percent and fixed modes with `negative_mark_value`
- [ ] 4.7 Confirm the quiz scoring keys are included in the attempt `attempt_info` snapshot, and verify the grader reads that snapshot rather than live quiz meta
- [ ] 4.8 Confirm no question_settings scoring keys are added, and verify question save/reload does not introduce partial or negative overrides

## 5. Builder UI & form validation

- [ ] 5.1 Implement quiz-level controls directly in `QuizSettings.tsx` inside the `Grading` section: `FormSwitch` for Partial marking (with sub-answers tooltip) and `FormCheckbox` for Negative marking (with penalty per wrong answer value input and Pts/% dropdown)
- [ ] 5.2 Add grandfathering check for Partial marking in `QuizSettings.tsx`: display control if `isTutorPro && (adminPartialEnabled || quizOptionPartialAlreadyOn)`, ensuring existing enabled quizzes remain editable even when admin toggle is off
- [ ] 5.3 Add react-hook-form validation rules to `negative_mark_value` in `QuizSettings.tsx` (validating range 0–100 for percent mode and ≥ 0 for fixed mode with descriptive error messages, blocking form submission on invalid values)
- [ ] 5.4 Gate negative marking controls: display only when `isTutorPro && adminNegativeEnabled`
- [ ] 5.5 Verify controls are completely hidden when Tutor Pro is not active, and individual controls hide when their respective admin setting is off (except grandfathered partial quizzes)

## 6. Manual grading across Legacy and v4 & feedback flows

- [ ] 6.1 Remove binary `[✓]` / `[✕]` manual override buttons for all skipped questions and for manually gradeable questions (`open_ended`, `short_answer`) across both Legacy WP-Admin table and v4 attempt-details components
- [ ] 6.2 Display `(Overrides the auto-graded result)` beneath `[✓]` / `[✕]` override buttons on auto-graded questions when overridden by an instructor
- [ ] 6.3 Implement numeric obtained marks input `[ obtained_mark ] / {question_mark}` for `open_ended` and `short_answer` questions across Legacy and v4, updating `achieved_mark`, delta `earned_marks`, and strictly setting status to `Graded` (never correct/incorrect/partial, unaffected by partial/negative marking)
- [ ] 6.4 Implement v4 Instructor Dashboard inline feedback flow in `open-ended.php` / question review: `Add Feedback` inline accordion with `Cancel` / `Save`, `Show Feedback` inline accordion with `Delete`, `Cancel`, `Save`, with instant deletion without a confirmation dialog
- [ ] 6.5 Implement Legacy WP-Admin modal feedback flow in `views/quiz/attempt-details.php`: `Add Feedback` modal, `Show Feedback` modal, and `Delete` confirmation modal ("Are you sure you want to delete this feedback? [No, keep it] [Yes, delete]") via AJAX
- [ ] 6.6 Store question-level feedback in serialized `attempt_info['question_feedback'][$attempt_answer_id]`
- [ ] 6.7 Implement Student View across Legacy and v4: render "Feedback from instructor" callout beneath the student's answer when feedback exists, along with `Graded` badge and score, while keeping skipped questions completely hidden

## 7. Pro REST and import/export

- [ ] 7.1 Expose the quiz scoring keys on Pro-backed quiz option read/write paths when Pro is active, and verify a round-trip preserves percent and fixed modes with `negative_mark_value`
- [ ] 7.2 Carry quiz scoring keys through quiz import/export, and verify an exported quiz with fixed negative marks imports with the same values

## 8. QA and compatibility

- [ ] 8.1 Manual QA across Legacy and v4: Pro on/off, Gradebook addon on/off, Admin turn-off confirmation modals, Grandfathered quizzes continue to score and show switch when admin toggle off, negative mark input validation in Quiz settings, skipped questions hidden from students & visible to instructors with badge and no overrides, manual grading of open-ended and short-answer with numeric marks input & strictly pending/graded status (unaffected by partial/negative settings), Instructor dashboard inline feedback (instant delete), Admin dashboard modal feedback (delete confirmation modal), Student view feedback callout box, retry, reveal mode, matching/ordering/MC/FITB, percent vs fixed negative, H5P unchanged
- [ ] 8.2 Run PHPCS on touched PHP in tutor and tutor-pro and verify zero WordPress-standard errors
