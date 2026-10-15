import confetti from 'canvas-confetti';

import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';

const MODAL_CLASS = {
  wrapper: 'tutor-modal',
  content: 'tutor-modal-content',
};
const TIME_SPENT_MODAL = 'tutor-time-spent-modal';
const CONFETTI_FRAME_RATE_FPS = 60;
const CONFETTI_FIRE_DURATION_MS = 850;
const CONFETTI_PARTICLE_TICKS = 850;
const CLEANUP_BUFFER_MS = 500; // safety margin for slower frame rates

const PARTICLE_LIFETIME_MS = (CONFETTI_PARTICLE_TICKS / CONFETTI_FRAME_RATE_FPS) * 1000;
const CLEANUP_TIMEOUT_MS = CONFETTI_FIRE_DURATION_MS + PARTICLE_LIFETIME_MS + CLEANUP_BUFFER_MS;

const findVisibleModal = (): HTMLElement | null => {
  const modals = document.querySelectorAll<HTMLElement>(`.${MODAL_CLASS.wrapper}`);

  for (const modal of modals) {
    if (window.getComputedStyle(modal).display !== 'none') {
      return modal;
    }
  }

  return null;
};

/**
 * Creates a full-viewport canvas inside the visible modal wrapper,
 * positioned between the backdrop and `.tutor-modal-content` in DOM
 * order so it layers above the backdrop but below the modal body.
 */
const createConfettiCanvas = (modalWrapper: HTMLElement): { canvas: HTMLCanvasElement; cleanup: () => void } => {
  const canvas = document.createElement('canvas');

  // Explicit buffer dimensions so confetti renders at full resolution.
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;

  // `position: absolute` works here because `.tutor-modal` is the
  // containing block (it has `position: fixed; width/height: 100%`).
  canvas.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;pointer-events:none;';

  // Insert before `.tutor-modal-content` (i.e. after the backdrop div)
  const content = modalWrapper.querySelector(`.${MODAL_CLASS.content}`);
  if (content) {
    modalWrapper.insertBefore(canvas, content);
  } else {
    modalWrapper.appendChild(canvas);
  }

  return { canvas, cleanup: () => canvas.remove() };
};

let cleanupTimeoutId: ReturnType<typeof setTimeout> | undefined;
let animationFrameId: number | undefined;
let activeCleanup: (() => void) | undefined;

const fire = () => {
  const modalWrapper = findVisibleModal();
  if (!modalWrapper) {
    return;
  }

  const { canvas, cleanup } = createConfettiCanvas(modalWrapper);
  activeCleanup = cleanup;

  const fireConfetti = confetti.create(canvas, { resize: true });
  const end = Date.now() + CONFETTI_FIRE_DURATION_MS;

  const frame = () => {
    fireConfetti({
      particleCount: 5,
      spread: 360,
      startVelocity: 30,
      origin: { x: Math.random(), y: 0 },
      shapes: ['square'],
      ticks: CONFETTI_PARTICLE_TICKS,
      scalar: 2,
    });

    if (Date.now() < end) {
      animationFrameId = requestAnimationFrame(frame);
    }
  };

  frame();

  cleanupTimeoutId = setTimeout(() => {
    cleanup();
    activeCleanup = undefined;
  }, CLEANUP_TIMEOUT_MS);
};

const stopConfetti = () => {
  if (animationFrameId !== undefined) {
    cancelAnimationFrame(animationFrameId);
    animationFrameId = undefined;
  }
  if (cleanupTimeoutId !== undefined) {
    clearTimeout(cleanupTimeoutId);
    cleanupTimeoutId = undefined;
  }
  activeCleanup?.();
  activeCleanup = undefined;
};

export function initializeConfetti(): void {
  document.addEventListener(TUTOR_CUSTOM_EVENTS.MODAL_OPEN, ((e: CustomEvent) => {
    if (e.detail?.id === TIME_SPENT_MODAL) {
      setTimeout(() => fire(), 300);
    }
  }) as EventListener);

  document.addEventListener(TUTOR_CUSTOM_EVENTS.MODAL_CLOSE, ((e: CustomEvent) => {
    if (e.detail?.id === TIME_SPENT_MODAL) {
      stopConfetti();
    }
  }) as EventListener);
}
