# Vendor the skin source and suppress fullscreen for YouTube

**Status**: superseded by ADR-0009 (kept as the fallback if the container-fullscreen spike fails)

## Context

YouTube lesson branding must not be visible. The Provider's own chrome is only revealed
in fullscreen, so the fix is to remove the fullscreen control for YouTube-sourced
lessons. Video.js skins render their controls inside a shadow root, and the packaged
skin exposes no supported setting to add or remove controls, so external CSS cannot hide
the control for YouTube only. Vendor the skin source (the Shadcn `@videojs/video` source)
so the control lives in our own code and can be conditioned on the Provider.

## Decision

Install and commit the Video.js skin source, then remove the fullscreen control when the
active media is a YouTube media component. The packaged Default skin is the base.

## Considered Options

- **Packaged skin + CSS.** Rejected: cannot reach the control inside the shadow root.
- **Packaged skin + `::part`.** Rejected: the skin does not expose the fullscreen control
  as a part.
- **Remove fullscreen for every Provider via the store.** Rejected as the primary
  approach: it would also remove fullscreen where it is harmless; kept only as a fallback
  if the vendored-source route is blocked.

## Consequences

- The skin source is committed and becomes a build input; `shadcn`/Video.js skin updates
  replace local files and must be reviewed as diffs.
- YouTube lessons have no fullscreen. This is intentional and must not be "fixed" later.
- Other Providers keep fullscreen.
