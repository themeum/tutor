const QUIZ_TIMER_CLASSES = {
  PROGRESS_ANIMATE: 'tutor-quiz-progress-animate',
} as const;

/**
 * Quiz Timer Component
 * Manages countdown timer for quiz attempts
 */
const quizTimer = (duration: number) => ({
  total: duration,
  remaining: duration,
  timer: null as number | null,
  $el: null as HTMLElement | null,

  init() {
    this.start();
  },

  start() {
    this.stop();

    this.timer = window.setInterval(() => {
      if (this.remaining > 0) {
        this.remaining--;
      } else {
        this.stop();
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

  get minutes() {
    return String(Math.floor(this.remaining / 60)).padStart(2, '0');
  },

  get seconds() {
    return String(this.remaining % 60).padStart(2, '0');
  },

  get progress() {
    return ((this.total - this.remaining) / this.total) * 100;
  },
});

export const quizTimerMeta = {
  name: 'quizTimer',
  component: quizTimer,
};
