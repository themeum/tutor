import { __ } from '@wordpress/i18n';
import axios from 'axios';

import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { type MutationState } from '@Core/ts/services/Query';
import type { AlpineComponentMeta } from '@Core/ts/types';
import { tutorConfig } from '@TutorShared/config/config';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';

interface QuizSubmissionConfig {
  formId: string;
  attemptId: string;
  quizId: number;
  feedbackMode?: string;
  revealWaitMs?: number;
}

interface QuizAutoStartConfig {
  quizID: number;
}

interface StartQuizPayload {
  quizID: number;
}

interface QuizLayoutConfig {
  layout: keyof typeof QuizLayoutType;
  formId: string;
  totalQuestions: number;
  feedbackMode?: string;
  revealWaitMs?: number;
}

type QuizFooterPosition = 'only' | 'first' | 'middle' | 'last';

const QUIZ_REVEAL_CONFIG = {
  ANSWER_CONTEXT_ID: 'tutor-quiz-context',
  DEFAULT_WAIT_MS: 2000,
  SUPPORTED_TYPES: ['true_false', 'single_choice', 'multiple_choice'] as const,
  OPTION_SELECTOR: '.tutor-quiz-question-option',
  QUESTION_SELECTOR: '.tutor-quiz-question',
  DATA_OPTION_ATTR: 'data-option',
  DATA_REVEALED_ATTR: 'data-revealed',
  DATA_OPTION_CORRECT: 'correct',
  DATA_OPTION_INCORRECT: 'incorrect',
} as const;

type RevealQuestionType = (typeof QUIZ_REVEAL_CONFIG.SUPPORTED_TYPES)[number];

const QUIZ_FOOTER_POSITIONS = {
  ONLY: 'only',
  FIRST: 'first',
  MIDDLE: 'middle',
  LAST: 'last',
} as const;

const QuestionTimeoutAction = {
  AUTO_ABANDON: 'auto_abandon',
  AUTO_SUBMIT: 'auto_submit',
};

const QuizLayoutType = {
  QUESTION_BELOW_EACH_OTHER: 'question_below_each_other',
  QUESTION_PAGINATION: 'question_pagination',
  SINGLE_QUESTION: 'single_question',
};

const ERROR_MESSAGES = {
  SUBMIT_FAILED: __('Failed to submit quiz', 'tutor'),
  ABANDON_FAILED: __('Failed to abandon quiz', 'tutor'),
  REQUIRED_QUESTIONS: __('Please answer all required questions before submitting.', 'tutor'),
} as const;

const QUIZ_LAYOUT_SELECTORS = {
  QUESTION_WRAPPER_ATTR: 'data-quiz-question-index',
  QUESTION_WRAPPER: '.tutor-quiz-question-wrapper',
} as const;

const QUIZ_LAYOUT_KEYS = {
  QUESTION_VALUE_PREFIX: '[quiz_question]',
} as const;

