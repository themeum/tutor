import { VimeoAdapter } from '@videojs/vimeo-video';
import { describe, expect, it, vi } from 'vitest';

import { hookVimeoPlayer, patchVimeoProvider } from './vimeoPatch';

const makePlayer = <T extends Record<string, unknown>>(overrides: T) => ({
  off: vi.fn(),
  on: vi.fn(),
  ...overrides,
});

describe('vimeoPatch', () => {
  it('treats only RangeError as non-fatal on the vimeo player', () => {
    let errorHandler: ((err: unknown) => void) | undefined;

    const player = makePlayer({
      on: vi.fn((event: string, cb: (err: unknown) => void) => {
        if (event === 'error') {
          errorHandler = cb;
        }
      }),
      setCurrentTime: vi.fn().mockResolvedValue(10),
    });

    const dispatchEvent = vi.fn();
    const adapter = {
      engine: player,
      dispatchEvent,
    };

    hookVimeoPlayer(adapter);

    expect(player.off).toHaveBeenCalledWith('error');
    expect(player.on).toHaveBeenCalledWith('error', expect.any(Function));
    expect(errorHandler).toBeDefined();

    // 1. Seek abort error from Vimeo iframe currently reaches the fatal handler.
    errorHandler?.({
      method: 'setCurrentTime',
      name: 'AbortError',
      message: 'The seek request was aborted',
    });
    expect(dispatchEvent).toHaveBeenCalledTimes(1);

    // 2. RangeError from seeking past duration is the one ignored error.
    errorHandler?.({
      method: 'setCurrentTime',
      name: 'RangeError',
      message: 'the time was less than 0 or greater than the videos duration',
    });
    expect(dispatchEvent).toHaveBeenCalledTimes(1);

    // 3. Generic seek failure also reaches the fatal handler.
    errorHandler?.({
      name: 'Error',
      message: 'Seek was aborted by a subsequent seek',
    });
    expect(dispatchEvent).toHaveBeenCalledTimes(2);

    // 4. Real fatal playback error (e.g. private video)
    errorHandler?.({
      name: 'PrivacyError',
      message: 'Because of its privacy settings, this video cannot be played here',
    });
    expect(dispatchEvent).toHaveBeenCalledTimes(3);
    expect(dispatchEvent).toHaveBeenLastCalledWith(expect.objectContaining({ type: 'error' }));
  });

  it('sends each seek immediately and verifies only the latest target', async () => {
    vi.useFakeTimers();

    const originalSetCurrentTime = vi.fn().mockResolvedValue(0);
    const getCurrentTime = vi.fn().mockResolvedValue(0);
    const player = makePlayer({
      setCurrentTime: originalSetCurrentTime,
      getCurrentTime,
    });

    hookVimeoPlayer({ engine: player, dispatchEvent: vi.fn() });

    void player.setCurrentTime(10);
    void player.setCurrentTime(20);
    expect(originalSetCurrentTime.mock.calls.map(([time]) => time)).toEqual([10, 20]);

    // The dropped final seek is re-applied; the abandoned 10 is not.
    await vi.advanceTimersByTimeAsync(1000);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(3);
    expect(originalSetCurrentTime).toHaveBeenLastCalledWith(20);

    vi.useRealTimers();
  });

  it('re-applies a dropped seek until the player reaches the target', async () => {
    vi.useFakeTimers();

    const originalSetCurrentTime = vi.fn().mockResolvedValue(15);
    const getCurrentTime = vi.fn().mockResolvedValueOnce(0).mockResolvedValue(15);
    const player = makePlayer({
      setCurrentTime: originalSetCurrentTime,
      getCurrentTime,
    });

    hookVimeoPlayer({ engine: player, dispatchEvent: vi.fn() });

    const seek = player.setCurrentTime(15);
    await expect(seek).resolves.toBe(15);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(1);

    await vi.advanceTimersByTimeAsync(1000);
    expect(getCurrentTime).toHaveBeenCalledTimes(1);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(2);
    expect(originalSetCurrentTime).toHaveBeenLastCalledWith(15);

    // The position sticks now, so no further re-apply is sent.
    await vi.advanceTimersByTimeAsync(1000);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(2);

    vi.useRealTimers();
  });

  it('re-applies a seek that the pending loadVideo reset to 0', async () => {
    vi.useFakeTimers();

    const originalSetCurrentTime = vi.fn().mockResolvedValue(10);
    const getCurrentTime = vi.fn().mockResolvedValueOnce(10).mockResolvedValueOnce(0).mockResolvedValue(10);
    const player = makePlayer({
      setCurrentTime: originalSetCurrentTime,
      getCurrentTime,
    });

    hookVimeoPlayer({ engine: player, dispatchEvent: vi.fn() });

    const seek = player.setCurrentTime(10);
    await expect(seek).resolves.toBe(10);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(1);

    // First check sees the seek; the embed then resets the position to 0.
    await vi.advanceTimersByTimeAsync(1000);
    expect(getCurrentTime).toHaveBeenCalledTimes(1);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(1);

    // Second check sees the reset and re-applies the resume seek.
    await vi.advanceTimersByTimeAsync(1000);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(2);
    expect(originalSetCurrentTime).toHaveBeenLastCalledWith(10);

    vi.useRealTimers();
  });

  it('stops re-applying after the retry budget', async () => {
    vi.useFakeTimers();

    const originalSetCurrentTime = vi.fn().mockResolvedValue(5);
    const getCurrentTime = vi.fn().mockResolvedValue(0);
    const player = makePlayer({
      setCurrentTime: originalSetCurrentTime,
      getCurrentTime,
    });

    hookVimeoPlayer({ engine: player, dispatchEvent: vi.fn() });

    void player.setCurrentTime(5);
    await vi.advanceTimersByTimeAsync(10_000);

    // Initial send plus at most three re-applies.
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(4);

    vi.useRealTimers();
  });

  it('stops verifying once the embed has loaded and the position is honored', async () => {
    vi.useFakeTimers();

    const originalSetCurrentTime = vi.fn().mockResolvedValue(10);
    const getCurrentTime = vi.fn().mockResolvedValue(10);
    const player = makePlayer({
      setCurrentTime: originalSetCurrentTime,
      getCurrentTime,
    });

    hookVimeoPlayer({ engine: player, dispatchEvent: vi.fn() });

    const seek = player.setCurrentTime(10);
    await expect(seek).resolves.toBe(10);

    (player as unknown as { __tutorSeekSettled?: () => void }).__tutorSeekSettled?.();
    await vi.advanceTimersByTimeAsync(5000);

    expect(getCurrentTime).toHaveBeenCalledTimes(1);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(1);

    vi.useRealTimers();
  });

  it('lands on the end and finishes when Vimeo rejects a seek past the duration', async () => {
    vi.useFakeTimers();

    const outOfRange = () => Object.assign(new Error('out of range'), { name: 'RangeError' });
    const originalSetCurrentTime = vi.fn((time: number) =>
      time > 100 ? Promise.reject(outOfRange()) : Promise.resolve(time),
    );
    const player = makePlayer({
      setCurrentTime: originalSetCurrentTime,
      getCurrentTime: vi.fn().mockResolvedValue(100),
      getDuration: vi.fn().mockResolvedValue(100),
      getPaused: vi.fn().mockResolvedValue(false),
      pause: vi.fn().mockResolvedValue(undefined),
    });

    const dispatchEvent = vi.fn();
    const adapter = { engine: player, dispatchEvent };
    hookVimeoPlayer(adapter);

    await expect(player.setCurrentTime(120)).resolves.toBe(100);
    expect(originalSetCurrentTime.mock.calls.map(([time]) => time)).toEqual([120, 100]);

    await vi.advanceTimersByTimeAsync(1500);

    expect(player.pause).toHaveBeenCalled();
    expect(dispatchEvent.mock.calls.some(([event]) => event.type === 'ended')).toBe(true);
    expect((adapter as unknown as { __tutorEnded?: boolean }).__tutorEnded).toBe(true);

    vi.useRealTimers();
  });

  it('clamps and validates currentTime on VimeoAdapter prototype', () => {
    patchVimeoProvider();

    const adapter = new VimeoAdapter();

    Object.defineProperty(adapter, 'duration', {
      value: 50,
      configurable: true,
    });

    const ensureEnd = vi.fn();
    Object.defineProperty(adapter, 'engine', {
      value: { __tutorEnsureEnd: ensureEnd },
      configurable: true,
    });

    adapter.currentTime = 75;
    expect(adapter.currentTime).toBe(50);
    expect(ensureEnd).toHaveBeenCalled();

    adapter.currentTime = -10;
    expect(adapter.currentTime).toBe(0);

    adapter.currentTime = NaN;
    expect(adapter.currentTime).toBe(0);
  });

  it('propagates a synchronous SDK throw without corrupting later seeks', async () => {
    const originalSetCurrentTime = vi
      .fn()
      .mockImplementationOnce(() => {
        throw new Error('Disconnected iframe');
      })
      .mockResolvedValue(20);

    const player = makePlayer({ setCurrentTime: originalSetCurrentTime });

    const adapter = {
      engine: player,
      dispatchEvent: vi.fn(),
    };

    hookVimeoPlayer(adapter);

    // The current wrapper lets a synchronous SDK throw escape to the caller.
    expect(() => player.setCurrentTime(10)).toThrow('Disconnected iframe');

    // The next seek still goes through.
    const seek = player.setCurrentTime(20);
    await expect(seek).resolves.toBe(20);
    expect(originalSetCurrentTime).toHaveBeenCalledTimes(2);
  });

  it('asserts @videojs/vimeo-video vendor contract expectations', () => {
    // Fails when @videojs/vimeo-video version or prototype changes so workarounds can be audited/upstreamed
    expect(VimeoAdapter).toBeDefined();
    expect(typeof VimeoAdapter.prototype.attach).toBe('function');
    expect(typeof VimeoAdapter.prototype.load).toBe('function');

    for (const key of ['error', 'ended', 'paused']) {
      const desc = Object.getOwnPropertyDescriptor(VimeoAdapter.prototype, key);
      expect(desc, key).toBeDefined();
      expect(typeof desc?.get, key).toBe('function');
    }

    expect(typeof Object.getOwnPropertyDescriptor(VimeoAdapter.prototype, 'currentTime')?.set).toBe('function');
  });
});
