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
  getPaused?: () => Promise<boolean>;
  play?: () => Promise<void>;
  pause?: () => Promise<void>;
  __tutorEnsureEnd?: () => void;
  /** Installed by `hookVimeoPlayer`: the embed finished loading, so re-check a pending seek. */
  __tutorSeekSettled?: () => void;
}

interface VimeoAdapterProto {
  attach(target: Element | null): void;
  load(): Promise<void>;
  currentTime: number;
  duration: number;
  engine: HookedVimeoPlayer | null;
  dispatchEvent(e: Event): boolean;
}

// A seek posted while the embed is still initializing is dropped, or acknowledged and then reset
// to 0 by the pending `loadVideo` (the resume seek at `loadedmetadata` is the usual victim).
// The player never reports either, so a requested seek is checked against the real position
// and re-applied until it sticks.
const SEEK_END_WAIT_MS = 1500;
const SEEK_VERIFY_MS = 1000;
const SEEK_MAX_REAPPLIES = 3;
const SEEK_MAX_CHECKS = 10;
/** A seek counts as honored once the player is within this many seconds of the target. */
const SEEK_TOLERANCE = 1;

const isOutOfRange = (err: unknown): boolean =>
  typeof err === 'object' && err !== null && (err as VimeoMethodError).name === 'RangeError';

/**
 * Vimeo's only documented `setCurrentTime` failure is a `RangeError`: the time was below 0 or
 * past the video's duration. The vendor adapter treats every player `error` as fatal, so a
 * skip-forward near the end would show the error UI.
 *
 * 1. Replaces the unconditional fatal error handler with one that ignores `RangeError`.
 * 2. Turns a rejected out-of-range seek into a seek to the end instead of dropping it.
 * 3. Makes a seek to the end finish the video (Vimeo never fires `ended` for it).
 * 4. Re-applies a seek that was dropped or reset while the embed was still loading.
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

  if (typeof player.setCurrentTime === 'function') {
    const originalSetCurrentTime = player.setCurrentTime;

    let wanted: number | null = null;
    let endedSeen = false;
    let endTimer: ReturnType<typeof setTimeout> | undefined;

    for (const type of ['play', 'playing', 'seeking']) {
      player.on?.(type, () => {
        (adapter as unknown as { __tutorEnded?: boolean }).__tutorEnded = false;
      });
    }
    player.on?.('ended', () => {
      endedSeen = true;
      clearTimeout(endTimer);
    });

    // Other providers fire `ended` when a seek reaches the end, which advances the lesson. Vimeo
    // only fires it when playback runs out, so a seek to the end (accepted or clamped) leaves the
    // lesson stuck. Let it play out; if no real `ended` arrives, raise it ourselves.
    const ensureEnd = async (): Promise<void> => {
      endedSeen = false;
      const paused = await Promise.resolve(player.getPaused?.()).catch(() => false);
      if (paused) {
        void Promise.resolve(player.play?.()).catch(() => undefined);
      }
      clearTimeout(endTimer);
      endTimer = setTimeout(() => {
        if (endedSeen) {
          return;
        }
        void Promise.resolve(player.pause?.()).catch(() => undefined);
        // The adapter's `ended`/`paused` getters read private fields that only Vimeo's own
        // events update, so mark the state ourselves or the player never sees the end.
        (adapter as unknown as { __tutorEnded?: boolean }).__tutorEnded = true;
        adapter.dispatchEvent?.(new Event('ended'));
      }, SEEK_END_WAIT_MS);
    };
    player.__tutorEnsureEnd = () => void ensureEnd();

    // Vimeo rejects a seek past the end with a RangeError. Land on the end instead of dropping
    // the seek: ask for the real duration and step back from the end until a time is accepted.
    const send = (time: number): Promise<number> =>
      Promise.resolve(originalSetCurrentTime.call(player, time)).catch(async (err: unknown) => {
        if (!isOutOfRange(err)) {
          throw err;
        }

        const duration = await Promise.resolve(player.getDuration?.()).catch(() => NaN);
        if (!Number.isFinite(duration) || (duration as number) <= 0) {
          // Duration unknown (still loading): the verify loop re-sends once the embed has loaded.
          return time;
        }

        const end = Math.min(time, duration as number);
        for (const target of [end, end - 0.1, end - 0.5, end - 1]) {
          if (target <= 0) {
            break;
          }
          if (target === time) {
            continue; // this exact value was just rejected
          }

          try {
            const landed = await Promise.resolve(originalSetCurrentTime.call(player, target));
            // The verify loop must chase the time that was accepted, not the unreachable one.
            if (wanted === time) {
              wanted = target;
            }
            if (time >= (duration as number)) {
              void ensureEnd();
            }
            return landed;
          } catch (retryErr) {
            if (!isOutOfRange(retryErr)) {
              throw retryErr;
            }
          }
        }

        return time;
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
  }
};

/**
 * Patches VimeoAdapter prototype to:
 * - Clamp and validate currentTime assignments, and finish the video on a seek to the end.
 * - Auto-hook VimeoPlayer instances created during attach and load.
 * - Clear a previously reported fatal error / ended state when a new load starts.
 * - Let a synthetic `ended` be reflected by the adapter's `ended` / `paused` getters.
 */
