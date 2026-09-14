# Implementation Task Checklist: Bug #47860 Resolution

## Phase 1: Backend & Attempt Management (`tutor`)

- [ ] **1.1 Attempt Question Sequence Locking & Cache Invalidation**
  - In `classes/Utils.php` (`get_random_questions_by_quiz()`):
    - When loading questions for an active attempt, check `$attempt_info['question_ids']`.
    - If empty, extract question IDs from query results and save into `$attempt->attempt_info['question_ids']`.
    - Update `attempt_info` in `tutor_quiz_attempts`.
    - Call `TutorCache::delete( "tutor_is_started_quiz_{$user_id}_{$quiz_id}" )` and update runtime `$attempt->attempt_info`.
    - If `$attempt_info['question_ids']` is already present, query questions using `WHERE question_id IN (...) ORDER BY FIELD(question_id, ...)`.
  - In `classes/Quiz.php` (`quiz_attempt()`):
    - When creating a new attempt, pre-seed `$attempt_info['question_ids']` if questions are fetched at start.
- [ ] **1.2 Generalized Incremental Save & Grading Endpoint (`tutor_quiz_reveal_answer`)**
  - In `classes/Quiz.php`:
    - Register `wp_ajax_tutor_quiz_reveal_answer`.
    - Implement `tutor_quiz_reveal_answer()`:
      - Verify nonce (`tutor()->nonce_action`).
      - Verify user authentication and attempt authorization (`$attempt->user_id === get_current_user_id()`).
      - Verify attempt status is active (`! in_array($attempt->attempt_status, [ATTEMPT_TIMEOUT, ATTEMPT_ENDED], true)`).
      - Check idempotency: if answer already exists in `tutor_quiz_attempt_answers`, return existing evaluation without re-grading.
      - Call `grade_single_question($attempt_id, $question_id, $answer)`:
        - For auto-gradable types (`true_false`, `single_choice`, `multiple_choice`), sanitize input (filter numeric IDs for array), apply filter `tutor_filter_quiz_answer_data`, grade against DB, serialize `given_answer`.
        - For other question types, save `given_answer` with `is_correct = 0` and `achieved_mark = 0` pending review.
        - Insert or update answer row in `tutor_quiz_attempt_answers`.
      - Check `enable_answer_reveal`:
        - If active: return `{ is_revealed: true, is_correct: bool, correct_answer_ids: int[], explanation: string }`.
        - If inactive: return `{ is_revealed: false }`.
- [ ] **1.3 Backend Idempotency in Final Submission**
  - In `classes/Quiz.php` (`manage_attempt_answers()`):
    - Before inserting a question evaluation, check if a row already exists in `tutor_quiz_attempt_answers` for `(attempt_id, question_id)`.
    - If it exists, tally the recorded `achieved_mark` into `$total_marks` and skip re-insert.
    - If not present, grade and insert normally.
- [ ] **1.4 Auto-Abandon Partial Answer Cleanup**
  - In `classes/Quiz.php` (`tutor_quiz_timeout()`):
    - Delete partial answers from `tutor_quiz_attempt_answers` where `quiz_attempt_id = %d`.
    - Set `earned_marks = 0` and `attempt_status = ATTEMPT_TIMEOUT`.

---

## Phase 2: Template Data Stripping & State Restoration

- [ ] **2.1 Modern Learning Area v4 Template (`tutor`)**
  - In `templates/learning-area/quiz/attempt.php`:
    - Delete `$quiz_answers` gathering loop (lines 72–85).
    - Delete `<script type="application/octet-stream" id="tutor-quiz-context">` (lines 412–414).
    - In `$default_values` population:
      - For `multiple_choice`, format key with `[]`: `attempt[{$attempt_id}][quiz_question][{$question_id}][]` and assign `maybe_unserialize($answer->given_answer)`.
      - For `single_choice` and `true_false`, format key without `[]`.
    - In `question.php`, if `enable_answer_reveal` is active and question has an existing attempt answer, pre-render as revealed: `data-is-revealed="1"`, input `disabled="disabled"`, and inject explanation.
- [ ] **2.2 Legacy Mode Templates (`tutor`)**
  - `templates/single/quiz/body.php`:
    - Delete `$quiz_answers = array();` (line 36) and `<script>window.tutor_quiz_context = ...;</script>` (lines 103–105).
  - `templates/single/quiz/parts/choice-box.php`:
    - Remove `$answer->is_correct ? $quiz_answers[] = ...` (line 69).
    - Restore checked and revealed state if previously answered.
  - `templates/single/quiz/parts/ordering.php`:
    - Remove `$answer->is_correct ? $quiz_answers[] = ...` (line 21).
  - `templates/single/quiz/parts/fill-in-the-blank.php`:
    - Remove `$answer->is_correct ? $quiz_answers[] = ...` (line 20).
