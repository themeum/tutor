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

  it('retries a seek the vimeo embed silently drops before it is ready', async () => {
    vi.useFakeTimers();

    const dropped = new Promise<number>(() => undefined);
    const originalSetCurrentTime = vi
      .fn()
      .mockImplementationOnce(() => dropped)
      .mockResolvedValueOnce(15);

    const player = {
      off: vi.fn(),
      on: vi.fn(),
      setCurrentTime: originalSetCurrentTime,
    };

    hookVimeoPlayer({ engine: player, dispatchEvent: vi.fn() });

    const seek = player.setCurrentTime(15);

    // A dropped seek never settles and is never acknowledged with `seeking`.
    await vi.advanceTimersByTimeAsync(1499);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(1);

    await vi.advanceTimersByTimeAsync(1);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(2);
    expect(originalSetCurrentTime).toHaveBeenLastCalledWith(15);
    await expect(seek).resolves.toBe(15);

    vi.useRealTimers();
  });

  it('re-applies a seek that the pending loadVideo reset to 0', async () => {
    vi.useFakeTimers();

    const originalSetCurrentTime = vi.fn().mockResolvedValue(10);
    const getCurrentTime = vi.fn().mockResolvedValue(0);

    const player = {
      off: vi.fn(),
      on: vi.fn(),
      setCurrentTime: originalSetCurrentTime,
      getCurrentTime,
    };

    let onSeeking: (() => void) | undefined;
    const adapter = {
      engine: player,
      dispatchEvent: vi.fn(),
      addEventListener: vi.fn((type: string, callback: () => void) => {
        if (type === 'seeking') {
          onSeeking = callback;
        }
      }),
      removeEventListener: vi.fn(),
    };

    hookVimeoPlayer(adapter);

    const seek = player.setCurrentTime(10);
    onSeeking?.();
    await vi.advanceTimersByTimeAsync(0);
    await expect(seek).resolves.toBe(10);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(1);

    // The embed's pending loadVideo reset the player after the acknowledged seek.
    await vi.advanceTimersByTimeAsync(1000);
    expect(getCurrentTime).toHaveBeenCalledTimes(1);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(2);
    expect(originalSetCurrentTime).toHaveBeenLastCalledWith(10);

    vi.useRealTimers();
  });

  it('holds the seek lock after a timeout until the SDK call settles', async () => {
    vi.useFakeTimers();

    let resolveFirst: (time: number) => void = () => undefined;
    const firstPromise = new Promise<number>((resolve) => {
      resolveFirst = resolve;
    });

    const originalSetCurrentTime = vi
      .fn()
      .mockImplementationOnce(() => firstPromise)
      .mockResolvedValue(30);

    const player = {
      off: vi.fn(),
      on: vi.fn(),
      setCurrentTime: originalSetCurrentTime,
    };

    let onSeeking: (() => void) | undefined;
    const adapter = {
      engine: player,
      dispatchEvent: vi.fn(),
      addEventListener: vi.fn((type: string, callback: () => void) => {
        if (type === 'seeking') {
          onSeeking = callback;
        }
      }),
      removeEventListener: vi.fn(),
    };

    hookVimeoPlayer(adapter);

    const seek1 = player.setCurrentTime(10);

    // Vimeo acknowledged the seek; no retry may overlap the pending call.
    onSeeking?.();

    // The caller stops waiting at the timeout...
    await vi.advanceTimersByTimeAsync(4000);
    await expect(seek1).resolves.toBe(10);

    // ...but the next seek still queues instead of overlapping the pending postMessage.
    const seek2 = player.setCurrentTime(30);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(1);

    resolveFirst(10);
    await vi.advanceTimersByTimeAsync(0);

    await expect(seek2).resolves.toBe(30);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(2);
    expect(originalSetCurrentTime).toHaveBeenLastCalledWith(30);

    vi.useRealTimers();
  });

  it('releases the seek lock at the hard cap when the SDK never settles', async () => {
    vi.useFakeTimers();

    const never = new Promise<number>(() => undefined);
    const originalSetCurrentTime = vi
      .fn()
      .mockImplementationOnce(() => never)
      .mockResolvedValue(42);

    const player = {
      off: vi.fn(),
      on: vi.fn(),
      setCurrentTime: originalSetCurrentTime,
    };

    let onSeeking: (() => void) | undefined;
    const adapter = {
      engine: player,
      dispatchEvent: vi.fn(),
      addEventListener: vi.fn((type: string, callback: () => void) => {
        if (type === 'seeking') {
          onSeeking = callback;
        }
      }),
      removeEventListener: vi.fn(),
    };

    hookVimeoPlayer(adapter);

    const seek1 = player.setCurrentTime(10);
    onSeeking?.();
    await vi.advanceTimersByTimeAsync(4000);
    await expect(seek1).resolves.toBe(10);

    const seek2 = player.setCurrentTime(20);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(1);

    // 8s after the abandoned call started, the lock opens and the queued target runs.
    await vi.advanceTimersByTimeAsync(4000);
    await expect(seek2).resolves.toBe(42);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(2);
    expect(originalSetCurrentTime).toHaveBeenLastCalledWith(20);

    vi.useRealTimers();
  });

  it('releases the seek lock as soon as the adapter reports seeked', async () => {
    const never = new Promise<number>(() => undefined);
    const originalSetCurrentTime = vi
      .fn()
      .mockImplementationOnce(() => never)
      .mockResolvedValue(7);
    let onSeeked: (() => void) | undefined;

    const player = {
      off: vi.fn(),
      on: vi.fn(),
      setCurrentTime: originalSetCurrentTime,
    };

    const adapter = {
      engine: player,
      dispatchEvent: vi.fn(),
      addEventListener: vi.fn((type: string, callback: () => void) => {
        if (type === 'seeked') {
          onSeeked = callback;
        }
      }),
      removeEventListener: vi.fn(),
    };

    hookVimeoPlayer(adapter);

    void player.setCurrentTime(10);
    const seek2 = player.setCurrentTime(20);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(1);

    // The iframe finished the first seek even though its SDK promise never settles.
    onSeeked?.();

    await expect(seek2).resolves.toBe(7);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(2);
    expect(originalSetCurrentTime).toHaveBeenLastCalledWith(20);
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
