## Purpose

Defines the manual grading workflow, numeric score assignment, per-question instructor feedback, and restriction of override buttons across both Tutor Legacy and v4 interfaces.

## ADDED Requirements

### Requirement: Manual grading for open-ended and short answer questions across Legacy and v4

The system SHALL implement manual grading for questions of type `open_ended` and `short_answer` across both Tutor Legacy (WP-Admin table view `views/quiz/attempt-details.php`) and v4 component architecture (`templates/shared/components/quiz/attempt-details/*`).

- For `open_ended` and `short_answer` questions, the status SHALL strictly be:
  - `pending` (rendered as `Pending Review` / `Pending`) before manual grading.
  - `graded` (rendered as `Graded` with label `Score: {achieved_mark}/{question_mark}`) once graded.
  - The status SHALL NEVER be `correct`, `incorrect`, or `partial`.
  - Manual grading SHALL NOT be affected by partial marking or negative marking settings.
- The binary `[✓]` and `[✕]` override buttons SHALL NOT be displayed for `open_ended` and `short_answer` questions in any interface.
- In both Legacy and v4, the UI SHALL provide an obtained marks input: `[ obtained_mark ] / {question_mark}` (e.g. `[ 3.0 ] / 5`).
- Instructors SHALL be able to assign any numeric mark between `0` and `question_mark`.
- When manual marks are saved:
  - The answer row's `achieved_mark` SHALL update to the specified mark.
  - The attempt's `earned_marks` SHALL adjust by the delta (`new_mark - previous_mark`).
  - The attempt's `is_manually_reviewed` SHALL be set to `1`, and `manually_reviewed_at` updated.
  - When all questions in the attempt have been evaluated, attempt status SHALL become `completed` (`ATTEMPT_ENDED`).
  - The status badge SHALL update to `Graded` with label `Score: {achieved_mark}/{question_mark}`.

#### Scenario: Essay receives manual score without becoming correct or incorrect

- **GIVEN** an open-ended essay question worth 5 points
- **WHEN** an instructor enters `3.0` and saves
- **THEN** `achieved_mark` is set to `3.00`
- **AND** the status badge displays `Graded` with `Score: 3/5`
- **AND** the question is not labeled as correct, incorrect, or partial

### Requirement: Instructor dashboard feedback flow (v4) is inline without delete confirmation

In the v4 Instructor Dashboard review interface (`templates/shared/components/quiz/attempt-details/*`):

- For `open_ended` and `short_answer` questions, if no feedback exists, the card SHALL display an `Add Feedback` button.
- If feedback already exists (loaded from the server), the card SHALL display a `Show Feedback` button instead.
- Clicking `Add Feedback` or `Show Feedback` SHALL expand an inline feedback panel titled **"Write feedback"** beneath the question card.
- The panel SHALL contain a `<textarea name="question_feedback[{attempt_answer_id}]">` (a plain form field participating in the parent form), `Cancel` and `Save` buttons, and — when existing feedback is present — a `Delete` button (red text).
- The panel-level **`Save` button SHALL be a client-side-only action**: it commits the draft textarea content to Alpine state, collapses the panel, and updates the trigger button to `Show Feedback`. **No API call is fired.**
- The panel-level **`Cancel` button** SHALL revert the draft to the last committed state and collapse the panel, discarding any unsaved edits.
- The **`Delete` button** (visible when existing feedback is present) SHALL clear the feedback field to an empty string in Alpine state and update the trigger to `Add Feedback`. **No API call is fired and no confirmation dialog is shown.**
- **All data (marks and feedback) SHALL be persisted in a single API call** when the instructor clicks the page-level form Submit button. There SHALL be no per-question or per-feedback AJAX call in the v4 flow.
- The server-side AJAX handler SHALL read feedback via `Input::post('question_feedback', [], Input::TYPE_ARRAY)` (an associative array keyed by `attempt_answer_id`) and marks via `Input::post('manual_marks', [], Input::TYPE_ARRAY)` (keyed by `question_id`). Both MUST use `Input::TYPE_ARRAY` to comply with the `QueryHelper` / `Input.php` standardisation requirement.

