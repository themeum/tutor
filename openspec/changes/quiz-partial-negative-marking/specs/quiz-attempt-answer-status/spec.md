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