export const patchVimeoProvider = (): void => {
  if (isPatched) {
    return;
  }
  isPatched = true;

  const proto = VimeoAdapter.prototype as unknown as VimeoAdapterProto;
  const originalAttach = proto.attach;
  const originalLoad = proto.load;

  if (typeof originalAttach === 'function') {
    proto.attach = function (target: Element | null): void {
      const result = originalAttach.call(this, target);
      hookVimeoPlayer(this);
      return result;
    };
  }

  if (typeof originalLoad === 'function') {
    proto.load = async function (): Promise<void> {
      // A stale error / ended state from the previous source must not stick across a reload.
      (this as unknown as { __tutorFatalError?: unknown }).__tutorFatalError = undefined;
      (this as unknown as { __tutorEnded?: boolean }).__tutorEnded = false;
      const result = await originalLoad.call(this);
      hookVimeoPlayer(this);
      // `loadVideo` has settled: a resume seek it reset can be re-applied now.
      this.engine?.__tutorSeekSettled?.();
      return result;
    };
  }

  const findDescriptor = (target: object | null, key: string): PropertyDescriptor | undefined => {
    for (let current = target; current; current = Object.getPrototypeOf(current)) {
      const descriptor = Object.getOwnPropertyDescriptor(current, key);
      if (descriptor) {
        return descriptor;
      }
    }
    return undefined;
  };

  const origErrorDesc = findDescriptor(proto, 'error');
  Object.defineProperty(proto, 'error', {
    configurable: true,
    enumerable: true,
    get(this: VimeoAdapterProto & { __tutorFatalError?: unknown }) {
      if (this.__tutorFatalError) {
        return this.__tutorFatalError;
      }
      return origErrorDesc?.get ? origErrorDesc.get.call(this) : null;
    },
    // Keep the vendor's inherited setter reachable so `this.error = ...` cannot throw.
    set(this: VimeoAdapterProto & { __tutorFatalError?: unknown }, value: unknown) {
      this.__tutorFatalError = value;
      origErrorDesc?.set?.call(this, value);
    },
  });

  for (const key of ['ended', 'paused'] as const) {
    const orig = findDescriptor(proto, key);
    if (orig?.get) {
      Object.defineProperty(proto, key, {
        configurable: true,
        enumerable: orig.enumerable,
        get(this: VimeoAdapterProto & { __tutorEnded?: boolean }) {
          if (this.__tutorEnded) {
            return true;
          }
          return orig.get!.call(this);
        },
      });
    }
  }

  const desc = Object.getOwnPropertyDescriptor(proto, 'currentTime');
  if (desc && desc.set) {
    const originalSet = desc.set;
    Object.defineProperty(proto, 'currentTime', {
      ...desc,
      set(this: VimeoAdapterProto, value: number) {
        if (!Number.isFinite(value)) {
          return;
        }
        let safeValue = Math.max(0, value);
        const duration = this.duration;
        if (Number.isFinite(duration) && duration > 0) {
          safeValue = Math.min(safeValue, duration);
        }
        originalSet.call(this, safeValue);
        // A seek that reaches the end (what `Player.seek` sends when you skip past it) is accepted
        // by Vimeo without ever firing `ended`.
        if (Number.isFinite(duration) && duration > 0 && safeValue >= duration - 0.05) {
          this.engine?.__tutorEnsureEnd?.();
        }
      },
    });
  }
};
