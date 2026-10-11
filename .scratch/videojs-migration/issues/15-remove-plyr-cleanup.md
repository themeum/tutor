Type: task
Status: open
Blocked by: 13, 14

## Question

Remove Plyr: the `tutor-plyr` enqueues at both sites in `classes/Assets.php`, the vendored
`assets/lib/plyr/*` files, Plyr-only rules in
`assets/src/scss/frontend/components/_player.scss`, and the now-unused Plyr type declaration.
Confirm no `cdn.plyr.io` request remains at runtime. Re-run the full ladder (`build-dev`,
`pnpm test`, `pnpm test:unit`, lint/typecheck).
