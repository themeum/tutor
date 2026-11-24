import { VimeoAdapter } from '@videojs/vimeo-video';

let isPatched = false;

interface VimeoMethodError {
  method?: string;
  name?: string;
  message?: string;
}

interface HookedVimeoPlayer {
  __tutorVimeoPatched?: boolean;
  on?: (event: string, callback: (...args: unknown[]) => void) => void;
  off?: (event: string, callback?: (...args: unknown[]) => void) => void;
  setCurrentTime?: (time: number) => Promise<number>;
  getCurrentTime?: () => Promise<number>;
  getDuration?: () => Promise<number>;
  /** Installed by `hookVimeoPlayer`: the embed finished loading, so re-check a pending seek. */
  __tutorSeekSettled?: () => void;
}

interface VimeoAdapterProto {
  attach(target: Element | null): void;
  load(): Promise<void>;
  engine: HookedVimeoPlayer | null;
}

export interface VimeoSeekClampResult {
  shouldSeek: boolean;
  targetTime: number;
  clampedAtEnd: boolean;
}

export interface VimeoLikeAdapter extends EventTarget {
  currentTime: number;
  readonly duration: number;
  readonly readyState?: number;
  readonly error?: unknown;
}

const DEFAULT_END_BUFFER_SECONDS = 1.0;
const SHORT_CLIP_BUFFER_RATIO = 0.15;
const SHORT_CLIP_THRESHOLD_SECONDS = 2.0;
const END_PROXIMITY_EPSILON = 0.05;
const SPURIOUS_SEEK_ERROR_WINDOW_MS = 1500;

// A seek posted while the embed is still initializing is dropped, or acknowledged and then reset
// to 0 by the pending `loadVideo` (the resume seek at `loadedmetadata` is the usual victim).
// The player never reports either, so a requested seek is checked against the real position
// and re-applied until it sticks.
const SEEK_VERIFY_MS = 1000;
const SEEK_MAX_REAPPLIES = 3;
const SEEK_MAX_CHECKS = 10;
/** A seek counts as honored once the player is within this many seconds of the target. */
const SEEK_TOLERANCE = 1;

const isOutOfRange = (err: unknown): boolean =>
  typeof err === 'object' && err !== null && (err as VimeoMethodError).name === 'RangeError';

/**
 * Computes a Vimeo-safe seek target strictly below `duration`.
 *
 * Vimeo's Player SDK (`setCurrentTime`) rejects seeks at or beyond the actual media duration
 * with a `RangeError`, and an accepted seek to the exact end parks the video without ever firing
 * `ended`. Vimeo's reported `getDuration()` can also round slightly above the playable end, so
 * forward seeks are clamped inside a small buffer before `duration`: the video then plays out
 * the last moments and Vimeo fires its own `ended`, like the other providers.
 */
export function clampVimeoSeekTime(
  targetSeconds: number,
  duration: number,
  currentSeconds = 0,
  endBufferSeconds = DEFAULT_END_BUFFER_SECONDS,
): VimeoSeekClampResult {
  if (!Number.isFinite(targetSeconds) || targetSeconds <= 0) {
    return { shouldSeek: true, targetTime: 0, clampedAtEnd: false };
  }

  if (!Number.isFinite(duration) || duration <= 0) {
    return { shouldSeek: true, targetTime: targetSeconds, clampedAtEnd: false };
  }

  const effectiveBuffer =
    duration > SHORT_CLIP_THRESHOLD_SECONDS
      ? Math.min(endBufferSeconds, duration * 0.25)
      : Math.min(0.25, duration * SHORT_CLIP_BUFFER_RATIO);

  const maxSafeTime = Math.max(0, duration - effectiveBuffer);

  if (targetSeconds < maxSafeTime) {
    return { shouldSeek: true, targetTime: targetSeconds, clampedAtEnd: false };
  }

  // Already at or past the safe end boundary and seeking forward (or to the end):
  // avoid issuing an out-of-range seek or jumping backwards.
  const safeCurrent = Number.isFinite(currentSeconds) ? currentSeconds : 0;
  if (safeCurrent >= maxSafeTime - END_PROXIMITY_EPSILON && targetSeconds >= safeCurrent) {
    return { shouldSeek: false, targetTime: safeCurrent, clampedAtEnd: true };
  }

  return { shouldSeek: true, targetTime: maxSafeTime, clampedAtEnd: true };
}

const guardedAdapters = new WeakSet<object>();

/**
 * Guards a mounted `VimeoAdapter` instance so seeks that reach or cross the video's total
 * duration never trigger Vimeo's out-of-range playback error.
 */