- [ ] **2.3 Pro Plugin Templates & Classes (`tutor-pro`)**
  - In `tutor-pro/templates/learning-area/quiz/answer-explanation.php`:
    - Remove `$encoded_explanation` (line 22) and `data-quiz-explanation-content` attribute (line 31).
  - In `tutor-pro/classes/Quiz.php`:
    - In `render_question_answer_explanation()` legacy branch (lines 324–337), leave explanation body container empty on initial render.

---

## Phase 3: Modern Learning Area v4 Frontend

- [ ] **3.1 Endpoints & Constants**
  - In `assets/core/ts/utils/endpoints.ts`, ensure `QUIZ_REVEAL_ANSWER: 'tutor_quiz_reveal_answer'`.
  - In `assets/src/js/frontend/learning-area/quiz/constants.ts`, remove `ANSWER_CONTEXT_ID`.
- [ ] **3.2 Dynamic Explanation Injection in Helpers**
  - In `assets/src/js/frontend/learning-area/quiz/helpers.ts`, update `revealQuestionWithAnswers(wrapper, revealAnswerIds, explanationHtml?)` to inject `explanationHtml` into `.tutor-quiz-explanation-body` dynamically.
- [ ] **3.3 Per-Question Save & Reveal in Layout**
  - In `assets/src/js/frontend/learning-area/quiz/layout.ts`:
    - Remove `revealAnswerIds` state and `getRevealAnswerIds()`.
    - In `goNext()`:
      - If timer expired (`hasTimedOut`), skip AJAX and advance immediately.
      - If skipped (`skipValidation: true`) or no input, advance immediately.
      - Dispatch `api.wpPost(endpoints.QUIZ_REVEAL_ANSWER, { attempt_id, quiz_id, question_id, answer })`.
      - If `response.data.is_revealed`: highlight answers, inject explanation, wait `revealWaitTime`, then `moveToNextQuestion()`.
      - If `! response.data.is_revealed`: call `moveToNextQuestion()` immediately.
    - Set initial `currentIndex` to first unanswered question based on saved form values.
- [ ] **3.4 Concurrency Mutex & Submit Simplification in Submission**
  - In `assets/src/js/frontend/learning-area/quiz/submission.ts`:
    - Add `isSubmitting: false` state.
    - In `handleQuizSubmit()`, set `isSubmitting = true` synchronously at entry.
    - Submit immediately without reveal delays.
    - Clean up dead methods: `getRevealAnswerIds()`, `revealOnSubmit()`.
    - In `handleQuizTimeout()`, abort `AUTO_ABANDON` if `this.isSubmitting` is true.

---

## Phase 4: Legacy Mode Frontend

- [ ] **4.1 Update `_spotlight-quiz.js`**
  - Remove `window.tutor_quiz_context` JSON parsing.
  - In `.tutor-quiz-answer-next-btn` click handler:
    - Send AJAX `action: 'tutor_quiz_reveal_answer'`.
    - If `response.data.is_revealed`: highlight correct/incorrect options, inject explanation text, wait `get_reveal_wait_time()`, then transition.
    - If `! response.data.is_revealed`: transition to next question immediately.
  - In submit handler:
    - Set `window._tutor_is_submitting = true` synchronously.
    - Submit form immediately without reveal delay.
- [ ] **4.2 Update `_spotlight-quiz-timing.js`**
  - In timeout handler: check `if (window._tutor_is_submitting) return;` before dispatching `action: 'tutor_quiz_timeout'`.

---

## Phase 5: Verification & Testing

- [ ] **5.1 HTML Leak Inspection**
  - Check v4 page source: `#tutor-quiz-context` and `data-quiz-explanation-content` absent with reveal on/off.
  - Check Legacy page source: `window.tutor_quiz_context` and inline explanation text absent with reveal on/off.
- [ ] **5.2 Question Set Stability Test**
  - Configure quiz with `questions_order = 'rand'` and `max_questions_for_answer = 5` out of 20.
  - Start attempt, record question IDs.
  - Answer 2 questions, navigate to another lesson, then return to the quiz.
  - **Assert:** The exact same 5 questions load in the exact same sequence; answered questions retain their selections.
- [ ] **5.3 Incremental Save & Reveal API Tests**
  - Test `tutor_quiz_reveal_answer` authentication, attempt authorization, question types, grading correctness, idempotency.
  - Test with reveal ON (returns correct IDs and explanation).
  - Test with reveal OFF (saves to DB, returns `{ is_revealed: false }` with no answer keys or explanation).
- [ ] **5.4 Concurrency & Auto-Abandon Cleanup Tests**
  - Test `auto_abandon` does not overwrite in-flight submission.
  - Test `auto_submit` bypasses reveal delay and submits immediately.
  - Test that after `auto_abandon` timeout, `tutor_quiz_attempt_answers` has 0 rows for that attempt.
