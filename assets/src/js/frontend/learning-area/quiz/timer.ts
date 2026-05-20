import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';

import { getAttemptedQuestionCountFromForm } from './helpers';

const QUIZ_TIMER_CLASSES = {
  PROGRESS_ANIMATE: 'tutor-quiz-progress-animate',
} as const;

type QuizExpireAction = 'auto_submit' | 'auto_abandon' | 'autosubmit';
type TimerState = 'initial' | 'warning' | 'critical';
type TimerFormat = 'compact' | 'hours' | 'days';
type TimerDisplayToken = {
  key: string;
  type: 'digit' | 'separator' | 'suffix' | 'spacer';
  value: string;
};

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
    isReady: false,
    expiresAction,
    formId: config.formId ?? '',
    timer: null as number | null,
    shakeTimer: null as number | null,
    shaking: false,
    totalQuestions: Number(config.totalQuestions) || 0,
    $el: null as HTMLElement | null,

    init() {
      if (!this.hasLimit) {
        this.isReady = true;
        return;
      }

      document.addEventListener(TUTOR_CUSTOM_EVENTS.QUIZ_ATTEMPT_COMPLETED, ((event: Event) => {
        const detail = (event as CustomEvent)?.detail ?? {};
        if (detail?.formId && detail.formId !== this.formId) {
          return;
        }

        this.stop();
      }) as EventListener);

      if (this.remaining <= 0) {
        this.isReady = true;
        this.handleExpire();
        return;
      }

      this.isReady = true;
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
      if (this.days > 0 || this.hours > 0) {
        return Math.floor((this.remaining % 3600) / 60);
      }

      return Math.floor(this.remaining / 60);
    },

    get hours() {
      if (this.days > 0) {
        return Math.floor((this.remaining % 86400) / 3600);
      }

      return Math.floor(this.remaining / 3600);
    },

    get days() {
      return Math.floor(this.remaining / 86400);
    },

    get seconds() {
      return this.remaining % 60;
    },

    get timerFormat(): TimerFormat {
      if (this.days > 0) {
        return 'days';
      }

      if (this.hours > 0) {
        return 'hours';
      }

      return 'compact';
    },

    get displayTokens(): TimerDisplayToken[] {
      const tokens: TimerDisplayToken[] = [];
      const pushDigits = (prefix: string, value: string) => {
        value.split('').forEach((char, index) => {
          tokens.push({
            key: `${prefix}-${index}`,
            type: 'digit',
            value: char,
          });
        });
      };

      if (this.timerFormat === 'days') {
        pushDigits('days', String(this.days));
        tokens.push({ key: 'days-suffix', type: 'suffix', value: 'd' });
        tokens.push({ key: 'days-spacer', type: 'spacer', value: ' ' });
      }

      if (this.timerFormat === 'days' || this.timerFormat === 'hours') {
        pushDigits('hours', String(this.hours).padStart(2, '0'));
        tokens.push({ key: 'hours-separator', type: 'separator', value: ':' });
      }

      pushDigits('minutes', String(this.minutes).padStart(2, '0'));
      tokens.push({ key: 'minutes-separator', type: 'separator', value: ':' });
      pushDigits('seconds', String(this.seconds).padStart(2, '0'));

      return tokens;
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
