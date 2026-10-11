import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { type AlpineComponentMeta } from '@Core/ts/types';
import { type PlayerOptions, type PlayerUnsubscribe, type TutorPlayer } from '@Core/ts/types/player';
import { isMobileDevice } from '@Core/ts/utils/util';

export interface PlayerProps {
  options?: PlayerOptions;
}

export interface AlpinePlayerData {
  $el?: HTMLElement;
  player: TutorPlayer | null;
  init(): void;
  destroy(): void;
}

export const player = (props: PlayerProps = {}): AlpinePlayerData => {
  const unsubscribers: PlayerUnsubscribe[] = [];

  return {
    player: null,
    $el: undefined as HTMLElement | undefined,

    init() {
      const service = window.TutorCore?.player;

      if (!service) {
        // eslint-disable-next-line no-console
        console.warn('Tutor player engine is not loaded.');
        return;
      }

      const element = this.$el;

      if (!element) {
        return;
      }

      const handle = service.create(element, props.options);
      this.player = handle;

      unsubscribers.push(
        handle.on('ready', () => {
          // Remove loading spinner
          element.closest('.tutor-video-player')?.querySelector('.loading-spinner')?.remove();

          /**
           * Fix: Mobile Vimeo autoplay sound issue
           * Always start muted on mobile to comply with autoplay policy.
           */
          if (handle.provider === 'vimeo' && isMobileDevice()) {
            handle.setMuted(true);
          }
        }),
        handle.on('play', () => {
          if (handle.provider === 'vimeo' && isMobileDevice()) {
            handle.setMuted(false);
          }
        }),
        handle.on('fullscreenchange', ({ fullscreen, element: host }) => {
          host.classList.toggle('is-fullscreen', fullscreen);
        }),
      );

      // Dispatch custom event when player is ready
      element.dispatchEvent(
        new CustomEvent(TUTOR_CUSTOM_EVENTS.TUTOR_PLAYER_READY, {
          detail: {
            player: handle,
            component: this,
          },
          bubbles: true,
        }),
      );
    },

    destroy() {
      unsubscribers.forEach((off) => off());
      unsubscribers.length = 0;

      this.player?.dispose();
      this.player = null;
    },
  };
};

export const playerMeta: AlpineComponentMeta<PlayerProps> = {
  name: 'player',
  component: player,
};
