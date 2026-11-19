# Upload subtitle tracks for Native Providers

**Status**: accepted (supersedes ADR-0006 for captions; Storyboard Track stays
capability-only)

## Context

ADR-0006 kept captions capability-only: the player could render `<track>` elements, but
there was no storage, no upload UI, and no meta keys. Teachers who self-host a video
(`html5`) or point at an external file (`external_url`) had no way to supply captions,
because those Providers expose no Embedded Captions. The player UI for track selection
already ships with the Video.js skin, so only the data path was missing.

## Decision

Add subtitle-track upload to the existing video field, shared by Course Intro Video,
Lesson Video, and Content Bank lesson video.

- **Sources**: Subtitle Tracks are offered only for Native Providers (`html5`,
  `external_url`). YouTube and Vimeo keep Embedded Captions; their adapters ignore
  `<track>` children. The subtitle section is hidden for every other Provider.
- **Format**: WebVTT (`.vtt`) only. No SRT conversion, no server-side transcription.
- **Storage**: `_video` meta gains a `subtitles` key:
  `[{ attachment_id, srclang, label, default }]`. `filter_video_meta` whitelists and
  normalizes it.
- **UI fields**: language select (ISO 639-1), label prefilled from the language and
  editable, "On by default" toggle, remove. One implementation inside the shared
  `FormVideoInput` covers all three surfaces.
- **Default behavior**: teacher-controlled; at most one Default Subtitle Track; no
  automatic default and no automatic enabling otherwise.
- **Scope**: player captions only. No transcript panel.
- **Delivery**: core `tutor` repo. `tutor-pro` changes only the Content Bank payload
  serializer, because Content Bank reuses the core field and save path.

## Considered Options

- **Store tracks as a JSON string in one form key.** Rejected: the form encoder and
  `sanitize_array`/`wp_unslash` can corrupt quotes inside labels. Nested
  `video[subtitles][i][field]` keys arrive in PHP as a real array.
- **Support SRT and convert server-side.** Rejected: more surface, no accepted need.
- **Render a transcript panel.** Rejected: out of scope for this feature.

## Consequences

- `filter_video_meta` now normalizes tracks: positive attachment ID, lowercase BCP 47
  `srclang`, sanitized label, boolean `default`, and only the first track can stay
  default.
- The templates emit `<track kind="subtitles">` inside `<video>` for `html5` and
  `external_url`; Video.js and native media pick them up without player changes.
- Import/export copies `_video` as-is, so caption attachment IDs share the existing
  cross-site remap limitation of the poster attachment. Not addressed here.
