import type { AlpineComponentMeta } from '@Core/ts/types';

import { QUIZ_EVENTS } from './constants';

const RADAR_DEFAULT_SECONDS = 5;
const RADAR_SELECTORS = {
  BG_CANVAS: '[data-quiz-autostart-canvas="bg"]',
  SWEEP_CANVAS: '[data-quiz-autostart-canvas="sweep"]',
  DIGIT: '[data-quiz-autostart-digit]',
} as const;

export interface QuizRadarConfig {
  seconds: number;
  eventName: string;
}

const quizRadar = (config: QuizRadarConfig) => ({
  seconds: Number(config.seconds) || RADAR_DEFAULT_SECONDS,
  eventName: config.eventName || QUIZ_EVENTS.AUTO_START_COMPLETE,
  remaining: 0,
  angle: -90,
  lastTick: null as number | null,
  animationFrame: null as number | null,
  countdownTimer: null as number | null,
  card: null as HTMLElement | null,
  bgCanvas: null as HTMLCanvasElement | null,
  sweepCanvas: null as HTMLCanvasElement | null,
  resizeHandler: null as (() => void) | null,
  $el: null as HTMLElement | null,

  init() {
    this.remaining = this.seconds;
    this.cacheElements();
    this.waitForLayout();
  },

  destroy() {
    if (this.animationFrame) {
      window.cancelAnimationFrame(this.animationFrame);
      this.animationFrame = null;
    }
    if (this.countdownTimer) {
      window.clearInterval(this.countdownTimer);
      this.countdownTimer = null;
    }
    if (this.resizeHandler) {
      window.removeEventListener('resize', this.resizeHandler);
      this.resizeHandler = null;
    }
  },

  cacheElements() {
    this.card = this.$el as HTMLElement;
    this.bgCanvas = this.card.querySelector<HTMLCanvasElement>(RADAR_SELECTORS.BG_CANVAS);
    this.sweepCanvas = this.card.querySelector<HTMLCanvasElement>(RADAR_SELECTORS.SWEEP_CANVAS);

    if (!this.resizeHandler) {
      this.resizeHandler = () => this.resize();
      window.addEventListener('resize', this.resizeHandler);
    }
  },

  resize() {
    if (!this.card || !this.bgCanvas || !this.sweepCanvas) {
      return;
    }

    const rect = this.card.getBoundingClientRect();
    const width = Math.max(0, Math.floor(rect.width));
    const height = Math.max(0, Math.floor(rect.height));
    this.bgCanvas.width = width;
    this.bgCanvas.height = height;
    this.sweepCanvas.width = width;
    this.sweepCanvas.height = height;
    this.drawBackground();
  },

  waitForLayout(retries = 30) {
    if (!this.card) {
      return;
    }

    const rect = this.card.getBoundingClientRect();
    if (rect.width > 0 && rect.height > 0) {
      this.resize();
      this.startRadarAnimation();
      this.startCountdownTimer();
      return;
    }

    if (retries <= 0) {
      return;
    }

    window.requestAnimationFrame(() => this.waitForLayout(retries - 1));
  },

  drawBackground() {
    if (!this.bgCanvas || !this.card) {
      return;
    }

    const ctx = this.bgCanvas.getContext('2d');
    if (!ctx) {
      return;
    }

    const width = this.card.clientWidth;
    const height = this.card.clientHeight;
    const centerX = width / 2;
    const centerY = height / 2;
    const radius = Math.sqrt(centerX * centerX + centerY * centerY);
    const innerRadius = radius * 0.3;
    const outerRadius = radius * 0.52;

    ctx.clearRect(0, 0, width, height);
    ctx.beginPath();
    ctx.arc(centerX, centerY, innerRadius, 0, Math.PI * 2);
    ctx.fillStyle = '#E7EDFC';
    ctx.fill();

    ctx.beginPath();
    ctx.arc(centerX, centerY, outerRadius, 0, Math.PI * 2);
    ctx.strokeStyle = '#E7EDFC';
    ctx.lineWidth = 1.5;
    ctx.stroke();
  },

  drawSweep() {
    if (!this.sweepCanvas || !this.card) {
      return;
    }

    const ctx = this.sweepCanvas.getContext('2d');
    if (!ctx) {
      return;
    }

    const width = this.card.clientWidth;
    const height = this.card.clientHeight;
    const centerX = width / 2;
    const centerY = height / 2;
    const radius = Math.sqrt(centerX * centerX + centerY * centerY);

    // Fixed anchor: 12 o'clock
    const START_DEG = -90;
    const startRad = (START_DEG * Math.PI) / 180;

    // Moving end: goes from 0° → 360° (i.e. -90° clockwise back to -90°) over 1 s
    const progress = this.angle / 360; // 0..1
    const sweepRad = startRad + progress * Math.PI * 2;

    const steps = 340;
    const colorStart = [197, 208, 245];
    const colorEnd = [241, 245, 254];

    ctx.clearRect(0, 0, width, height);

    // Draw gradient fan from the fixed anchor to the moving arm
    for (let i = steps; i >= 0; i--) {
      const t = i / steps; // 1 = near moving arm, 0 = near anchor
      const a = sweepRad - (sweepRad - startRad) * t;
      const colorT = t < 0.3 ? 0 : (t - 0.3) / 0.7;
      const rgb = [
        Math.round(colorStart[0] + (colorEnd[0] - colorStart[0]) * colorT),
        Math.round(colorStart[1] + (colorEnd[1] - colorStart[1]) * colorT),
        Math.round(colorStart[2] + (colorEnd[2] - colorStart[2]) * colorT),
      ];
      const alpha = 0.3 + (1 - 0.3) * colorT;

      ctx.beginPath();
      ctx.moveTo(centerX, centerY);
      ctx.arc(centerX, centerY, radius, a, a + 0.025);
      ctx.closePath();
      ctx.fillStyle = `rgba(${rgb[0]},${rgb[1]},${rgb[2]},${(alpha * (1 - t * 0.5)).toFixed(3)})`;
      ctx.fill();
    }

    // Draw moving arm
    ctx.beginPath();
    ctx.moveTo(centerX, centerY);
    ctx.lineTo(centerX + Math.cos(sweepRad) * radius, centerY + Math.sin(sweepRad) * radius);
    ctx.strokeStyle = 'rgba(62,100,222,0.30)';
    ctx.lineWidth = 1.5;
    ctx.stroke();
  },

  startRadarAnimation() {
    // this.angle tracks degrees elapsed within the current 1-second cycle (0..360)
    this.angle = 0;

    const tick = (timestamp: number) => {
      if (this.lastTick !== null) {
        const dt = timestamp - this.lastTick;
        // Complete one full revolution per second
        this.angle += (360 / 1000) * dt;

        // Reset to beginning of next cycle (stays in sync with 1-s countdown)
        if (this.angle >= 360) {
          this.angle -= 360;
        }
      }

      this.drawSweep();
      this.lastTick = timestamp;
      this.animationFrame = window.requestAnimationFrame(tick);
    };

    this.animationFrame = window.requestAnimationFrame(tick);
  },

  startCountdownTimer() {
    const digit = this.card?.querySelector<HTMLElement>(RADAR_SELECTORS.DIGIT);
    if (digit) {
      digit.textContent = String(this.remaining);
    }

    this.countdownTimer = window.setInterval(() => {
      this.remaining = Math.max(0, this.remaining - 1);
      if (digit) {
        digit.textContent = String(this.remaining);
      }

      if (this.remaining === 0) {
        this.finishCountdown();
      }
    }, 1000);
  },

  finishCountdown() {
    if (this.countdownTimer) {
      window.clearInterval(this.countdownTimer);
      this.countdownTimer = null;
    }
    document.dispatchEvent(new CustomEvent(this.eventName));
  },
});

export const quizRadarMeta: AlpineComponentMeta = {
  name: 'radar',
  component: quizRadar,
};