#### Scenario: Inline feedback authoring and deletion on instructor dashboard

- **GIVEN** an instructor reviewing an attempt in the v4 instructor dashboard
- **WHEN** the instructor clicks `Add Feedback`, types text, and clicks the panel `Save` button
- **THEN** the draft is committed to Alpine state and the panel collapses
- **AND** the trigger button changes to `Show Feedback` (no API call fired yet)
- **WHEN** the instructor clicks the page-level form `Submit` button
- **THEN** all marks and feedback are sent in a single API call
- **WHEN** the instructor clicks `Show Feedback` and clicks `Delete`
- **THEN** the feedback field is cleared to empty string in Alpine state (no API call, no confirmation prompt)
- **AND** the trigger reverts to `Add Feedback`

### Requirement: Admin dashboard feedback flow (Legacy) uses modals and delete confirmation

In the WP-Admin Quiz Attempts details table (`views/quiz/attempt-details.php`):

- For `open_ended` and `short_answer` rows, the `Manual Review` column SHALL display the obtained marks input `[ obtained_mark ] / {question_mark}` and a feedback action link (`Add Feedback` or `Show Feedback`).
- Clicking `Add Feedback` SHALL open a modal dialog:
  - Title: "Write feedback"
  - Textarea with placeholder "Write feedback on this essay"
  - Actions: `Cancel` and `Save` (persisted via AJAX)
- Once feedback exists, clicking `Show Feedback` SHALL open the "Edit feedback" modal with `Delete` (red), `Cancel`, and `Save`.
- Clicking `Delete` SHALL open a confirmation modal:
  - Icon: Warning document icon
  - Title: "Are you sure you want to delete this feedback?"
  - Actions: `No, keep it` and `Yes, delete` (red button)
- Clicking `Yes, delete` SHALL delete the feedback via AJAX and revert the trigger to `Add Feedback`.

#### Scenario: Admin deletes feedback through confirmation modal

- **GIVEN** an essay in the WP-Admin Quiz Attempts view has saved feedback
- **WHEN** the admin clicks `Show Feedback` and clicks `Delete`
- **THEN** a confirmation modal is displayed asking "Are you sure you want to delete this feedback?"
- **AND** clicking "Yes, delete" removes the feedback via AJAX

### Requirement: Student view displays instructor feedback and keeps skipped questions hidden

When a student views their quiz attempt results:

- Skipped questions SHALL NOT be visible to the student, maintaining Tutor's existing student view behavior.
- For any open-ended or short answer question with instructor feedback, the UI SHALL display a callout box titled **"Feedback from instructor"** beneath the student's submitted response.
- The question header SHALL display the `Graded` badge with `Score: {achieved_mark}/{question_mark}`.

#### Scenario: Student sees feedback callout on graded essay

- **GIVEN** an essay question was manually graded with feedback
- **WHEN** the student views the attempt details
- **THEN** the question displays the `Graded` badge and score
- **AND** a "Feedback from instructor" callout box displays the instructor's feedback

### Requirement: Removal of override buttons on skipped and manually gradeable questions

Across both Legacy and v4 interfaces:

- The binary `[✓]` and `[✕]` manual override buttons SHALL NOT be displayed for any question that the student skipped.
- The binary `[✓]` and `[✕]` manual override buttons SHALL NOT be displayed for `open_ended` or `short_answer` questions.
- Answered auto-graded questions SHALL retain the `[✓]` and `[✕]` manual override buttons. When overridden by an instructor, the UI SHALL display the label `(Overrides the auto-graded result)` beneath the buttons.

#### Scenario: Skipped question has no override controls

- **GIVEN** an instructor views an attempt with a skipped question in either Legacy admin or v4 dashboard
- **WHEN** the question is rendered
- **THEN** no `[✓]` or `[✕]` override buttons are displayed
- **AND** the question displays the `Skipped` badge
