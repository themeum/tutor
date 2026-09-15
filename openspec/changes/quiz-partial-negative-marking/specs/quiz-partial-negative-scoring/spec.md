## Purpose

Defines Tutor Pro scoring for partial credit and optional negative marking on six multi-item auto-graded question types, including how those marks combine with core all-or-nothing submit and pass/fail totals.

## ADDED Requirements

### Requirement: Partial and negative scoring run only in Pro for supported types

When Tutor Pro is active, the system SHALL apply partial and negative scoring only to these auto-graded types: multiple choice with more than one correct option, matching, image matching, ordering, fill-in-the-blank, and image answering.

The system MUST NOT apply this scoring to true/false, single-select multiple choice, open ended, short answer, H5P, puzzle, pin, draw, scale, or graph questions. Those types SHALL keep their existing all-or-nothing or addon grading.

When Tutor Pro is not active, new attempts SHALL keep core all-or-nothing marks (`full` or `0`) and status `1` or `0` (or `null` for pending review).

#### Scenario: Supported type with Pro active

- **GIVEN** Tutor Pro is active and quiz partial marking is enabled
- **WHEN** the student places some matching items correctly and some incorrectly
- **THEN** the attempt answer receives proportional marks and a partial status

#### Scenario: Unsupported modern Pro type is unchanged

- **WHEN** a student submits a puzzle, pin, draw, scale, graph, or H5P question
- **THEN** this scoring path does not change that question's marks or status

#### Scenario: Free site stays all-or-nothing

- **GIVEN** Tutor Pro is not active
- **WHEN** a student submits a multi-correct multiple-choice question with some correct options selected
- **THEN** the attempt answer is stored as fully correct or incorrect with full marks or zero

### Requirement: Flags come from the attempt quiz snapshot only

The grader SHALL read `enable_partial_marking`, `enable_negative_marking`, `negative_mark_type`, `negative_mark_percent`, and `negative_mark_value` from the attempt's snapshotted quiz options, not from live quiz settings edited after the attempt started. The system MUST NOT read per-question scoring overrides.

#### Scenario: Later quiz edit does not change an in-flight attempt

- **GIVEN** an attempt started with partial marking off
- **AND** an instructor later enables partial marking on the quiz
- **WHEN** the student submits that attempt
- **THEN** the attempt is still graded all-or-nothing

### Requirement: Partial marks use type-specific proportional formulas

When quiz partial marking is enabled, the system SHALL compute a raw partial mark as follows, then round to two decimal places:

- Matching, image matching, and ordering: `correct_positions / total_items * question_mark`
- Fill-in-the-blank: `correct_blanks / total_blanks * question_mark` using the same case-insensitive comparison as today
- Image answering: `correct_labels / total_images * question_mark`
- Multiple choice with more than one correct option:

```
raw = (correct_selected / total_correct - incorrect_selected / total_incorrect) * question_mark
partial = max(0, raw)
```

If every option is correct (`total_incorrect` is `0`), the system SHALL omit the incorrect-option penalty term.

When quiz partial marking is off, a supported type SHALL keep full marks only when every item is correct, otherwise `0`, and SHALL store `is_correct` as `1` only when every item is correct, otherwise `0`. The system MUST write `is_correct` equal to `2` only when quiz partial marking is on and the item-level result is mixed.

#### Scenario: Matching awards half credit

- **GIVEN** a 4-item matching question worth 4 points with quiz partial marking on
- **WHEN** the student matches 2 items to the correct position
- **THEN** `achieved_mark` is `2.00` before any negative penalty
- **AND** `is_correct` is `2`

#### Scenario: Select-all multiple choice cannot full-score

- **GIVEN** a multiple-choice question with 2 correct and 2 incorrect options, worth 4 points, quiz partial marking on
- **WHEN** the student selects every option
- **THEN** the raw partial mark is `0` and the answer is not fully correct

#### Scenario: All-correct multiple-choice options skip the penalty term

- **GIVEN** a multiple-choice question where every option is correct
- **AND** quiz partial marking is on
- **WHEN** the student selects a subset of the options
- **THEN** the mark is `selected / total_correct * question_mark` with no incorrect-option penalty

#### Scenario: Partial marking off stores mixed answers as incorrect

- **GIVEN** quiz partial marking is off
- **WHEN** a student answers some matching items correctly and some incorrectly
- **THEN** `achieved_mark` is `0` and `is_correct` is `0`

### Requirement: Negative marking applies only to answered, not-fully-correct questions

When quiz negative marking is enabled, the system SHALL compute `minus_mark` only if the student answered the question and the item-level result is not fully correct:

- If `negative_mark_type` is `percent`: `minus_mark = (negative_mark_percent / 100) * question_mark`
- If `negative_mark_type` is `fixed`: `minus_mark = negative_mark_value`

Skipped or blank questions SHALL receive `is_correct` `0`, `achieved_mark` `0`, and `minus_mark` `0`.

The awarded mark SHALL be `max(0, partial_or_full - minus_mark)`. Quiz `earned_marks` SHALL be the sum of awarded marks and MUST NOT go below `0`. Pass/fail SHALL continue to use earned percentage against the passing grade. Round `minus_mark` and `achieved_mark` to two decimal places.

#### Scenario: Wrong answered question takes a percent penalty

- **GIVEN** a 10-point question with negative marking type `percent` at 20 percent
- **WHEN** the student submits an answered question with no correct items
- **THEN** `minus_mark` is `2.00` and `achieved_mark` is `0.00`

#### Scenario: Wrong answered question takes a fixed penalty

- **GIVEN** a 10-point question with negative marking type `fixed` and value `1.50`
- **WHEN** the student submits an answered question with no correct items
- **THEN** `minus_mark` is `1.50` and `achieved_mark` is `0.00`

#### Scenario: Partial then percent penalty floors at zero

- **GIVEN** a 10-point question with a 2-point partial raw mark and negative marking type `percent` at 30 percent
- **WHEN** the penalty is applied
- **THEN** `minus_mark` is `3.00` and `achieved_mark` is `0.00`

#### Scenario: Partial then fixed penalty floors at zero

- **GIVEN** a 10-point question with a 2-point partial raw mark and negative marking type `fixed` with value `3`
- **WHEN** the penalty is applied
- **THEN** `minus_mark` is `3.00` and `achieved_mark` is `0.00`

#### Scenario: Skipped question has no penalty

- **WHEN** the student skips a supported question
- **THEN** `minus_mark` is `0` and `achieved_mark` is `0`

#### Scenario: Fully correct question has no penalty

- **WHEN** every item is correct
- **THEN** `minus_mark` is `0` and the student receives the full question mark

### Requirement: Pro adjusts the running quiz total without rewriting core submit

Core submit SHALL continue to add full marks only when the answer is 100 percent correct, otherwise `0`. After Pro rewrites the attempt-answer marks:

- if the core row was not fully correct, the system SHALL add the new `achieved_mark` to the running total
- if the core row was fully correct, the running total SHALL stay unchanged unless negative marking does not apply (fully correct has no penalty)

The system MUST NOT change the core all-or-nothing assignment path itself.

#### Scenario: Partial answer adds marks that core omitted

- **GIVEN** core stored `achieved_mark` `0` for a partially correct matching question
- **WHEN** Pro awards `1.50` for that question
- **THEN** the quiz earned total increases by `1.50`

#### Scenario: Fully correct answer is not double-counted

- **GIVEN** core already added the full question mark
- **WHEN** Pro confirms the answer is fully correct
- **THEN** the quiz earned total does not increase again
