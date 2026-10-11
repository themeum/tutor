# Keep the fullscreen control and fullscreen the container, not the YouTube iframe

**Status**: accepted (supersedes ADR-0008; supersedes ADR-0003)

## Context

ADR-0003 and ADR-0008 both started from one assumption: that fullscreen reveals YouTube's
own chrome, so the YouTube fullscreen _control_ had to go (by vendoring the skin, then by
omitting the fullscreen feature). Two version-matched facts undo that assumption:

- The **fullscreen feature** — the one the packaged Default skin's
  `<media-fullscreen-button>` uses — "tries the container element first, falls back to the
  media component." So the skin fullscreens the **player container**, not the iframe.
- Only the _media component's own_ `requestFullscreen` targets the iframe: the
  `youtube-video` reference notes "Fullscreen targets the iframe, so YouTube's own chrome
  shows in fullscreen." That is the fallback path, not the skin's path.
- YouTube's chrome-related player parameters (`modestbranding`, `showinfo`, `autohide`,
  `theme`) are **deprecated** and are deliberately absent from the `youtube-video` engine
  options, so there is nothing to configure at the embed level.

If the skin already fullscreens the container, no control has to be removed: YouTube's
chrome only appears when the iframe itself is fullscreened.

## Decision

Keep the fullscreen control and the **full `videoFeatures` bundle for every Provider**,
including YouTube. Register a single player element class (`tutor-video-player`) built from
`createPlayer({ features: videoFeatures })`. Fullscreen relies on the feature's
container-first behaviour, which keeps our controls, captions, and overlays (and Pro's
in-fullscreen note affordance) intact.

No skin is vendored: no `components.json`, no `@/*` alias, no committed skin source.

## Validation gate

The Phase 0 spike fullscreens a YouTube lesson on the packaged Default skin and asserts:

1. `document.fullscreenElement` is the player **container**, not the `<iframe>`.
2. YouTube's chrome does **not** appear.

Only if YouTube chrome still appears _and_ cannot be avoided by container targeting does
this decision fall back — first to ADR-0008 (omit the fullscreen feature for the YouTube
player element class), then to ADR-0003 (vendor the skin source and remove the control).

## Considered Options

- **Omit the fullscreen feature for YouTube (ADR-0008).** Rejected: it removes a control
  that only needed to be removed when the iframe was fullscreened; the packaged skin does
  not do that. Retained as the first fallback if the spike shows chrome.
- **Vendor the skin source and delete the control (ADR-0003).** Rejected: heavier (a
  committed skin, `components.json`, a `@/*` alias, skin-update churn) for a control we now
  keep. Retained as the second fallback.
- **Accept YouTube chrome and keep the control anywhere.** Rejected as the goal: the spike
  establishes whether container fullscreen avoids the chrome; if it does not, we escalate
  to the fallbacks above rather than silently ship branding.

## Consequences

- One player element class; fullscreen is available for **all** Providers, YouTube included.
- Pro's in-fullscreen note button works for YouTube again — the ADR-0005 caveat that it
  "never activates for YouTube lessons" is void.
- Branding outcomes depend on YouTube's presentation of a container-fullscreened iframe;
  this is not documented and is verified by the Phase 0 spike on each YouTube path.
- If YouTube changes so its chrome appears in a container-fullscreened iframe, the
  fallbacks (ADR-0008, then ADR-0003) remain the escape hatches.
