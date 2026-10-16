import { TutorComponentRegistry } from '@Core/ts/ComponentRegistry';
import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';

import { defineVideoPlayer, setPlayerElementHooks } from './elements';
import { playerServiceMeta } from './PlayerService';

import '@videojs/html/video/skin';

setPlayerElementHooks({
  onMount: (player) => playerServiceMeta.instance.track(player),
  onDispose: (player) => playerServiceMeta.instance.untrack(player),
});

defineVideoPlayer();

// Register with the core bundle's registry when it is already loaded; otherwise fall back to
// the imported instance (load order varies by page). Either way window.TutorCore.player is set.
(window.TutorComponentRegistry ?? TutorComponentRegistry).register({ type: 'service', meta: playerServiceMeta });

window.dispatchEvent(new CustomEvent(TUTOR_CUSTOM_EVENTS.PLAYER_ENGINE_READY));

export { createTutorPlayer } from './Player';
export { TutorPlayerService } from './PlayerService';
export * from './providers';
