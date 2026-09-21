import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { type AlpineComponentMeta } from '@Core/ts/types';
import { bindPlyrMobileFullscreenFix } from '@Core/ts/utils/player-fullscreen';
import { isMobileDevice } from '@Core/ts/utils/util';

import { isVimeoPlyr } from '@FrontendTypes/index';

export interface PlayerProps {
  config?: Plyr.Options;
}

export interface AlpinePlayerData {
  $el?: HTMLElement;
  plyr: Plyr | null;
  unbindFullscreenFix?: () => void;
  init(): void;
  destroy(): void;
}

export const player = (props: PlayerProps = {}): AlpinePlayerData => ({
  plyr: null,
  $el: undefined as HTMLElement | undefined,
  unbindFullscreenFix: undefined,

  init() {
    if (typeof window.Plyr === 'undefined') {
      // eslint-disable-next-line no-console
      console.warn('Plyr is not defined. Ensure Plyr library is loaded.');
      return;
    }

    if (!this.$el) {
      return;
    }

    this.plyr = new window.Plyr(this.$el, props.config);
    this.unbindFullscreenFix = bindPlyrMobileFullscreenFix(this.plyr);

    if (this.plyr) {
      this.plyr.on('ready', () => {
        // Remove loading spinner
        this.$el?.closest('.tutor-video-player')?.querySelector('.loading-spinner')?.remove();

        /**
         * Fix: Mobile Vimeo autoplay sound issue
         * Always start muted on mobile to comply with autoplay policy.
         */
        if (this.plyr && isVimeoPlyr(this.plyr) && isMobileDevice()) {
          try {
            this.plyr.muted = true;
            if (typeof this.plyr.mute === 'function') {
              this.plyr.mute();
            }
          } catch (err) {
            // eslint-disable-next-line no-console
            console.warn('Vimeo mute init failed:', err);
          }
        }
      });

      this.plyr.on('play', () => {
        /**
         * Unmute automatically after first user interaction
         * Mobile browsers allow audio only after gesture.
         */
        if (this.plyr && isVimeoPlyr(this.plyr) && isMobileDevice()) {
          try {
            this.plyr.muted = false;
            if (typeof this.plyr.unmute === 'function') {
              this.plyr.unmute();
            }
          } catch (err) {
            // eslint-disable-next-line no-console
            console.warn('Vimeo unmute on play failed:', err);
          }
        }
      });
    }

    // Dispatch custom event when player is ready
    this.$el.dispatchEvent(
      new CustomEvent(TUTOR_CUSTOM_EVENTS.TUTOR_PLAYER_READY, {
        detail: {
          plyr: this.plyr,
          component: this,
        },
        bubbles: true,
      }),
    );
  },

  destroy() {
    this.unbindFullscreenFix?.();
    this.unbindFullscreenFix = undefined;
  },
});

export const playerMeta: AlpineComponentMeta<PlayerProps> = {
  name: 'player',
  component: player,
  global: true,
};
