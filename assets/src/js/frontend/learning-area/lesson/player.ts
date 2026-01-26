import { isMobileDevice } from '@Core/ts/utils/util';
import { isVimeoPlyr, isYouTubePlyr } from '@FrontendTypes/index';
import { tutorConfig } from '@TutorShared/config/config';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import { __, sprintf } from '@wordpress/i18n';

interface AutoLoadPayload {
  post_id: number;
}

interface AutoLoadResponse {
  success: boolean;
  data?: {
    next_url: string;
  };
}

interface PlayerData {
  strict_mode?: boolean;
  control_video_lesson_completion?: boolean;
  lesson_completed?: boolean;
  is_enrolled?: boolean;
  best_watch_time?: number;
  required_percentage?: number;
  video_duration?: number;
  post_id?: number;
  autoload_next_course_content?: boolean;
}

class LessonPlayer {
  private element: HTMLElement;
  private player: Plyr | null = null;
  private playerData: PlayerData;
  private syncInterval: ReturnType<typeof setInterval> | null = null;
  private maxSeekTime: number = 0;
  private playedOnce: boolean = false;

  constructor(element: HTMLElement) {
    this.element = element;
    this.playerData = this.getPlayerData();
    this.maxSeekTime = this.playerData.best_watch_time || 0;
    this.initPlyr();
  }

  /**
   * Get player data from hidden input
   */
  private getPlayerData(): PlayerData {
    const el = document.getElementById('tutor_video_tracking_information');

    if (!(el instanceof HTMLInputElement) || !el.value) {
      return {};
    }

    try {
      return JSON.parse(el.value) as PlayerData;
    } catch {
      return {};
    }
  }

  /**
   * Check if the current lesson requires a watch percentage
   */
  private isRequiredPercentage(): boolean {
    const { strict_mode, control_video_lesson_completion, lesson_completed, is_enrolled } = this.playerData;
    return Boolean(
      tutorConfig.tutor_pro_url && is_enrolled && !lesson_completed && strict_mode && control_video_lesson_completion,
    );
  }

  /**
   * Calculate percentage
   */
  private getPercentage(value: number, total: number): number {
    if (value > 0 && total > 0) {
      return Math.round((value / total) * 100);
    }
    return 0;
  }

  /**
   * UI: Enable Complete Lesson Button
   */
  private enableCompleteLessonBtn() {
    const btn = document.querySelector('button[name="complete_lesson_btn"]') as HTMLButtonElement;
    if (!btn || !this.player) return;

    const completedPercentage = this.getPercentage(Number(this.player.currentTime), Number(this.player.duration));
    const requiredPercentage = this.playerData.required_percentage || 0;

    if (completedPercentage >= requiredPercentage) {
      btn.disabled = false;
      if (btn.nextElementSibling?.classList.contains('tutor-tooltip')) {
        btn.nextElementSibling.remove();
      }
      if (btn.parentElement?.classList.contains('tutor-tooltip-wrap')) {
        btn.parentElement.replaceWith(btn);
      }
    }
  }

  /**
   * UI: Disable Complete Lesson Button
   */
  private disableCompleteLessonBtn() {
    const { best_watch_time = 0, video_duration = 0, required_percentage = 0 } = this.playerData;
    const completedPercentage = this.getPercentage(Number(best_watch_time), Number(video_duration));

    if (completedPercentage < required_percentage) {
      const btn = document.querySelector('button[name="complete_lesson_btn"]') as HTMLButtonElement;
      if (btn && !btn.disabled) {
        btn.disabled = true;
        if (!btn.parentElement?.classList.contains('tutor-tooltip-wrap')) {
          const wrapper = document.createElement('div');
          wrapper.className = 'tutor-tooltip-wrap';
          wrapper.setAttribute('x-data', 'tutorTooltip({ placement: "top" })');
          btn.parentNode?.insertBefore(wrapper, btn);
          wrapper.appendChild(btn);
          btn.setAttribute('x-ref', 'trigger');
        }

        if (!btn.nextElementSibling?.classList.contains('tutor-tooltip')) {
          btn.insertAdjacentHTML(
            'afterend',
            `<div x-ref="content" class="tutor-tooltip" x-show="open" x-cloak x-transition>${sprintf(
              /* translators: %s is the required watch percentage (e.g., 80) */
              __('Watch at least %s%% to complete the lesson.', 'tutor'),
              required_percentage,
            )}</div>`,
          );

          if (window.Alpine) {
            window.Alpine.initTree(btn.parentElement as HTMLElement);
          }
        }
      }
    }
  }

  /**
   * Sync time with server
   */
  private syncTime(options: Record<string, unknown> = {}) {
    if (!this.player || !this.playerData.post_id) return;

    if (this.isRequiredPercentage()) {
      this.enableCompleteLessonBtn();
    }

    const data: Record<string, unknown> = {
      currentTime: this.player.currentTime,
      duration: this.player.duration,
      post_id: this.playerData.post_id,
      ...options,
    };

    // Include nonce if available
    const nonceKey = window._tutorobject?.nonce_key;
    if (nonceKey && window._tutorobject?.[nonceKey as keyof typeof window._tutorobject]) {
      data[nonceKey] = window._tutorobject[nonceKey as keyof typeof window._tutorobject];
    }

    wpAjaxInstance.post('sync_video_playback', data);

    const currentTime = this.player.currentTime;
    const bestWatchTime = this.playerData.best_watch_time || 0;
    const seekTime = bestWatchTime > currentTime ? bestWatchTime : currentTime;

    if (seekTime > this.maxSeekTime) {
      this.maxSeekTime = seekTime;
    }
  }

