# Verification Report: Quiz Partial & Negative Marking Tasks

**Date:** 2026-09-16  
**Target Specification:** [openspec/changes/quiz-partial-negative-marking/tasks.md](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/openspec/changes/quiz-partial-negative-marking/tasks.md)  
**Verification Method:** Primary source analysis across `tutor` and `tutor-pro` codebases, PHP syntax verification, PHPCS audits, and TypeScript / Rspack production build validation.

---

## Executive Summary

While the **majority of functional requirements are implemented and working** (including the Pro `QuizGrader`, proportional & negative grading math, unit tests, settings, grandfathering, and v4 feedback flows), **not all tasks are 100% complete**. 

Specifically, **6 tasks remain incomplete or contain compliance violations**:
1. **Task 1.6 & 6.7 (Incomplete):** Skipped questions are filtered out for students in v4, but **not in Legacy student view** (`views/quiz/attempt-details.php`). Students can still see skipped question rows and the "Skipped" badge.
2. **Task 6.2 (Not Implemented):** The label `(Overrides the auto-graded result)` beneath `[✓]` / `[✕]` override buttons when overridden is completely missing from both v4 and Legacy templates.
3. **Task 1.7, 6.8, 8.3 & 9.4 (Compliance Failure):** Newly added helper methods `get_question_feedback_map` and `save_question_feedback_map` in `tutor/classes/Quiz.php` use raw direct database calls (`$wpdb->get_row` and `$wpdb->update`) instead of `QueryHelper::get_row` and `QueryHelper::update`.
4. **Task 8.2 (Incomplete):** PHPCS reports WordPress standard violations in touched files (`tutor-pro/rest-api/Controllers/QuizController.php` has closing brace and blank line errors; `classes/QuizGrader.php` has an unused parameter warning).
5. **Task 8.1 (Incomplete):** Manual QA verification checklist is not yet fully executed across the complete matrix.

---

## Detailed Task-by-Task Verification

### Section 1: Core attempt-answer status

