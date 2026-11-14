# Remove the YouTube fullscreen control via the feature set, not a vendored skin

**Status**: superseded by ADR-0009 (kept as the first fallback if the container-fullscreen spike fails)

## Context

ADR-0003 assumed the packaged skin could not drop a control, so it vendored the skin
source to remove `<media-fullscreen-button>` for YouTube. Two version-matched facts
weaken that assumption:

- Fullscreen is an opt-out-able feature: `selectFullscreen` "returns `undefined` if the
  fullscreen feature is not configured."
- `<media-fullscreen-button>` self-hides: its `hidden` state is true while availability is
  `unavailable`/`unsupported`, and "the button remains hidden until fullscreen is
  available."

DOM is already built per provider (ADR-0004), so the feature set can be chosen at player
creation time.

## Decision

Remove the YouTube fullscreen control by **building YouTube players with a feature bundle
that omits the fullscreen feature**, rather than by vendoring the skin. The packaged
`<video-skin>` and its `<media-fullscreen-button>` self-hide when the feature is absent.
This drops the shadcn skin-source step entirely: no `components.json`, no `@/*` alias, no
committed skin source.

Concretely: define a second player element class from
`createPlayer({ features: videoFeaturesWithoutFullscreen })` (e.g. `tutor-video-player-nofs`)
and use it for YouTube sources; keep the full `videoFeatures` class for every other
Provider.

## Validation gate

Because the presets guide warns that "skins depend tightly on their feature bundle" and
only documents _extending_ a bundle, this is validated by a spike on the packaged Default
skin before it is relied on: assert the fullscreen button is hidden and leaves no layout
gap, and that fullscreen still works for the non-YouTube class. If the spike fails, fall
back to ADR-0003 (vendor the skin source and remove the control directly).

## Considered Options

- **Vendor the skin source (ADR-0003).** Kept as the fallback: guaranteed, but adds a
  committed skin, `components.json`, a `@/*` alias, and manual skin-update churn.
- **Reach into the shadow root and remove the button.** Rejected as the primary route:
  depends on the skin's encapsulated internals (discouraged by the docs) and is no simpler
  than the feature set.
- **CSS `::part`.** Rejected: the skin exposes no part for the fullscreen control.

## Consequences

- Two player element classes (with and without fullscreen), selected per Provider.
- Other Providers keep fullscreen; YouTube lessons stay fullscreen-free (unchanged UX
  outcome from ADR-0003).
- Packaged-skin design updates apply automatically via version bumps.
- Risk: removing a feature from under a packaged skin is undocumented and may change
  between releases; the fallback (ADR-0003) remains available if that happens.