export function attachSafeVimeoSeekGuard(
  adapter: VimeoLikeAdapter | null | undefined,
  options?: { now?: () => number; endBufferSeconds?: number },
): void {
  if (!adapter || guardedAdapters.has(adapter)) {
    return;
  }

  const proto = Object.getPrototypeOf(adapter);
  const currentTimeDesc = proto ? Object.getOwnPropertyDescriptor(proto, 'currentTime') : undefined;

  if (!currentTimeDesc?.get || !currentTimeDesc?.set) {
    return;
  }

  const errorDesc = proto ? Object.getOwnPropertyDescriptor(proto, 'error') : undefined;
  const protoDispatch = EventTarget.prototype.dispatchEvent;
  const now = options?.now ?? (() => Date.now());
  const endBufferSeconds = options?.endBufferSeconds ?? DEFAULT_END_BUFFER_SECONDS;

  let lastNearEndSeekAt = 0;
  let suppressAdapterError = false;

  const emitSeekSettled = (): void => {
    queueMicrotask(() => {
      protoDispatch.call(adapter, new Event('seeked'));
      protoDispatch.call(adapter, new Event('timeupdate'));
    });
  };

  Object.defineProperty(adapter, 'currentTime', {
    configurable: true,
    enumerable: true,
    get() {
      return currentTimeDesc.get!.call(adapter);
    },
    set(value: number) {
      if (!Number.isFinite(value)) {
        return;
      }

      const current = currentTimeDesc.get!.call(adapter) as number;
      const result = clampVimeoSeekTime(value, adapter.duration, current, endBufferSeconds);

      if (result.clampedAtEnd) {
        lastNearEndSeekAt = now();
      }

      if (!result.shouldSeek) {
        emitSeekSettled();
        return;
      }

      // The vendor setter returns early when the time is unchanged; still resolve the Video.js
      // store's pending `seeked` listener so `seeking` does not hang.
      if (Math.abs(result.targetTime - current) < 0.001) {
        emitSeekSettled();
        return;
      }

      currentTimeDesc.set!.call(adapter, result.targetTime);
    },
  });

  if (errorDesc?.get) {
    Object.defineProperty(adapter, 'error', {
      configurable: true,
      enumerable: true,
      get() {
        if (suppressAdapterError) {
          return null;
        }
        return errorDesc.get!.call(adapter);
      },
      // Keep assignment from throwing in strict mode; the vendor stores its error privately.
      set(value: unknown) {
        errorDesc.set?.call(adapter, value);
      },
    });
  }

  Object.defineProperty(adapter, 'dispatchEvent', {
    configurable: true,
    writable: true,
    value(event: Event) {
      if (event.type === 'emptied' || event.type === 'loadstart') {
        lastNearEndSeekAt = 0;
        suppressAdapterError = false;
      }

      if (event.type === 'error') {
        const withinSeekWindow = lastNearEndSeekAt > 0 && now() - lastNearEndSeekAt <= SPURIOUS_SEEK_ERROR_WINDOW_MS;
        const hasLoadedMetadata = typeof adapter.readyState === 'number' && adapter.readyState >= 1;

        if (withinSeekWindow && hasLoadedMetadata) {
          suppressAdapterError = true;
          emitSeekSettled();
          return false;
        }
        suppressAdapterError = false;
      }

      return protoDispatch.call(adapter, event);
    },
  });

  guardedAdapters.add(adapter);
}

/**
 * Hooks the underlying Vimeo player:
 * 1. Replaces the vendor's unconditional fatal `error` handler with one that ignores `RangeError`.
 * 2. Re-applies a seek that was dropped or reset while the embed was still loading.
 * 3. Turns a rejected out-of-range seek into one retry just before the end instead of dropping it.
 */
