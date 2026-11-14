Type: task
Status: done
Blocked by: —

## Question

Add the pinned runtime deps (`@videojs/html`, `@videojs/youtube-video`, `@videojs/vimeo-video`
at `10.0.1`) and Vitest via pnpm; add the ES-module player entry
`assets/core/ts/services/player/index.ts` to `rspack.config.mjs` (production returns
`[iifeConfig, playerModuleConfig]` because one config cannot mix `output.module` with the
IIFE entries); enqueue the built module with `type="module"` via a `script_loader_tag`
filter in `classes/Assets.php`.

No behavior change yet — the entry may be a stub. Outcome to record: `pnpm build-dev` emits
the player as an ES module and the enqueued `<script>` carries `type="module"`, while the
existing IIFE entries still build.
