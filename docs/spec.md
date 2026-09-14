# Specification: Bug #47860 — Quiz Answer Keys & Explanations Leak

## 1. Overview & Problem Definition

**Bug Report #47860**: Quiz answer keys and explanation content are transmitted to the student client prior to the student answering questions.

- **Affected Versions**: Tutor LMS 1.6.4 to 4.0.7
- **Affects**: Both Modern Learning Area (v4) and Legacy Learning Mode (Spotlight Quiz) in Free & Pro editions.

### The Invariant

> _No correct-answer data — including answer IDs and answer explanations — reaches the client before the answer it grades has been submitted._

A simple PHP check on `enable_answer_reveal` is insufficient because whenever the setting is active, the complete answer key would still be transmitted to the browser prior to answering question 1.

---

## 2. Leaks Identified & Addressed

### Leak 1: Correct Answer IDs

- **v4 Learning Area**: `templates/learning-area/quiz/attempt.php:72–85` builds `$quiz_answers` and `attempt.php:412–414` outputs `<script type="application/octet-stream" id="tutor-quiz-context">bin2hex(json_encode($quiz_answers))</script>`.
- **Legacy Mode**: `templates/single/quiz/parts/{choice-box,ordering,fill-in-the-blank}.php` appends `$answer->answer_id` into `$quiz_answers` if `$answer->is_correct`, and `templates/single/quiz/body.php:104` outputs `window.tutor_quiz_context = 'strrev(json_encode($quiz_answers))';`.

### Leak 2: Answer Explanations

- **v4 Pro**: `tutor-pro/templates/learning-area/quiz/answer-explanation.php:22,31` embeds `data-quiz-explanation-content="bin2hex(rawurlencode($answer_explanation))"` on every question wrapper.
- **Legacy Pro**: `tutor-pro/classes/Quiz.php:324–337` outputs inline explanation markup `<div class="tutor-quiz-explanation-wrapper tutor-d-none">` containing the raw explanation HTML on initial load.

---

## 3. Architecture & Resolution Strategy

### A. Total Removal of Pre-loaded Data

1. All client-side answer contexts (`#tutor-quiz-context` and `window.tutor_quiz_context`) are completely removed from PHP templates.
2. Initial explanation content (`data-quiz-explanation-content` attribute and legacy inline explanation text) is completely stripped from rendered templates. Empty placeholder wrappers remain for DOM styling/animation targets.

### B. Unified AJAX Action: `tutor_quiz_reveal_answer` (Incremental Auto-Save & Reveal)

Both v4 (via `window.TutorCore.api.wpPost`) and Legacy Mode (via jQuery `$.ajax` to `_tutorobject.ajaxurl`) communicate with a single backend endpoint: `wp_ajax_tutor_quiz_reveal_answer`.

- **Action Name**: `tutor_quiz_reveal_answer`
- **Request Parameters**:
  - `_tutor_nonce` or `_wpnonce`: Nonce matching `tutor()->nonce_action`
  - `attempt_id`: Active attempt ID (integer)
  - `quiz_id`: Quiz post ID (integer)
  - `question_id`: Target question ID (integer)
  - `answer`: Submitted response (scalar integer, string, or array)
- **Validation Pipeline**:
  1. `is_user_logged_in()` check.
  2. `tutor_utils()->check_nonce()`.
  3. Validate attempt exists and belongs to current user (`Quiz::validate_attempt()`).
  4. Validate attempt is active (`attempt_status !== ATTEMPT_TIMEOUT && attempt_status !== ATTEMPT_ENDED`).
  5. **Idempotency Guard**: If `(attempt_id, question_id)` is already recorded in `tutor_quiz_attempt_answers`:
     - If `enable_answer_reveal` is ON, return the previously evaluated result, correct answer IDs, and explanation (read-only replay).
     - If `enable_answer_reveal` is OFF, return success without evaluating or modifying existing DB record.
