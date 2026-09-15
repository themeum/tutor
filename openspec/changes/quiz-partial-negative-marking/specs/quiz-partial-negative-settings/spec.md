## Purpose

Defines how instructors and administrators configure partial and negative marking. Defines the admin `Grading` settings page, who can see the quiz-level controls, client-side validation in quiz settings, and how those settings persist through builder save, Pro REST, and quiz import/export. There are no per-question scoring overrides in v1.

## ADDED Requirements

### Requirement: Admin Grading settings provide independent Automatic Assessment toggles

The system SHALL register a `Grading` tab in Tutor admin settings whenever Tutor Pro is active, without requiring the `Gradebook` add-on to be enabled.

The `Grading` tab SHALL include an `Automatic Assessment` settings block containing two independent toggle switches:

- `enable_quiz_partial_marking`: `toggle_switch`, default `'off'`, label "Partial marking", description "Award credit for correct sub-answers on multi-part questions", with tooltip.
- `enable_quiz_negative_marking`: `toggle_switch`, default `'off'`, label "Negative marking", description "Deducts points for each wrong answer once enabled", with tooltip.

If the `Gradebook` add-on is enabled, the Gradebook settings block SHALL render above the `Automatic Assessment` block. If the `Gradebook` add-on is disabled, the `Grading` tab SHALL display only the `Automatic Assessment` block.

Tutor core SHALL localize both `enable_quiz_partial_marking` and `enable_quiz_negative_marking` in `Course.php` so they are accessible via `tutorConfig.settings` in the course builder.

#### Scenario: Grading tab visible with Gradebook add-on disabled

- **GIVEN** Tutor Pro is active
- **AND** the Gradebook add-on is disabled
- **WHEN** an admin navigates to Tutor Settings
- **THEN** the `Grading` tab is visible
- **AND** it displays the `Automatic Assessment` block with `Partial marking` and `Negative marking` toggles
- **AND** the Gradebook configuration block is not displayed

#### Scenario: Grading tab ordering with Gradebook add-on enabled

- **GIVEN** Tutor Pro is active
- **AND** the Gradebook add-on is enabled
- **WHEN** an admin navigates to the `Grading` tab in Tutor Settings
- **THEN** the Gradebook settings block is displayed at the top
- **AND** the `Automatic Assessment` block is displayed below the Gradebook settings block

#### Scenario: Free site does not show Grading tab

- **GIVEN** Tutor Pro is not active
- **WHEN** an admin navigates to Tutor Settings
- **THEN** the `Grading` tab is not present

### Requirement: Quiz-level defaults are off until an instructor enables them

A quiz SHALL store these options in existing quiz settings, with defaults that keep current all-or-nothing behavior:

- `enable_partial_marking`: `0` or `1`, default `0`
- `enable_negative_marking`: `0` or `1`, default `0`
- `negative_mark_type`: `percent` or `fixed`, default `percent`
- `negative_mark_value`: number, default `0` (percentage between `0` and `100` when `negative_mark_type` is `percent`; absolute marks $\ge 0$ when `negative_mark_type` is `fixed`)

The course-builder quiz settings in `QuizSettings.tsx` SHALL implement these controls directly (not via injection field slots) and SHALL gate them independently:

1. `enable_partial_marking` toggle is displayed ONLY when Tutor Pro is active (`!!tutorConfig.tutor_pro_url`) AND `tutorConfig.settings?.enable_quiz_partial_marking === 'on'`.
2. `enable_negative_marking` toggle, type switch, and `negative_mark_value` input are displayed ONLY when Tutor Pro is active (`!!tutorConfig.tutor_pro_url`) AND `tutorConfig.settings?.enable_quiz_negative_marking === 'on'`.

If Tutor Pro is not active, `QuizSettings.tsx` SHALL NOT display any partial or negative marking controls. Existing quizzes without these keys SHALL behave as if every flag is off and type is `percent`.

#### Scenario: New quiz defaults to all-or-nothing

- **WHEN** an instructor creates a quiz and does not change scoring options
- **THEN** partial marking is off, negative marking is off, type is `percent`, and `negative_mark_value` is `0`

#### Scenario: Free builder hides the controls

