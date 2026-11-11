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
}

interface VimeoAdapterProto {
  attach(target: Element | null): void;
  load(): Promise<void>;
  currentTime: number;
  duration: number;
  engine: HookedVimeoPlayer | null;
  dispatchEvent(e: Event): boolean;
}

/**
 * Hardens a VimeoPlayer instance against rapid seek errors:
 * 1. Removes the blanket fatal error handler registered by VimeoAdapter.
 * 2. Installs an error filter that ignores non-fatal seek/method aborts (e.g. AbortError, RangeError).
 * 3. Serializes rapid setCurrentTime calls so overlapping postMessages don't trigger Vimeo iframe rejections.
 */
export function hookVimeoPlayer(adapter: {
  engine?: HookedVimeoPlayer | null;
  dispatchEvent?: (e: Event) => boolean;
}): void {
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
      // Method-level rejections (aborted seeks, rapid seeks, duration edge cases)
      // are non-fatal and should never crash the player UI.
      if (
        errorObj?.method === 'setCurrentTime' ||
        errorObj?.method === 'seek' ||
        errorObj?.name === 'AbortError' ||
        errorObj?.name === 'RangeError' ||
        (typeof errorObj?.message === 'string' && /seek|abort/i.test(errorObj.message))
      ) {
        return;
      }

      // If it's a genuine playback error (e.g. video unavailable, privacy restriction):
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

  // 3. Serialize and debounce setCurrentTime calls to prevent overlapping postMessages
  if (typeof player.setCurrentTime === 'function') {
    const originalSetCurrentTime = player.setCurrentTime;
    let inFlight: Promise<number> | null = null;
    let pendingTarget: number | null = null;

    player.setCurrentTime = function (time: number): Promise<number> {
      if (!Number.isFinite(time)) {
        return Promise.resolve(0);
      }
      const clampedTime = Math.max(0, time);

      // If a seek is already in flight, queue this target time instead of
      // spamming Vimeo with overlapping postMessages that trigger AbortError.
      if (inFlight) {
        pendingTarget = clampedTime;
        return inFlight;
      }

      const runSeek = (targetTime: number): Promise<number> => {
        inFlight = Promise.resolve(originalSetCurrentTime.call(player, targetTime))
          .catch(() => targetTime)
          .finally(() => {
            inFlight = null;
            if (pendingTarget !== null) {
              const next = pendingTarget;
              pendingTarget = null;
              void runSeek(next);
            }
          });
        return inFlight;
      };

      return runSeek(clampedTime);
    };
  }
}

/**
 * Patches VimeoAdapter prototype to:
 * - Clamp and validate currentTime assignments.
 * - Auto-hook VimeoPlayer instances created during attach and load.
 */
export function patchVimeoProvider(): void {
  if (isPatched) {
    return;
  }
  isPatched = true;

  const proto = VimeoAdapter.prototype as unknown as VimeoAdapterProto;
  const originalAttach = proto.attach;
  const originalLoad = proto.load;

  proto.attach = function (target: Element | null): void {
    const result = originalAttach.call(this, target);
    hookVimeoPlayer(this);
    return result;
  };

  proto.load = async function (): Promise<void> {
    const result = await originalLoad.call(this);
    hookVimeoPlayer(this);
    return result;
  };

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
}
