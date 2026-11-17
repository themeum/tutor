import { defineVideoPlayer, setPlayerElementHooks } from './elements';
import { playerServiceMeta } from './PlayerService';

// Imported by ./index before it registers the skin. The host class must be defined first: the
// skin's controls resolve the player store context once, when they upgrade, so a skin defined
// before this host leaves every control unbound. The vendor's packaged-skin snippet imports the
// player module before the skin module for the same reason.
setPlayerElementHooks({
  onMount: (player) => playerServiceMeta.instance.track(player),
  onDispose: (player) => playerServiceMeta.instance.untrack(player),
});

defineVideoPlayer();
