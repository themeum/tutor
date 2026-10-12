import { afterEach, describe, expect, it, vi } from 'vitest';

import type { PlayerEventMap, TutorPlayer } from '@Core/ts/types/player';

import {
  getPercentage,
  type LessonEnvironment,
  LessonPlaybackController,
  type PlayerData,
  readPlayerData,
} from './player';

type Handler = (payload: unknown) => void;

function createFakePlayer(overrides: Partial<TutorPlayer> = {}) {
  const handlers = new Map<string, Set<Handler>>();

  const player: TutorPlayer = {
    provider: 'html5',
    element: document.createElement('div'),
    media: null,
    getCurrentTime: vi.fn(() => 0),
    getDuration: vi.fn(() => 100),
    isPaused: vi.fn(() => true),
    getVolume: vi.fn(() => 1),
    setVolume: vi.fn(),
    isMuted: vi.fn(() => false),
    setMuted: vi.fn(),
    isFullscreen: vi.fn(() => false),
    play: vi.fn(async () => undefined),
    pause: vi.fn(),
    seek: vi.fn(),
    on: vi.fn((event: keyof PlayerEventMap, handler: Handler) => {
      const set = handlers.get(event) ?? new Set<Handler>();
      set.add(handler);
      handlers.set(event, set);
      return () => set.delete(handler);
    }),
    dispose: vi.fn(),
    ...overrides,
  };

  const emit = <E extends keyof PlayerEventMap>(event: E, payload: Partial<PlayerEventMap[E]> = {}): void => {
    handlers.get(event)?.forEach((handler) => handler({ player, ...payload }));
  };

  return { player, emit };
}

function createEnvironment(overrides: Partial<LessonEnvironment> = {}): LessonEnvironment {
  return {
    proActive: true,
    autoPlayNext: false,
    isMobile: false,
    syncIntervalSeconds: 10,
    syncPlayback: vi.fn(),
    fetchNextUrl: vi.fn(async () => null),
    toastWarning: vi.fn(),
    openUrl: vi.fn(),
    document,
    ...overrides,
  };
}

const strictData: PlayerData = {
  strict_mode: true,
  control_video_lesson_completion: true,
  lesson_completed: false,
  is_enrolled: true,
  post_id: 1,
  required_percentage: 80,
  video_duration: 100,
};

afterEach(() => {
  document.body.innerHTML = '';
  vi.useRealTimers();
  vi.restoreAllMocks();
});

describe('getPercentage', () => {
  it('rounds the ratio and guards zero/negative totals', () => {
    expect(getPercentage(85, 100)).toBe(85);
    expect(getPercentage(1, 3)).toBe(33);
    expect(getPercentage(0, 100)).toBe(0);
    expect(getPercentage(5, 0)).toBe(0);
  });
});

describe('readPlayerData', () => {
  it('parses the hidden tracking input', () => {
    const input = document.createElement('input');
    input.id = 'tutor_video_tracking_information';
    input.value = JSON.stringify({ post_id: 3, strict_mode: true });
    document.body.append(input);

    expect(readPlayerData(document)).toEqual({ post_id: 3, strict_mode: true });
  });

  it('returns an empty object when missing or invalid', () => {
    expect(readPlayerData(document)).toEqual({});

    const input = document.createElement('input');
    input.id = 'tutor_video_tracking_information';
    input.value = 'not json';
    document.body.append(input);

    expect(readPlayerData(document)).toEqual({});
  });
});

describe('LessonPlaybackController strict mode', () => {
  it('seeks back and warns on a forward jump', () => {
    const { player, emit } = createFakePlayer();
    const env = createEnvironment();
    const controller = new LessonPlaybackController(player, strictData, env);
    controller.start();

    emit('ready');
    emit('timeupdate', { currentTime: 50 });

    expect(player.seek).toHaveBeenCalledWith(0);
    expect(env.toastWarning).toHaveBeenCalledTimes(1);
  });

  it('allows natural playback progression within tolerance', () => {
    const { player, emit } = createFakePlayer();
    const controller = new LessonPlaybackController(player, strictData, createEnvironment());
    controller.start();

    emit('ready');
    emit('timeupdate', { currentTime: 1 });
    emit('timeupdate', { currentTime: 2 });

    expect(player.seek).not.toHaveBeenCalled();
  });
});