- **GIVEN** Tutor Pro is not active
- **WHEN** an instructor opens quiz settings in the course builder
- **THEN** partial and negative marking controls are not shown

#### Scenario: Partial marking admin setting disabled hides quiz partial toggle

- **GIVEN** Tutor Pro is active
- **AND** `enable_quiz_partial_marking` is disabled in admin settings
- **WHEN** an instructor opens quiz settings in the course builder
- **THEN** the partial marking toggle is not shown in `QuizSettings.tsx`

#### Scenario: Negative marking admin setting disabled hides quiz negative controls

- **GIVEN** Tutor Pro is active
- **AND** `enable_quiz_negative_marking` is disabled in admin settings
- **WHEN** an instructor opens quiz settings in the course builder
- **THEN** the negative marking toggle, type, and value controls are not shown in `QuizSettings.tsx`

#### Scenario: Both admin settings enabled exposes all controls

- **GIVEN** Tutor Pro is active
- **AND** both `enable_quiz_partial_marking` and `enable_quiz_negative_marking` are enabled in admin settings
- **WHEN** an instructor opens quiz settings in the course builder
- **THEN** both partial marking and negative marking controls are displayed in `QuizSettings.tsx`

#### Scenario: Invalid percent value is rejected

- **WHEN** `negative_mark_type` is `percent`
- **AND** a save or REST request sets `negative_mark_value` below `0` or above `100`
- **THEN** the value is rejected or clamped to the `0`–`100` range and is never stored outside that range

#### Scenario: Invalid fixed value is rejected

- **WHEN** `negative_mark_type` is `fixed`
- **AND** a save or REST request sets `negative_mark_value` below `0`
- **THEN** the value is rejected or clamped to `0` or greater and is never stored as negative

#### Scenario: Invalid type is rejected

- **WHEN** a save or REST request sets `negative_mark_type` to a value other than `percent` or `fixed`
- **THEN** the value is rejected or falls back to `percent`

### Requirement: Negative mark input field in Quiz settings validates user input

In `QuizSettings.tsx`, when negative marking is enabled, the `negative_mark_value` input field SHALL enforce client-side form validation via `react-hook-form` based on `negative_mark_type`:

- If `negative_mark_type` is `percent`, the value MUST be between `0` and `100` (inclusive). If the value is $< 0$ or $> 100$, the form SHALL display a validation error message and prevent form submission.
- If `negative_mark_type` is `fixed`, the value MUST be $\ge 0$. If the value is $< 0$, the form SHALL display a validation error message and prevent form submission.

#### Scenario: Negative mark percent value out of range displays validation error

- **GIVEN** negative marking is enabled and `negative_mark_type` is `percent`
- **WHEN** the instructor enters a value less than 0 or greater than 100 in `QuizSettings.tsx`
- **THEN** an inline validation error is displayed and the quiz settings form cannot be submitted

#### Scenario: Negative mark fixed value below zero displays validation error

- **GIVEN** negative marking is enabled and `negative_mark_type` is `fixed`
- **WHEN** the instructor enters a value less than 0 in `QuizSettings.tsx`
- **THEN** an inline validation error is displayed and the quiz settings form cannot be submitted

### Requirement: Scoring settings are quiz-level only

The system MUST NOT store per-question `partial_marking`, `negative_marking`, `negative_mark_type`, or `negative_mark_value` overrides. Question editors and the Pro content-bank question editor SHALL NOT show partial or negative marking controls. Supported auto-graded types inherit the quiz snapshot flags for every question in that attempt.

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

- **WHEN** an instructor sets `negative_mark_type` to `percent` and `negative_mark_value` to `25`, then saves
- **THEN** a subsequent load shows type `percent` and value `25`

#### Scenario: Import restores scoring options

- **WHEN** a quiz exported with partial marking on and negative marking `fixed` at `2` is imported
- **THEN** the imported quiz has the same scoring options

#### Scenario: REST exposes quiz keys only with Pro

- **GIVEN** Tutor Pro is active
- **WHEN** a client reads or writes quiz options through a Pro-backed quiz settings path
- **THEN** the payload includes the partial and negative marking keys
