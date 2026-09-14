# Tutor LMS Quiz & Attempt Lifecycle Context

The quiz domain within Tutor LMS models the creation, execution, progression, evaluation, and grading of student assessments across both Modern Learning Area (v4) and Legacy Learning Mode architectures.

## Language

### Learning Modes & Layouts

**Modern Learning Area (v4)**:
The current Alpine.js + TypeScript learning interface rendered via `templates/learning-area/` and powered by `tutor-learning-area.js`.
_Avoid_: New learning area, v4 mode, modern mode

**Legacy Learning Mode**:
The classic jQuery-driven student interface rendered via `templates/single/quiz/` and powered by `_spotlight-quiz.js` within `tutor-front.js`. Enabled when `learning_mode === 'legacy'`.
_Avoid_: Old mode, classic mode, spotlight mode

**Question Layout View**:
The display arrangement of quiz questions during an active attempt: `single_question` (one question at a time), `question_pagination` (paginated steps), or `question_below_each_other` (all questions on one page).
_Avoid_: Quiz view, question display mode

---

### Quiz Attempt & Lifecycle

**Quiz Attempt**:
A persisted session record representing a student's engagement with a specific quiz, stored in `tutor_quiz_attempts` with a unique `attempt_id` and tracked lifecycle states (`attempt_started`, `attempt_ended`, `attempt_timeout`, `review_required`).
_Avoid_: Quiz session, exam run, test instance

**Attempt Answer**:
A record in `tutor_quiz_attempt_answers` capturing the student's submitted response (`given_answer`), evaluation outcome (`is_correct`), and points earned (`achieved_mark`) for a single question within an attempt.
_Avoid_: User answer, submitted answer, response row

**Attempt Question Set Locking**:
The persistence of the specific ordered list of question IDs assigned to a quiz attempt (stored in `tutor_quiz_attempts.attempt_info['question_ids']`), ensuring deterministic question selection and sequence across page reloads and course navigation under random ordering or question limit configurations.
_Avoid_: Frozen questions, question caching, attempt snapshot

**Lock-in Grading (Option A)**:
The evaluation pattern where a student's answer for an individual question is submitted, graded on the server, and permanently persisted to `tutor_quiz_attempt_answers` at the moment they advance to the next question. Operates for all quizzes, preserving attempt progress across refreshes and disconnections.
_Avoid_: Incremental save, pre-submission, progressive grading

**Answer Reveal Mode (`enable_answer_reveal`)**:
A quiz configuration allowing students to receive immediate per-question visual feedback (correct/incorrect highlighting and answer explanation) upon submitting an answer. When disabled, incremental lock-in still occurs on the server without returning correct answer data to the browser.
_Avoid_: Instant feedback, show answers, reveal mode

**Answer Explanation**:
A descriptive explanation text authored by an instructor to provide pedagogical context on why a given answer is correct.
_Avoid_: Solution note, answer feedback, hint

---

### Timeout & Termination

**Expiration Action (`quiz_when_time_expires`)**:
The configured automated behavior when a quiz attempt's countdown reaches zero: either `auto_submit` (finalizing the attempt with answers submitted so far) or `auto_abandon` (terminating the attempt with zero earned marks and clearing partial attempt answers).
_Avoid_: Timeup action, timeout policy

**Submission Mutex (`isSubmitting`)**:
A client-side re-entry lock ensuring that once an attempt finalization is initiated, concurrent timeout handlers (`auto_abandon`) or secondary submit clicks cannot void, overwrite, or duplicate the submission.
_Avoid_: Submit lock, double-submit guard

**Fail-Open Under Deadline**:
The requirement that when a quiz timer expires, any visual reveal delays, timeouts, or network reveal requests are unconditionally bypassed so the attempt payload is dispatched immediately to the server.
_Avoid_: Fast submit, skip wait
