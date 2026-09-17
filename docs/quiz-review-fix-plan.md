# Quiz Partial/Negative Marking — Review Fix Plan

Source: code review of `dev...HEAD` (31 commits, quiz partial/negative marking
feature) against `openspec/changes/quiz-partial-negative-marking/` and the
Fowler-smell baseline (the repo declares no formal coding standards).

Scope note: this is a plan only. No tracker issues were created.

Legend: `P0` = shipped bug, `P1` = spec gap/regression, `P2` = cleanliness.

---

## Problem Statement

The 4.0 quiz feature branch works end to end, but the review found two shipped
bugs (a settings toggle that doesn't persist, a dead-code guard that relies on
`wp_die` side effects) and two spec gaps (Legacy admin summary drops partial
counts; manually graded attempts never become "completed"). Standards findings
are limited to refactors; the earlier claim about hardcoded hex colors was
retracted after research — this SCSS tree has no color-token system.

## Solution

Ship-fix `P0`, close the `P1` gaps, then run a small set of `P2` refactors that
shrink the surface the reviewers flagged. Each fix is smallest-possible and
kept inside the files this branch already touched.

## User Stories

1. As an admin, I want to turn off negative marking with no modal when no quiz
   customizes it, so that my toggle actually persists.
2. As an instructor, I want deleting a removed question feedback to actually
   stop the handler there, so the request can't accidentally continue.
3. As an instructor using the Legacy attempt page, I want partially-correct
   answers to appear in the summary counts, so they're not silently dropped.
4. As an instructor, I want finishing a manual-review question to complete the
   attempt, so the attempt status reflects reality (parity with open/short).
5. As an instructor, I want an ungraded auto-graded answer (e.g.
   image_answering pending review) to show Pending, not Incorrect, so students
   aren't shown a wrong result before grading.
6. As a maintainer, I want attempt-info parsing centralized, so the six
   duplicated feedback/manual-override reads live in one place.
7. As a maintainer, I want `is_correct` written through the existing constants,
   so the new manual-graded state (3) can't be misread as correct.

## Upstream findings (fix list)

### P0 bugs

1. **Negative-marking turn-off does not persist** (`options.js`, toggle
   handler usage-check branch). When the AJAX usage check returns
   `has_customized: false`, neither `revertToggle()` nor `proceedWithTurnoff()`
   runs, so the hidden input still submits `'on'`. Spec scenario "turn off
   immediately, no modal" is broken.
   Fix: add the missing else-path that calls `proceedWithTurnoff()` when the
   usage check says nothing customizes negative marking. Reword the handler's
   doc comment to describe the no-customization branch.

2. **Missing early-return in `delete_question_feedback`** (`QuizController`
   ~`delete_question_feedback`). The `!isset($feedback_map[$attempt_answer_id])`
   guard calls `response_success()` then falls through to dead code; it only
   "works" because `response_success` ends in `wp_send_json` → `wp_die`.
   Fix: `return;` immediately after the guard's `response_success()` call, and
   mirror it in the "already deleted" early-out so the control flow is explicit.

### P1 spec gaps / regressions

3. **Legacy admin summary drops partial counts**
   (`views/quiz/attempt-details.php` statics block). Counts come from
   `QuizModel::get_attempt_answer_counts()`, whose Pro filter adds a `partial`
   key, but the Legacy statics list only renders `correct`/`incorrect`, and the
   `tutor_quiz_attempt_summary_statics_after_correct` action (fired in the v4
   shared summary) is absent here.
   Fix:
   - Read `$answer_counts['partial'] ?? 0`.
   - Render a partial stat cell when `> 0` (mirroring the sibling
     `incorrect` cell markup, "N partially correct").
   - Fire `tutor_quiz_attempt_summary_statics_after_correct( $attempt_data, $answers )`
     at the same position as the shared v4 summary so the Pro statics renderer
     is consistent on both surfaces.

4. **Manual grading never completes the attempt**
   (`QuizController` `apply_manual_quiz_answer_mark` call sites). The sibling
   `apply_quiz_answer_review` sets `attempt_status = ATTEMPT_ENDED` for
   `open_ended`/`short_answer`; the manual-mark paths (single
   `review_quiz_answer` POST and `review_quiz_answers_bulk`) never do, and
   `QuizModel::update_attempt_result()` only updates the `result` column.
   Spec: "when all questions are evaluated the attempt SHALL become
   completed (`ATTEMPT_ENDED`)".
   Fix: set `attempt_status => QuizModel::ATTEMPT_ENDED` in the attempt update
   that saves `earned_marks`/`is_manually_reviewed` in both manual-mark paths.
   (Deterministic, mirrors existing open/short behavior; see Open Decisions.)

5. **Null `is_correct` on auto-graded types regresses to Incorrect**
   (`QuizModel::get_attempt_answer_status`). A null `is_correct` only reaches
   the status resolver when a grading hook (Pro) deferred the answer — e.g.
   `image_answering` awaiting review — and the pure-free path sets binary
   correct/incorrect at submit. Dev rendered that state as `pending`; HEAD drops
   it into the `else` → `incorrect`.
   Fix: keep `skipped` first, then classify **any** null `is_correct` as
   `pending` before the correct/incorrect branches (manual types already
   resolve null → `pending`, unchanged). `partial` (2) stays filter-driven;
   no change to the counts resolver.

### P2 refactors

