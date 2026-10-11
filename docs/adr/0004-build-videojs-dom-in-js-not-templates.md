# Build the Video.js DOM in JS, not in the PHP templates

**Status**: accepted

## Context

Video.js 10 requires a composed custom-element tree (`<video-player>` → skin → media
element). The lesson templates currently emit either a `<video class="tutorPlayer">`
(HTML5/external) or a `.plyr__video-embed` wrapper around an iframe (YouTube/Vimeo), and
the markup is filtered by `tutor_single_lesson_video`, which Pro and addons hook.

## Decision

A shared TypeScript helper reads the existing `.tutorPlayer` markup, determines the
Provider and its source URL/poster, and replaces that element with the Video.js tree. The
PHP templates keep emitting their current markup.

## Considered Options

- **Rewrite the PHP templates to emit Video.js markup.** Rejected: larger blast radius,
  and it would break the `tutor_single_lesson_video` filter contract that Pro and addons
  rely on.

## Consequences

- All three init paths (Learning Area, Legacy Lesson Page, Alpine `player` component)
  share one helper, so Provider detection and DOM construction live in one place.
- The helper must tolerate the existing markup shapes (html5 `<video>`, external URL,
  YouTube/Vimeo `.plyr__video-embed`, embedded iframe, shortcode) and must not assume
  attributes the templates do not set.
