import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import type { PlayerUnsubscribe, TutorPlayer } from '@Core/ts/types/player';

export interface LegacyFullscreenEvent {
  srcElement: HTMLElement | null;
}

export interface LegacyTutorLessonPlayer {
  currentTime: number;
  play(): void;
  on(event: 'enterfullscreen' | 'exitfullscreen', handler: (event: LegacyFullscreenEvent) => void): void;
}

let warned = false;
let singleton: LegacyTutorLessonPlayer | null = null;

const getActivePlayer = (): TutorPlayer | null => window.TutorCore?.player?.getActive() ?? null;

export function createLegacyTutorLessonPlayer(): LegacyTutorLessonPlayer {
  if (!warned) {
    warned = true;
    // eslint-disable-next-line no-console
    console.warn('[Tutor] window.TutorLessonPlayer is deprecated and will be removed. Use TutorCore.player instead.');
  }

  const enterHandlers = new Set<(event: LegacyFullscreenEvent) => void>();
  const exitHandlers = new Set<(event: LegacyFullscreenEvent) => void>();
  let unsubscribe: PlayerUnsubscribe | null = null;

  const subscribe = (): void => {
    if (unsubscribe) {
      return;
    }

    const service = window.TutorCore?.player;

    if (!service) {
      // The engine is a deferred module; retry once it announces itself.
      window.addEventListener(TUTOR_CUSTOM_EVENTS.PLAYER_ENGINE_READY, subscribe, { once: true });
      return;
    }

    // The service late-binds, so handlers registered before any player exists still
    // receive events from the first player that appears.
    unsubscribe = service.on('fullscreenchange', ({ fullscreen, element }) => {
      const handlers = fullscreen ? enterHandlers : exitHandlers;
      handlers.forEach((handler) => handler({ srcElement: element }));
    });
  };

  return {
    get currentTime(): number {
      return getActivePlayer()?.getCurrentTime() ?? 0;
    },
    set currentTime(value: number) {
      getActivePlayer()?.seek(value);
    },
    play(): void {
      void getActivePlayer()?.play();
    },
    on(event, handler): void {
      if (event === 'enterfullscreen') {
        enterHandlers.add(handler);
      } else {
        exitHandlers.add(handler);
      }

      subscribe();
    },
  };
}

export function getLegacyTutorLessonPlayer(): LegacyTutorLessonPlayer {
  if (!singleton) {
    singleton = createLegacyTutorLessonPlayer();
  }
  return singleton;
}
