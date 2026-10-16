# Map: declarative `<tutor-video-player>` authored in templates

## Destination

The four `templates/single/video/*.php` author the full
`<tutor-video-player><video-skin><media>` tree; the element self-boots; ownership is
element-centric; the service registers through `ComponentRegistry`; the legacy
class-query path survives one cycle as a deprecated shim. Delivered in two stages:
stage 1 (invisible internals against today's markup), stage 2 (template flip). Ends at
mergeable in the free plugin — Pro needs no changes.

## Notes

- Design record: `docs/adr/0010-declarative-player-tree-in-templates.md` (supersedes
  ADR-0004). Evidence: `docs/research/videojs-v10-maintainability.md` (F1, F5, F9) and
  the grilling rounds in the working session.
- Delivery: the engine is a standard IIFE `jsEntries` entry enqueued as a classic script
  — ADR-0002 superseded by ADR-0011 (no separate module config, no `type="module"` tag).
- Hard contract (ADR-0010): the player element must be defined before a declared media
  adapter upgrades (one-shot context registration). Never import adapters eagerly
  before the player definition, or the store silently ends with `target.media === null`.
- Stage 1 must leave every engine page behavior-identical; stage 2 must be reversible
  by reverting four templates.
- Claim a ticket (`Status: claimed`) before working it.

## Decisions so far

<!-- one line per resolved ticket, with a link and a gist -->

- [01](issues/01-registry-service-registration.md): registry bridge at core module scope;
  player service registered via `ComponentRegistry` with the imported instance as fallback.
- [02](issues/02-self-boot-element-and-service-tracking.md): element self-boots and owns
  the handle (`element.player`); service tracks via wired hooks, `on()` late-binds,
  `create()` ensures a handle for legacy markup; adapter import moved into the element.
- [03](issues/03-space-reservation-css.md): standalone `tutor-video-player` reservation
  rule; wrapper + spinner already covered; inert until 05.

## Not yet specified

- Removal date of the shim + `TutorLessonPlayer` (ticket 06) — tied to the release that
  starts the deprecation clock; Pro's stale `tutorPlayerReady` / `event.detail.plyr`
  listener is Pro's own cleanup.

## Out of scope

- `embedded`/`shortcode` providers and the native-iframe YouTube/Vimeo branches
  (migration Q3/Q4 stay decided).
- Pro template changes (none needed).
- Bundle/perf work (research F9: a budget, not a backlog).
- Research findings F2 (JS CI), F3, F4, F6, F8 — separate workstreams, not this map.