describe('LessonPlaybackController sync', () => {
  it('syncs on the interval while playing and again on pause', () => {
    vi.useFakeTimers();
    const { player, emit } = createFakePlayer();
    const env = createEnvironment();
    const controller = new LessonPlaybackController(player, { post_id: 1 }, env);
    controller.start();

    emit('play');
    vi.advanceTimersByTime(10_000);
    expect(env.syncPlayback).toHaveBeenCalledTimes(1);

    emit('pause');
    expect(env.syncPlayback).toHaveBeenCalledTimes(2);

    vi.advanceTimersByTime(10_000);
    expect(env.syncPlayback).toHaveBeenCalledTimes(2);
  });

  it('does not sync when pro is inactive', () => {
    vi.useFakeTimers();
    const { player, emit } = createFakePlayer();
    const env = createEnvironment({ proActive: false });
    const controller = new LessonPlaybackController(player, { post_id: 1 }, env);
    controller.start();

    emit('play');
    vi.advanceTimersByTime(30_000);
    emit('pause');

    expect(env.syncPlayback).not.toHaveBeenCalled();
  });
});

describe('LessonPlaybackController resume', () => {
  it('seeks to the floored best watch time on ready', () => {
    const { player, emit } = createFakePlayer();
    const controller = new LessonPlaybackController(player, { best_watch_time: 42.7, post_id: 1 }, createEnvironment());
    controller.start();

    emit('ready');

    expect(player.seek).toHaveBeenCalledWith(42);
  });

  it('does not resume when pro is inactive', () => {
    const { player, emit } = createFakePlayer();
    const env = createEnvironment({ proActive: false });
    const controller = new LessonPlaybackController(player, { best_watch_time: 42, post_id: 1 }, env);
    controller.start();

    emit('ready');

    expect(player.seek).not.toHaveBeenCalled();
  });
});

describe('LessonPlaybackController completion button', () => {
  it('disables the button and adds a tooltip when below the required percentage', () => {
    document.body.innerHTML = '<button name="complete_lesson_btn"></button>';
    const { player, emit } = createFakePlayer();
    const controller = new LessonPlaybackController(player, strictData, createEnvironment());
    controller.start();

    emit('ready');

    const btn = document.querySelector('button[name="complete_lesson_btn"]') as HTMLButtonElement;
    expect(btn.disabled).toBe(true);
    expect(btn.nextElementSibling?.classList.contains('tutor-tooltip')).toBe(true);
  });

  it('enables the button once the required percentage is reached', () => {
    document.body.innerHTML = '<button name="complete_lesson_btn" disabled></button>';
    const { player, emit } = createFakePlayer({
      getCurrentTime: vi.fn(() => 85),
      getDuration: vi.fn(() => 100),
    });
    const controller = new LessonPlaybackController(
      player,
      { ...strictData, best_watch_time: 85 },
      createEnvironment(),
    );
    controller.start();

    emit('ready');

    const btn = document.querySelector('button[name="complete_lesson_btn"]') as HTMLButtonElement;
    expect(btn.disabled).toBe(false);
    expect(btn.nextElementSibling).toBeNull();
  });
});

describe('LessonPlaybackController mobile vimeo', () => {
  it('starts muted and unmutes on play for mobile vimeo', () => {
    const { player, emit } = createFakePlayer({ provider: 'vimeo' });
    const env = createEnvironment({ isMobile: true });
    const controller = new LessonPlaybackController(player, { post_id: 1 }, env);
    controller.start();

    emit('ready');
    expect(player.setMuted).toHaveBeenCalledWith(true);

    emit('play');
    expect(player.setMuted).toHaveBeenCalledWith(false);
  });
});

