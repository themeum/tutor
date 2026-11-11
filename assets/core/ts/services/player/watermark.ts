import { type TutorPlayer } from '@Core/ts/types/player';

export const WATERMARK_POSITIONS = [
  'pos-top-left',
  'pos-top-center',
  'pos-top-right',
  'pos-middle-left',
  'pos-middle-right',
  'pos-bottom-left',
  'pos-bottom-center',
  'pos-bottom-right',
] as const;

export type WatermarkPosition = (typeof WATERMARK_POSITIONS)[number];

export interface WatermarkControllerOptions {
  element: HTMLElement;
  player?: TutorPlayer | null;
  intervalMs?: number;
  fadeMs?: number;
}

export class WatermarkController {
  readonly #element: HTMLElement;
  readonly #player: TutorPlayer | null;
  readonly #intervalMs: number;
  readonly #fadeMs: number;

  #currentPosIndex = 0;
  #timer: ReturnType<typeof setInterval> | null = null;
  #disposed = false;

  constructor({ element, player = null, intervalMs = 30000, fadeMs = 250 }: WatermarkControllerOptions) {
    this.#element = element;
    this.#player = player;
    this.#intervalMs = intervalMs;
    this.#fadeMs = fadeMs;

    this.#init();
  }

  get currentPosition(): WatermarkPosition {
    return WATERMARK_POSITIONS[this.#currentPosIndex];
  }

  #init(): void {
    // Clean existing position classes and set initial
    WATERMARK_POSITIONS.forEach((pos) => this.#element.classList.remove(pos));
    this.#element.classList.add(WATERMARK_POSITIONS[this.#currentPosIndex]);
    this.#element.classList.add('is-active');

    this.start();

    if (this.#player) {
      this.#player.on('play', () => this.start());
      this.#player.on('pause', () => this.stop());
      this.#player.on('ended', () => this.stop());
    }
  }

  jumpToNextPosition(): void {
    if (this.#disposed) {
      return;
    }

    let nextIndex: number;
    do {
      nextIndex = Math.floor(Math.random() * WATERMARK_POSITIONS.length);
    } while (nextIndex === this.#currentPosIndex && WATERMARK_POSITIONS.length > 1);

    this.#element.classList.add('is-fading');

    setTimeout(() => {
      if (this.#disposed) {
        return;
      }
      this.#element.classList.remove(WATERMARK_POSITIONS[this.#currentPosIndex]);
      this.#element.classList.add(WATERMARK_POSITIONS[nextIndex]);
      this.#currentPosIndex = nextIndex;
      this.#element.classList.remove('is-fading');
    }, this.#fadeMs);
  }

  start(): void {
    this.stop();
    if (!this.#disposed) {
      this.#timer = setInterval(() => this.jumpToNextPosition(), this.#intervalMs);
    }
  }

  stop(): void {
    if (this.#timer) {
      clearInterval(this.#timer);
      this.#timer = null;
    }
  }

  dispose(): void {
    this.#disposed = true;
    this.stop();
  }
}

/**
 * Initializes watermarks inside a player element container.
 */
export function initPlayerWatermark(container: HTMLElement, player?: TutorPlayer | null): WatermarkController | null {
  const watermarkEl = container.querySelector('.tutor-video-watermark') as HTMLElement | null;
  if (!watermarkEl) {
    return null;
  }

  const existing = (watermarkEl as unknown as { __wmController?: WatermarkController }).__wmController;
  if (existing) {
    return existing;
  }

  const controller = new WatermarkController({ element: watermarkEl, player });
  (watermarkEl as unknown as { __wmController: WatermarkController }).__wmController = controller;

  return controller;
}
