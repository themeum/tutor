import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import { type PlayerEventMap, type TutorPlayer } from '@Core/ts/types/player';

import { createLegacyTutorLessonPlayer, type LegacyFullscreenEvent } from './legacy';

interface FakePlayer {
  player: TutorPlayer;
  emitFullscreen: (payload: PlayerEventMap['fullscreenchange']) => void;
}

function fakePlayer(): FakePlayer {
  let fullscreenHandler: ((payload: PlayerEventMap['fullscreenchange']) => void) | null = null;

  const player: TutorPlayer = {
    provider: 'html5',
    element: document.createElement('div'),
    media: null,
    getCurrentTime: vi.fn(() => 12.5),
    getDuration: vi.fn(() => 100),
    isPaused: vi.fn(() => false),
    getVolume: vi.fn(() => 1),
    setVolume: vi.fn(),
    isMuted: vi.fn(() => false),
    setMuted: vi.fn(),
    isFullscreen: vi.fn(() => false),
    play: vi.fn(async () => undefined),
    pause: vi.fn(),
    seek: vi.fn(),
    on: vi.fn((event, handler) => {
      if (event === 'fullscreenchange') {
        fullscreenHandler = handler;
      }
      return () => {
        fullscreenHandler = null;
      };
    }),
    dispose: vi.fn(),
  };

  return {
    player,
    emitFullscreen: (payload) => fullscreenHandler?.(payload),
  };
}

function useActivePlayer(player: TutorPlayer | null): void {
  window.TutorCore = Object.assign(window.TutorCore || {}, {
    player: { getActive: () => player },
  });
}

describe('createLegacyTutorLessonPlayer', () => {
  beforeEach(() => {
    vi.spyOn(console, 'warn').mockImplementation(() => undefined);
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  it('reads and seeks through the active player', () => {
    const { player } = fakePlayer();
    useActivePlayer(player);
    const legacy = createLegacyTutorLessonPlayer();

    expect(legacy.currentTime).toBe(12.5);

    legacy.currentTime = 42;
    expect(player.seek).toHaveBeenCalledWith(42);

    legacy.play();
    expect(player.play).toHaveBeenCalled();
  });

  it('re-emits fullscreen events with srcElement', () => {
    const { player, emitFullscreen } = fakePlayer();
    useActivePlayer(player);
    const legacy = createLegacyTutorLessonPlayer();

    const onEnter = vi.fn<(event: LegacyFullscreenEvent) => void>();
    const onExit = vi.fn<(event: LegacyFullscreenEvent) => void>();
    legacy.on('enterfullscreen', onEnter);
    legacy.on('exitfullscreen', onExit);

    emitFullscreen({ player, fullscreen: true, element: player.element });
    expect(onEnter).toHaveBeenCalledWith({ srcElement: player.element });
    expect(onExit).not.toHaveBeenCalled();

    emitFullscreen({ player, fullscreen: false, element: player.element });
    expect(onExit).toHaveBeenCalledWith({ srcElement: player.element });
  });

  it('guards against a missing active player', () => {
    useActivePlayer(null);
    const legacy = createLegacyTutorLessonPlayer();

    expect(legacy.currentTime).toBe(0);
    expect(() => {
      legacy.currentTime = 10;
      legacy.play();
    }).not.toThrow();
  });
});
