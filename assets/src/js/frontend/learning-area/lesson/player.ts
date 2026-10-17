import { __, sprintf } from '@wordpress/i18n';

import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { CONTENT_TITLE_ATTRIBUTE } from '@Core/ts/services/player/elements';
import { getLegacyTutorLessonPlayer } from '@Core/ts/services/player/legacy';
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

export function getPercentage(value: number, total: number): number {
  if (value > 0 && total > 0) {
    return Math.round((value / total) * 100);
  }
  return 0;
}

export function readPlayerData(document: Document): PlayerData {
  const element = document.getElementById('tutor_video_tracking_information');

  if (!(element instanceof HTMLInputElement) || !element.value) {
    return {};
  }

  try {
    return JSON.parse(element.value) as PlayerData;
  } catch {
    return {};
  }
}

export class LessonPlaybackController {
  #player: TutorPlayer;
  #data: PlayerData;
  #env: LessonEnvironment;
  #syncTimer: ReturnType<typeof setInterval> | null = null;
  #maxSeekTime = 0;
  #lastTime = 0;
  #playedOnce = false;
  #unsubscribers: PlayerUnsubscribe[] = [];

  constructor(player: TutorPlayer, data: PlayerData, environment: LessonEnvironment) {
    this.#player = player;
    this.#data = data;
    this.#env = environment;
    this.#maxSeekTime = data.best_watch_time || 0;
    this.#lastTime = data.best_watch_time || 0;
  }

  start(): void {
    this.#unsubscribers = [
      this.#player.on('ready', this.#onReady),
      this.#player.on('play', this.#onPlay),
      this.#player.on('pause', this.#onPause),
      this.#player.on('ended', this.#onEnded),
      this.#player.on('timeupdate', this.#onTimeUpdate),
      this.#player.on('fullscreenchange', this.#onFullscreenChange),
    ];
  }

  destroy(): void {
    this.#clearSyncTimer();
    this.#unsubscribers.forEach((unsubscribe) => unsubscribe());
    this.#unsubscribers = [];
  }

  #onReady = (): void => {
    this.#env.document.querySelector('.tutor-video-player .loading-spinner')?.remove();

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

    this.#env.notifyReady?.(this.#player);
  };

  #onPlay = (): void => {
    this.#playedOnce = true;

    if (this.#env.isMobile && this.#player.provider === 'vimeo') {
      this.#player.setMuted(false);
    }

    if (this.#env.proActive) {
      this.#clearSyncTimer();
      this.#syncTimer = setInterval(() => this.#syncTime(), this.#env.syncIntervalSeconds * 1000);
    }
  };

  #onPause = (): void => {
    this.#clearSyncTimer();

    if (this.#env.proActive) {
      this.#syncTime();
    }
  };

  #onEnded = (): void => {
    this.#clearSyncTimer();
    this.#syncTime({ is_ended: true });

    if (this.#env.autoPlayNext && this.#playedOnce) {
      void this.#autoloadContent();
    }
  };

  #onTimeUpdate = ({ currentTime }: PlayerEventMap['timeupdate']): void => {
    if (this.#isStrict()) {
      const limit = Math.max(this.#maxSeekTime, this.#lastTime);

      if (currentTime > limit + SEEK_TOLERANCE) {
        this.#player.seek(this.#maxSeekTime);
        this.#env.toastWarning(__('Forward seeking is disabled', 'tutor'));
        return;
      }
    }

    this.#lastTime = currentTime;

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

    const currentTime = this.#player.getCurrentTime();

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

      if (btn.nextElementSibling?.classList.contains('tutor-tooltip')) {
        btn.nextElementSibling.remove();
      }

      if (btn.parentElement?.classList.contains('tutor-tooltip-wrap')) {
        btn.parentElement.replaceWith(btn);
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

  async #autoloadContent(): Promise<void> {
    if (!this.#data.post_id) {
      return;
    }

    const nextUrl = await this.#env.fetchNextUrl(this.#data.post_id);

    if (nextUrl) {
      this.#env.openUrl(nextUrl);
    }
  }

  #clearSyncTimer(): void {
    if (this.#syncTimer) {
      clearInterval(this.#syncTimer);
      this.#syncTimer = null;
    }
  }
}

function createEnvironment(document: Document): LessonEnvironment {
  const core = window.TutorCore;
  const config = core.config.tutorConfig;

  return {
    proActive: Boolean(config.tutor_pro_url),
    autoPlayNext: Boolean(config.user_preferences?.auto_play_next),
    isMobile: core.device.isMobileDevice(),
    syncIntervalSeconds: DEFAULT_SYNC_INTERVAL_SECONDS,
    syncPlayback: (data) => {
      core.api.wpPost('sync_video_playback', data);
    },
    fetchNextUrl: async (postId) => {
      const response = await core.api.wpPost<AutoLoadResponse>('autoload_next_course_content', { post_id: postId });
      return response?.data?.next_url ?? null;
    },
    toastWarning: (message) => {
      core.toast.warning(message);
    },
    openUrl: (url) => {
      window.location.assign(url);
    },
    notifyReady: (player) => {
      window.dispatchEvent(new CustomEvent(TUTOR_CUSTOM_EVENTS.LESSON_PLAYER_READY, { detail: { player } }));
      window.TutorLessonPlayer = getLegacyTutorLessonPlayer();
    },
    document,
  };
}

export const initializeLessonPlayer = (): void => {
  if (!window.TutorCore?.player) {
    // The engine is a classic script that loads after this IIFE bundle.
    window.addEventListener(TUTOR_CUSTOM_EVENTS.PLAYER_ENGINE_READY, initializeLessonPlayer, { once: true });
    return;
  }

  // Declarative element first; `.tutorPlayer` keeps third-party legacy markup on the shim
  // path until it is retired.
  const target = document.querySelector(
    '.tutor-lesson-video-wrapper tutor-video-player, .tutor-lesson-video-wrapper .tutorPlayer',
  ) as HTMLElement | null;

  if (!target) {
    document.querySelector('.tutor-video-player .loading-spinner')?.remove();
    return;
  }

  const player = window.TutorCore.player.create(target);
  new LessonPlaybackController(player, readPlayerData(document), createEnvironment(document)).start();
};
