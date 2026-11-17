Type: task
Status: open
Blocked by: 05 + one release cycle

## Question

Retire the compatibility layer, together with `window.TutorLessonPlayer`:

- Delete detection/replacement/canonicalization from the engine (`providers.ts` shrinks
  to the tag-scan or nothing), the legacy branch of `create()`, `legacy.ts`, and the
  `tutorLessonPlayerReady` dispatch; evaluate whether `PLAYER_ENGINE_READY` is still
  needed for controller handshakes or can go.
- Pro side (Pro ticket): remove the stale `tutorPlayerReady` / `event.detail.plyr`
  listener and any `TutorLessonPlayer` reads.
- Trigger: the release that starts the deprecation clock for ADR-0007's one-cycle shim.
