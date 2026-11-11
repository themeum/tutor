import { VimeoAdapter } from '@videojs/vimeo-video';
import { describe, expect, it, vi } from 'vitest';

import { hookVimeoPlayer, patchVimeoProvider } from './vimeoPatch';

describe('vimeoPatch', () => {
  it('suppresses non-fatal seek and abort errors on vimeo player', () => {
    let errorHandler: ((err: unknown) => void) | undefined;

    const player = {
      off: vi.fn(),
      on: vi.fn((event: string, cb: (err: unknown) => void) => {
        if (event === 'error') {
          errorHandler = cb;
        }
      }),
      setCurrentTime: vi.fn().mockResolvedValue(10),
    };

    const dispatchEvent = vi.fn();
    const adapter = {
      engine: player,
      dispatchEvent,
    };

    hookVimeoPlayer(adapter);

    expect(player.off).toHaveBeenCalledWith('error');
    expect(player.on).toHaveBeenCalledWith('error', expect.any(Function));
    expect(errorHandler).toBeDefined();

    // 1. Seek abort error from Vimeo iframe
    errorHandler?.({
      method: 'setCurrentTime',
      name: 'AbortError',
      message: 'The seek request was aborted',
    });
    expect(dispatchEvent).not.toHaveBeenCalled();

    // 2. RangeError from seeking past duration
    errorHandler?.({
      method: 'setCurrentTime',
      name: 'RangeError',
      message: 'the time was less than 0 or greater than the videos duration',
    });
    expect(dispatchEvent).not.toHaveBeenCalled();

    // 3. Generic seek failure
    errorHandler?.({
      name: 'Error',
      message: 'Seek was aborted by a subsequent seek',
    });
    expect(dispatchEvent).not.toHaveBeenCalled();

    // 4. Real fatal playback error (e.g. private video)
    errorHandler?.({
      name: 'PrivacyError',
      message: 'Because of its privacy settings, this video cannot be played here',
    });
    expect(dispatchEvent).toHaveBeenCalledTimes(1);
    expect(dispatchEvent).toHaveBeenCalledWith(expect.objectContaining({ type: 'error' }));
  });

  it('serializes rapid setCurrentTime calls without overlapping', async () => {
    let resolveFirst: (time: number) => void = () => undefined;
    const firstPromise = new Promise<number>((res) => {
      resolveFirst = res;
    });

    const secondPromise = Promise.resolve(30);

    const originalSetCurrentTime = vi
      .fn()
      .mockImplementationOnce(() => firstPromise)
      .mockImplementationOnce(() => secondPromise);

    const player = {
      off: vi.fn(),
      on: vi.fn(),
      setCurrentTime: originalSetCurrentTime,
    };

    const adapter = {
      engine: player,
      dispatchEvent: vi.fn(),
    };

    hookVimeoPlayer(adapter);

    // Call seek 1: starts immediately
    const seek1 = player.setCurrentTime(10);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(1);
    expect(originalSetCurrentTime).toHaveBeenLastCalledWith(10);

    // Call seek 2 & 3 while seek 1 is still in flight:
    // They should queue without calling originalSetCurrentTime yet
    const seek2 = player.setCurrentTime(20);
    const seek3 = player.setCurrentTime(30);

    expect(originalSetCurrentTime).toHaveBeenCalledTimes(1);

    // Now resolve seek 1
    resolveFirst(10);
    await seek1;

    // After seek 1 settles, only the latest seek (30) should be called!
    await seek2;
    await seek3;

    expect(originalSetCurrentTime).toHaveBeenCalledTimes(2);
    expect(originalSetCurrentTime).toHaveBeenLastCalledWith(30);
  });

  it('clamps and validates currentTime on VimeoAdapter prototype', () => {
    patchVimeoProvider();

    const adapter = new VimeoAdapter();

    // Attach dummy duration getter
    Object.defineProperty(adapter, 'duration', {
      value: 50,
      configurable: true,
    });

    adapter.currentTime = 75;
    expect(adapter.currentTime).toBe(50);

    adapter.currentTime = -10;
    expect(adapter.currentTime).toBe(0);

    adapter.currentTime = NaN;
    expect(adapter.currentTime).toBe(0);
  });
});
