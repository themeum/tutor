# Author the player tree in the templates (declarative `tutor-video-player`)

**Status**: accepted (supersedes ADR-0004)

## Context

ADR-0004 kept the PHP templates on legacy Plyr-era markup and had the engine
reverse-engineer intent from it: class-based detection (`.tutorPlayer` /
`.plyr__video-embed`), URL re-parsing, a JS-built element tree, and replacement of the
source element. That trade-off was taken mid-migration to avoid touching the
`tutor_single_lesson_video` filter contract and the template blast radius. The migration
has shipped, and the ground has shifted:

- Video.js 10's canonical usage is server-authored markup — `@videojs/html@10.0.1`
  `docs/guides/installation.md:121-143` authors the full tree in HTML and JS only
  registers the elements. The JS-built tree and the append-after-adapter-import dance
  are Tutor-specific inventions; the dance is unnecessary (a declared media child
  upgrades with the adapter, per the package source).
- ADR-0004's stated blocker — "Pro and addons hook `tutor_single_lesson_video`" — has
  no consumer in this repo or `tutor-pro` (grepped, all file types). The filter still
  fires in the same places; only the markup it receives changes.
- The detection path is lossy: `buildMediaSource` rebuilds canonical YouTube/Vimeo URLs
  and drops extra params (`t`/`list`), while the media adapters accept raw
  watch/share/embed forms as `src` (`docs/reference/api/resolve-adapter-type.md`).
- Service-centric ownership produced real defects: a disposed player stays `#active`
  and early `on()` subscriptions are dropped
  (`docs/research/videojs-v10-maintainability.md`, F1).
- Every mount surface needs JS to remember to run (learning-area init, the
  `tutor-front.js` loop, the Alpine `player` component — the latter dormant and
  unreferenced in both repos).
- Delivery is ESM-only (ADR-0002), and the deferred engine module cannot reach
  `window.TutorComponentRegistry` as currently exposed (inside the async
  `initializePlugin`).

## Decision

The templates author the full player tree and the element boots itself.

- **Scope**: the four `templates/single/video/*.php` — all lesson surfaces and the
  course intro funnel through `tutor_lesson_video()` / `tutor_course_video()`.
  `embedded`/`shortcode` and the native-iframe branches (migration Q3/Q4) stay
  untouched.
- **Templates**: emit `<tutor-video-player><video-skin><media></video-skin></tutor-video-player>`,
  passing the raw user URL (`source_youtube` etc. — no canonicalization); drop
  `class="tutorPlayer"`; keep the `.tutor-video-player` wrapper, spinner and tracking
  input (Pro depends on those names); keep `controls` on `<video>` as the
  no-JS/module-failure fallback — the element removes it on connect.
- **Self-boot**: the declared element creates its handle on upgrade, exposes it as
  `element.player`, registers with the service, and lazy-loads the media adapter by
  scanning its subtree for `youtube-video`/`vimeo-video` tags.
- **Ownership**: element-centric. `TutorPlayerService` keeps its public shape for one
  deprecation cycle; `create()` becomes an idempotent "ensure a handle for legacy
  markup" (detect → replace → self-boot → return) used by the compat shim and the
  Alpine component. Dispose clears the active slot; late `on()` binds when a player
  appears (F1).
- **Registry**: the player registers as a `ComponentRegistry` service
  (`register({ type: 'service', meta })`). `window.TutorComponentRegistry` moves to
  module scope in `assets/core/ts/index.ts` so the deferred engine can use the window
  bridge at eval time, with a fallback to its own registry instance.
- **Compat**: the class-based detection/replacement path stays for one cycle as a
  deprecated shim for third-party markup, removed together with
  `window.TutorLessonPlayer`.
- **Pre-upgrade**: space is reserved in the already-loaded IIFE CSS
  (`tutor-video-player { display: block; … }` + wrapper aspect-ratio); the spinner
  stays visible; no `:not(:defined)` hiding.

### Hard contract: define before upgrade

The media element's context registration is one-shot: `<tutor-video-player>` must be
defined before a declared media adapter upgrades. The engine defines the player element
and lazily imports adapters afterwards, so the order holds by construction. It is
load-bearing — an eager adapter import before the player definition silently yields a
store with `target.media === null`.

## Considered Options

- **Keep the JS DOM builder (ADR-0004 status quo).** Rejected: keeps the lossy
  detect/rebuild path, the mount-call-site coupling, and the service-centric lifecycle
  behind F1 — while upstream's supported composition is declarative.
- **Declarative markup with service-as-factory (`create(element)` adopts declared
  elements).** Rejected: a page that forgets the call renders a dead player — the
  coupling this change deletes.
- **Hard cutover, no shim.** Rejected: third-party themes/addons still emit the legacy
  shapes; the one-cycle shim is cheap and retires with `TutorLessonPlayer`.
- **Engine registers through its own `ComponentRegistry` instance (no core change).**
  Rejected: two registry instances; `window.TutorComponentRegistry.has('player')`
  would be false forever.

## Consequences

- `tutor_single_lesson_video` now receives declarative markup — a deliberate,
  documented markup change; the shim still tolerates legacy shapes. Release notes must
  say so.
- Detection/canonicalization survives only for the shim and is deleted with it; until
  then the code-deletion win is partial.
- `window.TutorComponentRegistry` exposure moves to module scope — inert for existing
  registrants (all register on `alpine:init` or later), but a core-wide change; verify
  on dashboard + learning-area pages.
- F1 is fixed structurally, not patched.
- No-JS/module-failure: html5/external keep native playback via the retained
  `controls`; the yt/vimeo opt-in branch loses its no-JS iframe fallback (accepted —
  opt-in only).
- No bundle/perf change (research F9); this is a maintainability change.
- Delivery is staged: stage 1 (invisible internals against today's markup) then stage 2
  (template flip). Shim + `TutorLessonPlayer` removal is one ticket tied to the same
  release.
