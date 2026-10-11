import {
  type PlayerEvent,
  type PlayerEventMap,
  type PlayerOptions,
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
  document?: Document;
}

const noop: PlayerUnsubscribe = () => undefined;

export class TutorPlayerService implements PlayerService {
  #active: TutorPlayer | null = null;
  #createHandle: PlayerHandleFactory;
  #createElement: (source: DetectedSource, document: Document) => VideoPlayerElement;
  #getStore: (element: VideoPlayerElement) => PlayerStore | null;
  #document: Document;

  constructor(dependencies: PlayerServiceDependencies = {}) {
    this.#createHandle = dependencies.createHandle ?? createTutorPlayer;
    this.#createElement = dependencies.createElement ?? createPlayerElement;
    this.#getStore = dependencies.getStore ?? getStore;
    this.#document = dependencies.document ?? document;
  }

  get active(): TutorPlayer | null {
    return this.#active;
  }

  create(target: HTMLElement | string, options: PlayerOptions = {}): TutorPlayer {
    const element = this.#resolve(target);

    if (!element) {
      throw new Error('[tutor] player: target element not found');
    }

    const source: DetectedSource | null = options.src
      ? { provider: options.provider ?? 'html5', src: options.src, poster: options.poster }
      : detectSource(element);

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

  getDuration(): number {
    return this.#active?.getDuration() ?? 0;
  }

  seek(time: number): void {
    this.#active?.seek(time);
  }

  play(): void {
    void this.#active?.play();
  }

  pause(): void {
    this.#active?.pause();
  }

  on<E extends PlayerEvent>(event: E, handler: (payload: PlayerEventMap[E]) => void): PlayerUnsubscribe {
    return this.#active?.on(event, handler) ?? noop;
  }

  #resolve(target: HTMLElement | string): HTMLElement | null {
    if (typeof target !== 'string') {
      return target;
    }
    return this.#document.querySelector<HTMLElement>(target);
  }
}
