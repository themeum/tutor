import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';

import { defineVideoPlayer } from './elements';
import { TutorPlayerService } from './PlayerService';

import '@videojs/html/video/skin';

defineVideoPlayer();

const player = new TutorPlayerService();

window.TutorCore = Object.assign(window.TutorCore || {}, { player });

window.dispatchEvent(new CustomEvent(TUTOR_CUSTOM_EVENTS.PLAYER_ENGINE_READY));

export { createTutorPlayer } from './Player';
export { TutorPlayerService } from './PlayerService';
export * from './providers';