export const hookVimeoPlayer = (adapter: {
  engine?: HookedVimeoPlayer | null;
  dispatchEvent?: (e: Event) => boolean;
}): void => {
  const player = adapter.engine;
  if (!player || player.__tutorVimeoPatched) {
    return;
  }
  player.__tutorVimeoPatched = true;

  if (typeof player.off === 'function' && typeof player.on === 'function') {
    // Remove the unconditional fatal error handler registered by VimeoAdapter.
    player.off('error');

    player.on('error', (err: unknown) => {
      if (isOutOfRange(err)) {
        return;
      }

      const errorObj = typeof err === 'object' && err !== null ? (err as VimeoMethodError) : undefined;
      (adapter as unknown as { __tutorFatalError?: unknown }).__tutorFatalError = {
        // `MEDIA_ERR_SRC_NOT_SUPPORTED`, not 1 (`MEDIA_ERR_ABORTED`): the dialog would
        // otherwise treat a real failure as a user-cancelled load.
        code: 4,
        message: errorObj?.message || 'Vimeo playback error',
      };

      adapter.dispatchEvent?.(new CustomEvent('error', { bubbles: true, composed: true, detail: err }));
    });
  }

  if (typeof player.setCurrentTime !== 'function') {
    return;
  }

  const originalSetCurrentTime = player.setCurrentTime;
  let wanted: number | null = null;

  // A rejected out-of-range seek (cached duration missing or stale) gets one retry just before
  // the real end, so the seek lands instead of being dropped, and never rejects unhandled.
  const send = (time: number): Promise<number> =>
    Promise.resolve(originalSetCurrentTime.call(player, time)).catch(async (err: unknown) => {
      if (!isOutOfRange(err)) {
        throw err;
      }

      const duration = await Promise.resolve(player.getDuration?.()).catch(() => NaN);
      if (!Number.isFinite(duration) || (duration as number) <= 0) {
        return time; // duration unknown: the verify loop re-sends once the embed has loaded
      }

      const { targetTime } = clampVimeoSeekTime(time, duration as number);
      if (wanted === time) {
        wanted = targetTime;
      }
      return Promise.resolve(originalSetCurrentTime.call(player, targetTime)).catch((retryErr: unknown) => {
        if (isOutOfRange(retryErr)) {
          wanted = null; // unreachable: stop chasing it
          return targetTime;
        }
        throw retryErr;
      });
    });

  let settled = false;
  let reapplies = 0;
  let checks = 0;
  let verifyTimer: ReturnType<typeof setTimeout> | undefined;

  const scheduleVerify = (): void => {
    clearTimeout(verifyTimer);
    verifyTimer = setTimeout(() => void verify(), SEEK_VERIFY_MS);
  };

  const verify = async (): Promise<void> => {
    const target = wanted;
    if (target === null || typeof player.getCurrentTime !== 'function') {
      return;
    }

    const current = await Promise.resolve(player.getCurrentTime()).catch(() => NaN);
    // A newer seek replaced this one while we waited; it owns the verification now.
    if (wanted !== target) {
      return;
    }

    checks += 1;
    const honored = Number.isFinite(current) && current + SEEK_TOLERANCE >= target;

    if (honored) {
      // Keep watching until the embed has finished loading: its `loadVideo` can still reset us.
      if (settled || checks >= SEEK_MAX_CHECKS) {
        wanted = null;
        return;
      }
      scheduleVerify();
      return;
    }

    if (!Number.isFinite(current) || reapplies >= SEEK_MAX_REAPPLIES || checks >= SEEK_MAX_CHECKS) {
      wanted = null;
      return;
    }

    reapplies += 1;
    send(target).catch(() => undefined);
    scheduleVerify();
  };

  // The adapter calls this once the embed is loaded (and on its `loaded` event).
  player.__tutorSeekSettled = (): void => {
    settled = true;
    if (wanted !== null) {
      void verify();
    }
  };

  if (typeof player.on === 'function') {
    player.on('loaded', () => player.__tutorSeekSettled?.());
  }

  player.setCurrentTime = function (time: number): Promise<number> {
    if (Number.isFinite(time)) {
      wanted = Math.max(0, time);
      reapplies = 0;
      checks = 0;
      scheduleVerify();
    }
    return send(time);
  };
};

/**
 * Patches the VimeoAdapter prototype to:
 * - Guard each adapter instance's seeks against the video's end (`attachSafeVimeoSeekGuard`).
 * - Auto-hook the Vimeo player created during attach and load.
 * - Clear a previously reported fatal error when a new load starts.
 */
export const patchVimeoProvider = (): void => {
  if (isPatched) {
    return;
  }
  isPatched = true;

  const proto = VimeoAdapter.prototype as unknown as VimeoAdapterProto;
  const originalAttach = proto.attach;
  const originalLoad = proto.load;

  const findDescriptor = (target: object | null, key: string): PropertyDescriptor | undefined => {
    for (let current = target; current; current = Object.getPrototypeOf(current)) {
      const descriptor = Object.getOwnPropertyDescriptor(current, key);
      if (descriptor) {
        return descriptor;
      }
    }
    return undefined;
  };

  // Must be installed before any instance guard reads the prototype's `error` descriptor.
  const origErrorDesc = findDescriptor(proto, 'error');
  Object.defineProperty(proto, 'error', {
    configurable: true,
    enumerable: true,
    get(this: { __tutorFatalError?: unknown }) {
      if (this.__tutorFatalError) {
        return this.__tutorFatalError;
      }
      return origErrorDesc?.get ? origErrorDesc.get.call(this) : null;
    },
    // Keep the vendor's inherited setter reachable so `this.error = ...` cannot throw.
    set(this: { __tutorFatalError?: unknown }, value: unknown) {
      this.__tutorFatalError = value;
      origErrorDesc?.set?.call(this, value);
    },
  });

  if (typeof originalAttach === 'function') {
    proto.attach = function (this: VimeoAdapterProto & VimeoLikeAdapter, target: Element | null): void {
      const result = originalAttach.call(this, target);
      attachSafeVimeoSeekGuard(this);
      hookVimeoPlayer(this);
      return result;
    };
  }

  if (typeof originalLoad === 'function') {
    proto.load = async function (this: VimeoAdapterProto): Promise<void> {
      // A stale error from the previous source must not stick across a reload.
      (this as unknown as { __tutorFatalError?: unknown }).__tutorFatalError = undefined;
      const result = await originalLoad.call(this);
      hookVimeoPlayer(this);
      // `loadVideo` has settled: a resume seek it reset can be re-applied now.
      this.engine?.__tutorSeekSettled?.();
      return result;
    };
  }
};
