import type { AlpineComponentMeta } from '@Core/ts/types';
import { tutorConfig } from '@TutorShared/config/config';

import { QUIZ_LAYOUT_KEYS, QUIZ_LAYOUT_SELECTORS, QUIZ_REVEAL_CONFIG, QuizLayoutType } from './constants';
import { revealQuestionWithAnswers } from './helpers';

export interface QuizLayoutConfig {
  layout: (typeof QuizLayoutType)[keyof typeof QuizLayoutType];
  formId: string;
  totalQuestions: number;
  feedbackMode?: string;
  revealWaitMs?: number;
}

const quizLayout = (config: QuizLayoutConfig) => {
  const form = window.TutorCore?.form;
  const docWithViewTransition = document as Document & {
    startViewTransition?: (callback: () => void) => { finished: Promise<void> };
  };

  return {
    layout: config.layout ?? QuizLayoutType.QUESTION_BELOW_EACH_OTHER,
    formId: config.formId ?? '',

    totalQuestions: Number(config.totalQuestions) || 0,
    currentIndex: 1,
    feedbackMode: config.feedbackMode ?? '',
    revealWaitMs: config.revealWaitMs ?? null,
    revealAnswerIds: [] as number[],
    answerRequiredByIndex: {} as Record<number, boolean>,
    revealFooterState: '' as '' | 'correct' | 'incorrect',
    isRevealing: false,

    $el: null as HTMLElement | null,
    $root: null as HTMLElement | null,

    init() {
      this.revealAnswerIds = this.getRevealAnswerIds();
      this.answerRequiredByIndex = this.getAnswerRequiredMap();
      if (this.layout === QuizLayoutType.QUESTION_BELOW_EACH_OTHER) {
        return;
      }
      this.currentIndex = 1;
    },

    isQuestionActive(index: number) {
      if (this.layout === QuizLayoutType.QUESTION_BELOW_EACH_OTHER) {
        return true;
      }
      return index === this.currentIndex;
    },

    canSkip(index: number) {
      if (this.layout === QuizLayoutType.QUESTION_BELOW_EACH_OTHER) {
        return false;
      }
      if (index >= this.totalQuestions) {
        return false;
      }

      if (Object.prototype.hasOwnProperty.call(this.answerRequiredByIndex, index)) {
        return !this.answerRequiredByIndex[index];
      }

      const wrapper = this.getQuestionWrapper(index);
      if (!wrapper) {
        return false;
      }
      return wrapper.dataset.answerRequired !== '1';
    },

    hasAttemptedValue(value: unknown): boolean {
      if (value === null || value === undefined) {
        return false;
      }

      if (typeof value === 'string') {
        return value.trim().length > 0;
      }

      if (Array.isArray(value)) {
        return value.some((item) => this.hasAttemptedValue(item));
      }

      if (typeof value === 'object') {
        return Object.values(value as Record<string, unknown>).some((item) => this.hasAttemptedValue(item));
      }

      return true;
    },

    isQuestionAttempted(index: number): boolean {
      if (!form || !this.formId || !form.hasForm(this.formId)) {
        return false;
      }

      const values = form.getFormState(this.formId).values ?? {};
      const fieldNames = this.getQuestionFieldNames(values, index);

      if (!fieldNames.length) {
        return false;
      }

      return fieldNames.some((fieldName) => this.hasAttemptedValue(values[fieldName]));
    },

    shouldDisableNextButton(): boolean {
      if (this.layout !== QuizLayoutType.SINGLE_QUESTION) {
        return false;
      }

      return !this.isQuestionAttempted(this.currentIndex);
    },

    syncRevealFooterState(wrapper: HTMLElement) {
      if (!this.isRevealMode()) {
        this.revealFooterState = '';
        return;
      }

      const question = this.getQuestionElement(wrapper);
      if (!question || question.getAttribute(QUIZ_REVEAL_CONFIG.DATA_REVEALED_ATTR) !== '1') {
        this.revealFooterState = '';
        return;
      }

      const result = question.getAttribute(QUIZ_REVEAL_CONFIG.DATA_RESULT_ATTR);
      if (result === QUIZ_REVEAL_CONFIG.DATA_OPTION_CORRECT) {
        this.revealFooterState = 'correct';
        return;
      }
      if (result === QUIZ_REVEAL_CONFIG.DATA_OPTION_INCORRECT) {
        this.revealFooterState = 'incorrect';
        return;
      }

      this.revealFooterState = '';
    },

    goPrev() {
      if (this.layout === QuizLayoutType.QUESTION_BELOW_EACH_OTHER) {
        return;
      }
      if (this.currentIndex > 1) {
        this.runWithViewTransition(() => {
          this.currentIndex -= 1;
          this.revealFooterState = '';
        }, 'back');
        this.scrollToQuestion();
      }
    },

    async goNext({ skipValidation = false }: { skipValidation?: boolean } = {}) {
      if (this.layout === QuizLayoutType.QUESTION_BELOW_EACH_OTHER) {
        return;
      }
      if (this.isRevealing) {
        return;
      }
      if (!skipValidation && this.shouldDisableNextButton()) {
        return;
      }

      const wrapper = this.getQuestionWrapper(this.currentIndex);
      if (!wrapper) {
        return;
      }
      if (!skipValidation) {
        const isValid = await this.triggerQuestionValidation(this.currentIndex);
        if (!isValid) {
          return;
        }
      }

      if (!skipValidation && this.isRevealMode() && this.shouldReveal(wrapper)) {
        this.isRevealing = true;
        this.revealQuestion(wrapper);
        this.syncRevealFooterState(wrapper);
        const wait = this.getRevealWaitTime();
        window.setTimeout(() => {
          this.isRevealing = false;
          if (this.currentIndex < this.totalQuestions) {
            this.runWithViewTransition(() => {
              this.currentIndex += 1;
              this.revealFooterState = '';
            });
            this.scrollToQuestion();
          }
        }, wait);
        return;
      }

      if (this.currentIndex < this.totalQuestions) {
        this.runWithViewTransition(() => {
          this.currentIndex += 1;
          this.revealFooterState = '';
        });
        this.scrollToQuestion();
      }
    },

    goTo(index: number) {
      if (this.layout !== QuizLayoutType.QUESTION_PAGINATION) {
        return;
      }
      if (!index || index < 1 || index > this.totalQuestions) {
        return;
      }
      this.runWithViewTransition(
        () => {
          this.currentIndex = index;
          this.revealFooterState = '';
        },
        index < this.currentIndex ? 'back' : 'forward',
      );
      this.scrollToQuestion();
    },

    runWithViewTransition(update: () => void, direction: 'forward' | 'back' = 'forward') {
      const transitionRoot = document.documentElement;
      transitionRoot.style.setProperty('--tutor-quiz-vt-dir', direction === 'back' ? '-1' : '1');

      if (!docWithViewTransition.startViewTransition) {
        update();
        transitionRoot.style.removeProperty('--tutor-quiz-vt-dir');
        return;
      }

      const transition = docWithViewTransition.startViewTransition.call(document, () => {
        update();
      });

      transition.finished.finally(() => {
        transitionRoot.style.removeProperty('--tutor-quiz-vt-dir');
      });
    },

    getRevealWaitTime(): number {
      const feedbackWaitMs = Number(this.revealWaitMs ?? '');
      if (!Number.isNaN(feedbackWaitMs) && feedbackWaitMs > 0) {
        return feedbackWaitMs;
      }
      const configValue = Number(tutorConfig.quiz_answer_display_time ?? '');
      if (!Number.isNaN(configValue) && configValue > 0) {
        return configValue;
      }
      return QUIZ_REVEAL_CONFIG.DEFAULT_WAIT_MS;
    },

    isRevealMode(): boolean {
      const feedbackMode =
        this.feedbackMode || (tutorConfig as { quiz_options?: { feedback_mode?: string } }).quiz_options?.feedback_mode;
      return feedbackMode === 'reveal';
    },

    getRevealAnswerIds(): number[] {
      const script = document.getElementById(QUIZ_REVEAL_CONFIG.ANSWER_CONTEXT_ID);
      if (!script?.textContent) {
        return [];
      }

      try {
        const encoded = script.textContent.trim();
        const decoded = encoded
          .match(/.{1,2}/g)
          ?.map((byte) => String.fromCharCode(parseInt(byte, 16)))
          .join('');
        if (!decoded) {
          return [];
        }
        const parsed = JSON.parse(decoded);
        if (!Array.isArray(parsed)) {
          return [];
        }
        return parsed.map((value) => Number(value)).filter((value) => !Number.isNaN(value));
      } catch {
        return [];
      }
    },

    getQuestionElement(wrapper: HTMLElement): HTMLElement | null {
      return wrapper.querySelector(QUIZ_REVEAL_CONFIG.QUESTION_SELECTOR);
    },

    getQuestionType(wrapper: HTMLElement): string {
      return this.getQuestionElement(wrapper)?.dataset?.question ?? '';
    },

    shouldReveal(wrapper: HTMLElement): boolean {
      if (!this.isRevealMode()) {
        return false;
      }
      if (!this.revealAnswerIds.length) {
        return false;
      }
      const questionType = this.getQuestionType(wrapper);
      return (QUIZ_REVEAL_CONFIG.SUPPORTED_TYPES as readonly string[]).includes(questionType);
    },

    revealQuestion(wrapper: HTMLElement) {
      revealQuestionWithAnswers(wrapper, this.revealAnswerIds);
    },

    revealOnSubmit(): boolean {
      if (!this.isRevealMode()) {
        return false;
      }

      if (this.layout === QuizLayoutType.QUESTION_BELOW_EACH_OTHER) {
        const wrappers = Array.from(
          (this.$root ?? this.$el)?.querySelectorAll<HTMLElement>(`${QUIZ_LAYOUT_SELECTORS.QUESTION_WRAPPER}`) ?? [],
        );
        let revealedAny = false;
        wrappers.forEach((wrapper) => {
          if (this.shouldReveal(wrapper)) {
            this.revealQuestion(wrapper);
            revealedAny = true;
          }
        });
        return revealedAny;
      }

      const wrapper = this.getQuestionWrapper(this.currentIndex);
      if (!wrapper || !this.shouldReveal(wrapper)) {
        return false;
      }
      this.revealQuestion(wrapper);
      return true;
    },

    getQuestionWrapper(index: number) {
      const root = this.$root ?? this.$el;
      return root?.querySelector(
        `${QUIZ_LAYOUT_SELECTORS.QUESTION_WRAPPER}[${QUIZ_LAYOUT_SELECTORS.QUESTION_WRAPPER_ATTR}="${index}"]`,
      ) as HTMLElement | null;
    },

    getAnswerRequiredMap(): Record<number, boolean> {
      const root = this.$root ?? this.$el;
      if (!root) {
        return {};
      }

      const wrappers = Array.from(root.querySelectorAll<HTMLElement>(QUIZ_LAYOUT_SELECTORS.QUESTION_WRAPPER));
      const map: Record<number, boolean> = {};

      wrappers.forEach((wrapper) => {
        const index = Number(wrapper.getAttribute(QUIZ_LAYOUT_SELECTORS.QUESTION_WRAPPER_ATTR));
        if (Number.isNaN(index) || index < 1) {
          return;
        }
        map[index] = wrapper.dataset.answerRequired === '1';
      });

      return map;
    },

    scrollToQuestion() {
      const wrapper = this.getQuestionWrapper(this.currentIndex);
      if (!wrapper) {
        return;
      }
      wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },

    getQuestionIdByIndex(values: Record<string, unknown>, index: number) {
      const entry = Object.entries(values).find(([key]) => key.includes('[quiz_question_ids]'));
      if (!entry) {
        return null;
      }
      const [, value] = entry;
      const ids = Array.isArray(value) ? value : [];

      if (!ids.length) {
        return null;
      }

      return ids[index - 1] ?? null;
    },

    getQuestionFieldNames(values: Record<string, unknown>, index: number) {
      const questionId = this.getQuestionIdByIndex(values, index);
      if (!questionId) {
        return [];
      }
      const needle = `${QUIZ_LAYOUT_KEYS.QUESTION_VALUE_PREFIX}[${questionId}]`;
      return Object.keys(values).filter((key) => key.includes(needle));
    },

    async triggerQuestionValidation(index: number) {
      if (!form || !this.formId || !form.hasForm(this.formId)) {
        return true;
      }

      const values = form.getFormState(this.formId).values ?? {};
      const fieldNames = this.getQuestionFieldNames(values, index);
      if (!fieldNames.length) {
        return true;
      }

      return await form.trigger(this.formId, fieldNames);
    },
  };
};

export const quizLayoutMeta: AlpineComponentMeta = {
  name: 'quizLayout',
  component: quizLayout,
};
