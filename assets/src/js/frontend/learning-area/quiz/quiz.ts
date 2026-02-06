import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { type MutationState } from '@Core/ts/services/Query';
import type { AlpineComponentMeta } from '@Core/ts/types';
import { tutorConfig } from '@TutorShared/config/config';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';
import { __ } from '@wordpress/i18n';
import axios from 'axios';

interface QuizSubmissionConfig {
  formId: string;
  attemptId: string;
  quizId: number;
}

interface QuizAutoStartConfig {
  quizID: number;
}

interface StartQuizPayload {
  quizID: number;
}

const ERROR_MESSAGES = {
  SUBMIT_FAILED: __('Failed to submit quiz', 'tutor'),
  ABANDON_FAILED: __('Failed to abandon quiz', 'tutor'),
  REQUIRED_QUESTIONS: __('Please answer all required questions before submitting.', 'tutor'),
} as const;

const quizSubmission = (config: QuizSubmissionConfig) => {
  const query = window.TutorCore.query;
  const toast = window.TutorCore.toast;
  const form = window.TutorCore.form;

  return {
    formId: config.formId,
    attemptId: config.attemptId,
    quizId: config.quizId,
    submitQuizMutation: null as MutationState<{ success?: boolean; data?: unknown }, Record<string, unknown>> | null,
    abandonQuizMutation: null as MutationState<{ success?: boolean }, Record<string, unknown>> | null,
    timeoutQuizMutation: null as MutationState<{ success?: boolean }, Record<string, unknown>> | null,
    hasTimedOut: false,
    $el: null as HTMLFormElement | null,

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
      const payload = this.buildSubmitPayload(data);
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

    handleQuizTimeoutAbandon() {
      if (!this.quizId) {
        return;
      }

      this.timeoutQuizMutation?.mutate({ quiz_id: this.quizId });
    },

    handleQuizTimeout(detail: { action?: string; formId?: string }) {
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

      if (action === 'auto_submit') {
        this.hasTimedOut = true;
        this.handleQuizSubmit(data);
        return;
      }

      if (action === 'auto_abandon') {
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

    // @TODO: Need to handle abandon action.
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
