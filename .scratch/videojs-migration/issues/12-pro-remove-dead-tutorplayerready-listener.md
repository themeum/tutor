Type: task
Status: open
Blocked by: 09

## Question

Runs in `../tutor-pro`. Remove the dead `tutorPlayerReady` YouTube-poster listener at
`assets/src/js/front.js:141` — nothing dispatches that camelCase event, and the poster fix
already lives in the free plugin (`tutor-front.js:231–232`, `lesson/player.ts:331–367`).
Confirm no other consumer before deleting.
