# Captions and Scrub Previews are capability-only in this migration

**Status**: accepted

## Context

The lesson video meta (`_video`, filtered by `tutor_allowed_video_meta_keys`) has no
caption-track or Storyboard Track keys, and there is no admin UI to upload them. Adding
that storage and UI is a product feature, not part of swapping the player.

## Decision

This migration only wires the capability: emit `<track kind="captions|subtitles">` and
`<track kind="metadata" label="thumbnails">` when such data is present, and rely on
Embedded Captions for YouTube and Vimeo. No caption/storyboard admin UI, no server-side
generation, and no new meta keys are added here.

## Considered Options

- **Add caption/storyboard upload + `_video` meta now.** Rejected: scope creep beyond the
  player swap.

## Consequences

- With no meta present, lesson players currently show only Embedded Captions and no Scrub
  Previews. This is expected, not a bug.
- If caption/storyboard meta is added later, `filter_video_meta` must be extended to
  whitelist the new keys and the helper taught to read them.
