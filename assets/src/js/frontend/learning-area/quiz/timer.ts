import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';

const QUIZ_TIMER_CLASSES = {
  PROGRESS_ANIMATE: 'tutor-quiz-progress-animate',
} as const;

type QuizExpireAction = 'auto_submit' | 'auto_abandon' | 'autosubmit';

interface QuizTimerConfig {
  duration: number;
  expiresAction?: QuizExpireAction;
  formId?: string;
}

/**
 * Quiz Timer Component
 * Manages countdown timer for quiz attempts
 */
const quizTimer = (config: QuizTimerConfig) => {
  const total = Math.max(0, config.duration);
  const expiresAction = config.expiresAction ?? 'auto_submit';

  return {
    total,
    remaining: total,
    hasLimit: total > 0,
    expired: false,
    expiresAction,
    formId: config.formId ?? '',
    timer: null as number | null,
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

      this.$el?.classList.add(QUIZ_TIMER_CLASSES.PROGRESS_ANIMATE);
    },

    stop() {
      if (this.timer) {
        clearInterval(this.timer);
        this.timer = null;
        this.$el?.classList.remove(QUIZ_TIMER_CLASSES.PROGRESS_ANIMATE);
      }
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
  };
};

export const quizTimerMeta = {
  name: 'quizTimer',
  component: quizTimer,
};