  /**
   * Autoload next course content
   */
  private autoloadContent() {
    if (!this.playerData.post_id) return;

    wpAjaxInstance
      .post<AutoLoadPayload, AutoLoadResponse>('autoload_next_course_content', {
        post_id: this.playerData.post_id,
      })
      .then((response) => {
        if (response.success && response.data?.next_url) {
          window.location.href = response.data.next_url;
        }
      });
  }

  /**
   * Get target time from seek event input
   */
  private getTargetTime(player: Plyr, event: Plyr.PlyrEvent): number {
    const target = event.target;
    if (target instanceof HTMLInputElement) {
      const value = Number(target.value);
      const max = Number(target.max) || 1;
      return (value / max) * player.duration;
    }
    return Number(event);
  }

  /**
   * Initialize Plyr
   */
  private initPlyr() {
    if (typeof window.Plyr === 'undefined') return;

    const requiredPercentage = this.isRequiredPercentage();
    const config: Plyr.Options = {
      keyboard: {
        focused: !requiredPercentage,
        global: false,
      },
      listeners: {
        ...(requiredPercentage && {
          seek: (e) => {
            if (!this.player) return true;
            const newTime = this.getTargetTime(this.player, e);
            const currentTime = this.player.currentTime;
            const maxSeek = currentTime > this.maxSeekTime ? currentTime : this.maxSeekTime;

            if (newTime > maxSeek) {
              e.preventDefault();
              window.TutorCore.toast.warning(__('Forward seeking is disabled', 'tutor'));
              return false;
            }
            return true;
          },
        }),
      },
    };

    this.player = new window.Plyr(this.element, config);
    this.setupEvents();

    if (requiredPercentage) {
      this.disableCompleteLessonBtn();
    }
  }

  /**
   * Setup Plyr events
   */
  private setupEvents() {
    if (!this.player) return;

    this.player.on('ready', (event: Plyr.PlyrEvent) => {
      // Remove loading spinner
      document.querySelector('.tutor-video-player .loading-spinner')?.remove();

      this.syncTime();

      const instance = event.detail.plyr;

      /**
       * Play from best watch time
       */
      const { best_watch_time = 0 } = this.playerData;
      if (tutorConfig.tutor_pro_url && best_watch_time > 0) {
        const previousDuration = Math.floor(best_watch_time);
        setTimeout(() => {
          if (instance.playing !== true && instance.currentTime !== previousDuration) {
            if (isYouTubePlyr(instance)) {
              instance.embed.seekTo(best_watch_time);
            } else {
              instance.currentTime = previousDuration;
            }
          }
        }, 0);
      }

      /**
       * Fix: Mobile Vimeo autoplay sound issue
       * Always start muted on mobile to comply with autoplay policy.
       */
      if (isVimeoPlyr(instance) && isMobileDevice()) {
        try {
          instance.muted = true;
          if (typeof instance.mute === 'function') {
            instance.mute();
          }
        } catch (err) {
          // eslint-disable-next-line no-console
          console.warn('Vimeo mute init failed:', err);
        }
      }

      // Dispatch custom event and set player to window
      const customEvent = new CustomEvent('tutorLessonPlayerReady', {
        detail: { player: instance },
      });
      window.dispatchEvent(customEvent);
      window.TutorLessonPlayer = instance;
    });

    this.player.on('play', (event: Plyr.PlyrEvent) => {
      if (this.syncInterval) clearInterval(this.syncInterval);

      this.playedOnce = true;

      /**
       * Send to tutor backend about video playing time in this interval
       */
      const intervalSeconds = 10;
      if (tutorConfig.tutor_pro_url) {
        this.syncInterval = setInterval(() => {
          this.syncTime();
        }, intervalSeconds * 1000);
      }

      /**
       * Unmute automatically after first user interaction
       * Mobile browsers allow audio only after gesture.
       */
      const instance = event.detail.plyr;
      if (isVimeoPlyr(instance) && isMobileDevice()) {
        try {
          instance.muted = false;
          if (typeof instance.unmute === 'function') {
            instance.unmute();
          }
        } catch (err) {
          // eslint-disable-next-line no-console
          console.warn('Vimeo unmute on play failed:', err);
        }
      }

      /**
       * Fix: YouTube poster issue
       */
      if (tutorConfig.tutor_pro_url && instance.provider === 'youtube') {
        const poster = instance.elements.container?.querySelector('.plyr__poster') as HTMLElement;
        if (poster) {
          poster.style.opacity = '0';
        }
      }
    });

    this.player.on('pause', () => {
      if (this.syncInterval) clearInterval(this.syncInterval);

      if (tutorConfig.tutor_pro_url) {
        this.syncTime();
      }
    });

    this.player.on('ended', (event: Plyr.PlyrEvent) => {
      if (this.syncInterval) clearInterval(this.syncInterval);

      const instance = event.detail.plyr;
      this.syncTime({ is_ended: true });

      if (this.playerData.autoload_next_course_content && this.playedOnce) {
        this.autoloadContent();
      }

      /**
       * Fix: YouTube poster issue
       */
      if (tutorConfig.tutor_pro_url && instance.provider === 'youtube') {
        const poster = instance.elements.container?.querySelector('.plyr__poster') as HTMLElement;
        if (poster) {
          poster.style.opacity = '1';
        }
      }
    });
  }

  /**
   * Initialize Lesson Player
   */
  public static init() {
    const player = document.querySelector('.tutor-lesson-video-wrapper .tutorPlayer') as HTMLElement;
    if (player) {
      new LessonPlayer(player);
    }
  }
}

export const initializeLessonPlayer = () => {
  LessonPlayer.init();
};
