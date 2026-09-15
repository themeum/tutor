## Purpose

Defines how instructors and administrators configure partial and negative marking. Defines the admin `Grading` settings page, confirmation modals, default penalty settings, quiz grandfathering, client-side validation in quiz settings, and how those settings persist through builder save, Pro REST, and quiz import/export. There are no per-question scoring overrides in v1.

## ADDED Requirements

### Requirement: Admin Grading settings provide independent Automatic Assessment toggles and confirmation modals

The system SHALL register a `Grading` tab in Tutor admin settings whenever Tutor Pro is active, without requiring the `Gradebook` add-on to be enabled.

The `Grading` tab SHALL include an `Automatic Assessment` settings block containing:

- `enable_quiz_partial_marking`: `toggle_switch`, default `'off'`, label "Partial marking", description "Award credit for correct sub-answers on multi-part questions".
  - When switched from ON to OFF, the UI SHALL display a confirmation modal:
    - Title: "Turn off Partial marking?"
    - Content: "Partial marking will be disabled for new quizzes. Quizzes that already have it enabled will continue to work as they are."
    - Buttons: "No, keep it" (cancels toggle) and "Yes, turn off" (applies turn-off).
- `enable_quiz_negative_marking`: `toggle_switch`, default `'off'`, label "Negative marking", description "Deducts points for each wrong answer once enabled", with tooltip:
  - Tooltip: "Final quiz marks ≥ 0; Individual question scores can be negative, but a student's final earned score can never be less than 0. If negative scores reduce the total below 0, the final score will be set to 0."
  - When enabled, exposes sub-fields: "Penalty per wrong answer" with a numeric input (default `0.15`) and a unit dropdown (`Pts` or `%`, defaulting to `Pts`).
  - When switched from ON to OFF, the UI SHALL check if negative marking has been customized in any quiz:
    - IF negative marking has been customized in at least one quiz, the UI SHALL display a confirmation modal:
      - Title: "Turn off Negative marking?"
      - Content: "This hides negative marking across all quizzes. Your question-level values stay saved until you turn it back on."
      - Buttons: "No, keep it" (cancels toggle) and "Yes, turn off" (applies turn-off).
    - IF negative marking has NOT been customized in any quiz, the toggle SHALL turn off immediately without displaying a confirmation modal.

If the `Gradebook` add-on is enabled, the Gradebook settings block SHALL render above the `Automatic Assessment` block. If the `Gradebook` add-on is disabled, the `Grading` tab SHALL display only the `Automatic Assessment` block.

Tutor core SHALL localize `enable_quiz_partial_marking`, `enable_quiz_negative_marking`, and negative marking default penalty values in `Course.php` so they are accessible via `tutorConfig.settings` in the course builder.

#### Scenario: Grading tab visible with Gradebook add-on disabled

- **GIVEN** Tutor Pro is active
- **AND** the Gradebook add-on is disabled
- **WHEN** an admin navigates to Tutor Settings
- **THEN** the `Grading` tab is visible
- **AND** it displays the `Automatic Assessment` block with `Partial marking` and `Negative marking` controls
- **AND** the Gradebook configuration block is not displayed

#### Scenario: Turning off Partial marking displays grandfathering confirmation modal

- **GIVEN** `enable_quiz_partial_marking` is currently ON in Admin Settings
- **WHEN** the admin clicks the toggle to turn it OFF
- **THEN** a confirmation modal is shown with title "Turn off Partial marking?" and body stating that existing quizzes will continue to work as they are
- **AND** clicking "No, keep it" keeps the toggle ON
- **AND** clicking "Yes, turn off" turns the toggle OFF

#### Scenario: Turning off Negative marking displays modal only when values are customized

- **GIVEN** `enable_quiz_negative_marking` is currently ON in Admin Settings
- **AND** at least one quiz has customized negative marking enabled or configured
- **WHEN** the admin clicks the toggle to turn it OFF
- **THEN** a confirmation modal is shown with title "Turn off Negative marking?"
- **AND** clicking "Yes, turn off" turns the toggle OFF

#### Scenario: Turning off Negative marking does not show modal when no customizations exist

- **GIVEN** `enable_quiz_negative_marking` is currently ON in Admin Settings
- **AND** no quiz has customized negative marking enabled or configured
- **WHEN** the admin clicks the toggle to turn it OFF
- **THEN** the toggle turns OFF immediately without displaying a confirmation modal

### Requirement: Grandfathering preserves existing quizzes when admin toggle turns off

Turning the admin `enable_quiz_partial_marking` toggle OFF SHALL NOT modify or disable partial marking on quizzes that already had it enabled. Quizzes with `enable_partial_marking == 1` SHALL continue to score partially as configured and SHALL continue to display the Partial marking setting in the course builder.

#### Scenario: Existing quiz with partial marking remains active after admin toggle turned off

- **GIVEN** a quiz was created with `enable_partial_marking` set to 1
- **AND** the admin later turns off `enable_quiz_partial_marking` in Admin Settings
- **WHEN** a student takes that quiz
- **THEN** partial scoring still applies according to the quiz options
- **AND** when an instructor opens that quiz in the course builder, the Partial marking switch remains visible and enabled

#### Scenario: New quiz does not have partial marking when admin toggle is off

- **GIVEN** `enable_quiz_partial_marking` is OFF in Admin Settings
- **WHEN** an instructor creates a new quiz in the course builder
- **THEN** the Partial marking switch is not shown

### Requirement: Course Builder Quiz settings layout and validation

In `QuizSettings.tsx`, under the `Grading` card section (below "Passing grade (%)"):

- `Partial marking` SHALL be rendered as a `FormSwitch` with description "Award credit for correct sub-answers on multi-part questions" and tooltip: "Applies to question types with multiple sub-answers (Matching, Ordering, Image Matching, Fill in the Blanks (multiple answer), Puzzle, multi-select Multiple Choice)".
  - Visible if `isTutorPro && (adminPartialEnabled || quizOptionPartialAlreadyOn)`.
- `Negative marking` SHALL be rendered as a `FormCheckbox` with label "Negative marking", tooltip, and description "Deducts points for each wrong answer once enabled".
  - Visible if `isTutorPro && adminNegativeEnabled`.
  - When checked, reveals "Penalty per wrong answer" with numeric input `negative_mark_value` and dropdown `negative_mark_type` (`%` or `Pts`).
  - The input field SHALL enforce client-side form validation via `react-hook-form`:
    - When `negative_mark_type` is `percent` (`%`), the value MUST be between `0` and `100` (inclusive).
    - When `negative_mark_type` is `fixed` (`Pts`), the value MUST be $\ge 0$.
    - Invalid values display inline error messages and block form submission.

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
