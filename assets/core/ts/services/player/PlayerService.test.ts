import { afterEach, describe, expect, it, vi } from 'vitest';

import { type TutorPlayer } from '@Core/ts/types/player';

import { type VideoPlayerElement } from './elements';
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
  // As a connected element would expose it after self-boot.
  host.player = handle;

  const service = new TutorPlayerService({
    createElement: () => host,
  });

  return { service, handle, host };
}

function target(): HTMLElement {
  document.body.innerHTML =
    '<div class="tutor-video-player"><video class="tutorPlayer" src="https://lesson.test/wp-content/uploads/a.mp4"></video></div>';
  return document.querySelector('.tutorPlayer') as HTMLElement;
}

describe('TutorPlayerService', () => {
  it('returns the handle booted by the built element and tracks it as active', () => {
    const { service, handle } = setup();

    expect(service.create(target())).toBe(handle);
    expect(service.getActive()).toBe(handle);
    expect(service.getCurrentTime()).toBe(5);
  });

  it('adopts the handle of an element that booted itself', () => {
    const { service, handle, host } = setup();
    document.body.appendChild(host);

    expect(service.create(host)).toBe(handle);
    expect(service.getActive()).toBe(handle);
    // The declarative subtree is kept as-is: no replacement host was built.
    expect(document.body.contains(host)).toBe(true);
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

  it('binds on() to the next player when none exists yet', () => {
    const { service, handle } = setup();
    const onPlay = vi.fn();

    service.on('play', onPlay);
    expect(handle.on).not.toHaveBeenCalled();

    service.create(target());

    expect(handle.on).toHaveBeenCalledWith('play', onPlay);
  });

  it('drops a queued handler removed before a player appears', () => {
    const { service, handle } = setup();
    const off = service.on('play', vi.fn());

    off();
    service.create(target());

    expect(handle.on).not.toHaveBeenCalled();
  });

  it('clears the active player when it is untracked', () => {
    const { service, handle } = setup();
    service.create(target());

    service.untrack(handle);

    expect(service.getActive()).toBeNull();
    expect(service.getCurrentTime()).toBe(0);
  });

  it('throws when no source can be detected', () => {
    const { service } = setup();
    document.body.innerHTML = '<div class="tutor-video-player"></div>';
    const empty = document.querySelector('.tutor-video-player') as HTMLElement;

    expect(() => service.create(empty)).toThrow();
  });

  it('throws when the built element does not boot a handle', () => {
    const service = new TutorPlayerService({
      createElement: () => document.createElement('div') as unknown as VideoPlayerElement,
    });

    expect(() => service.create(target())).toThrow();
  });
});

afterEach(() => {
  document.body.innerHTML = '';
});