- **Lock-in Grading & Incremental Save (Option A)**:
  - Invokes `Quiz::grade_single_question()`.
  - For auto-gradable questions (`true_false`, `single_choice`, `multiple_choice`), sanitizes input, applies filter `tutor_filter_quiz_answer_data`, grades against DB correct answers, and serializes `given_answer`.
  - For non-auto-gradable question types, saves the given answer into `tutor_quiz_attempt_answers` with `is_correct = 0` and `achieved_mark = 0` pending review.
  - Inserts or updates the answer row in `tutor_quiz_attempt_answers`.
- **Response Format**:
  - If `enable_answer_reveal` is **ON**:
    ```json
    {
      "success": true,
      "data": {
        "is_revealed": true,
        "is_correct": true,
        "correct_answer_ids": [14, 18],
        "explanation": "<p>Explanation text...</p>"
      }
    }
    ```
  - If `enable_answer_reveal` is **OFF**:
    ```json
    {
      "success": true,
      "data": {
        "is_revealed": false
      }
    }
    ```
    _(No answer IDs or explanations are sent to the client)_.

### C. Final Question & Submit Behavior

- On the final question ($N$), when the student clicks "Submit Quiz":
  - No artificial 2-step "Check then Submit" action is introduced.
  - No reveal delay is executed on submit.
  - The frontend immediately dispatches the quiz submission (`submitQuizMutation` in v4 or form POST in Legacy).
  - The server grades any un-evaluated answers in `manage_attempt_answers()`, tallies marks, and marks the attempt completed (`attempt_status = ATTEMPT_ENDED`).
  - The student views their complete score and feedback on the attempt results view.
- For `question_below_each_other` layout:
  - All questions are displayed simultaneously with a single submit button.
  - Submission submits directly without reveal delay.

### D. Backend Idempotency & Attempt Finalization

When the final quiz submission is posted (`manage_attempt_answers()`):

- Checks whether `(attempt_id, question_id)` is already recorded in `tutor_quiz_attempt_answers`.
- If already present, skips `wpdb->insert`, but adds the existing achieved marks to `$total_marks`.
- If not present (e.g., unanswered or un-revealed questions), grades and inserts normally.
- Updates attempt record with `total_answered_questions`, `earned_marks = $total_marks`, and `attempt_status = ATTEMPT_ENDED`.

### E. Auto-Abandon Record Cleanup (`Quiz::tutor_quiz_timeout`)

Because Option A locks in records to `tutor_quiz_attempt_answers` on each question transition, if the attempt is terminated via `auto_abandon` (which executes `Quiz::tutor_quiz_timeout()`):

- The attempt status is marked `attempt_status = ATTEMPT_TIMEOUT` and `earned_marks = 0`.
- **Cleanup Requirement**: `Quiz::tutor_quiz_timeout()` executes:
  ```php
  $wpdb->delete(
      $wpdb->prefix . 'tutor_quiz_attempt_answers',
      array( 'quiz_attempt_id' => $attempt_id )
  );
  ```
  This guarantees that:
  1. The database state remains consistent: `earned_marks = 0` and zero attempt answers recorded.
  2. Historical Tutor LMS behavior is preserved (an abandoned attempt has no recorded answers).
  3. No partial answers with passing marks appear in attempt review/reports for an abandoned attempt.

---

## 4. Attempt Question Set & Order Stability (Random Order & Max Limit)

### The Problem

In Tutor LMS, quizzes can be configured with:

- `questions_order = 'rand'` (the default setting)
- `max_questions_for_answer = N` (e.g. choose 5 questions out of a bank of 20)

Previously, `tutor_utils()->get_random_questions_by_quiz()` executed `ORDER BY RAND() LIMIT %d` on every page request. The selected question IDs and their sequence were **never persisted** to the attempt record.
When combined with per-question Lock-in Grading (Option A), if a student navigated to another lesson or refreshed the page mid-quiz:

1. `ORDER BY RAND()` re-ran, generating a **different question set or sequence**.
2. Questions already answered and locked in DB might no longer exist on the reloaded page.
3. Questions could appear in different positions, causing idempotency conflicts when encountering previously answered questions.
4. The student could end up answering more questions than `max_questions_for_answer`.

### The Solution: Attempt-Level Question Locking & Cache Invalidation