| Task | Description | Status | Primary Source Evidence |
| :--- | :--- | :---: | :--- |
| **1.1** | Add `QuizModel` constants (`0`/`1`/`2`) for attempt rows only | **COMPLETED** | [`QuizModel.php:43-45`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/models/QuizModel.php#L43-L45): `ATTEMPT_ANSWER_INCORRECT = 0`, `ATTEMPT_ANSWER_CORRECT = 1`, `ATTEMPT_ANSWER_PARTIAL = 2`. Docblock confirms question option rows remain binary. |
| **1.2** | Update `get_attempt_answer_status()` with 6 statuses | **COMPLETED** | [`QuizModel.php:1100-1121`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/models/QuizModel.php#L1100-L1121): Handles `skipped`, `pending`/`graded` for manual review types (`open_ended`, `short_answer`), and `correct`/`partial`/`incorrect` for auto-graded. |
| **1.3** | Audit truthy `is_correct` checks across all attempt views | **COMPLETED** | Checked [`QuizModel.php:258-268`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/models/QuizModel.php#L258-L268), [`summary.php:62-69`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/templates/shared/components/quiz/attempt-details/summary.php#L62-L69), [`attempt-table.php:72-79`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/views/quiz/attempt-table.php#L72-L79), [`attempt-details.php:227-234`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/views/quiz/attempt-details.php#L227-L234), [`questions-sidebar.php:30-53`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/templates/shared/components/quiz/attempt-details/questions-sidebar.php#L30-L53), [`tutor-pro/classes/Quiz.php:1936-1948`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/Quiz.php#L1936-L1948). None treat `2` as fully correct. |
| **1.4** | Count auto-graded `1`, `2`, and `0` separately; exclude manual | **COMPLETED** | Verified in `QuizModel::format_quiz_attempts`, `summary.php`, and `attempt-table.php`. Manual questions resolve to `pending`/`graded` and are omitted from auto counts. |
| **1.5** | Partially correct badge and sidebar state | **COMPLETED** | [`question.php:69-74`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/templates/shared/components/quiz/attempt-details/question.php#L69-L74), [`questions-sidebar.php:30-35`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/templates/shared/components/quiz/attempt-details/questions-sidebar.php#L30-L35), [`summary.php:196-210`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/templates/shared/components/quiz/attempt-details/summary.php#L196-L210), [`views/quiz/attempt-details.php:422`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/views/quiz/attempt-details.php#L422). |
| **1.6** | Skipped badge for instructors only; hidden from students in Legacy & v4 | **INCOMPLETE** | **v4:** Implemented via [`attempt-details.php:71`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/templates/shared/components/quiz/attempt-details.php#L71) using `filter_attempt_answers_for_details`.<br>**Legacy:** In [`views/quiz/attempt-details.php:399-422`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/views/quiz/attempt-details.php#L399-L422), answers are not filtered for students; skipped questions render with a `Skipped` badge at [L816-818](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/views/quiz/attempt-details.php#L816-L818). |
| **1.7** | New reads use `QueryHelper::get_all`/`get_row`; new AJAX uses `JsonResponse` | **INCOMPLETE** | [`tutor/classes/Quiz.php:536-545, 570-595`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/classes/Quiz.php#L536-L595) introduce direct `$wpdb->get_row()` and `$wpdb->update()` calls in new helper methods. |

---

### Section 2: Pro grader and unit tests

| Task | Description | Status | Primary Source Evidence |
| :--- | :--- | :---: | :--- |
| **2.1** | Pro `QuizGrader` resolves flags from `attempt_info` snapshot only | **COMPLETED** | [`QuizGrader.php:72-77`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/QuizGrader.php#L72-L77). Reads only from `$attempt->attempt_info`. |
| **2.2** | Matching, image matching, ordering proportional marks | **COMPLETED** | [`QuizGrader.php:228-265`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/QuizGrader.php#L228-L265) and [`QuizGraderTest.php:32-68`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/tests/PHPUnit/QuizGraderTest.php#L32-L68). Verified 2 of 4 items yields `2.00` and `is_correct = 2`. |
| **2.3** | Fill-in-the-blank (case-insensitive) & image-answering | **COMPLETED** | [`QuizGrader.php:267-347`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/QuizGrader.php#L267-L347). Line 128 sets `is_correct = 2` even when marks floor to 0 after penalty. |
| **2.4** | Moodle-style multi-select multiple choice | **COMPLETED** | [`QuizGrader.php:348-400`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/QuizGrader.php#L348-L400). Penalizes incorrect selections, skips penalty if `total_incorrect == 0`, floors at 0. |
| **2.5** | Negative marking for percent and fixed; skipped rows unpenalized | **COMPLETED** | [`QuizGrader.php:93-105, 130-136, 142-146`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/QuizGrader.php#L93-L105). Skipped questions are guarded with `minus_mark = 0`. |
| **2.6** | PHPUnit tests for all six types | **COMPLETED** | [`QuizGraderTest.php:1-229`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/tests/PHPUnit/QuizGraderTest.php#L1-L229). All 8 test methods cover the required scenarios. |
| **2.7** | `QuizGrader` uses `QueryHelper::get_all`/`get_row` | **COMPLETED** | [`QuizGrader.php:82, 231, 268, 312, 349`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/QuizGrader.php#L82). Zero direct database queries. |

---

### Section 3: Wire Pro submit filters

| Task | Description | Status | Primary Source Evidence |
| :--- | :--- | :---: | :--- |
| **3.1** | Hook `tutor_filter_quiz_answer_data` in `TUTOR_PRO\Quiz` | **COMPLETED** | [`tutor-pro/classes/Quiz.php:49`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/Quiz.php#L49) and [`QuizGrader.php:63-65`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/QuizGrader.php#L63-L65). Only rewrites supported types. |
| **3.2** | Hook `tutor_filter_quiz_total_marks` with delta tracking | **COMPLETED** | [`tutor-pro/classes/Quiz.php:50`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/Quiz.php#L50) and [`QuizGrader.php:153-176`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/QuizGrader.php#L153-L176). Running delta adjustments prevent double-counting. |
| **3.3** | Core `Quiz.php` submit writes all-or-nothing before filters | **COMPLETED** | [`tutor/classes/Quiz.php:1100-1127`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/classes/Quiz.php#L1100-L1127). Schema untouched. |
| **3.4** | New Pro AJAX uses `JsonResponse`; DB writes use `QueryHelper::update` | **COMPLETED** | `TUTOR_PRO\Quiz` uses `JsonResponse` trait. |

---

### Section 4: Admin Grading settings & builder settings persistence

| Task | Description | Status | Primary Source Evidence |
| :--- | :--- | :---: | :--- |
| **4.1** | Quiz defaults via `tutor_quiz_default_settings` | **COMPLETED** | [`tutor-pro/classes/Quiz.php:53, 2316-2326`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/Quiz.php#L53). |
| **4.2** | Rename Gradebook to Grading & register Automatic Assessment | **COMPLETED** | [`GradeBook.php:234-235`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/addons/gradebook/classes/GradeBook.php#L234-L235), [`Init.php:99-101`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/addons/gradebook/classes/Init.php#L99-L101), [`tutor-pro/classes/Quiz.php:2244-2309`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/Quiz.php#L2244-L2309). |
| **4.3** | Confirmation modal when turning off partial marking in settings | **COMPLETED** | [`options.js:716-838`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/assets/src/js/admin-dashboard/segments/options.js#L716-L838), [`tutor-pro/classes/Quiz.php:2258`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/Quiz.php#L2258), [`toggle_switch.php:24-34`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/views/options/field-types/toggle_switch.php#L24-L34). |
| **4.4** | Conditional confirmation modal when turning off negative marking | **COMPLETED** | [`options.js:755-787`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/assets/src/js/admin-dashboard/segments/options.js#L755-L787), [`tutor-pro/classes/Quiz.php:2267, 2328-2348`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/Quiz.php#L2267). |
| **4.5** | Localize keys in `Course.php` (`$required_options`) | **COMPLETED** | [`Course.php:1567-1570`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/classes/Course.php#L1567-L1570). |
| **4.6** | Persist quiz keys on save; builder save/reload roundtrip | **COMPLETED** | [`quiz.ts:221-227, 288-291`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/assets/src/js/v3/entries/course-builder/services/quiz.ts#L221-L227). |
| **4.7** | Keys snapshotted in `attempt_info`; grader reads snapshot | **COMPLETED** | Verified in `classes/Quiz.php:1131` and `QuizGrader.php:72`. |
| **4.8** | Confirm no `question_settings` scoring keys are added | **COMPLETED** | Scoring keys are purely quiz-level options. |
| **4.9** | Negative check uses `QueryHelper::query`; endpoints use `JsonResponse` | **COMPLETED** | [`tutor-pro/classes/Quiz.php:2336-2346`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/Quiz.php#L2336-L2346). |

---

### Section 5: Builder UI & form validation

| Task | Description | Status | Primary Source Evidence |
| :--- | :--- | :---: | :--- |
| **5.1** | Quiz-level controls in `QuizSettings.tsx` Grading section | **COMPLETED** | [`QuizSettings.tsx:210-275`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/assets/src/js/v3/entries/course-builder/components/curriculum/QuizSettings.tsx#L210-L275). `FormSwitch` and `FormCheckbox` wired. |
| **5.2** | Grandfathering check for partial marking | **COMPLETED** | [`QuizSettings.tsx:214`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/assets/src/js/v3/entries/course-builder/components/curriculum/QuizSettings.tsx#L214). `adminPartialEnabled || quizOptionPartialAlreadyOn`. |
| **5.3** | React Hook Form validation for `negative_mark_value` | **COMPLETED** | [`QuizSettings.tsx:246-254`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/assets/src/js/v3/entries/course-builder/components/curriculum/QuizSettings.tsx#L246-L254). Validates `0-100` for percent and `≥ 0` for fixed. |
| **5.4** | Gate negative marking controls: Pro && adminNegativeEnabled | **COMPLETED** | [`QuizSettings.tsx:228`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/assets/src/js/v3/entries/course-builder/components/curriculum/QuizSettings.tsx#L228). |
| **5.5** | Verify controls hidden when Pro inactive or admin disabled | **COMPLETED** | Wrapped in outer `<Show when={isTutorPro}>` and inner gating guards. |

---

### Section 6: Manual grading across Legacy and v4 & feedback flows

| Task | Description | Status | Primary Source Evidence |
| :--- | :--- | :---: | :--- |
| **6.1** | Remove binary override buttons for skipped and manual questions | **COMPLETED** | [`question-header.php:127`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/templates/shared/components/quiz/attempt-details/question-header.php#L127) and [`views/quiz/attempt-details.php:837, 850`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/views/quiz/attempt-details.php#L837). |
| **6.2** | Display `(Overrides the auto-graded result)` beneath override buttons | **NOT COMPLETED** | **Missing from codebase.** Neither `question-header.php` nor `views/quiz/attempt-details.php` renders this label when an instructor overrides an auto-graded question. |
| **6.3** | Numeric obtained marks input for `open_ended` and `short_answer` | **COMPLETED** | [`open-ended.php:48-65`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/templates/shared/components/quiz/attempt-details/questions/open-ended.php#L48-L65), [`views/quiz/attempt-details.php:837-841`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/views/quiz/attempt-details.php#L837-L841), [`Quiz.php:1320-1345`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/classes/Quiz.php#L1320-L1345). Delta updates `earned_marks` and marks question as `graded`. |
| **6.4** | v4 Instructor Dashboard inline feedback flow (instant delete) | **COMPLETED** | [`open-ended.php:83-149`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/templates/shared/components/quiz/attempt-details/questions/open-ended.php#L83-L149), [`quiz-attempt-feedback.ts:206-287`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/assets/src/js/frontend/dashboard/pages/quiz-attempt-feedback.ts#L206-L287). `del()` executes without modal. |
| **6.5** | Legacy WP-Admin modal feedback flow (with confirmation modal) | **COMPLETED** | [`views/quiz/attempt-details.php:842-848, 882-943`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/views/quiz/attempt-details.php#L842-L848), [`quiz-attempt.js:82-185`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/assets/src/js/v2/quiz-attempt.js#L82-L185). Modal and delete confirmation modal wired. |
| **6.6** | Store feedback in serialized `attempt_info['question_feedback'][$attempt_answer_id]` | **COMPLETED** | [`Quiz.php:549, 579-586`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/classes/Quiz.php#L549). |
| **6.7** | Student View feedback callout; keep skipped questions hidden | **INCOMPLETE** | **Feedback callouts:** Implemented in [`open-ended.php:152-159`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/templates/shared/components/quiz/attempt-details/questions/open-ended.php#L152-L159) and [`views/quiz/attempt-details.php:601-609`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/views/quiz/attempt-details.php#L601-L609).<br>**Skipped questions hidden:** Only filtered in v4; **not filtered in Legacy student view** ([`views/quiz/attempt-details.php:380-425`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/views/quiz/attempt-details.php#L380-L425)). |
| **6.8** | New endpoints use `JsonResponse` & `QueryHelper::get_row`/`update` | **INCOMPLETE** | [`tutor/classes/Quiz.php:536-545, 570-595`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/classes/Quiz.php#L536-L595) use direct `$wpdb->get_row()` and `$wpdb->update()`. |

---

### Section 7: Pro REST and import/export

| Task | Description | Status | Primary Source Evidence |
| :--- | :--- | :---: | :--- |
| **7.1** | Expose quiz scoring keys in Pro REST controller | **COMPLETED** | [`QuizController.php:410-422`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/rest-api/Controllers/QuizController.php#L410-L422). |
| **7.2** | Carry keys through quiz import/export | **COMPLETED** | [`QuizImportExport.php:182-185, 341-344, 372-375`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/addons/quiz-import-export/classes/QuizImportExport.php#L182-L185). |
| **7.3** | New endpoints use `JsonResponse` and `QueryHelper` | **COMPLETED** | No new REST endpoints were created; existing endpoints were updated cleanly. |

---

### Section 8: QA and compatibility

| Task | Description | Status | Primary Source Evidence |
| :--- | :--- | :---: | :--- |
| **8.1** | Manual QA across Legacy and v4 matrix | **INCOMPLETE** | Requires end-to-end user browser testing against live WordPress instance. |
| **8.2** | Run PHPCS on touched PHP and verify zero errors | **INCOMPLETE** | PHPCS run on touched files detected errors in [`QuizController.php:455`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/rest-api/Controllers/QuizController.php#L455) (2 errors) and a warning in [`QuizGrader.php:186`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor-pro/classes/QuizGrader.php#L186). |
| **8.3** | Verify no raw `$wpdb` calls in new PHP code | **INCOMPLETE** | [`tutor/classes/Quiz.php:536, 570, 587`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/classes/Quiz.php#L536) contain direct `$wpdb` operations in newly added methods. |

---

### Section 9: Learning Area Quiz Summary & Builder Polish

| Task | Description | Status | Primary Source Evidence |
| :--- | :--- | :---: | :--- |
| **9.1** | Builder uses `FormInputWithContent` for penalty, lock type | **COMPLETED** | [`QuizSettings.tsx:257-268`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/assets/src/js/v3/entries/course-builder/components/curriculum/QuizSettings.tsx#L257-L268), [`quiz.ts:223-227`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/assets/src/js/v3/entries/course-builder/services/quiz.ts#L223-L227). |
| **9.2** | Negative penalty helper using `QueryHelper::get_all` | **COMPLETED** | [`tutor/classes/Quiz.php:2069-2095`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/classes/Quiz.php#L2069-L2095) (`get_negative_marking_summary_label`). |
| **9.3** | Render partial & negative parameter rows in summary & template | **COMPLETED** | [`Quiz.php:1984-2000`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/classes/Quiz.php#L1984-L2000), [`content.php:68`](file:///Users/blind/Local%20Sites/themeum-tutor/app/public/wp-content/plugins/tutor/templates/learning-area/quiz/content.php#L68). |
| **9.4** | Cross-cutting audit for `QueryHelper` and `JsonResponse` | **INCOMPLETE** | Blocked by `$wpdb` direct queries in `classes/Quiz.php:536-595`. |
| **9.5** | Build and typecheck validation | **COMPLETED** | `rspack --mode=production` compiled successfully (exit code 0); `tsc --noEmit` passed with 0 errors. |

---

## Conclusion & Actionable Items to Reach 100% Completion

To achieve complete task completion without regressions:
1. **Implement Task 6.2:** Add the `(Overrides the auto-graded result)` label under the `[✓]` / `[✕]` override buttons when overridden in `question-header.php` and `views/quiz/attempt-details.php`.
2. **Fix Task 1.6 & 6.7 in Legacy:** In `views/quiz/attempt-details.php`, filter out skipped questions when `$is_instructor_review` is false (e.g. using `QuizModel::filter_attempt_answers_for_details( $answers, $is_instructor_review )`), ensuring students cannot see skipped questions.
3. **Refactor Database Access in `tutor/classes/Quiz.php` (Tasks 1.7, 6.8, 8.3, 9.4):** Replace `$wpdb->get_row(...)` and `$wpdb->update(...)` in `get_question_feedback_map` and `save_question_feedback_map` with `QueryHelper::get_row()` and `QueryHelper::update()`.
4. **Fix PHPCS Violations (Task 8.2):** Clean up the formatting in `tutor-pro/rest-api/Controllers/QuizController.php` (line 455) and remove the unused `$question_type` parameter in `tutor-pro/classes/QuizGrader.php` (line 186).
