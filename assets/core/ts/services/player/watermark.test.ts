import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import type { TutorPlayer } from '@Core/ts/types/player';

import { initPlayerWatermark, WATERMARK_POSITIONS, WatermarkController } from './watermark';

describe('WatermarkController', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  it('initializes with pos-top-left and is-active classes', () => {
    const el = document.createElement('div');
    el.className = 'tutor-video-watermark';

    const controller = new WatermarkController({ element: el });
    expect(el.classList.contains('pos-top-left')).toBe(true);
    expect(el.classList.contains('is-active')).toBe(true);
    expect(controller.currentPosition).toBe('pos-top-left');

    controller.dispose();
  });

  it('jumps to a different position after interval', () => {
    const el = document.createElement('div');
    el.className = 'tutor-video-watermark';

    const controller = new WatermarkController({ element: el, intervalMs: 1000, fadeMs: 100 });

    vi.advanceTimersByTime(1000);
    expect(el.classList.contains('is-fading')).toBe(true);

    vi.advanceTimersByTime(100);
    expect(el.classList.contains('is-fading')).toBe(false);
    expect(WATERMARK_POSITIONS).toContain(controller.currentPosition);
    expect(el.classList.contains(controller.currentPosition)).toBe(true);

    controller.dispose();
  });

  it('pauses and resumes with player events', () => {
    const el = document.createElement('div');
    el.className = 'tutor-video-watermark';

    const handlers = new Map<string, () => void>();
    const fakePlayer = {
      on: (event: string, fn: () => void) => handlers.set(event, fn),
    };

    const controller = new WatermarkController({
      element: el,
      player: fakePlayer as unknown as TutorPlayer,
      intervalMs: 1000,
    });

    const jumpSpy = vi.spyOn(controller, 'jumpToNextPosition');

    // Pause player
    handlers.get('pause')?.();
    vi.advanceTimersByTime(2000);
    expect(jumpSpy).not.toHaveBeenCalled();

    // Play player
    handlers.get('play')?.();
    vi.advanceTimersByTime(1000);
    expect(jumpSpy).toHaveBeenCalled();

    controller.dispose();
  });

  it('initPlayerWatermark discovers and initializes element', () => {
    const container = document.createElement('div');
    const el = document.createElement('div');
    el.className = 'tutor-video-watermark';
    container.appendChild(el);

    const controller = initPlayerWatermark(container);
    expect(controller).not.toBeNull();
    expect(el.classList.contains('is-active')).toBe(true);

    // Idempotent
    expect(initPlayerWatermark(container)).toBe(controller);

    controller?.dispose();
  });
});
