Type: task
Status: open
Blocked by: 06, 07, 08

## Question

HITL. A human runs the provider matrix before cutover (Chrome, Firefox, Safari, iOS Safari;
for html5 / external / YouTube / Vimeo) against the plan's Layer 3 table — play & seek,
container fullscreen (no YouTube chrome), Strict Mode, resume, auto-next, mobile Vimeo mute,
captions. Record the results on this ticket. This gates ticket 15.

Also verify the YouTube title/branding is hidden on hover/pause and in container fullscreen
(the `::part(iframe)` crop from ticket 04), including the mobile-fullscreen aspect fix.
