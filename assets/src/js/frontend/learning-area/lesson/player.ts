import { __, sprintf } from '@wordpress/i18n';

import { CONTENT_TITLE_ATTRIBUTE, TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import type { PlayerEventMap, PlayerUnsubscribe, TutorPlayer } from '@Core/ts/types/player';

interface AutoLoadResponse {
  success: boolean;
  data?: {
    next_url: string;
  };
}

export interface PlayerData {
  strict_mode?: boolean;
  control_video_lesson_completion?: boolean;
  lesson_completed?: boolean;
  is_enrolled?: boolean;
  best_watch_time?: number;
  required_percentage?: number;
  video_duration?: number;
  post_id?: number;
  autoload_next_course_content?: boolean;
  lesson_title?: string;
}

export interface LessonEnvironment {
  proActive: boolean;
  autoPlayNext: boolean;
  isMobile: boolean;
  syncIntervalSeconds: number;
  syncPlayback: (data: Record<string, unknown>) => void;
  fetchNextUrl: (postId: number) => Promise<string | null>;
  toastWarning: (message: string) => void;
  openUrl: (url: string) => void;
  notifyReady?: (player: TutorPlayer) => void;
  document: Document;
}

const SEEK_TOLERANCE = 1;
const DEFAULT_SYNC_INTERVAL_SECONDS = 10;
const SPINNER_TRANSITION_MS = 250;
const SPINNER_FALLBACK_TIMEOUT_MS = 2500;
const SPINNER_HARD_CAP_TIMEOUT_MS = 10000;

export const getPercentage = (value: number, total: number): number => {
  if (value > 0 && total > 0) {
    return Math.round((value / total) * 100);
  }
  return 0;
};

export const readPlayerData = (document: Document): PlayerData => {
  const element = document.getElementById('tutor_video_tracking_information');

  if (!(element instanceof HTMLInputElement) || !element.value) {
    return {};
  }

  try {
    const data = JSON.parse(element.value) as PlayerData | null;
    // Video templates have shipped value="null" since before the migration; treat it as absent.
    return data && typeof data === 'object' ? data : {};
  } catch {
    return {};
  }
};

export class LessonPlaybackController {
  #player: TutorPlayer;
  #data: PlayerData;
  #env: LessonEnvironment;
  #syncTimer: ReturnType<typeof setInterval> | null = null;
  #maxSeekTime = 0;
  #observedTime = 0;
  #observedAt = 0;
  #playing = false;
  #playedOnce = false;
  #seekingBack = false;
  #seekDebounceTimer: ReturnType<typeof setTimeout> | null = null;
  #spinnerRemoved = false;
  #spinnerFallbackTimer: ReturnType<typeof setTimeout> | null = null;
  #spinnerCleanups: (() => void)[] = [];
  #unsubscribers: PlayerUnsubscribe[] = [];

  constructor(player: TutorPlayer, data: PlayerData, environment: LessonEnvironment) {
    this.#player = player;
    this.#data = data;
    this.#env = environment;
    this.#maxSeekTime = data.best_watch_time || 0;
    this.#observedTime = data.best_watch_time || 0;
  }

  start(): void {
    this.#unsubscribers = [
      this.#player.on('ready', this.#onReady),
      this.#player.on('play', this.#onPlay),
      this.#player.on('pause', this.#onPause),
      this.#player.on('ended', this.#onEnded),
      this.#player.on('timeupdate', this.#onTimeUpdate),
      this.#player.on('fullscreenchange', this.#onFullscreenChange),
      this.#player.on('dispose', () => this.destroy()),
    ];
  }

  destroy(): void {
    this.#clearSyncTimer();
    if (this.#seekDebounceTimer) {
      clearTimeout(this.#seekDebounceTimer);
      this.#seekDebounceTimer = null;
    }
    if (this.#spinnerFallbackTimer) {
      clearTimeout(this.#spinnerFallbackTimer);
      this.#spinnerFallbackTimer = null;
    }
    this.#spinnerCleanups.forEach((cleanup) => cleanup());
    this.#spinnerCleanups = [];
    this.#unsubscribers.forEach((unsubscribe) => unsubscribe());
    this.#unsubscribers = [];
  }

  #removeLoadingSpinner = (): void => {
    if (this.#spinnerRemoved) {
      return;
    }
    this.#spinnerRemoved = true;
    if (this.#spinnerFallbackTimer) {
      clearTimeout(this.#spinnerFallbackTimer);
      this.#spinnerFallbackTimer = null;
    }
    this.#spinnerCleanups.forEach((cleanup) => cleanup());
    this.#spinnerCleanups = [];

    const spinner =
      this.#player.element.closest('.tutor-video-player')?.querySelector('.loading-spinner') ??
      this.#env.document.querySelector('.tutor-video-player .loading-spinner');

    if (spinner) {
      spinner.classList.add('hide');
      setTimeout(() => spinner.remove(), SPINNER_TRANSITION_MS);
    }
  };

  #scheduleSpinnerRemoval = (): void => {
    const media = this.#player.media;

    const isMediaReady = (): boolean => {
      if (this.#playing || this.#player.getCurrentTime() > 0) {
        return true;
      }
      if (media instanceof HTMLVideoElement) {
        return media.readyState >= 2;
      }
      return this.#player.getDuration() > 0;
    };

    if (isMediaReady()) {
      this.#removeLoadingSpinner();
      return;
    }

    this.#spinnerFallbackTimer = setTimeout(() => {
      // A slow buffer must keep the spinner; only hide it once the media is really ready.
      // Playback errors remove it below so the error dialog is never covered.
      if (isMediaReady()) {
        this.#spinnerFallbackTimer = null;
        this.#removeLoadingSpinner();
        return;
      }

      // Hard cap: a provider that never becomes ready and never errors (blocked embed,
      // CSP) must not leave an opaque overlay covering the controls forever.
      this.#spinnerFallbackTimer = setTimeout(() => {
        this.#spinnerFallbackTimer = null;
        this.#removeLoadingSpinner();
      }, SPINNER_HARD_CAP_TIMEOUT_MS - SPINNER_FALLBACK_TIMEOUT_MS);
    }, SPINNER_FALLBACK_TIMEOUT_MS);

    const onLoaded = (): void => {
      this.#removeLoadingSpinner();
    };

    const offTime = this.#player.on('timeupdate', ({ duration }) => {
      if (duration > 0) {
        if (media instanceof HTMLVideoElement && media.readyState < 2) {
          return;
        }
        onLoaded();
      }
    });

    const offPlay = this.#player.on('play', onLoaded);
    const offError = this.#player.on('error', onLoaded);

    this.#spinnerCleanups.push(offTime, offPlay, offError);

    if (media) {
      media.addEventListener('loadeddata', onLoaded, { once: true });
      media.addEventListener('canplay', onLoaded, { once: true });
      media.addEventListener('loadedmetadata', onLoaded, { once: true });

      this.#spinnerCleanups.push(() => {
        media.removeEventListener('loadeddata', onLoaded);
        media.removeEventListener('canplay', onLoaded);
        media.removeEventListener('loadedmetadata', onLoaded);
      });
    }
  };

  #onReady = (): void => {
    this.#syncTime();

    const bestWatchTime = this.#data.best_watch_time || 0;

    if (this.#env.proActive && bestWatchTime > 0) {
      this.#player.seek(Math.floor(bestWatchTime));
    }

    if (this.#isStrict()) {
      this.#disableCompleteLessonBtn();
    }

    if (this.#env.isMobile && this.#player.provider === 'vimeo') {
      this.#player.setMuted(true);
    }

    this.#scheduleSpinnerRemoval();

    this.#env.notifyReady?.(this.#player);
  };

  #onPlay = (): void => {
    this.#removeLoadingSpinner();
    this.#playedOnce = true;
    this.#playing = true;
    // A paused stretch must not count as allowed forward progress.
    this.#observedAt = performance.now();

    if (this.#env.isMobile && this.#player.provider === 'vimeo') {
      this.#player.setMuted(false);
    }

    if (this.#env.proActive) {
      this.#clearSyncTimer();
      this.#syncTimer = setInterval(() => this.#syncTime(), this.#env.syncIntervalSeconds * 1000);
    }
  };

  #onPause = (): void => {
    this.#playing = false;
    this.#clearSyncTimer();

    if (this.#env.proActive) {
      // Do not sync forward position if paused during an unauthorized forward seek
      if (this.#isStrict() && this.#player.getCurrentTime() > this.#maxSeekTime + SEEK_TOLERANCE) {
        return;
      }
      this.#syncTime();
    }
  };

  #onEnded = (): void => {
    this.#playing = false;
    this.#clearSyncTimer();

    // In strict mode, reject ending if user jumped ahead without watching
    if (this.#isStrict() && this.#maxSeekTime < this.#player.getDuration() - SEEK_TOLERANCE) {
      return;
    }

    this.#syncTime({ is_ended: true });

    if (this.#env.autoPlayNext && this.#playedOnce) {
      void this.#autoloadContent();
    }
  };

  #onTimeUpdate = ({ currentTime }: PlayerEventMap['timeupdate']): void => {
    const now = performance.now();

    if (this.#isStrict()) {
      // Throttled timers (background tabs) deliver updates late while playback
      // continues, so allow the wall-clock time that passed while playing.
      const elapsed = this.#playing ? (now - this.#observedAt) / 1000 : 0;
      const limit = Math.max(this.#maxSeekTime, this.#observedTime + elapsed);

      if (currentTime > limit + SEEK_TOLERANCE) {
        if (!this.#seekingBack) {
          this.#seekingBack = true;
          this.#player.seek(this.#maxSeekTime);
          this.#env.toastWarning(__('Forward seeking is disabled', 'tutor'));
          if (this.#seekDebounceTimer) {
            clearTimeout(this.#seekDebounceTimer);
          }
          this.#seekDebounceTimer = setTimeout(() => {
            this.#seekingBack = false;
            this.#seekDebounceTimer = null;
          }, 500);
        }
        return;
      }
    }

    this.#observedTime = currentTime;
    this.#observedAt = now;

    if (currentTime > this.#maxSeekTime) {
      this.#maxSeekTime = currentTime;
    }
  };

  #onFullscreenChange = ({ fullscreen }: PlayerEventMap['fullscreenchange']): void => {
    this.#player.element.classList.toggle('is-fullscreen', fullscreen);

    // The skin's media-title follows the player content-title: lesson title in
    // fullscreen, empty (hidden) otherwise. Never the provider's own title.
    this.#player.element.setAttribute(
      CONTENT_TITLE_ATTRIBUTE,
      fullscreen ? (this.#data.lesson_title?.trim() ?? '') : '',
    );

    if (!fullscreen) {
      this.#resyncFullscreen();
    }
  };

  #resyncFullscreen(): void {
    const document = this.#player.element.ownerDocument;

    if (!document || document.fullscreenElement || !this.#player.isFullscreen()) {
      return;
    }

    // The packaged store syncs synchronously on document fullscreenchange but
    // trusts the provider's fullscreen flag, which Vimeo only settles
    // asynchronously via its SDK echo — so after an exit the store can stay
    // stuck reporting fullscreen and the next toggle misfires exitFullscreen
    // ("Document not active"). Reset a stale provider flag, then re-run the
    // store sync. The webkit alias avoids re-firing our own and Pro's
    // fullscreenchange handlers.
    const resync = (): void => {
      if (!document.fullscreenElement && this.#player.isFullscreen()) {
        document.dispatchEvent(new Event('webkitfullscreenchange'));
      }
    };

    try {
      const media = this.#player.media as { exitFullscreen?: () => unknown } | null;
      Promise.resolve(media?.exitFullscreen?.()).then(resync, resync);
    } catch {
      resync();
    }
  }

  #isStrict(): boolean {
    const { strict_mode, control_video_lesson_completion, lesson_completed, is_enrolled } = this.#data;

    return Boolean(
      this.#env.proActive && is_enrolled && !lesson_completed && strict_mode && control_video_lesson_completion,
    );
  }

  #syncTime(options: Record<string, unknown> = {}): void {
    if (!this.#data.post_id) {
      return;
    }

    if (this.#isStrict()) {
      this.#enableCompleteLessonBtn();
    }

    const rawCurrentTime = this.#player.getCurrentTime();
    const currentTime = this.#isStrict()
      ? Math.min(rawCurrentTime, this.#maxSeekTime + SEEK_TOLERANCE)
      : rawCurrentTime;

    this.#env.syncPlayback({
      currentTime,
      duration: this.#player.getDuration(),
      post_id: this.#data.post_id,
      ...options,
    });

    const bestWatchTime = this.#data.best_watch_time || 0;
    const seekTime = Math.max(bestWatchTime, currentTime);

    if (seekTime > this.#maxSeekTime) {
      this.#maxSeekTime = seekTime;
    }
  }

  #enableCompleteLessonBtn(): void {
    const btn = this.#env.document.querySelector('button[name="complete_lesson_btn"]') as HTMLButtonElement | null;

    if (!btn) {
      return;
    }

    const completedPercentage = getPercentage(this.#player.getCurrentTime(), this.#player.getDuration());
    const requiredPercentage = this.#data.required_percentage || 0;

    if (completedPercentage >= requiredPercentage) {
      btn.disabled = false;

      const tooltip = btn.nextElementSibling;

      if (tooltip?.classList.contains('tutor-tooltip') || tooltip?.classList.contains('tooltip-txt')) {
        tooltip.remove();
      }

      const wrapper = btn.parentElement;

      if (wrapper?.classList.contains('tutor-tooltip-wrap') || wrapper?.classList.contains('tooltip-wrap')) {
        wrapper.replaceWith(btn);
      }
    }
  }

  #disableCompleteLessonBtn(): void {
    const { best_watch_time = 0, video_duration = 0, required_percentage = 0 } = this.#data;
    const completedPercentage = getPercentage(Number(best_watch_time), Number(video_duration));

    if (completedPercentage >= required_percentage) {
      return;
    }

    const document = this.#env.document;
    const btn = document.querySelector('button[name="complete_lesson_btn"]') as HTMLButtonElement | null;

    if (!btn || btn.disabled) {
      return;
    }

    btn.disabled = true;

    // Learning Area pages run Alpine and the `tutorTooltip` component; legacy pages don't.
    const alpine = window.Alpine;

    if (
      !btn.parentElement?.classList.contains('tutor-tooltip-wrap') &&
      !btn.parentElement?.classList.contains('tooltip-wrap')
    ) {
      const wrapper = document.createElement('div');
      wrapper.className = alpine ? 'tutor-tooltip-wrap' : 'tooltip-wrap';

      if (alpine) {
        wrapper.setAttribute('x-data', 'tutorTooltip({ placement: "top" })');
      }

      btn.parentNode?.insertBefore(wrapper, btn);
      wrapper.appendChild(btn);

      if (alpine) {
        btn.setAttribute('x-ref', 'trigger');
      }
    }

    const sibling = btn.nextElementSibling;
    const hasTooltip = sibling?.classList.contains('tutor-tooltip') || sibling?.classList.contains('tooltip-txt');

    if (!hasTooltip) {
      const message = sprintf(
        /* translators: %s is the required watch percentage (e.g., 80) */
        __('Watch at least %s%% to complete the lesson.', 'tutor'),
        required_percentage,
      );

      if (alpine) {
        btn.insertAdjacentHTML(
          'afterend',
          `<div x-ref="content" class="tutor-tooltip" x-show="open" x-cloak x-transition>${message}</div>`,
        );
        alpine.initTree(btn.parentElement as HTMLElement);
      } else {
        // Pre-migration markup: the stylesheet shows this span on hover.
        btn.insertAdjacentHTML('afterend', `<span class="tooltip-txt tooltip-bottom">${message}</span>`);
      }
    }
  }

  async #autoloadContent(): Promise<void> {
    if (!this.#data.post_id) {
      return;
    }

    try {
      const nextUrl = await this.#env.fetchNextUrl(this.#data.post_id);

      if (nextUrl) {
        this.#env.openUrl(nextUrl);
      }
    } catch {
      // Ignore autoload failures
    }
  }

  #clearSyncTimer(): void {
    if (this.#syncTimer) {
      clearInterval(this.#syncTimer);
      this.#syncTimer = null;
    }
  }
}

