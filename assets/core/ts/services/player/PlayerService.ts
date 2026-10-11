import {
  type PlayerEvent,
  type PlayerEventMap,
  type PlayerService,
  type PlayerUnsubscribe,
  type TutorPlayer,
} from '@Core/ts/types/player';

import { createPlayerElement, getStore, type VideoPlayerElement } from './elements';
import { createTutorPlayer, type PlayerStore } from './Player';
import { type DetectedSource, detectSource } from './providers';

export type PlayerHandleFactory = (options: {
  element: HTMLElement;
  store: PlayerStore;
  provider: DetectedSource['provider'];
}) => TutorPlayer;

export interface PlayerServiceDependencies {
  createHandle?: PlayerHandleFactory;
  createElement?: (source: DetectedSource, document: Document) => VideoPlayerElement;
  getStore?: (element: VideoPlayerElement) => PlayerStore | null;
}

const noop: PlayerUnsubscribe = () => undefined;

export class TutorPlayerService implements PlayerService {
  #active: TutorPlayer | null = null;
  #createHandle: PlayerHandleFactory;
  #createElement: (source: DetectedSource, document: Document) => VideoPlayerElement;
  #getStore: (element: VideoPlayerElement) => PlayerStore | null;

  constructor(dependencies: PlayerServiceDependencies = {}) {
    this.#createHandle = dependencies.createHandle ?? createTutorPlayer;
    this.#createElement = dependencies.createElement ?? createPlayerElement;
    this.#getStore = dependencies.getStore ?? getStore;
  }

  get active(): TutorPlayer | null {
    return this.#active;
  }

  create(element: HTMLElement): TutorPlayer {
    const source: DetectedSource | null = detectSource(element);

    if (!source) {
      throw new Error('[tutor] player: no playable source found on target');
    }

    const host = this.#createElement(source, element.ownerDocument);
    element.replaceWith(host);

    const store = this.#getStore(host);
    if (!store) {
      throw new Error('[tutor] player: store unavailable after init');
    }

    const player = this.#createHandle({ element: host, store, provider: source.provider });
    this.#active = player;

    return player;
  }

  getActive(): TutorPlayer | null {
    return this.#active;
  }

  getCurrentTime(): number {
    return this.#active?.getCurrentTime() ?? 0;
  }

  seek(time: number): void {
    this.#active?.seek(time);
  }

  on<E extends PlayerEvent>(event: E, handler: (payload: PlayerEventMap[E]) => void): PlayerUnsubscribe {
    return this.#active?.on(event, handler) ?? noop;
  }
}
