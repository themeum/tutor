import { type MutationState } from '@Core/ts/services/Query';
import type { AlpineComponentMeta } from '@Core/ts/types';
import { tutorConfig } from '@TutorShared/config/config';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';
import { __ } from '@wordpress/i18n';
import axios, { type AxiosResponse } from 'axios';

interface QuizSubmissionConfig {
  formId: string;
  attemptId: string;
}

interface QuizAutoStartConfig {
  quizID: number;
  autoStart: boolean;
}

interface StartQuizPayload {
  quizID: number;
}

const ERROR_MESSAGES = {
  SUBMIT_FAILED: __('Failed to submit quiz', 'tutor'),
  REQUIRED_QUESTIONS: __('Please answer all required questions before submitting.', 'tutor'),
} as const;

const quizSubmission = (config: QuizSubmissionConfig) => {
  const query = window.TutorCore.query;
  const toast = window.TutorCore.toast;

  return {
    formId: config.formId,
    attemptId: config.attemptId,
    submitQuizMutation: null as MutationState<{ success?: boolean; data?: unknown }, Record<string, unknown>> | null,
    $el: null as HTMLFormElement | null,

    init() {
      this.handleQuizSubmit = this.handleQuizSubmit.bind(this);
      this.handleQuizError = this.handleQuizError.bind(this);
      this.submitQuizMutation = query.useMutation(this.submitQuizAttempt, {
        onSuccess: (response: AxiosResponse<{ success?: boolean }>) => {
          if (response?.data?.success === false) {
            toast.error(ERROR_MESSAGES.SUBMIT_FAILED);
            return;
          }
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
  };
};

export const quizSubmissionMeta: AlpineComponentMeta = {
  name: 'quizSubmission',
  component: quizSubmission,
};

const quizAutoStart = (config: QuizAutoStartConfig) => ({
  quizID: config.quizID,
  autoStart: config.autoStart,
  query: window.TutorCore.query,
  toast: window.TutorCore.toast,
  startQuizMutation: null as MutationState<unknown, StartQuizPayload> | null,

  init() {
    this.startQuizMutation = this.query.useMutation(this.startQuiz, {
      onSuccess: () => {
        window.location.reload();
      },
      onError: (error: Error) => {
        this.toast.error(convertToErrorMessage(error));
      },
    });

    if (!this.autoStart) {
      return;
    }

    this.startQuizMutation?.mutate({
      quizID: this.quizID,
    });
  },

  startQuiz(payload: StartQuizPayload) {
    return axios.postForm(window.location.href, {
      quiz_id: payload.quizID,
      tutor_action: endpoints.START_QUIZ,
      _tutor_nonce: tutorConfig._tutor_nonce,
    });
  },
});

export const quizAutoStartMeta: AlpineComponentMeta = {
  name: 'quizAutoStart',
  component: quizAutoStart,
};