const createEnvironment = (document: Document): LessonEnvironment => {
  const core = window.TutorCore;
  const config = core.config.tutorConfig;

  return {
    proActive: Boolean(config.tutor_pro_url),
    autoPlayNext: Boolean(config.user_preferences?.auto_play_next),
    isMobile: core.device.isMobileDevice(),
    syncIntervalSeconds: DEFAULT_SYNC_INTERVAL_SECONDS,
    syncPlayback: (data) => {
      void core.api.wpPost('sync_video_playback', data).catch((error: unknown) => {
        // Progress sync is best-effort; a failure must not break playback.
        // eslint-disable-next-line no-console
        console.debug('[tutor] playback sync failed:', error);
      });
    },
    fetchNextUrl: async (postId) => {
      try {
        const response = await core.api.wpPost<AutoLoadResponse>('autoload_next_course_content', { post_id: postId });
        return response?.data?.next_url ?? null;
      } catch {
        return null;
      }
    },
    toastWarning: (message) => {
      core.toast.warning(message);
    },
    openUrl: (url) => {
      window.location.assign(url);
    },
    notifyReady: (player) => {
      window.dispatchEvent(new CustomEvent(TUTOR_CUSTOM_EVENTS.LESSON_PLAYER_READY, { detail: { player } }));
    },
    document,
  };
};

export const initializeLessonPlayer = (): void => {
  if (!window.TutorCore?.player) {
    // The engine is a classic script that loads after this IIFE bundle.
    window.addEventListener(TUTOR_CUSTOM_EVENTS.PLAYER_ENGINE_READY, initializeLessonPlayer, { once: true });
    return;
  }

  // Declarative element first; `.tutorPlayer` is legacy markup from older templates and themes.
  const target = document.querySelector(
    '.tutor-lesson-video-wrapper tutor-video-player, .tutor-lesson-video-wrapper .tutorPlayer',
  ) as HTMLElement | null;

  if (!target) {
    document.querySelector('.tutor-video-player .loading-spinner')?.remove();
    return;
  }

  const playerContainer = target.closest('.tutor-video-player') ?? target.parentElement;

  let player: TutorPlayer;
  try {
    player = window.TutorCore.player.create(target);
  } catch (error) {
    // eslint-disable-next-line no-console
    console.warn('[tutor] player: skipped an unplayable element', target, error);
    playerContainer?.querySelector('.loading-spinner')?.remove();
    return;
  }

  new LessonPlaybackController(player, readPlayerData(document), createEnvironment(document)).start();
};