describe('LessonPlaybackController fullscreen', () => {
  it('toggles is-fullscreen on the host element', () => {
    const { player, emit } = createFakePlayer();
    const controller = new LessonPlaybackController(player, { post_id: 1 }, createEnvironment());
    controller.start();

    emit('fullscreenchange', { fullscreen: true });
    expect(player.element.classList.contains('is-fullscreen')).toBe(true);

    emit('fullscreenchange', { fullscreen: false });
    expect(player.element.classList.contains('is-fullscreen')).toBe(false);
  });

  it('sets the lesson title on fullscreen and clears it on exit', () => {
    const { player, emit } = createFakePlayer();
    const controller = new LessonPlaybackController(
      player,
      { post_id: 1, lesson_title: 'Direct Links To Video Files' },
      createEnvironment(),
    );
    controller.start();

    emit('fullscreenchange', { fullscreen: true });
    expect(player.element.getAttribute('content-title')).toBe('Direct Links To Video Files');

    emit('fullscreenchange', { fullscreen: false });
    expect(player.element.getAttribute('content-title')).toBe('');
  });

  it('clears the title on fullscreen without a lesson title', () => {
    const { player, emit } = createFakePlayer();
    const controller = new LessonPlaybackController(player, { post_id: 1 }, createEnvironment());
    controller.start();

    emit('fullscreenchange', { fullscreen: true });
    expect(player.element.getAttribute('content-title')).toBe('');
  });

  it('re-syncs a stuck fullscreen store when the media echo lands after exit', () => {
    Object.defineProperty(document, 'fullscreenElement', { configurable: true, value: null });
    const resync = vi.fn();
    document.addEventListener('webkitfullscreenchange', resync);

    try {
      const { player } = createFakePlayer({ isFullscreen: vi.fn(() => true) });
      const media = document.createElement('vimeo-video');
      player.element.appendChild(media);
      const controller = new LessonPlaybackController(player, { post_id: 1 }, createEnvironment());
      controller.start();

      media.dispatchEvent(new Event('fullscreenchange'));

      expect(resync).toHaveBeenCalledTimes(1);
      controller.destroy();
    } finally {
      document.removeEventListener('webkitfullscreenchange', resync);
    }
  });

  it('skips the re-sync when already exited or still fullscreen', () => {
    Object.defineProperty(document, 'fullscreenElement', { configurable: true, value: null });
    const resync = vi.fn();
    document.addEventListener('webkitfullscreenchange', resync);

    try {
      const { player } = createFakePlayer({ isFullscreen: vi.fn(() => false) });
      const media = document.createElement('vimeo-video');
      player.element.appendChild(media);
      const controller = new LessonPlaybackController(player, { post_id: 1 }, createEnvironment());
      controller.start();

      media.dispatchEvent(new Event('fullscreenchange'));

      expect(resync).not.toHaveBeenCalled();
      controller.destroy();
    } finally {
      document.removeEventListener('webkitfullscreenchange', resync);
    }
  });

  it('stops observing the media echo after destroy', () => {
    Object.defineProperty(document, 'fullscreenElement', { configurable: true, value: null });
    const resync = vi.fn();
    document.addEventListener('webkitfullscreenchange', resync);

    try {
      const { player } = createFakePlayer({ isFullscreen: vi.fn(() => true) });
      const media = document.createElement('vimeo-video');
      player.element.appendChild(media);
      const controller = new LessonPlaybackController(player, { post_id: 1 }, createEnvironment());
      controller.start();
      controller.destroy();

      media.dispatchEvent(new Event('fullscreenchange'));

      expect(resync).not.toHaveBeenCalled();
    } finally {
      document.removeEventListener('webkitfullscreenchange', resync);
    }
  });
});

describe('LessonPlaybackController auto-next', () => {
  it('loads the next url after playing once and ending', async () => {
    const { player, emit } = createFakePlayer();
    const env = createEnvironment({ autoPlayNext: true, fetchNextUrl: vi.fn(async () => 'https://next.example') });
    const controller = new LessonPlaybackController(player, { post_id: 5 }, env);
    controller.start();

    emit('play');
    emit('ended');

    await vi.waitFor(() => expect(env.openUrl).toHaveBeenCalledWith('https://next.example'));
  });

  it('does not load the next url when the video never played', () => {
    const { player, emit } = createFakePlayer();
    const env = createEnvironment({ autoPlayNext: true });
    const controller = new LessonPlaybackController(player, { post_id: 5 }, env);
    controller.start();

    emit('ended');

    expect(env.fetchNextUrl).not.toHaveBeenCalled();
  });
});
