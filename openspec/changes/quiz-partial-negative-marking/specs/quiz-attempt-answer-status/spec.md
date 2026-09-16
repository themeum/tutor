## Purpose

Defines how a quiz attempt answer is classified and displayed across Tutor Legacy and v4. Auto-graded answers are classified as `correct`, `partial`, `incorrect`, or `skipped`. Manually gradeable questions (`open_ended` and `short_answer`) strictly use `pending` or `graded` and are never categorized as `correct`, `partial`, or `incorrect`. Skipped questions remain completely hidden from student results.

## ADDED Requirements

### Requirement: Attempt answer statuses distinguish auto-graded and manually gradeable questions

For auto-graded questions:
The system SHALL persist attempt-answer correctness on the existing attempt-answer `is_correct` column using:

- `1` — fully correct (all graded items match)
- `2` — partially correct (quiz partial marking is on with mixed items)
- `0` — incorrect (no items correct, or mixed items with partial marking off, or skipped)

For manually gradeable questions (`open_ended` and `short_answer`):

- The question SHALL strictly have one of two statuses: `pending` or `graded`.
- It SHALL NOT be categorized as `correct`, `partial`, or `incorrect` at any point.
- It SHALL NOT be affected by quiz-level partial marking or negative marking settings.
- Before manual grading, its status SHALL be `pending` (rendered as `Pending` / `Pending Review`).
- Once graded by an instructor (numeric marks assigned and saved), its status SHALL be `graded` (rendered as `Graded` with label `Score: {achieved_mark}/{question_mark}`).

#### Scenario: Open-ended essay awaiting review is pending

- **WHEN** a student submits an open-ended essay question
- **THEN** its status is `pending`
- **AND** it is not labeled as correct, partial, or incorrect

#### Scenario: Graded essay has graded status, not correct or incorrect

- **GIVEN** an instructor awards 3 marks out of 5 for an open-ended question
- **WHEN** the attempt details are displayed
- **THEN** its status is `graded`
- **AND** the badge displays `Graded` with `Score: 3/5`
- **AND** it is not marked as `correct`, `partial`, or `incorrect`

### Requirement: Skipped questions remain hidden from students and visible only to instructors

When an attempt answer is skipped by a student:

- The question SHALL NOT be displayed in student results views, maintaining Tutor's existing student view behavior.
- In instructor views (both WP-Admin Quiz Attempts details and Instructor Dashboard review), the skipped question SHALL be displayed with a `Skipped` badge.
- Skipped questions SHALL award 0 marks, SHALL NOT incur any negative marking penalty, and SHALL NOT display `[✓]` / `[✕]` manual override buttons.

#### Scenario: Student results view does not show skipped questions

- **GIVEN** a student skipped Question 3 in a quiz
- **WHEN** the student views their quiz attempt results
- **THEN** Question 3 is not visible in the results view

#### Scenario: Instructor view displays skipped question with badge and no overrides

- **GIVEN** a student skipped Question 3 in a quiz
- **WHEN** an instructor views the attempt details
- **THEN** Question 3 is visible with a `Skipped` badge
- **AND** the score is 0 with no penalty
- **AND** no `[✓]` or `[✕]` override buttons are displayed

### Requirement: Summary and attempt stats count states separately

The system SHALL count fully correct answers as `is_correct === 1` only. Partially correct answers (`2`) SHALL be counted separately. Incorrect answers SHALL be `is_correct === 0`.
Manually gradeable questions (`pending` or `graded`) SHALL NOT be included in correct, partial, or incorrect counts.

#### Scenario: Mixed attempt summary excludes manual questions from correct/incorrect counts

- **GIVEN** an attempt with one fully correct auto-graded question, one incorrect auto-graded question, and one graded open-ended question
- **WHEN** attempt summary statistics are computed
- **THEN** correct answer count is `1`, incorrect answer count is `1`, and the graded open-ended question is not added to either count

### Requirement: Partially graded questions display an `N/M correct` badge in question header instead of `Partially correct`

When an auto-graded question has partial status (`is_correct === 2`), the question header SHALL NOT display a static `Partially correct` badge. Instead, it SHALL display an `{N}/{M} correct` badge, where:

- `N` is the number of correct answers or items matched/answered by the user.
- `M` is the total number of correct answers or items possible for that question.

The count mapping per supported question type SHALL be:

- **Multiple choice (multi-correct)**: `N` = count of correct options selected by user, `M` = total correct options.
- **Matching, Image matching, Ordering**: `N` = count of items placed in correct position, `M` = total items.
- **Fill in the blank**: `N` = count of blanks correctly filled, `M` = total blanks.
- **Image answering**: `N` = count of images correctly labeled, `M` = total images.

#### Scenario: Partially matched question displays N/M correct badge without static Partially correct badge

- **GIVEN** a 4-item matching question with quiz partial marking enabled
- **WHEN** the student correctly matches 2 of the 4 items
- **THEN** the attempt answer status is `partial`
- **AND** the question header displays a `2/4 correct` badge
- **AND** the static `Partially correct` badge is omitted

#### Scenario: Multi-choice question displays correct selections count

- **GIVEN** a multiple choice question with 3 correct options out of 5 options
- **WHEN** the student selects 2 of the correct options and 0 incorrect options
- **THEN** the attempt answer status is `partial`
- **AND** the question header displays a `2/3 correct` badge

### Requirement: Question header displays score for evaluated questions

In both student results and instructor attempt details views (across Legacy and v4), every evaluated question SHALL display `Score: {achieved_mark}/{question_mark}` in the top right of the question card.
The header score SHALL NOT be displayed for:

- Questions with status `pending` (pending review).
- Questions with status `skipped` (skipped by student).

#### Scenario: Evaluated question displays achieved and total marks in header

