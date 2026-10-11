Type: task
Status: open
Blocked by: 04

## Question

Rewrite the Alpine `player` component (`assets/core/ts/components/player.ts`) to call
`TutorCore.player.create()` instead of `new window.Plyr`. Keep the `tutor-player-ready`
dispatch (`TUTOR_CUSTOM_EVENTS.TUTOR_PLAYER_READY`) and update its `detail` shape to carry
the `TutorPlayer` handle. The component is consumed as `x-data="tutorPlayer"`.
