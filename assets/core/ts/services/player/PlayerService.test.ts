import { afterEach, describe, expect, it, vi } from 'vitest';

import { type TutorPlayer } from '@Core/ts/types/player';

import { type VideoPlayerElement } from './elements';
import { type PlayerStore } from './Player';
import { TutorPlayerService } from './PlayerService';

function fakePlayer(): TutorPlayer {
  return {
    provider: 'html5',
    element: document.createElement('div'),
    media: null,
    getCurrentTime: vi.fn(() => 5),
    getDuration: vi.fn(() => 50),
    isPaused: vi.fn(() => true),
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
}

function setup() {
  const handle = fakePlayer();
  const host = document.createElement('div') as unknown as VideoPlayerElement;
  (host as unknown as { store: PlayerStore }).store = {} as PlayerStore;

  const service = new TutorPlayerService({
    createHandle: () => handle,
    createElement: () => host,
    getStore: (element) => element.store ?? null,
  });

  return { service, handle, host };
}

function target(): HTMLElement {
  document.body.innerHTML =
    '<div class="tutor-video-player"><video class="tutorPlayer" src="https://lesson.test/wp-content/uploads/a.mp4"></video></div>';
  return document.querySelector('.tutorPlayer') as HTMLElement;
}

describe('TutorPlayerService', () => {
  it('tracks the active player created from a target', () => {
    const { service, handle } = setup();

    expect(service.create(target())).toBe(handle);
    expect(service.getActive()).toBe(handle);
    expect(service.active).toBe(handle);
    expect(service.getCurrentTime()).toBe(5);
  });

  it('proxies calls to the active player', () => {
    const { service, handle } = setup();
    service.create(target());

    service.seek(10);

    expect(handle.seek).toHaveBeenCalledWith(10);
  });

  it('replaces the target element with the built player', () => {
    const { service, host } = setup();
    const element = target();

    service.create(element);

    expect(element.isConnected).toBe(false);
    expect(document.body.contains(host)).toBe(true);
  });

  it('returns a noop unsubscribe and zeroed time when there is no active player', () => {
    const { service } = setup();

    expect(typeof service.on('play', () => undefined)).toBe('function');
    expect(service.getCurrentTime()).toBe(0);
  });

  it('throws when no source can be detected', () => {
    const { service } = setup();
    document.body.innerHTML = '<div class="tutor-video-player"></div>';
    const empty = document.querySelector('.tutor-video-player') as HTMLElement;

    expect(() => service.create(empty)).toThrow();
  });
});

afterEach(() => {
  document.body.innerHTML = '';
});
