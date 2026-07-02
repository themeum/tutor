import confetti from 'canvas-confetti';

import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';

const TIME_SPENT_MODAL = 'tutor-time-spent-modal';

const fire = () => {
  const end = Date.now() + 850;

  const frame = () => {
    confetti({
      particleCount: 5,
      spread: 360,
      startVelocity: 30,
      origin: {
        x: Math.random(),
        y: 0,
      },
      shapes: ['square'],
      ticks: 850,
      zIndex: 100000,
      scalar: 2,
    });

    if (Date.now() < end) {
      requestAnimationFrame(frame);
    }
  };

  frame();
};

export function initializeConfetti(): void {
  document.addEventListener(TUTOR_CUSTOM_EVENTS.MODAL_OPEN, ((e: CustomEvent) => {
    if (e.detail?.id === TIME_SPENT_MODAL) {
      fire();
    }
  }) as EventListener);
}
