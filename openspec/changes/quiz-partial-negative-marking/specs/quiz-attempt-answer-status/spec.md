## Purpose

Defines how a quiz attempt answer is classified as fully correct, partially correct, incorrect, or pending, and how results UIs count and display those states. Status is persisted on the attempt-answer row and is independent of awarded marks.

## ADDED Requirements

### Requirement: Attempt answers use a four-state correctness value

The system SHALL persist attempt-answer correctness on the existing attempt-answer `is_correct` column using these values only:

- `1` — every graded item on the question is correct
- `2` — at least one graded item is correct and at least one is not
- `0` — no graded item is correct, or the question was skipped or left blank
- `null` — the question is awaiting instructor review (open ended or short answer)

The option-bank `is_correct` value that marks which option is the right answer SHALL remain `0` or `1`. The system MUST NOT introduce a new database column for attempt status.

#### Scenario: Fully correct attempt answer

- **WHEN** every graded item on an auto-graded question matches the key
- **THEN** the attempt answer is stored with `is_correct` equal to `1`

#### Scenario: Partially correct attempt answer

- **WHEN** some graded items match the key and some do not
- **THEN** the attempt answer is stored with `is_correct` equal to `2`

#### Scenario: Incorrect or skipped attempt answer

- **WHEN** no graded item matches, or the student skipped or left the question blank
- **THEN** the attempt answer is stored with `is_correct` equal to `0`

#### Scenario: Pending manual review

- **WHEN** the question type is open ended or short answer and has not been reviewed
- **THEN** the attempt answer is stored with `is_correct` equal to `null`

#### Scenario: Option bank stays binary

- **WHEN** an instructor saves a question's answer options in the builder
- **THEN** each option's correctness remains `0` or `1` and is never stored as `2`

### Requirement: Status is derived from item results, not marks

The system MUST determine attempt-answer status from item-level correctness, not from `achieved_mark`. A row MAY have `is_correct` equal to `2` and `achieved_mark` equal to `0` when negative marking floors the score.

#### Scenario: Partial status survives a zero score

- **GIVEN** a multi-item question where some items are correct
- **AND** negative marking reduces the awarded mark to `0`
- **WHEN** the attempt answer is stored
- **THEN** `is_correct` is `2` and the results UI shows Partially correct, not Incorrect

### Requirement: Results UI distinguishes partial from correct and incorrect

The system SHALL map stored attempt-answer values to these display statuses, checking `null` and `2` before any truthy test:

- `null` → pending
- `1` → correct
- `2` → partial
- any other value → incorrect

Attempt-details question headers SHALL show a **Partially correct** badge for partial answers. The questions sidebar SHALL use a distinct `partial` state and MUST NOT map `2` to correct or incorrect. Legacy attempt-details badges SHALL include a `partial` case.

#### Scenario: Question header shows Partially correct

- **WHEN** a student or instructor opens attempt details for an answer with `is_correct` equal to `2`
- **THEN** the question header displays Partially correct

#### Scenario: Sidebar does not treat partial as correct

- **WHEN** the questions sidebar renders an answer with `is_correct` equal to `2`
- **THEN** the item uses the partial state and is not styled or labeled as correct

#### Scenario: Truthy two is not fully correct

- **WHEN** any attempt-row consumer evaluates `is_correct` equal to `2`
- **THEN** the consumer treats it as partial, not as fully correct

### Requirement: Summary and attempt stats count states separately

The system SHALL count fully correct answers as `is_correct === 1` only. Partially correct answers (`2`) SHALL be counted separately. Incorrect answers SHALL be `is_correct === 0`. Pending answers SHALL not be counted as correct, partial, or incorrect.

#### Scenario: Mixed attempt summary

- **GIVEN** an attempt with one fully correct, one partially correct, and one incorrect auto-graded answer
- **WHEN** the attempt summary or formatted attempt stats are computed
- **THEN** correct is `1`, partial is `1`, and incorrect is `1`

#### Scenario: Partial is not added to correct count

- **WHEN** formatted quiz-attempt stats include an answer with `is_correct` equal to `2`
- **THEN** that answer is not added to `correct_answers`

### Requirement: Instructor review remains binary and adjusts marks from partial

Instructor review SHALL set attempt-answer `is_correct` to `1` (correct) or `0` (incorrect) and MUST NOT offer a partial-credit review action in this version. When the previous value is `2`, changing the review to correct or incorrect SHALL still update the attempt `earned_marks`. The review UI MAY show a read-only Partially correct badge before the instructor acts.

#### Scenario: Review a partial answer as correct

- **GIVEN** an attempt answer with `is_correct` equal to `2` and a non-zero `achieved_mark`
- **WHEN** the instructor marks the answer correct
- **THEN** `is_correct` becomes `1`, `achieved_mark` becomes the full question mark, and `earned_marks` increases by the difference

#### Scenario: Review a partial answer as incorrect

- **GIVEN** an attempt answer with `is_correct` equal to `2`
- **WHEN** the instructor marks the answer incorrect
- **THEN** `is_correct` becomes `0`, `achieved_mark` becomes `0`, and `earned_marks` decreases by the previous achieved mark

### Requirement: Partial status remains visible without Pro

The system SHALL display stored `is_correct` equal to `2` as Partially correct even when Tutor Pro is not active. New attempts graded without Pro SHALL continue to store only `0`, `1`, or `null`.

#### Scenario: Historical partial after Pro is deactivated

- **GIVEN** an existing attempt answer stored with `is_correct` equal to `2`
- **AND** Tutor Pro is not active
- **WHEN** a user views attempt details
- **THEN** the answer still displays as Partially correct
