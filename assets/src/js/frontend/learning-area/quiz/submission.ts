import axios from 'axios';

import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { type MutationState } from '@Core/ts/services/Query';
import type { AlpineComponentMeta } from '@Core/ts/types';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';

import { tutorConfig } from '@TutorShared/config/config';
import {
  ERROR_MESSAGES,
  QUIZ_ABANDON_CONFIG,
  QUIZ_LAYOUT_SELECTORS,
  QUIZ_REVEAL_CONFIG,
  QuestionTimeoutAction,
} from './constants';
import { getAttemptedQuestionCountFromForm, revealQuestionWithAnswers } from './helpers';

export interface QuizSubmissionConfig {
  formId: string;
  attemptId: string;
  quizId: number;
  abandonModalId: string;
  totalQuestions: number;
  enableAnswerReveal?: boolean;
  revealWaitMs?: number;
  submittedModalId?: string;
  timeoutModalId?: string;
}

const quizSubmission = (config: QuizSubmissionConfig) => {
  const query = window.TutorCore.query;
  const toast = window.TutorCore.toast;
  const form = window.TutorCore.form;
  const modal = window.TutorCore.modal;

  return {
    formId: config.formId,
    attemptId: config.attemptId,
    quizId: config.quizId,
    abandonModalId: config.abandonModalId,
    totalQuestions: Number(config.totalQuestions) || 0,
    enableAnswerReveal: config.enableAnswerReveal ?? false,
    revealWaitMs: config.revealWaitMs ?? null,
    submittedModalId: config.submittedModalId ?? '',
    timeoutModalId: config.timeoutModalId ?? '',

    submitQuizMutation: null as MutationState<{ success?: boolean; data?: unknown }, Record<string, unknown>> | null,
    abandonQuizMutation: null as MutationState<{ success?: boolean }, Record<string, unknown>> | null,
    timeoutQuizMutation: null as MutationState<{ success?: boolean }, Record<string, unknown>> | null,

    hasTimedOut: false,
    isRevealSubmitting: false,
    beforeUnloadTriggered: false,
    isAbandoningNavigation: false,
    skipBeforeUnload: false,
    pendingNavigationAction: '' as 'reload' | 'navigate' | '',
    pendingNavigationUrl: '',
    pendingTimeoutSubmission: false,
    resultModalOpenId: '',
    modalCloseHandler: null as ((event: Event) => void) | null,

    beforeUnloadHandler: null as ((event: BeforeUnloadEvent) => string | void) | null,
    navigationHandler: null as ((event: MouseEvent) => void) | null,

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

      this.beforeUnloadHandler = this.handleBeforeUnload.bind(this);
      this.navigationHandler = this.handleNavigationAttempt.bind(this);
      this.modalCloseHandler = this.handleModalClose.bind(this);

      window.addEventListener('beforeunload', this.beforeUnloadHandler);
      document.addEventListener(QUIZ_ABANDON_CONFIG.NAVIGATION_EVENT, this.navigationHandler, true);
      document.addEventListener(TUTOR_CUSTOM_EVENTS.MODAL_CLOSED, this.modalCloseHandler);

      this.submitQuizMutation = query.useMutation(this.submitQuizAttempt, {
        onSuccess: () => {
          const isTimeout = this.pendingTimeoutSubmission;
          this.pendingTimeoutSubmission = false;
          this.handleSubmissionSuccess(isTimeout);
        },
        onError: (error: Error) => {
          this.pendingTimeoutSubmission = false;
          toast.error(convertToErrorMessage(error));
        },
      });

      this.abandonQuizMutation = query.useMutation(this.abandonQuizAttempt, {
        onSuccess: () => {
          this.isAbandoningNavigation = false;
          if (this.pendingNavigationAction === 'navigate' && this.pendingNavigationUrl) {
            const nextUrl = this.pendingNavigationUrl;
            this.pendingNavigationAction = '';
            this.pendingNavigationUrl = '';
            this.performSafeNavigate(nextUrl);
            return;
          }
          this.pendingNavigationAction = '';
          this.pendingNavigationUrl = '';
          this.performSafeReload();
        },
        onError: (error: Error) => {
          this.isAbandoningNavigation = false;
          this.skipBeforeUnload = false;
          toast.error(convertToErrorMessage(error));
        },
      });

      this.timeoutQuizMutation = query.useMutation(this.timeoutQuizAttempt, {
        onSuccess: () => {
          this.handleTimeoutSuccess();
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
      return this.enableAnswerReveal;
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

    revealQuestion(wrapper: HTMLElement, revealAnswerIds: number[]) {
      revealQuestionWithAnswers(wrapper, revealAnswerIds);
    },

    revealOnSubmit(): boolean {
      const revealAnswerIds = this.getRevealAnswerIds();
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
        const questionType = question.dataset?.question ?? '';
        if (!(QUIZ_REVEAL_CONFIG.SUPPORTED_TYPES as readonly string[]).includes(questionType)) {
          return;
        }
        // Types like scale do not use revealAnswerIds; still reveal (show correct/reference).
        if (questionType !== 'scale' && !revealAnswerIds.length) {
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

    handleAbandonConfirm() {
      this.isAbandoningNavigation = true;
      this.prepareForNavigation();
      if (!this.pendingNavigationAction) {
        this.pendingNavigationAction = 'reload';
      }
      this.handleAbandonQuiz();
    },

    handleAbandonCancel() {
      this.pendingNavigationAction = '';
      this.pendingNavigationUrl = '';
      this.isAbandoningNavigation = false;
      this.skipBeforeUnload = false;
      this.beforeUnloadTriggered = false;
    },

    handleNavigationAttempt(event: MouseEvent) {
      const target = event.target as HTMLElement | null;
      if (!target) {
        return;
      }

      const link = target.closest('a');
      if (!link) {
        return;
      }

      if (link.hasAttribute('download')) {
        return;
      }

      const href = link.getAttribute('href') || '';
      if (!href || QUIZ_ABANDON_CONFIG.IGNORE_ANCHOR_PREFIXES.some((prefix) => href.startsWith(prefix))) {
        return;
      }

      const targetAttr = (link.getAttribute('target') || '').toLowerCase();
      if (targetAttr && targetAttr !== '_self') {
        return;
      }

      if (!this.shouldWarnOnUnload()) {
        return;
      }

      event.preventDefault();
      this.pendingNavigationAction = 'navigate';
      this.pendingNavigationUrl = link.href;
      modal?.showModal?.(this.abandonModalId);
    },

    handleBeforeUnload(event: BeforeUnloadEvent) {
      if (!this.shouldWarnOnUnload()) {
        this.beforeUnloadTriggered = false;
        return;
      }
      this.beforeUnloadTriggered = true;
      event.preventDefault();
      event.returnValue = '';
      return '';
    },

    shouldWarnOnUnload(): boolean {
      if (!this.formId || !form?.hasForm?.(this.formId)) {
        return false;
      }
      if (this.skipBeforeUnload) {
        return false;
      }
      if (this.isAbandoningNavigation) {
        return false;
      }
      if (this.hasTimedOut || this.isRevealSubmitting) {
        return false;
      }
      if (this.submitQuizMutation?.isPending || this.abandonQuizMutation?.isPending) {
        return false;
      }
      return true;
    },

    prepareForNavigation() {
      this.skipBeforeUnload = true;
      this.beforeUnloadTriggered = false;
      if (this.beforeUnloadHandler) {
        window.removeEventListener('beforeunload', this.beforeUnloadHandler);
      }
      if (this.navigationHandler) {
        document.removeEventListener(QUIZ_ABANDON_CONFIG.NAVIGATION_EVENT, this.navigationHandler, true);
      }
    },

    performSafeReload() {
      this.prepareForNavigation();
      window.location.reload();
    },

    performSafeNavigate(url: string) {
      this.prepareForNavigation();
      window.location.assign(url);
    },

    getAttemptedCount(): number {
      return getAttemptedQuestionCountFromForm(this.formId);
    },

    openResultModal(modalId: string, payload?: { attempted?: number; total?: number }) {
      if (!modalId) {
        this.performSafeReload();
        return;
      }
      this.prepareForNavigation();
      this.resultModalOpenId = modalId;
      modal?.showModal?.(modalId, payload ?? null);
    },

    handleSubmissionSuccess(isTimeout: boolean) {
      if (isTimeout) {
        this.openResultModal(this.timeoutModalId, {
          attempted: this.getAttemptedCount(),
          total: this.totalQuestions,
        });
        return;
      }

      this.openResultModal(this.submittedModalId);
    },

    handleTimeoutSuccess() {
      this.openResultModal(this.timeoutModalId, {
        attempted: this.getAttemptedCount(),
        total: this.totalQuestions,
      });
    },

    handleModalClose(event: Event) {
      const detail = (event as CustomEvent)?.detail ?? {};
      const targetId = detail?.id as string | undefined;
      if (!this.resultModalOpenId) {
        return;
      }
      if (targetId && targetId !== this.resultModalOpenId) {
        return;
      }
      const shouldReload =
        this.resultModalOpenId === this.submittedModalId || this.resultModalOpenId === this.timeoutModalId;
      this.resultModalOpenId = '';
      if (shouldReload) {
        this.performSafeReload();
      }
    },

    handleQuizTimeoutAbandon() {
      if (!this.quizId) {
        return;
      }

      this.timeoutQuizMutation?.mutate({ quiz_id: this.quizId });
    },

    handleQuizTimeout(detail: {
      action?: (typeof QuestionTimeoutAction)[keyof typeof QuestionTimeoutAction];
      formId?: string;
    }) {
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
        this.pendingTimeoutSubmission = true;
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

    destroy() {
      if (this.beforeUnloadHandler) {
        window.removeEventListener('beforeunload', this.beforeUnloadHandler);
      }
      if (this.navigationHandler) {
        document.removeEventListener(QUIZ_ABANDON_CONFIG.NAVIGATION_EVENT, this.navigationHandler, true);
      }
      if (this.modalCloseHandler) {
        document.removeEventListener(TUTOR_CUSTOM_EVENTS.MODAL_CLOSED, this.modalCloseHandler);
      }
    },
  };
};

export const quizSubmissionMeta: AlpineComponentMeta = {
  name: 'quizSubmission',
  component: quizSubmission,
};
