import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { type PlayerEventMap, type TutorPlayer } from '@Core/ts/types/player';

import { createLegacyTutorLessonPlayer, type LegacyFullscreenEvent } from './legacy';

interface FakeEngine {
  player: TutorPlayer;
  on: ReturnType<typeof vi.fn>;
  emitFullscreen: (payload: PlayerEventMap['fullscreenchange']) => void;
}

function fakeEngine(): FakeEngine {
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
    on: vi.fn(() => () => undefined),
    dispose: vi.fn(),
  };

  const on = vi.fn((event: string, handler: (payload: PlayerEventMap['fullscreenchange']) => void) => {
    if (event === 'fullscreenchange') {
      fullscreenHandler = handler;
    }
    return () => {
      fullscreenHandler = null;
    };
  });

  return {
    player,
    on,
    emitFullscreen: (payload) => fullscreenHandler?.(payload),
  };
}

function useEngine(engine: FakeEngine | null): void {
  window.TutorCore = Object.assign(window.TutorCore || {}, {
    player: engine ? { getActive: () => engine.player, on: engine.on } : undefined,
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
    const engine = fakeEngine();
    useEngine(engine);
    const legacy = createLegacyTutorLessonPlayer();

    expect(legacy.currentTime).toBe(12.5);

    legacy.currentTime = 42;
    expect(engine.player.seek).toHaveBeenCalledWith(42);

    legacy.play();
    expect(engine.player.play).toHaveBeenCalled();
  });

  it('re-emits fullscreen events with srcElement', () => {
    const engine = fakeEngine();
    useEngine(engine);
    const legacy = createLegacyTutorLessonPlayer();

    const onEnter = vi.fn<(event: LegacyFullscreenEvent) => void>();
    const onExit = vi.fn<(event: LegacyFullscreenEvent) => void>();
    legacy.on('enterfullscreen', onEnter);
    legacy.on('exitfullscreen', onExit);

    expect(engine.on).toHaveBeenCalledWith('fullscreenchange', expect.any(Function));

    engine.emitFullscreen({ player: engine.player, fullscreen: true, element: engine.player.element });
    expect(onEnter).toHaveBeenCalledWith({ srcElement: engine.player.element });
    expect(onExit).not.toHaveBeenCalled();

    engine.emitFullscreen({ player: engine.player, fullscreen: false, element: engine.player.element });
    expect(onExit).toHaveBeenCalledWith({ srcElement: engine.player.element });
  });

  it('subscribes once the engine announces itself', () => {
    useEngine(null);
    const legacy = createLegacyTutorLessonPlayer();

    const onEnter = vi.fn();
    legacy.on('enterfullscreen', onEnter);

    const engine = fakeEngine();
    useEngine(engine);
    window.dispatchEvent(new CustomEvent(TUTOR_CUSTOM_EVENTS.PLAYER_ENGINE_READY));

    expect(engine.on).toHaveBeenCalledWith('fullscreenchange', expect.any(Function));

    engine.emitFullscreen({ player: engine.player, fullscreen: true, element: engine.player.element });
    expect(onEnter).toHaveBeenCalledWith({ srcElement: engine.player.element });
  });

  it('guards against a missing active player', () => {
    window.TutorCore = Object.assign(window.TutorCore || {}, {
      player: { getActive: () => null, on: vi.fn(() => () => undefined) },
    });
    const legacy = createLegacyTutorLessonPlayer();

    expect(legacy.currentTime).toBe(0);
    expect(() => {
      legacy.currentTime = 10;
      legacy.play();
    }).not.toThrow();
  });
});
