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
}

interface VimeoAdapterProto {
  attach(target: Element | null): void;
  load(): Promise<void>;
  currentTime: number;
  duration: number;
  engine: HookedVimeoPlayer | null;
  dispatchEvent(e: Event): boolean;
}

const SEEK_TIMEOUT_MS = 4000;
// Vimeo silently drops seeks posted while the embed is still initializing; it acknowledges
// an accepted one with `seeking` within a few hundred milliseconds. Its own pending
// `loadVideo` can then reset the player to 0, so verify the position afterwards.
const SEEK_RETRY_MS = 1500;
const SEEK_VERIFY_MS = 1000;
const SEEK_MAX_ATTEMPTS = 3;

/**
 * Hardens a VimeoPlayer instance against rapid seek errors and load-time seeks:
 * 1. Removes the blanket fatal error handler registered by VimeoAdapter.
 * 2. Installs an error filter that ignores non-fatal seek/method aborts (e.g. AbortError, RangeError).
 * 3. Serializes rapid setCurrentTime calls so overlapping postMessages don't trigger Vimeo iframe rejections.
 * 4. Retries seeks the loading embed drops and re-applies one that its pending `loadVideo` reset.
 */
export const hookVimeoPlayer = (adapter: {
  engine?: HookedVimeoPlayer | null;
  dispatchEvent?: (e: Event) => boolean;
  addEventListener?: (type: string, callback: () => void, options?: { once?: boolean }) => void;
  removeEventListener?: (type: string, callback: () => void) => void;
}): void => {
  const player = adapter.engine;
  if (!player || player.__tutorVimeoPatched) {
    return;
  }
  player.__tutorVimeoPatched = true;

  // 1. Remove the unconditional fatal error handler registered by VimeoAdapter
  if (typeof player.off === 'function') {
    player.off('error');
  }

  // 2. Re-bind a hardened error listener that ignores non-fatal method errors
  if (typeof player.on === 'function') {
    player.on('error', (err: unknown) => {
      const errorObj = typeof err === 'object' && err !== null ? (err as VimeoMethodError) : undefined;

      // Method-level rejections on seek/setCurrentTime are non-fatal and should never crash the player UI.
      const isSeekAbort =
        errorObj?.method === 'setCurrentTime' ||
        errorObj?.method === 'seek' ||
        errorObj?.name === 'AbortError' ||
        /seek.*abort|abort.*seek/i.test(errorObj?.message ?? '');

      if (isSeekAbort) {
        return;
      }

      // If it's a genuine playback error (e.g. video unavailable, privacy restriction):
      const errorPayload = {
        // `MEDIA_ERR_SRC_NOT_SUPPORTED`, not 1 (`MEDIA_ERR_ABORTED`): the dialog would
        // otherwise treat a real failure as a user-cancelled load.
        code: 4,
        message: errorObj?.message || 'Vimeo playback error',
      };
      (adapter as unknown as { __tutorFatalError?: unknown }).__tutorFatalError = errorPayload;

      if (typeof adapter.dispatchEvent === 'function') {
        adapter.dispatchEvent(
          new CustomEvent('error', {
            bubbles: true,
            composed: true,
            detail: err,
          }),
        );
      }
    });
  }

  // 3. Serialize setCurrentTime calls so overlapping postMessages don't trigger rejections
  if (typeof player.setCurrentTime === 'function') {
    const originalSetCurrentTime = player.setCurrentTime;
    let inFlight: Promise<number> | null = null;
    let pending: { time: number; resolve: (time: number) => void } | null = null;
    let seekId = 0;

    const runSeek = (targetTime: number, rechecked = false): Promise<number> => {
      const id = ++seekId;
      let softTimer: ReturnType<typeof setTimeout> | undefined;
      let retryTimer: ReturnType<typeof setTimeout> | undefined;
      let accepted = false;
      let attempts = 0;
      let latestAttempt = 0;
      let released = false;

      let resolveCaller: (time: number) => void = () => undefined;
      const caller = new Promise<number>((resolve) => {
        resolveCaller = resolve;
      });

      const onSeeking = (): void => {
        accepted = true;
      };

      // Vimeo's pending `loadVideo` resets the player after an acknowledged seek (the resume
      // seek at `loadedmetadata` is its usual victim), so re-apply once if it fell behind.
      const verify = async (): Promise<void> => {
        if (id !== seekId || inFlight) {
          return;
        }
        const current = (await Promise.resolve(player.getCurrentTime?.()).catch(() => NaN)) ?? NaN;
        if (id !== seekId || inFlight || !Number.isFinite(current) || current + 1 >= targetTime) {
          return;
        }
        void runSeek(targetTime, true);
      };

      // Hold the lock until the SDK call settles: releasing it at the soft timeout would let
      // the next postMessage overlap a still-pending one, which is what this serializes away.
      // Each caller still resolves at the soft timeout so a hung seek cannot freeze the UI.
      const release = (): void => {
        if (released) {
          return;
        }
        released = true;
        adapter.removeEventListener?.('seeked', release);
        adapter.removeEventListener?.('seeking', onSeeking);
        if (id !== seekId) {
          // A newer seek owns the lock; this abandoned call settled too late to free it.
          return;
        }
        // `seeked`/the hard cap can end a seek whose SDK promise never settles.
        resolveCaller(targetTime);
        clearTimeout(softTimer);
        clearTimeout(hardTimer);
        clearTimeout(retryTimer);
        inFlight = null;
        if (accepted && !rechecked) {
          setTimeout(() => void verify(), SEEK_VERIFY_MS);
        }
        if (pending) {
          const next = pending;
          pending = null;
          void runSeek(next.time).then(next.resolve, next.resolve);
        }
      };

      const attempt = (): void => {
        const mine = ++latestAttempt;
        attempts += 1;

        const call = Promise.resolve(originalSetCurrentTime.call(player, targetTime));
        // A dropped attempt settles late, after a newer one owns the seek.
        const settle = (time: number | undefined): void => {
          if (mine !== latestAttempt) {
            return;
          }
          if (id === seekId) {
            resolveCaller(Number.isFinite(time) ? (time as number) : targetTime);
          }
          release();
        };
        void call.then(
          (time) => settle(typeof time === 'number' ? time : undefined),
          () => settle(undefined),
        );

        retryTimer = setTimeout(() => {
          if (mine !== latestAttempt || accepted || attempts >= SEEK_MAX_ATTEMPTS) {
            return;
          }
          attempt();
        }, SEEK_RETRY_MS);
      };

      // The iframe acknowledges a seek with `seeking`; its `seeked` is the ground truth even
      // when the SDK promise never settles, so release then instead of waiting for the cap.
      adapter.addEventListener?.('seeking', onSeeking, { once: true });
      adapter.addEventListener?.('seeked', release, { once: true });

      // Hard cap: a request the SDK never settles (iframe reload, dropped postMessage) must
      // not wedge every later seek. One overlapping message beats a dead player.
      const hardTimer = setTimeout(release, SEEK_TIMEOUT_MS * 2);

      attempt();

      inFlight = Promise.race([
        caller,
        new Promise<number>((resolve) => {
          softTimer = setTimeout(() => resolve(targetTime), SEEK_TIMEOUT_MS);
        }),
      ]).catch(() => targetTime);

      return inFlight;
    };

    player.setCurrentTime = function (time: number): Promise<number> {
      if (!Number.isFinite(time)) {
        return Promise.resolve(0);
      }
      const clampedTime = Math.max(0, time);

      // If a seek is already in flight, queue the latest target instead of spamming
      // Vimeo with overlapping postMessages that trigger AbortError.
      if (inFlight) {
        return new Promise<number>((resolve) => {
          // A queued caller must still get its own target back, not the earlier seek's result.
          pending?.resolve(pending.time);
          pending = { time: clampedTime, resolve };
        });
      }

      return runSeek(clampedTime);
    };
  }
};

/**
 * Patches VimeoAdapter prototype to:
 * - Clamp and validate currentTime assignments.
 * - Auto-hook VimeoPlayer instances created during attach and load.
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

  if (typeof originalAttach === 'function') {
    proto.attach = function (target: Element | null): void {
      const result = originalAttach.call(this, target);
      hookVimeoPlayer(this);
      return result;
    };
  }

  if (typeof originalLoad === 'function') {
    proto.load = async function (): Promise<void> {
      // A stale error from the previous source must not stick across a reload.
      (this as unknown as { __tutorFatalError?: unknown }).__tutorFatalError = undefined;
      const result = await originalLoad.call(this);
      hookVimeoPlayer(this);
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
      },
    });
  }
};
