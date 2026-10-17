import { TutorComponentRegistry } from '@Core/ts/ComponentRegistry';
import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';

import { patchVideoSkinStyles } from './elements';
import { playerServiceMeta } from './PlayerService';

import './boot'; // defines the host before the skin import below; see boot.ts
import '@videojs/html/video/skin';

// Requires the skin class, so it runs after the import above.
patchVideoSkinStyles();

// The registry is shared through window (see ComponentRegistry), so this registers into the
// core bundle's instance even when this engine script evaluates first.
TutorComponentRegistry.register({ type: 'service', meta: playerServiceMeta });

window.dispatchEvent(new CustomEvent(TUTOR_CUSTOM_EVENTS.PLAYER_ENGINE_READY));

export { createTutorPlayer } from './Player';
export { TutorPlayerService } from './PlayerService';
export * from './providers';