6. **Centralize attempt-info parsing.** The
   `maybe_unserialize($attempt_info)` + `question_feedback` /
   `manual_overrides` extraction is repeated in `QuizController`
   (`get_question_feedback_map`, `save_question_feedback_map`,
   `apply_quiz_answer_review`, bulk handler) and five templates
   (Legacy attempt-details, v4 summary, question, open-ended, question-header).
   Add to QuizModel:
   - `get_attempt_feedback_map(array $attempt_info): array`
   - `get_manual_overrides_map(array $attempt_info): array`
   then swap every call site to use them. Behavior identical; deletes the
   clump.

7. **Deduplicate the manual-graded badge/mark icons across Legacy + v4.**
   The sibling SVG/icon list for the manual-mark row is duplicated (Legacy
   inline SVG vs. v4 `SvgIcon`). Standardize on one `SvgIcon`/icon-font source
   wired through the shared mark component; every surface gets the same mark
   input.

8. **Replace remaining literal `is_correct` writes/checks with constants.**
   Sweep `QuizController` for `? 1 : 0`, `= 1`/`= 0` writes and truthy
   `is_correct` checks in the changed surface; substitute
   `ATTEMPT_ANSWER_CORRECT` / `ATTEMPT_ANSWER_INCORRECT`. Add a doc note that
   `ATTEMPT_ANSWER_MANUAL_GRADED (=3)` is intentionally truthy-as-unknown and
   that graded-manual must be read via the constants, never a raw truthiness
   branch.

9. **Split `review_quiz_answers_bulk`.** Extract
   `apply_quiz_feedback_bulk()` (feedback map read/build/save) and
   `apply_manual_marks_bulk()` (per-question mark loop/delta) so the handler
   reads as three clear phases.

10. **`toggle_switch.php` indentation** — restore the mangled checkbox block
    indent (no behavior change).

### Dropped after research

- Hardcoded hex colors in `quiz-attempts.scss`. Retracted: no
  `$tutor-*`/`--tutor-*` palette exists in this SCSS tree; the file follows the
  repo's actual (hardcoded) convention. Retain observation for a future global
  token adoption, out of scope here.
- `Icon::PARTIAL` presence in Free core: intentional shared asset used by Pro;
  no change.

## Implementation Decisions

- **Seam:** every code fix lives in `QuizController`, `QuizModel`, the two
  attempt-details surfaces (Legacy + v4 shared), `options.js`, and
  `toggle_switch.php` — all files this branch already modified. No new modules,
  no schema changes, no new endpoints.
- **Status resolver ordering (`get_attempt_answer_status`)** — canonical order
  becomes: `skipped` → `pending` (null) → `correct` → `incorrect`, with the
  manual-type `graded` branch left intact and Pro's
  `tutor_quiz_attempt_answer_status` filter last. This one ordering is the
  single source of truth for badges, labels, and counts across Legacy and v4.
- **Attempt completion on manual mark** — follow the exact precedent of
  `apply_quiz_answer_review`, which completes the attempt on the mark action
  (not "last question evaluated"). Documented in the spec as accepted
  behavior drift.
- **Legacy partial stat cell** mirrors sibling `incorrect` markup and ALSO
  fires `tutor_quiz_attempt_summary_statics_after_correct` so Pro's existing
  renderer needs no signature change.
- **Feedback map keys** stay `attempt_answer_id`-preferred with
  `question_id` fallback everywhere (both Legacy and v4 already behave this
  way; the centralized helpers just formalize it).
- **No new i18n.** Use existing strings (`%d` partially correct reuses the
  correct/incorrect pattern where possible); where a new string is required,
  use the same `translate` pattern as siblings.

## Testing Decisions

- Good tests assert **external behavior**: status strings, counts keys, and
  persist-on-save of the toggle — never the internals of the resolver.
- Highest seam for all new logic is `QuizModel`; add
  `tests/phpunit/QuizModelTest.php` (fresh file, mirrors `CourseModelTest.php`
  style) covering:
  - `get_attempt_answer_status`: forced-null `is_correct` → `pending`
    (covers image_answering regression), skipped → `skipped`, manual-graded
    null → `pending`, `1`→`correct`, `0`→`incorrect`.
  - `get_attempt_answer_counts`: only `correct`/`incorrect` keys present when
    the Pro filter is absent.
  - `get_attempt_feedback_map` / `get_manual_overrides_map`: unserialize/hand
    over array shapes incl. empty/`null` `attempt_info`.
- `QuizController` AJAX fixes (`P0.1`, `P0.2`, `P1.3`, `P1.4`) are surfaced as
  WordPress HTTP + PHPUnit integration context (existing `tests/phpunit`
  bootstrap); assert on the response bodies for the delete-guard and
  bulk-review paths where the harness supports it — otherwise leave a manual QA
  checklist step.
- `options.js` (`P0.1`) has no JS test harness in this repo; verify by manual
  QA (toggle-off with and without customized quizzes) and by asserting the
  hidden input value on the usage-check branch via browser console.

## Out of Scope

- Partial/negative scoring engine itself (Pro repo).
- `tutor_quiz_attempt_answer_{status,badge,counts}` filter implementations.
- Global design-token adoption (noted as future work).
- Migration / schema / performance work.
- Any changes to the shared v4 summary partial stat (already correct).

## Further Notes

- Open decision to confirm before implementing: whether `P1.4` should mark the
  attempt ended on each manual mark action (sibling precedent) rather than
  waiting until all questions are evaluated (literal spec wording). The plan
  follows the sibling precedent for consistency; the reviewer flags this as a
  deliberate drift to record.
- Manual QA steps for the persist fix must cover both branches of the
  usage check (customized → modal; not customized → straight off).
- After implementation, re-run `git diff dev...HEAD --stat` and re-read the
  changed hunks; the intended end-state is that none of the review's spec-axis
  findings survive.