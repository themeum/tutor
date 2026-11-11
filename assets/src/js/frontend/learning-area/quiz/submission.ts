import { type MutationState } from '@Core/ts/services/Query';
import type { AlpineComponentMeta } from '@Core/ts/types';

import { ERROR_MESSAGES, QuestionTimeoutAction, QUIZ_ABANDON_CONFIG } from './constants';
import { getAttemptedQuestionCountFromForm } from './helpers';

export interface QuizSubmissionConfig {
  formId: string;
  attemptId: string;
  quizId: number;
  abandonModalId: string;
  totalQuestions: number;
  submittedModalId?: string;
  timeoutModalId?: string;
  beforeSubmit?: () => Promise<number | boolean>;
}

const quizSubmission = (config: QuizSubmissionConfig) => {
  const { query, toast, form, modal, constants, endpoints } = window.TutorCore;
  const { convertToErrorMessage } = window.TutorCore.error;
  const { wpPostForm, wpPost } = window.TutorCore.api;
  const { TUTOR_CUSTOM_EVENTS } = constants;

  return {
    formId: config.formId,
    attemptId: config.attemptId,
    quizId: config.quizId,
    abandonModalId: config.abandonModalId,
    totalQuestions: Number(config.totalQuestions) || 0,
    submittedModalId: config.submittedModalId ?? '',
    timeoutModalId: config.timeoutModalId ?? '',

    submitQuizMutation: null as MutationState<unknown, Record<string, unknown>> | null,
    abandonQuizMutation: null as MutationState<unknown, Record<string, unknown>> | null,
    timeoutQuizMutation: null as MutationState<unknown, Record<string, unknown>> | null,

    hasTimedOut: false,
    isRevealSubmitting: false,
    isWaitingSubmit: false,
    submitTimeoutId: null as number | null,
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

    clearSubmitTimeout() {
      if (this.submitTimeoutId !== null) {
        window.clearTimeout(this.submitTimeoutId);
        this.submitTimeoutId = null;
      }
      this.isWaitingSubmit = false;
      this.isRevealSubmitting = false;
    },

    async handleQuizSubmit(data: Record<string, unknown>) {
      if (this.submitQuizMutation?.isPending) {
        return;
      }

      if (this.isWaitingSubmit) {
        this.clearSubmitTimeout();
        const payload = this.buildSubmitPayload(data);
        this.submitQuizMutation?.mutate(payload);
        return;
      }

      if (this.isRevealSubmitting) {
        return;
      }

      this.isRevealSubmitting = true;

      const payload = this.buildSubmitPayload(data);

      if (config.beforeSubmit) {
        try {
          const delay = await config.beforeSubmit.call(this);
          if (typeof delay === 'number' && delay > 0) {
            this.isRevealSubmitting = false;
            this.isWaitingSubmit = true;
            this.submitTimeoutId = window.setTimeout(() => {
              this.submitTimeoutId = null;
              this.isWaitingSubmit = false;
              this.submitQuizMutation?.mutate(payload);
            }, delay);
            return;
          }
        } catch {
          // Proceed with submission on reveal failure
        }
      }

      this.isRevealSubmitting = false;
      this.submitQuizMutation?.mutate(payload);
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
      if (this.hasTimedOut || this.isRevealSubmitting || this.isWaitingSubmit) {
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

    notifyAttemptCompleted() {
      document.dispatchEvent(
        new CustomEvent(TUTOR_CUSTOM_EVENTS.QUIZ_ATTEMPT_COMPLETED, {
          detail: {
            formId: this.formId,
            attemptId: this.attemptId,
            quizId: this.quizId,
          },
        }),
      );
    },

    handleSubmissionSuccess(isTimeout: boolean) {
      this.notifyAttemptCompleted();

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
      this.notifyAttemptCompleted();
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

    submitQuizAttempt(payload: object) {
      return wpPostForm(window.location.href, {
        tutor_action: endpoints.QUIZ_ATTEMPT_SUBMIT,
        ...payload,
      });
    },

    abandonQuizAttempt(payload: Record<string, unknown>) {
      return wpPost(endpoints.QUIZ_ABANDON, {
        tutor_action: endpoints.QUIZ_ATTEMPT_SUBMIT,
        ...payload,
      });
    },

    timeoutQuizAttempt(payload: Record<string, unknown>) {
      return wpPost(endpoints.QUIZ_TIMEOUT, {
        ...payload,
      });
    },

    destroy() {
      this.clearSubmitTimeout();
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