When an attempt is initialized (in `Quiz::quiz_attempt()`) or first loaded by `get_random_questions_by_quiz()`:

1. The exact ordered list of assigned question IDs `[id1, id2, id3, ...]` is stored in the attempt record:
   `$attempt_info['question_ids'] = wp_list_pluck( $questions, 'question_id' );`
   and persisted to `tutor_quiz_attempts.attempt_info`.
2. **Cache Invalidation**: Whenever `attempt_info` is updated on an active attempt, `TutorCache::delete( "tutor_is_started_quiz_{$user_id}_{$quiz_id}" )` must be invoked to ensure subsequent `is_started_quiz()` calls do not read stale attempt metadata.
3. On every subsequent render of `get_random_questions_by_quiz()`:
   - If `! empty( $attempt_info['question_ids'] )`, it bypasses `ORDER BY RAND()` and executes:
     ```sql
     SELECT * FROM {$wpdb->prefix}tutor_quiz_questions
     WHERE question_id IN (id1, id2, ...)
     ORDER BY FIELD(question_id, id1, id2, ...)
     ```
   - This ensures the exact same questions remain in the exact same order for the entire lifecycle of the attempt across all browser refreshes, tab switches, and device changes.

### State Restoration on Reload

- `attempt.php` and `single/quiz/parts/question.php` query existing answers via `QuizModel::get_quiz_answers_by_attempt_id( $attempt_id )`.
- **Form Value Normalization**:
  - For `multiple_choice`: Form key is keyed with array notation `attempt[<attempt_id>][quiz_question][<question_id>][]` and mapped to `maybe_unserialize( $answer->given_answer )`.
  - For `single_choice` and `true_false`: Keyed as `attempt[<attempt_id>][quiz_question][<question_id>]` with scalar value.
- **Visual Restoration**:
  - If `enable_answer_reveal` is active, questions already evaluated in `tutor_quiz_attempt_answers` are rendered as revealed:
    - Options flagged with `data-option="correct"` or `data-option="incorrect"`.
    - Input controls disabled (`disabled="disabled"`).
    - Question wrapper marked with `data-is-revealed="1"`.
    - Explanation body populated with saved explanation.
  - In v4 `layout.ts`, `currentIndex` starts at the first **unanswered** question.
  - In Legacy mode, saved options render with `checked="checked"` (and disabled/revealed if reveal was active).

---

## 5. Concurrency & Deadline Invariants (C1 – C5)

1. **C1 — Mandatory Fail-Open Under Deadline**: If the timer hits zero or `pendingTimeoutSubmission` is active, skip all reveal network calls and `revealWaitMs` animation timeouts. Submit immediately.
2. **C2 — `isSubmitting` Mutex Guard**: An explicit boolean lock (`isSubmitting`) must be acquired synchronously when `handleQuizSubmit()` begins. `handleQuizTimeout` and `handleQuizTimeoutAbandon` must check `isSubmitting` and abort if an active submit is underway, preventing `auto_abandon` from prematurely marking `ATTEMPT_TIMEOUT` and wiping student marks to 0.
3. **C3 — Re-Entry Lock**: Re-entry must be locked synchronously prior to the first async dispatch to prevent duplicate submit mutations.
4. **C4 — Server Late-Submission Tolerance**: Server will accept submissions as long as status is not `ATTEMPT_TIMEOUT`. Preventing `auto_abandon` from prematurely timing out an in-flight submission guarantees the late submission is graded.
5. **C5 — Eager Grading Safety**: Eager grading across the quiz ensures answers are safe in the DB before the deadline hits; final submit only finalizes the attempt record.

---

## 6. Affected Files & Changes Summary