- **GIVEN** an auto-graded question worth 1 mark with achieved mark -0.25 (due to negative marking penalty)
- **WHEN** the attempt details are rendered
- **THEN** the top right displays `Score: -0.25/1`

#### Scenario: Partial question displays achieved and total marks in header

- **GIVEN** a question worth 4 marks with achieved mark 2
- **WHEN** the attempt details are rendered
- **THEN** the top right displays `Score: 2/4`

#### Scenario: Pending question omits header score

- **GIVEN** an open-ended question awaiting instructor grading
- **WHEN** the attempt details are rendered
- **THEN** the question displays a `Pending Review` badge and no `Score:` text in the top right

#### Scenario: Skipped question omits header score

- **GIVEN** a question skipped by the student
- **WHEN** an instructor views the attempt details
- **THEN** the question displays a `Skipped` badge and no `Score:` text in the top right

### Requirement: Student view displays negative mark penalty notice under Answer Explanation

In student attempt results, if a negative marking penalty was deducted for a question (`minus_mark > 0`), the system SHALL display the deducted penalty amount in red text directly beneath the `Answer Explanation` card (e.g. `-{minus_mark} points`).
If `minus_mark == 0`, no penalty text SHALL be displayed.

#### Scenario: Student views question with penalty applied

- **GIVEN** a student answered a question incorrectly where negative marking deducted 0.25 marks
- **WHEN** the student views the question result
- **THEN** `-0.25 points` is displayed in red text directly below the `Answer Explanation` card

#### Scenario: Student views question without penalty

- **GIVEN** a student answered a question correctly with 0 penalty
- **WHEN** the student views the question result
- **THEN** no penalty notice is displayed below the `Answer Explanation` card

### Requirement: Instructor view displays detailed mark breakdown table under Answer Explanation

In instructor attempt review (both Legacy and v4), for all evaluated questions (excluding `pending` review), the system SHALL display a detailed mark breakdown beneath the `Answer Explanation` card:

- **Earned credit row**:
  - For `correct` or `incorrect` questions: label `Earned` with value `{earned_mark}` (where raw earned mark = `achieved_mark + minus_mark`).
  - For `partial` questions: label `Partial credit` with value `{earned_mark}`.
- **Penalty deduction row** (displayed only when `minus_mark > 0`):
  - Label `Penalty -{minus_mark} deducted` in red text with value `-{minus_mark}` in red text.
- **Total score row**:
  - Label `Score: {achieved_mark}/{question_mark}` displayed right-aligned.

This breakdown table SHALL NOT be displayed for questions with status `pending`.

#### Scenario: Instructor views incorrect question with penalty

- **GIVEN** an instructor views an answered question worth 1 mark where earned marks is 0 and penalty deduction is 0.08
- **WHEN** the question details are displayed
- **THEN** under Answer Explanation, Row 1 displays `Earned` with `0`
- **AND** Row 2 displays `Penalty -0.08 deducted` with `-0.08` in red
- **AND** Row 3 displays `Score: -0.08/1`

#### Scenario: Instructor views partial question with penalty

- **GIVEN** an instructor views a 4-point question where earned credit is 2.00 and penalty deduction is 0.50
- **WHEN** the question details are displayed
- **THEN** under Answer Explanation, Row 1 displays `Partial credit` with `2`
- **AND** Row 2 displays `Penalty -0.50 deducted` with `-0.50` in red
- **AND** Row 3 displays `Score: 1.50/4`

### Requirement: Admin attempt details table displays inline mark breakdown in Result column

In the legacy WP-Admin attempt details view (`views/quiz/attempt-details.php`), the `Result` column SHALL display:

- For `correct` status: Badge `Correct` (`label-success`) and a secondary line `Score: {achieved_mark}/{question_mark}`.
- For `partial` status: Badge `{N}/{M} Correct` (`label-success`).
  - If `minus_mark > 0`: line 1 displays `(+{earned_mark}) -{minus_mark}` where `(+{earned_mark})` has green text class `tutor-color-success` and `-{minus_mark}` has red text class `tutor-color-danger`, followed by line 2 displaying `Score: {achieved_mark}/{question_mark}`.
  - If `minus_mark == 0`: line 1 displays `Score: {achieved_mark}/{question_mark}`.
- For `incorrect` status: Badge `Incorrect` (`label-danger`).
  - If `minus_mark > 0`: line 1 displays `(+{earned_mark}) -{minus_mark}` and line 2 displays `Score: {achieved_mark}/{question_mark}`.
  - If `minus_mark == 0`: line 1 displays `Score: {achieved_mark}/{question_mark}`.
- For `pending` and `skipped` statuses: Badges `Pending` and `Skipped` respectively, with no score displayed in the Result column.
- For `graded` status: Badge `Graded` (`label-success`) and a secondary line `Score: {achieved_mark}/{question_mark}`.

#### Scenario: Admin table displays partial with negative penalty breakdown

- **GIVEN** a 6-item ordering question worth 1 mark where student got 4 correct (+0.67) and penalty is 0.16
- **WHEN** the admin views the attempt details table
- **THEN** the Result column displays badge `4/6 Correct`
- **AND** below it displays `(+0.67)` in green and `-0.16` in red
- **AND** below that displays `Score: 0.51/1`

#### Scenario: Admin table displays correct question with score

- **GIVEN** a question worth 1 mark answered correctly
- **WHEN** the admin views the attempt details table
- **THEN** the Result column displays badge `Correct`
- **AND** below it displays `Score: 1.00/1`

#### Scenario: Admin table displays pending question without score in Result column

- **GIVEN** an open-ended essay question awaiting review
- **WHEN** the admin views the attempt details table
- **THEN** the Result column displays badge `Pending`
- **AND** no score text is rendered in the Result column
