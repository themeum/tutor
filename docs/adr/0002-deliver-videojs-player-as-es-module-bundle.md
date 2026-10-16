# Deliver the Video.js player as an ES module bundle

**Status**: superseded by ADR-0011 (kept as the record of the module-delivery trade-off)

## Context

The rspack build emits every entry as an IIFE with no `library` export
(`rspack.config.mjs`). Video.js 10 packaged modules must be loaded from an ES module:
the docs state that loading the built output as a non-module (IIFE) script fails with
`StoreError: NO_TARGET`. WordPress also has no `type="module"` script enqueue by default
on the WordPress versions Tutor supports.

## Decision

The Video.js player is built as a separate ES module entry (`experiments.outputModule` /
`output.chunkFormat: 'module'`) that imports `@videojs/html` and the media packages, and
is enqueued with `type="module"`.

## Considered Options

- **Reuse the existing IIFE `tutor-learning-area` / `tutor-front` entries.** Rejected:
  IIFE output throws `NO_TARGET`.
- **CDN `<script type="module">`.** Rejected: we self-host and already ship a bundler.

## Consequences

- The player entry is added as its own rspack config alongside the existing per-entry
  configs, rather than folded into the IIFE entries.
- `Assets.php` needs a module-aware enqueue (WordPress `wp_enqueue_script_module` where
  available, otherwise a `script_loader_tag` filter that adds `type="module"`).
- Module scripts are deferred by default; anything that assumes the player global exists
  at parse time must wait for the `tutorLessonPlayerReady` event instead.