| Plugin      | Component            | File                                                      | Changes                                                                                                                                                                                                                                                              |
| ----------- | -------------------- | --------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `tutor`     | Question Selection   | `classes/Utils.php`                                       | Update `get_random_questions_by_quiz()` to read/persist `$attempt_info['question_ids']` using `ORDER BY FIELD`; invalidate `TutorCache`.                                                                                                                             |
| `tutor`     | Attempt Creation     | `classes/Quiz.php`                                        | In `quiz_attempt()`, pre-seed `attempt_info['question_ids']` upon start and invalidate `TutorCache`.                                                                                                                                                                 |
| `tutor`     | PHP Backend          | `classes/Quiz.php`                                        | Register `wp_ajax_tutor_quiz_reveal_answer`; implement `tutor_quiz_reveal_answer()`; implement `grade_single_question()` with filters and array sanitization; add idempotency check to `manage_attempt_answers()`; delete partial answers in `tutor_quiz_timeout()`. |
| `tutor`     | Core Endpoints       | `assets/core/ts/utils/endpoints.ts`                       | Add `QUIZ_REVEAL_ANSWER: 'tutor_quiz_reveal_answer'`.                                                                                                                                                                                                                |
| `tutor`     | v4 Template          | `templates/learning-area/quiz/attempt.php`                | Delete `$quiz_answers` loop; delete `#tutor-quiz-context` script; populate `$default_values` with proper array bracket normalization; pass `is-revealed` states.                                                                                                     |
| `tutor`     | v4 Question Template | `templates/learning-area/quiz/question.php`               | Handle pre-revealed state on reload when answer is already locked in DB.                                                                                                                                                                                             |
| `tutor`     | Legacy Templates     | `templates/single/quiz/body.php`                          | Delete `$quiz_answers` array and `window.tutor_quiz_context` script tag.                                                                                                                                                                                             |
| `tutor`     | Legacy Templates     | `templates/single/quiz/parts/choice-box.php`              | Delete `$answer->is_correct ? $quiz_answers[] = ...`; restore checked and revealed state if previously answered.                                                                                                                                                     |
| `tutor`     | Legacy Templates     | `templates/single/quiz/parts/ordering.php`                | Delete `$answer->is_correct ? $quiz_answers[] = ...`.                                                                                                                                                                                                                |
| `tutor`     | Legacy Templates     | `templates/single/quiz/parts/fill-in-the-blank.php`       | Delete `$answer->is_correct ? $quiz_answers[] = ...`.                                                                                                                                                                                                                |
| `tutor`     | v4 Constants         | `assets/src/js/frontend/learning-area/quiz/constants.ts`  | Remove `ANSWER_CONTEXT_ID: 'tutor-quiz-context'`.                                                                                                                                                                                                                    |
| `tutor`     | v4 Helpers           | `assets/src/js/frontend/learning-area/quiz/helpers.ts`    | Update `revealQuestionWithAnswers()` to accept `explanationHtml` and inject dynamically.                                                                                                                                                                             |
| `tutor`     | v4 Layout            | `assets/src/js/frontend/learning-area/quiz/layout.ts`     | Remove `revealAnswerIds` state and `getRevealAnswerIds()`; rewrite `goNext()` to dispatch save/reveal AJAX; add deadline check; resume at first unanswered question.                                                                                                 |
| `tutor`     | v4 Submission        | `assets/src/js/frontend/learning-area/quiz/submission.ts` | Add `isSubmitting` mutex; protect `handleQuizTimeout` from racing submission; clean up dead `revealOnSubmit()` and `getRevealAnswerIds()`.                                                                                                                           |
| `tutor`     | Legacy JS            | `assets/src/js/front/course/_spotlight-quiz.js`           | Remove `tutor_quiz_context` parsing; update `.tutor-quiz-answer-next-btn` to call AJAX reveal/save; add `isSubmitting` mutex.                                                                                                                                        |
| `tutor`     | Legacy Timing        | `assets/src/js/front/course/_spotlight-quiz-timing.js`    | Abort `tutor_quiz_timeout` if `isSubmitting` is true.                                                                                                                                                                                                                |
| `tutor-pro` | Pro v4 Template      | `templates/learning-area/quiz/answer-explanation.php`     | Delete `$encoded_explanation` and `data-quiz-explanation-content` attribute.                                                                                                                                                                                         |
| `tutor-pro` | Pro Class            | `classes/Quiz.php`                                        | In `render_question_answer_explanation()` legacy branch, leave explanation body empty on initial render.                                                                                                                                                             |
