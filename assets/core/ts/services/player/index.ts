import { defineVideoPlayer } from './elements';
import { TutorPlayerService } from './PlayerService';

import '@videojs/html/media/vimeo-video';
import '@videojs/html/media/youtube-video';
import '@videojs/html/video/skin';

defineVideoPlayer();

const player = new TutorPlayerService();

window.TutorCore = Object.assign(window.TutorCore || {}, { player });

export { createTutorPlayer } from './Player';
export type { PlayerHandleFactory, PlayerServiceDependencies } from './PlayerService';
export { TutorPlayerService } from './PlayerService';
export * from './providers';