const quizSubmission = (config: QuizSubmissionConfig) => {
  const query = window.TutorCore.query;
  const toast = window.TutorCore.toast;
  const form = window.TutorCore.form;

  return {
    formId: config.formId,
    attemptId: config.attemptId,
    quizId: config.quizId,
    feedbackMode: config.feedbackMode ?? '',
    revealWaitMs: config.revealWaitMs ?? null,
    submitQuizMutation: null as MutationState<{ success?: boolean; data?: unknown }, Record<string, unknown>> | null,
    abandonQuizMutation: null as MutationState<{ success?: boolean }, Record<string, unknown>> | null,
    timeoutQuizMutation: null as MutationState<{ success?: boolean }, Record<string, unknown>> | null,
    hasTimedOut: false,
    isRevealSubmitting: false,
    $el: null as HTMLFormElement | null,
    $root: null as HTMLElement | null,

    init() {
      this.handleQuizSubmit = this.handleQuizSubmit.bind(this);
      this.handleQuizError = this.handleQuizError.bind(this);
      this.handleQuizTimeout = this.handleQuizTimeout.bind(this);

      document.addEventListener(TUTOR_CUSTOM_EVENTS.QUIZ_TIME_EXPIRED, ((event: Event) => {
        const detail = (event as CustomEvent)?.detail ?? {};
        if (detail?.formId && detail.formId !== this.formId) {
          return;
        }
        this.handleQuizTimeout(detail);
      }) as EventListener);

      document.addEventListener(TUTOR_CUSTOM_EVENTS.QUIZ_ABANDON_REQUESTED, ((event: Event) => {
        const detail = (event as CustomEvent)?.detail ?? {};
        if (detail?.formId && detail.formId !== this.formId) {
          return;
        }
        this.handleAbandonQuiz();
      }) as EventListener);

      this.submitQuizMutation = query.useMutation(this.submitQuizAttempt, {
        onSuccess: () => {
          window.location.reload();
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.abandonQuizMutation = query.useMutation(this.abandonQuizAttempt, {
        onSuccess: () => {
          window.location.reload();
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.timeoutQuizMutation = query.useMutation(this.timeoutQuizAttempt, {
        onSuccess: () => {
          window.location.reload();
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });
    },

    handleQuizSubmit(data: Record<string, unknown>) {
      if (this.isRevealSubmitting) {
        return;
      }

      if (this.isRevealMode()) {
        const revealWait = this.getRevealWaitTime();
        const shouldDelay = this.revealOnSubmit();
        if (shouldDelay) {
          this.isRevealSubmitting = true;
          const payload = this.buildSubmitPayload(data);
          window.setTimeout(() => {
            this.submitQuizMutation?.mutate(payload);
            this.isRevealSubmitting = false;
          }, revealWait);
          return;
        }
      }

      const payload = this.buildSubmitPayload(data);
      this.submitQuizMutation?.mutate(payload);
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
      const feedbackMode = this.feedbackMode || tutorConfig.quiz_options?.feedback_mode;
      return feedbackMode === 'reveal';
    },

    getRevealAnswerIds(): number[] {
      const script = document.getElementById(QUIZ_REVEAL_CONFIG.ANSWER_CONTEXT_ID);
      if (!script?.textContent) {
        return [];
      }

      try {
        const decoded = window.atob(script.textContent.trim());
        const parsed = JSON.parse(decoded);
        if (!Array.isArray(parsed)) {
          return [];
        }
        return parsed.map((value) => Number(value)).filter((value) => !Number.isNaN(value));
      } catch {
        return [];
      }
    },

    revealQuestion(wrapper: HTMLElement, revealAnswerIds: number[]) {
      const question = wrapper.querySelector(QUIZ_REVEAL_CONFIG.QUESTION_SELECTOR) as HTMLElement | null;
      if (!question) {
        return;
      }
      if (question.getAttribute(QUIZ_REVEAL_CONFIG.DATA_REVEALED_ATTR) === '1') {
        return;
      }

      const inputs = Array.from(
        question.querySelectorAll<HTMLInputElement>('input[type="radio"], input[type="checkbox"]'),
      );

      inputs.forEach((input) => {
        const option = input.closest(QUIZ_REVEAL_CONFIG.OPTION_SELECTOR) as HTMLElement | null;
        if (!option) {
          return;
        }

        const answerId = Number(input.value);
        const isCorrect = revealAnswerIds.includes(answerId);

        if (isCorrect) {
          option.setAttribute(QUIZ_REVEAL_CONFIG.DATA_OPTION_ATTR, QUIZ_REVEAL_CONFIG.DATA_OPTION_CORRECT);
        } else if (input.checked) {
          option.setAttribute(QUIZ_REVEAL_CONFIG.DATA_OPTION_ATTR, QUIZ_REVEAL_CONFIG.DATA_OPTION_INCORRECT);
        }

        input.disabled = true;
      });

      question.setAttribute(QUIZ_REVEAL_CONFIG.DATA_REVEALED_ATTR, '1');
    },

    revealOnSubmit(): boolean {
      const revealAnswerIds = this.getRevealAnswerIds();
      if (!revealAnswerIds.length) {
        return false;
      }

      const root = this.$root ?? this.$el;
      if (!root) {
        return false;
      }

      const wrappers = Array.from(root.querySelectorAll<HTMLElement>(QUIZ_LAYOUT_SELECTORS.QUESTION_WRAPPER));
      let revealedAny = false;

      wrappers.forEach((wrapper) => {
        const question = wrapper.querySelector(QUIZ_REVEAL_CONFIG.QUESTION_SELECTOR) as HTMLElement | null;
        if (!question) {
          return;
        }
        const questionType = (question.dataset?.question ?? '') as RevealQuestionType;
        if (!(QUIZ_REVEAL_CONFIG.SUPPORTED_TYPES as readonly string[]).includes(questionType)) {
          return;
        }
        this.revealQuestion(wrapper, revealAnswerIds);
        revealedAny = true;
      });

      return revealedAny;
    },

    handleQuizError() {
      toast.error(ERROR_MESSAGES.REQUIRED_QUESTIONS);
    },

    handleAbandonQuiz() {
      if (!this.formId || !form.hasForm(this.formId)) {
        return;
      }

      const data = form.getFormState?.(this.formId)?.values ?? {};
      const payload = this.buildSubmitPayload(data);
      this.abandonQuizMutation?.mutate(payload);
    },

    handleQuizTimeoutAbandon() {
      if (!this.quizId) {
        return;
      }

      this.timeoutQuizMutation?.mutate({ quiz_id: this.quizId });
    },

    handleQuizTimeout(detail: { action?: keyof typeof QuestionTimeoutAction; formId?: string }) {
      const action = detail?.action;
      if (!action || !this.formId || !form.hasForm(this.formId)) {
        return;
      }

      if (this.hasTimedOut) {
        return;
      }

      if (
        this.submitQuizMutation?.isPending ||
        this.abandonQuizMutation?.isPending ||
        this.timeoutQuizMutation?.isPending
      ) {
        return;
      }

      const data = form.getFormState?.(this.formId)?.values ?? {};

      if (action === QuestionTimeoutAction.AUTO_SUBMIT) {
        this.hasTimedOut = true;
        this.handleQuizSubmit(data);
        return;
      }

      if (action === QuestionTimeoutAction.AUTO_ABANDON) {
        this.hasTimedOut = true;
        this.handleQuizTimeoutAbandon();
      }
    },

    buildSubmitPayload(data: Record<string, unknown>): Record<string, unknown> {
      const payload = this.normalizePayload(data);
      payload.attempt_id = this.attemptId;

      return payload;
    },

    normalizePayload(values: Record<string, unknown>): Record<string, unknown> {
      const counts = new Map<string, number>();

      return Object.entries(values).reduce<Record<string, unknown>>((acc, [key, value]) => {
        const baseKey = key.replace(/\[\].*$/, '');
        const prevCount = counts.get(baseKey) ?? 0;
        const nextCount = prevCount + 1;
        counts.set(baseKey, nextCount);

        const appendValue = (target: unknown[], incoming: unknown) => {
          if (Array.isArray(incoming)) {
            incoming.forEach((item) => target.push(item));
            return;
          }
          target.push(incoming);
        };

        if (nextCount === 1) {
          acc[baseKey] = value;
          return acc;
        }

        const existing = acc[baseKey];
        const nextValues: unknown[] = [];

        if (nextCount === 2) {
          appendValue(nextValues, existing);
        } else if (Array.isArray(existing)) {
          existing.forEach((item) => nextValues.push(item));
        }

        appendValue(nextValues, value);
        acc[baseKey] = nextValues;

        return acc;
      }, {});
    },

    submitQuizAttempt(payload: Record<string, unknown>) {
      return axios
        .postForm(window.location.href, {
          tutor_action: endpoints.QUIZ_ATTEMPT_SUBMIT,
          _tutor_nonce: tutorConfig._tutor_nonce,
          ...payload,
        })
        .then((res) => res.data);
    },

    abandonQuizAttempt(payload: Record<string, unknown>) {
      return wpAjaxInstance
        .post(endpoints.QUIZ_ABANDON, {
          tutor_action: endpoints.QUIZ_ATTEMPT_SUBMIT,
          ...payload,
        })
        .then((data) => data as { success?: boolean; data?: unknown });
    },

    timeoutQuizAttempt(payload: Record<string, unknown>) {
      return wpAjaxInstance
        .post(endpoints.QUIZ_TIMEOUT, {
          ...payload,
        })
        .then((data) => data as { success?: boolean; data?: unknown });
    },
  };
};

export const quizSubmissionMeta: AlpineComponentMeta = {
  name: 'quizSubmission',
  component: quizSubmission,
};

const quizLayout = (config: QuizLayoutConfig) => {
  const form = window.TutorCore?.form;

  return {
    layout: config.layout ?? QuizLayoutType.QUESTION_BELOW_EACH_OTHER,
    formId: config.formId ?? '',
    totalQuestions: Number(config.totalQuestions) || 0,
    currentIndex: 1,
    feedbackMode: config.feedbackMode ?? '',
    revealWaitMs: config.revealWaitMs ?? null,
    revealAnswerIds: [] as number[],
    isRevealing: false,
    $el: null as HTMLElement | null,
    $root: null as HTMLElement | null,

    init() {
      this.revealAnswerIds = this.getRevealAnswerIds();
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
      const wrapper = this.getQuestionWrapper(index);
      if (!wrapper || index >= this.totalQuestions) {
        return false;
      }
      return wrapper.dataset.answerRequired !== '1';
    },

    goPrev() {
      if (this.layout === QuizLayoutType.QUESTION_BELOW_EACH_OTHER) {
        return;
      }
      if (this.currentIndex > 1) {
        this.currentIndex -= 1;
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
        const wait = this.getRevealWaitTime();
        window.setTimeout(() => {
          this.isRevealing = false;
          if (this.currentIndex < this.totalQuestions) {
            this.currentIndex += 1;
            this.scrollToQuestion();
          }
        }, wait);
        return;
      }

      if (this.currentIndex < this.totalQuestions) {
        this.currentIndex += 1;
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
      this.currentIndex = index;
      this.scrollToQuestion();
    },

    getFooterPosition(): QuizFooterPosition {
      if (this.totalQuestions === 1) {
        return QUIZ_FOOTER_POSITIONS.ONLY;
      }
      if (this.currentIndex === 1) {
        return QUIZ_FOOTER_POSITIONS.FIRST;
      }
      if (this.currentIndex >= this.totalQuestions) {
        return QUIZ_FOOTER_POSITIONS.LAST;
      }
      return QUIZ_FOOTER_POSITIONS.MIDDLE;
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
        const decoded = window.atob(script.textContent.trim());
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
      const question = this.getQuestionElement(wrapper);
      if (!question) {
        return;
      }
      if (question.getAttribute(QUIZ_REVEAL_CONFIG.DATA_REVEALED_ATTR) === '1') {
        return;
      }

      const inputs = Array.from(
        question.querySelectorAll<HTMLInputElement>('input[type="radio"], input[type="checkbox"]'),
      );

      inputs.forEach((input) => {
        const option = input.closest(QUIZ_REVEAL_CONFIG.OPTION_SELECTOR) as HTMLElement | null;
        if (!option) {
          return;
        }

        const answerId = Number(input.value);
        const isCorrect = this.revealAnswerIds.includes(answerId);

        if (isCorrect) {
          option.setAttribute(QUIZ_REVEAL_CONFIG.DATA_OPTION_ATTR, QUIZ_REVEAL_CONFIG.DATA_OPTION_CORRECT);
        } else if (input.checked) {
          option.setAttribute(QUIZ_REVEAL_CONFIG.DATA_OPTION_ATTR, QUIZ_REVEAL_CONFIG.DATA_OPTION_INCORRECT);
        }

        input.disabled = true;
      });

      question.setAttribute(QUIZ_REVEAL_CONFIG.DATA_REVEALED_ATTR, '1');
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

const quizAutoStart = (config: QuizAutoStartConfig) => {
  const query = window.TutorCore.query;
  const toast = window.TutorCore.toast;

  return {
    quizID: config.quizID,
    autoStart: Number(tutorConfig.quiz_options?.quiz_auto_start),
    startQuizMutation: null as MutationState<unknown, StartQuizPayload> | null,

    init() {
      this.startQuizMutation = query.useMutation(this.startQuiz, {
        onSuccess: () => {
          window.location.reload();
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      if (!this.autoStart) {
        return;
      }

      this.startQuizMutation?.mutate({ quizID: this.quizID });
    },

    handleStartQuiz() {
      this.startQuizMutation?.mutate({ quizID: this.quizID });
    },

    startQuiz(payload: StartQuizPayload) {
      return axios.postForm(window.location.href, {
        quiz_id: payload.quizID,
        tutor_action: endpoints.START_QUIZ,
        _tutor_nonce: tutorConfig._tutor_nonce,
      });
    },
  };
};

export const quizAutoStartMeta: AlpineComponentMeta = {
  name: 'quizAutoStart',
  component: quizAutoStart,
};
