import { type ServiceMeta } from '@Core/ts/types';
import {
  type PlayerEvent,
  type PlayerEventMap,
  type PlayerService,
  type PlayerUnsubscribe,
  type TutorPlayer,
} from '@Core/ts/types/player';

import { createPlayerElement, type VideoPlayerElement } from './elements';
import { type DetectedSource, detectSource } from './providers';

export interface PlayerServiceDependencies {
  createElement?: (source: DetectedSource, document: Document) => VideoPlayerElement;
}

type AnyHandler = (payload: never) => void;

export class TutorPlayerService implements PlayerService {
  #active: TutorPlayer | null = null;
  #waiting: Array<{ event: PlayerEvent; handler: AnyHandler }> = [];
  #createElement: (source: DetectedSource, document: Document) => VideoPlayerElement;

  constructor(dependencies: PlayerServiceDependencies = {}) {
    this.#createElement = dependencies.createElement ?? createPlayerElement;
  }

  get active(): TutorPlayer | null {
    return this.#active;
  }

  /** Marks a player booted by its own element as active (wired to the element hooks). */
  track(player: TutorPlayer): void {
    this.#active = player;

    for (const { event, handler } of this.#waiting.splice(0)) {
      player.on(event, handler as (payload: PlayerEventMap[PlayerEvent]) => void);
    }
  }

  untrack(player: TutorPlayer): void {
    if (this.#active === player) {
      this.#active = null;
    }
  }

  create(element: HTMLElement): TutorPlayer {
    // Declarative <tutor-video-player> markup boots itself on connect: adopt that handle
    // instead of rebuilding the subtree. Legacy markup still takes the shim path below.
    const booted = (element as Partial<VideoPlayerElement>).player;

    if (booted) {
      this.track(booted);
      return booted;
    }

    const source: DetectedSource | null = detectSource(element);

    if (!source) {
      throw new Error('[tutor] player: no playable source found on target');
    }

    const host = this.#createElement(source, element.ownerDocument);
    element.replaceWith(host);

    const player = host.player;

    if (!player) {
      throw new Error('[tutor] player: handle unavailable after init');
    }

    this.track(player);

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
    const active = this.#active;

    if (active) {
      return active.on(event, handler);
    }

    // No player yet: bind to the first one that appears (research F1).
    const entry = { event, handler: handler as AnyHandler };
    this.#waiting.push(entry);

    return () => {
      const index = this.#waiting.indexOf(entry);
      if (index >= 0) {
        this.#waiting.splice(index, 1);
      }
    };
  }
}

export const playerServiceMeta: ServiceMeta<TutorPlayerService> = {
  name: 'player',
  instance: new TutorPlayerService(),
};
