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

  it('replays ready to a subscriber that arrives after the first sync', async () => {
    const { store, emit, set } = createStore();
    const player = createTutorPlayer({ element: element(), store, provider: 'html5' });

    set({ target: { media: document.createElement('video') } });
    emit();
    await Promise.resolve();

    const onReady = vi.fn();
    player.on('ready', onReady);
    await Promise.resolve();

    expect(onReady).toHaveBeenCalledWith(expect.objectContaining({ player }));
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
    const onEnter = vi.fn();
    const onExit = vi.fn();
    player.on('fullscreenchange', onChange);
    player.on('enterfullscreen', onEnter);
    player.on('exitfullscreen', onExit);

    Object.defineProperty(document, 'fullscreenElement', { configurable: true, value: player.element });
    document.dispatchEvent(new Event('fullscreenchange'));

    expect(onChange).toHaveBeenCalledWith(expect.objectContaining({ fullscreen: true, element: player.element }));
    expect(onEnter).toHaveBeenCalledWith(expect.objectContaining({ srcElement: player.element }));
    expect(onExit).not.toHaveBeenCalled();

    Object.defineProperty(document, 'fullscreenElement', { configurable: true, value: null });
    document.dispatchEvent(new Event('fullscreenchange'));

    expect(onChange).toHaveBeenCalledWith(expect.objectContaining({ fullscreen: false, element: player.element }));
    expect(onExit).toHaveBeenCalledWith(expect.objectContaining({ srcElement: player.element }));
  });

  it('ignores fullscreen changes that belong to another element', () => {
    const { store } = createStore();
    const player = createTutorPlayer({ element: element(), store, provider: 'html5' });
    const onChange = vi.fn();
    const onEnter = vi.fn();
    const onExit = vi.fn();
    player.on('fullscreenchange', onChange);
    player.on('enterfullscreen', onEnter);
    player.on('exitfullscreen', onExit);

    Object.defineProperty(document, 'fullscreenElement', { configurable: true, value: document.createElement('div') });
    document.dispatchEvent(new Event('fullscreenchange'));

    expect(onChange).not.toHaveBeenCalled();
    expect(onEnter).not.toHaveBeenCalled();

    Object.defineProperty(document, 'fullscreenElement', { configurable: true, value: null });
    document.dispatchEvent(new Event('fullscreenchange'));

    expect(onChange).not.toHaveBeenCalled();
    expect(onExit).not.toHaveBeenCalled();
  });

  it('treats a fullscreen ancestor of the player as its own', () => {
    const wrapper = document.createElement('div');
    const host = element();
    wrapper.appendChild(host);
    const { store } = createStore();
    const player = createTutorPlayer({ element: host, store, provider: 'html5' });
    const onEnter = vi.fn();
    player.on('enterfullscreen', onEnter);

    Object.defineProperty(document, 'fullscreenElement', { configurable: true, value: wrapper });
    document.dispatchEvent(new Event('fullscreenchange'));

    expect(onEnter).toHaveBeenCalledWith(expect.objectContaining({ srcElement: wrapper }));
  });

  it('reads fullscreen state from the store', () => {
    const { store, set } = createStore();
    const player = createTutorPlayer({ element: element(), store, provider: 'html5' });

    expect(player.isFullscreen()).toBe(false);

    set({ isFullscreen: true });

    expect(player.isFullscreen()).toBe(true);
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

  it('invokes onDispose once when the handle is disposed', () => {
    const { store } = createStore();
    const onDispose = vi.fn();
    const player = createTutorPlayer({ element: element(), store, provider: 'html5', onDispose });

    player.dispose();
    player.dispose();

    expect(onDispose).toHaveBeenCalledTimes(1);
  });
});

afterEach(() => {
  document.body.innerHTML = '';
});
