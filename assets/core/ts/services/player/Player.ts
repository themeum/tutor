import {
  type PlayerEvent,
  type PlayerEventMap,
  type PlayerProvider,
  type PlayerUnsubscribe,
  type TutorPlayer,
} from '@Core/ts/types/player';

/**
 * The subset of the Video.js player store the handle depends on. Kept structural so the
 * handle can be unit-tested against a fake store.
 */
export interface PlayerStore {
  readonly currentTime: number;
  readonly duration: number;
  readonly paused: boolean;
  readonly ended: boolean;
  readonly volume: number;
  readonly muted: boolean;
  readonly isFullscreen: boolean;
  readonly error?: Error | null;
  readonly target: { readonly media: Element | null } | null;
  play(): Promise<void>;
  pause(): void;
  seek(time: number): Promise<number> | number;
  setVolume(volume: number): number;
  setMuted(muted: boolean): boolean;
  subscribe(callback: () => void): () => void;
  destroy(): void;
}

export interface CreateTutorPlayerOptions {
  element: HTMLElement;
  store: PlayerStore;
  provider: PlayerProvider;
  /** Called once the handle is disposed, after the store and element are released. */
  onDispose?: () => void;
}

const clamp = (value: number, min: number, max: number): number => Math.min(Math.max(value, min), max);

type AnyHandler = (payload: never) => void;

export function createTutorPlayer({ element, store, provider, onDispose }: CreateTutorPlayerOptions): TutorPlayer {
  const listeners = new Map<PlayerEvent, Set<AnyHandler>>();

  const emit = <E extends PlayerEvent>(event: E, payload: PlayerEventMap[E]): void => {
    const handlers = listeners.get(event);
    if (!handlers) {
      return;
    }
    for (const handler of handlers) {
      (handler as (payload: PlayerEventMap[E]) => void)(payload);
    }
  };

  const snapshot = () => ({
    currentTime: store.currentTime,
    duration: store.duration,
    paused: store.paused,
    ended: store.ended,
    volume: store.volume,
    muted: store.muted,
    error: store.error ?? null,
  });

  let disposed = false;
  let ready = false;
  let ownFullscreen = false;
  let previous = snapshot();
  let unsubscribeStore: PlayerUnsubscribe = () => undefined;

  function sync(): void {
    if (disposed) {
      return;
    }

    if (!ready && store.target?.media) {
      ready = true;
      emit('ready', { player: handle });
    }

    const next = snapshot();

    if (next.paused !== previous.paused) {
      emit(next.paused ? 'pause' : 'play', { player: handle });
    }
    if (next.currentTime !== previous.currentTime) {
      emit('timeupdate', { player: handle, currentTime: next.currentTime, duration: next.duration });
    }
    if (next.ended && !previous.ended) {
      emit('ended', { player: handle });
    }
    if (next.volume !== previous.volume || next.muted !== previous.muted) {
      emit('volumechange', { player: handle, volume: next.volume, muted: next.muted });
    }
    if (next.error && !previous.error) {
      emit('error', { player: handle, error: next.error });
    }

    previous = next;
  }

  function onFullscreenChange(): void {
    if (disposed) {
      return;
    }
    const active = (element.ownerDocument?.fullscreenElement as HTMLElement | undefined) ?? null;
    // Another element's fullscreen (a lightbox, a different embed) is not this player's event.
    const mine = active !== null && (active === element || active.contains(element) || element.contains(active));

    if (active !== null ? !mine : !ownFullscreen) {
      return;
    }

    ownFullscreen = mine;

    const fullscreenElement = active ?? element;

    emit('fullscreenchange', { player: handle, fullscreen: mine, element: fullscreenElement });
    emit(mine ? 'enterfullscreen' : 'exitfullscreen', {
      player: handle,
      srcElement: fullscreenElement,
      target: fullscreenElement,
    });
  }

  const handle: TutorPlayer = {
    provider,
    element,
    get media(): Element | null {
      return store.target?.media ?? null;
    },

    getCurrentTime: () => store.currentTime,
    getDuration: () => store.duration,
    isPaused: () => store.paused,
    getVolume: () => store.volume,
    setVolume: (volume: number) => {
      store.setVolume(clamp(volume, 0, 1));
    },
    isMuted: () => store.muted,
    setMuted: (muted: boolean) => {
      store.setMuted(muted);
    },
    isFullscreen: () => store.isFullscreen,

    play: () => store.play(),
    pause: () => store.pause(),
    seek: (time: number) => {
      const duration = store.duration;
      store.seek(duration > 0 ? clamp(time, 0, duration) : Math.max(time, 0));
    },

    on: <E extends PlayerEvent>(event: E, handler: (payload: PlayerEventMap[E]) => void): PlayerUnsubscribe => {
      const handlers = listeners.get(event) ?? new Set<AnyHandler>();
      listeners.set(event, handlers);
      handlers.add(handler as unknown as AnyHandler);

      // `ready` is a latch, not a moment: declarative markup boots as soon as the engine
      // loads, which can be before the lesson controller subscribes.
      if (ready && (event as PlayerEvent) === 'ready') {
        queueMicrotask(() => {
          if (!disposed && handlers.has(handler as unknown as AnyHandler)) {
            (handler as (payload: PlayerEventMap['ready']) => void)({ player: handle });
          }
        });
      }

      return () => handlers.delete(handler as unknown as AnyHandler);
    },

    dispose: () => {
      if (disposed) {
        return;
      }
      disposed = true;
      unsubscribeStore();
      element.ownerDocument?.removeEventListener('fullscreenchange', onFullscreenChange);
      listeners.clear();
      store.destroy();
      element.remove();
      onDispose?.();
    },
  };

  unsubscribeStore = store.subscribe(sync);
  element.ownerDocument?.addEventListener('fullscreenchange', onFullscreenChange);
  queueMicrotask(sync);

  return handle;
}
