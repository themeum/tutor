// The Video.js lesson player engine, loaded as a separate ES module (ADR-0002) because a
// single rspack config cannot mix `output.module` with the IIFE entries.
//
// Stub: the engine lands in a later ticket. This keeps the module non-empty so the build,
// the `type="module"` enqueue and dependency resolution can be verified, and follows
// ADR-0007 by exposing the entry on `window.TutorCore.player`.
import { createPlayer } from '@videojs/html';

window.TutorCore = Object.assign(window.TutorCore || {}, {
  player: { createPlayer },
});
