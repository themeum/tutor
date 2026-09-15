## Purpose

Defines how instructors configure partial and negative marking at quiz and question level, which question types show the controls, and how those settings persist through builder save, Pro REST, and quiz import/export.

## ADDED Requirements

### Requirement: Quiz-level defaults are off until an instructor enables them

A quiz SHALL store these options in existing quiz settings, with defaults that keep current all-or-nothing behavior:

- `enable_partial_marking`: `0` or `1`, default `0`
- `enable_negative_marking`: `0` or `1`, default `0`
- `negative_mark_percent`: `0` through `100`, default `0`

When Tutor Pro is active, the course-builder quiz settings SHALL expose these controls. When Tutor Pro is not active, the free builder SHALL NOT show them. Existing quizzes without these keys SHALL behave as if every flag is off.

#### Scenario: New quiz defaults to all-or-nothing

- **WHEN** an instructor creates a quiz and does not change scoring options
- **THEN** partial marking is off, negative marking is off, and negative percent is `0`

#### Scenario: Free builder hides the controls

- **GIVEN** Tutor Pro is not active
- **WHEN** an instructor opens quiz settings
- **THEN** partial and negative marking controls are not shown

#### Scenario: Invalid percent is rejected

- **WHEN** a save or REST request sets `negative_mark_percent` below `0` or above `100`
- **THEN** the value is rejected or clamped to the `0`–`100` range and is never stored outside that range

### Requirement: Supported questions can inherit or override quiz defaults

Each supported question SHALL store these keys in existing question settings:

- `partial_marking`: `inherit`, `on`, or `off`
- `negative_marking`: `inherit`, `on`, or `off`
- `negative_mark_percent`: empty to inherit the quiz percent, or `0` through `100`

The question-level controls SHALL appear only for the six v1 types: multiple choice (multi-correct), matching, image matching, ordering, fill-in-the-blank, and image answering. The system MUST hide them for true/false, single-select multiple choice, open ended, short answer, H5P, puzzle, pin, draw, scale, and graph.

The same inherit/on/off controls SHALL be available in the Pro content-bank question editor.

#### Scenario: Question inherits quiz defaults

- **GIVEN** the quiz has partial marking on
- **AND** the question `partial_marking` is `inherit`
- **WHEN** the student is graded
- **THEN** partial marking is applied for that question

#### Scenario: Hidden for unsupported types

- **WHEN** an instructor edits a true/false or essay question
- **THEN** partial and negative marking controls are not shown

#### Scenario: Content bank mirrors course builder

- **WHEN** an instructor edits a matching question in the Pro content bank
- **THEN** inherit/on/off controls for partial and negative marking are available

### Requirement: Settings persist through builder, REST, and import/export

The system SHALL persist quiz-level keys with the quiz options and question-level keys with the question settings. A save MUST NOT drop unknown Pro keys. When Tutor Pro is active, the Pro quiz-question REST API SHALL accept and return these keys. Quiz import/export SHALL carry the keys in both directions.

Quiz-level flags that exist at attempt start SHALL be included in the attempt settings snapshot so later edits do not change historical or in-flight grading.

#### Scenario: Builder save keeps Pro keys

- **WHEN** an instructor enables partial marking and a 25 percent penalty, then saves the quiz
- **THEN** a subsequent load of the quiz shows those same values

#### Scenario: Question override survives reload

- **WHEN** an instructor sets a fill-in-the-blank question to `partial_marking` `off` and saves
- **THEN** the stored question settings still contain `partial_marking` `off` after reload

#### Scenario: Import restores scoring options

- **WHEN** a quiz exported with partial marking on and a question override of `negative_marking` `on` is imported
- **THEN** the imported quiz and question have the same scoring options

#### Scenario: REST exposes keys only with Pro

- **GIVEN** Tutor Pro is active
- **WHEN** a client reads or writes a supported quiz question through the Pro REST API
- **THEN** the payload includes the partial and negative marking keys
