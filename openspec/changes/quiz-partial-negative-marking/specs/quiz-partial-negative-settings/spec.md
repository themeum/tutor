## Purpose

Defines how instructors configure partial and negative marking at quiz level, who can see the controls, and how those settings persist through builder save, Pro REST, and quiz import/export. There are no per-question scoring overrides in v1.

## ADDED Requirements

### Requirement: Quiz-level defaults are off until an instructor enables them

A quiz SHALL store these options in existing quiz settings, with defaults that keep current all-or-nothing behavior:

- `enable_partial_marking`: `0` or `1`, default `0`
- `enable_negative_marking`: `0` or `1`, default `0`
- `negative_mark_type`: `percent` or `fixed`, default `percent`
- `negative_mark_percent`: `0` through `100`, default `0` (used when type is `percent`)
- `negative_mark_value`: `0` or greater, default `0` (absolute marks; used when type is `fixed`)

When Tutor Pro is active, the course-builder quiz settings SHALL expose these controls. When Tutor Pro is not active, the free builder SHALL NOT show them. Existing quizzes without these keys SHALL behave as if every flag is off and type is `percent`.

#### Scenario: New quiz defaults to all-or-nothing

- **WHEN** an instructor creates a quiz and does not change scoring options
- **THEN** partial marking is off, negative marking is off, type is `percent`, percent is `0`, and fixed value is `0`

#### Scenario: Free builder hides the controls

- **GIVEN** Tutor Pro is not active
- **WHEN** an instructor opens quiz settings
- **THEN** partial and negative marking controls are not shown

#### Scenario: Invalid percent is rejected

- **WHEN** a save or REST request sets `negative_mark_percent` below `0` or above `100`
- **THEN** the value is rejected or clamped to the `0`–`100` range and is never stored outside that range

#### Scenario: Invalid fixed value is rejected

- **WHEN** a save or REST request sets `negative_mark_value` below `0`
- **THEN** the value is rejected or clamped to `0` or greater and is never stored as negative

#### Scenario: Invalid type is rejected

- **WHEN** a save or REST request sets `negative_mark_type` to a value other than `percent` or `fixed`
- **THEN** the value is rejected or falls back to `percent`

### Requirement: Scoring settings are quiz-level only

The system MUST NOT store per-question `partial_marking`, `negative_marking`, `negative_mark_percent`, or `negative_mark_value` overrides. Question editors and the Pro content-bank question editor SHALL NOT show partial or negative marking controls. Supported auto-graded types inherit the quiz snapshot flags for every question in that attempt.

#### Scenario: Question sidebar has no scoring overrides

- **WHEN** an instructor edits a matching question in the course builder
- **THEN** no partial or negative marking inherit/on/off controls are shown on the question

#### Scenario: Content bank has no scoring overrides

- **WHEN** an instructor edits a matching question in the Pro content bank
- **THEN** no partial or negative marking controls are shown

### Requirement: Settings persist through builder, REST, and import/export

The system SHALL persist the quiz-level keys with the quiz options. A save MUST NOT drop unknown Pro keys. When Tutor Pro is active, quiz read/write paths that expose quiz options (builder payload, Pro REST where applicable, and quiz import/export) SHALL accept and return these keys.

Quiz-level flags that exist at attempt start SHALL be included in the attempt settings snapshot so later edits do not change historical or in-flight grading.

#### Scenario: Builder save keeps Pro keys

- **WHEN** an instructor enables partial marking, enables negative marking as `fixed` with value `1.5`, then saves the quiz
- **THEN** a subsequent load of the quiz shows those same values

#### Scenario: Percent mode survives reload

- **WHEN** an instructor sets `negative_mark_type` to `percent` and `negative_mark_percent` to `25`, then saves
- **THEN** a subsequent load shows type `percent` and percent `25`

#### Scenario: Import restores scoring options

- **WHEN** a quiz exported with partial marking on and negative marking `fixed` at `2` is imported
- **THEN** the imported quiz has the same scoring options

#### Scenario: REST exposes quiz keys only with Pro

- **GIVEN** Tutor Pro is active
- **WHEN** a client reads or writes quiz options through a Pro-backed quiz settings path
- **THEN** the payload includes the partial and negative marking keys
