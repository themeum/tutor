import { afterEach, describe, expect, it, vi } from 'vitest';

import { createTutorPlayer, type PlayerStore } from './Player';

function createStore(overrides: Partial<PlayerStore> = {}) {
  const listeners = new Set<() => void>();

  const store = {
    currentTime: 0,
    duration: 100,
    paused: true,
    ended: false,
    volume: 1,
    muted: false,
    isFullscreen: false,
    error: null as Error | null,
    target: null,
    play: vi.fn(async () => undefined),
    pause: vi.fn(),
    seek: vi.fn((time: number) => time),
    setVolume: vi.fn((volume: number) => volume),
    setMuted: vi.fn((muted: boolean) => muted),
    subscribe: (callback: () => void) => {
      listeners.add(callback);
      return () => listeners.delete(callback);
    },
    destroy: vi.fn(),
    ...overrides,
  } as unknown as PlayerStore;

  return {
    store,
    emit: () => listeners.forEach((listener) => listener()),
    set: (patch: Partial<PlayerStore>) => Object.assign(store, patch),
  };
}

const element = () => document.createElement('tutor-video-player');

afterEach(() => {
  Object.defineProperty(document, 'fullscreenElement', { configurable: true, value: null });
});

describe('createTutorPlayer', () => {
  it('emits ready once media attaches', async () => {
    const { store, emit, set } = createStore();
    const player = createTutorPlayer({ element: element(), store, provider: 'html5' });
    const onReady = vi.fn();
    player.on('ready', onReady);

    set({ target: { media: document.createElement('video') } });
    emit();
    await Promise.resolve();

    expect(onReady).toHaveBeenCalledTimes(1);
  });

  it('emits play/pause on paused transitions', async () => {
    const { store, emit, set } = createStore();
    const player = createTutorPlayer({ element: element(), store, provider: 'html5' });
    await Promise.resolve();

    const onPlay = vi.fn();
    const onPause = vi.fn();
    player.on('play', onPlay);
    player.on('pause', onPause);

    set({ paused: false });
    emit();
    expect(onPlay).toHaveBeenCalledTimes(1);
    expect(onPause).not.toHaveBeenCalled();

    set({ paused: true });
    emit();
    expect(onPause).toHaveBeenCalledTimes(1);
    expect(onPlay).toHaveBeenCalledTimes(1);
  });

  it('emits timeupdate when the current time changes', async () => {
    const { store, emit, set } = createStore();
    const player = createTutorPlayer({ element: element(), store, provider: 'html5' });
    await Promise.resolve();

    const onTime = vi.fn();
    player.on('timeupdate', onTime);

    set({ currentTime: 12 });
    emit();

    expect(onTime).toHaveBeenCalledWith(expect.objectContaining({ currentTime: 12, duration: 100 }));
  });

  it('clamps seek to the media duration and volume to [0, 1]', () => {
    const { store } = createStore({ duration: 60 });
    const player = createTutorPlayer({ element: element(), store, provider: 'html5' });

    player.seek(120);
    expect(store.seek).toHaveBeenLastCalledWith(60);

    player.seek(-5);
    expect(store.seek).toHaveBeenLastCalledWith(0);

    player.setVolume(2);
    expect(store.setVolume).toHaveBeenLastCalledWith(1);

    player.setVolume(-1);
    expect(store.setVolume).toHaveBeenLastCalledWith(0);
  });

  it('emits fullscreenchange from the document event', () => {
    const { store } = createStore();
    const player = createTutorPlayer({ element: element(), store, provider: 'html5' });
    const onChange = vi.fn();
    player.on('fullscreenchange', onChange);

    Object.defineProperty(document, 'fullscreenElement', { configurable: true, value: player.element });
    document.dispatchEvent(new Event('fullscreenchange'));

    expect(onChange).toHaveBeenCalledWith(expect.objectContaining({ fullscreen: true, element: player.element }));
  });

  it('returns an unsubscribe from on()', () => {
    const { store, emit, set } = createStore();
    const player = createTutorPlayer({ element: element(), store, provider: 'html5' });
    const onPlay = vi.fn();

    const off = player.on('play', onPlay);
    off();

    set({ paused: false });
    emit();

    expect(onPlay).not.toHaveBeenCalled();
  });

  it('dispose detaches the store and removes the element', () => {
    const { store, emit, set } = createStore();
    const host = element();
    document.body.appendChild(host);

    const player = createTutorPlayer({ element: host, store, provider: 'html5' });
    const onPlay = vi.fn();
    player.on('play', onPlay);

    player.dispose();

    expect(store.destroy).toHaveBeenCalledTimes(1);
    expect(host.isConnected).toBe(false);

    set({ paused: false });
    emit();

    expect(onPlay).not.toHaveBeenCalled();
  });
});

afterEach(() => {
  document.body.innerHTML = '';
});
