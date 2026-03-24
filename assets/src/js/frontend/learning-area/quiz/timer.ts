import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';

import { getAttemptedQuestionCountFromForm } from './helpers';

const QUIZ_TIMER_CLASSES = {
  PROGRESS_ANIMATE: 'tutor-quiz-progress-animate',
} as const;

type QuizExpireAction = 'auto_submit' | 'auto_abandon' | 'autosubmit';
type TimerState = 'initial' | 'warning' | 'critical';

interface QuizTimerConfig {
  duration: number;
  hasLimit?: boolean;
  expiresAction?: QuizExpireAction;
  formId?: string;
  totalQuestions?: number;
}

/**
 * Quiz Timer Component
 * Manages countdown timer for quiz attempts
 */
const quizTimer = (config: QuizTimerConfig) => {
  const total = Math.max(0, config.duration);
  const hasLimit = typeof config.hasLimit === 'boolean' ? config.hasLimit : total > 0;
  const expiresAction = config.expiresAction ?? 'auto_submit';

  return {
    total,
    remaining: total,
    hasLimit,
    expired: false,
    expiresAction,
    formId: config.formId ?? '',
    timer: null as number | null,
    shakeTimer: null as number | null,
    shaking: false,
    totalQuestions: Number(config.totalQuestions) || 0,
    $el: null as HTMLElement | null,

    init() {
      if (!this.hasLimit) {
        return;
      }

      if (this.remaining <= 0) {
        this.handleExpire();
        return;
      }

      this.start();
    },

    start() {
      this.stop();

      this.timer = window.setInterval(() => {
        if (this.remaining > 0) {
          this.remaining--;
        } else {
          this.handleExpire();
        }
      }, 1000);

      this.startShakeInterval();
      this.$el?.classList.add(QUIZ_TIMER_CLASSES.PROGRESS_ANIMATE);
    },

    stop() {
      if (this.timer) {
        clearInterval(this.timer);
        this.timer = null;
        this.$el?.classList.remove(QUIZ_TIMER_CLASSES.PROGRESS_ANIMATE);
      }
      this.stopShakeInterval();
    },

    startShakeInterval() {
      this.stopShakeInterval();
      this.shakeTimer = window.setInterval(() => {
        if (this.timerState === 'critical') {
          this.shaking = true;
          window.setTimeout(() => {
            this.shaking = false;
          }, 500);
        }
      }, 2000);
    },

    stopShakeInterval() {
      if (this.shakeTimer) {
        clearInterval(this.shakeTimer);
        this.shakeTimer = null;
      }
      this.shaking = false;
    },

    normalizeExpireAction(action: QuizExpireAction) {
      if (action === 'autosubmit') {
        return 'auto_submit';
      }
      return action;
    },

    dispatchQuizEvent(action: QuizExpireAction) {
      document.dispatchEvent(
        new CustomEvent(TUTOR_CUSTOM_EVENTS.QUIZ_TIME_EXPIRED, {
          detail: {
            action: this.normalizeExpireAction(action),
            formId: this.formId,
          },
        }),
      );
    },

    requestAbandon() {
      document.dispatchEvent(
        new CustomEvent(TUTOR_CUSTOM_EVENTS.QUIZ_ABANDON_REQUESTED, {
          detail: {
            formId: this.formId,
          },
        }),
      );
    },

    handleExpire() {
      if (this.expired) {
        return;
      }

      this.expired = true;
      this.remaining = 0;
      this.stop();

      this.dispatchQuizEvent(this.expiresAction);
    },

    get minutes() {
      return String(Math.floor(this.remaining / 60)).padStart(2, '0');
    },

    get seconds() {
      return String(this.remaining % 60).padStart(2, '0');
    },

    get progress() {
      if (!this.total) {
        return 0;
      }

      return ((this.total - this.remaining) / this.total) * 100;
    },

    get timerState(): TimerState {
      if (!this.total || !this.hasLimit) {
        return 'initial';
      }

      const remainingPercent = (this.remaining / this.total) * 100;

      if (remainingPercent <= 25) {
        return 'critical';
      }

      if (remainingPercent <= 50) {
        return 'warning';
      }

      return 'initial';
    },

    get attemptedCount(): number {
      return getAttemptedQuestionCountFromForm(this.formId);
    },

    get attemptedProgress(): number {
      if (!this.totalQuestions) {
        return 0;
      }

      return (this.attemptedCount / this.totalQuestions) * 100;
    },
  };
};

export const quizTimerMeta = {
  name: 'quizTimer',
  component: quizTimer,
};
