# Adopt Video.js 10 for lesson playback

**Status**: accepted

## Context

Lesson Videos are played by a vendored copy of Plyr 3.8.4 (`assets/lib/plyr/`). Plyr
leaks YouTube branding when a YouTube lesson enters fullscreen, and it needs more work
around captions and Scrub Previews than we want to keep maintaining. We are replacing
Plyr with Video.js 10 (`@videojs/html`), the framework-native rewrite that ships as
custom elements plus a reactive store.

## Decision

Lessons are played with Video.js 10 composed from `@videojs/html` (player + skin) plus
the per-Provider media packages `@videojs/youtube-video` and `@videojs/vimeo-video`. Plyr
is removed once every migrated path is verified.

## Considered Options

- **Stay on Plyr.** Rejected: the YouTube branding leak and the caption/preview work are
  the reasons for the change.
- **Video.js 8 (`video.js` npm).** Rejected: it is the legacy line; 10 is the current
  release and the target of the official Plyr migration guide.
- **Native `<video>` only.** Rejected: loses a consistent skin across Providers.

## Consequences

- Video.js 10 is ESM-only and its browser floor is roughly Chrome/Edge 111+, Safari/iOS
  16.4+, Firefox 121+ (~91.5% of users); older browsers, most in-app webviews, and smart
  TVs are no longer fully supported. This is a deliberate regression from Plyr.
- The player must be delivered as an ES module bundle (see ADR-0002).
- Existing playback behaviours (Strict Mode, resume, Watch Progress Sync, auto-next,
  mobile Vimeo mute, YouTube poster handling, fullscreen wrapper class) are preserved
  through the reactive store and a compatibility shim (see ADR-0005).
