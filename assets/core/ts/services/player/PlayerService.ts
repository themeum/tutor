import { type ServiceMeta } from '@Core/ts/types';
import {
  type PlayerEvent,
  type PlayerEventMap,
  type PlayerService,
  type PlayerUnsubscribe,
  type TutorPlayer,
} from '@Core/ts/types/player';

import { createPlayerElement, type VideoPlayerElement } from './elements';
import { type AnyHandler } from './Player';
import { type DetectedSource, detectSource } from './providers';

export interface PlayerServiceDependencies {
  createElement?: (source: DetectedSource, document: Document) => VideoPlayerElement;
}

type Sub = { event: PlayerEvent; handler: AnyHandler; off: PlayerUnsubscribe | null };

export class TutorPlayerService implements PlayerService {
  #active: TutorPlayer | null = null;
  #subs = new Set<Sub>();
  #live: TutorPlayer[] = [];
  #createElement: (source: DetectedSource, document: Document) => VideoPlayerElement;

  constructor(dependencies: PlayerServiceDependencies = {}) {
    this.#createElement = dependencies.createElement ?? createPlayerElement;
  }

  #bind(sub: Sub, player: TutorPlayer): void {
    sub.off?.();
    sub.off = player.on(sub.event, sub.handler as (payload: PlayerEventMap[PlayerEvent]) => void);
  }

  /** Marks a player booted by its own element as active (wired to the element hooks). */
  track(player: TutorPlayer): void {
    if (this.#active === player && this.#live.includes(player)) {
      return;
    }
    if (!this.#live.includes(player)) {
      this.#live.push(player);
    }
    this.#active = player;
    this.#subs.forEach((sub) => this.#bind(sub, player));
  }

  untrack(player: TutorPlayer): void {
    this.#live = this.#live.filter((p) => p !== player);
    if (this.#active !== player) {
      return;
    }
    this.#subs.forEach((sub) => {
      sub.off?.();
      sub.off = null;
    });
    this.#active = this.#live.at(-1) ?? null;
    if (this.#active) {
      this.#subs.forEach((sub) => this.#bind(sub, this.#active!));
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
      host.replaceWith(element);
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
    const sub: Sub = { event, handler: handler as AnyHandler, off: null };
    this.#subs.add(sub);

    if (this.#active) {
      this.#bind(sub, this.#active);
    }

    return () => {
      sub.off?.();
      this.#subs.delete(sub);
    };
  }
}

export const playerServiceMeta: ServiceMeta<TutorPlayerService> = {
  name: 'player',
  instance: new TutorPlayerService(),
};
