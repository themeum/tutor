# Tutor LMS — Lesson Playback

Terms specific to how a Tutor LMS lesson plays its attached video. General web/player
concepts (buffering, poster, autoplay) are out of scope here.

## Language

**Lesson Video**:
The media attached to a lesson and shown inside that lesson's player.
_Avoid_: Course video, lesson media

**Course Intro Video**:
The media attached to a course and shown on the course page, above the curriculum.
_Avoid_: Course video (when the intro is meant), promo video

**Provider**:
The origin platform a Lesson Video is played from: a self-hosted file, an external URL,
YouTube, Vimeo, an embedded iframe, or a shortcode embed.
_Avoid_: Source, type

**Native Provider**:
A Provider whose media plays in the HTML `<video>` element: a self-hosted file (`html5`)
or an external file URL (`external_url`). The only Providers that can use Subtitle Tracks.
_Avoid_: HTML5 source, direct source

**Lesson Player**:
The on-page player that plays a Lesson Video and owns its controls and playback state.
_Avoid_: Video player, media element

**Player Engine Names**:
The identifiers of the Lesson Player's implementation — `<tutor-video-player>`, the
`TutorPlayer` handle, the `TutorCore.player` service. Names for code, not for prose.
_Avoid_: using them when "Lesson Player" is meant

**Strict Mode**:
A lesson completion mode in which forward seeking is blocked and a minimum watch
percentage must be reached before the lesson can be completed.
_Avoid_: Locked mode, progress mode

**Best Watch Time**:
The furthest playback position a learner has reached in a Lesson Video; used to resume
playback and to decide lesson completion.
_Avoid_: Last position, resume time

**Watch Progress Sync**:
The periodic reporting of the learner's playback position back to the site while a
Lesson Video plays.
_Avoid_: Heartbeat, progress ping

**Scrub Preview**:
Thumbnail frames shown while the learner hovers or scrubs the Lesson Player timeline.
_Avoid_: Hover thumbnails, preview images

**Storyboard Track**:
The timed sprite description that supplies Scrub Preview frames to the Lesson Player.
_Avoid_: Thumbnail track, sprite sheet

**Embedded Captions**:
Captions supplied by the Provider itself, as opposed to captions uploaded and managed
inside Tutor. A Native Provider has no Embedded Captions.
_Avoid_: Subtitles, CC

**Subtitle Track**:
A WebVTT caption file uploaded in Tutor and attached to a Native Provider video. Rendered
as a `<track kind="subtitles">` inside the player.
_Avoid_: Captions file, subtitle file, CC file

**Default Subtitle Track**:
The single Subtitle Track marked to turn on automatically when the video starts.
_Avoid_: Auto captions, forced subtitles

**Provider-Aware Control**:
A Lesson Player control that is shown or hidden depending on the Provider — for example,
hiding fullscreen for YouTube.
_Avoid_: Conditional control

**Learning Area**:
The newer single-page course and lesson experience.
_Avoid_: Frontend, new frontend

**Legacy Lesson Page**:
The older, non-Learning-Area lesson page with its own player lifecycle.
_Avoid_: Old frontend, classic page

**Skin**:
The swappable visual control layer of a Video.js Lesson Player.
_Avoid_: Theme, UI
