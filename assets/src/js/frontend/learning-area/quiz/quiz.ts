import { type MutationState } from '@Core/ts/services/Query';
import type { AlpineComponentMeta } from '@Core/ts/types';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';
import { __ } from '@wordpress/i18n';

interface QuizSubmissionConfig {
  formId: string;
  attemptId: string;
}

const ERROR_MESSAGES = {
  SUBMIT_FAILED: __('Failed to submit quiz', 'tutor'),
} as const;

const quizSubmission = (config: QuizSubmissionConfig) => ({
  formId: config.formId,
  attemptId: config.attemptId,
  query: window.TutorCore.query,
  form: window.TutorCore.form,
  toast: window.TutorCore.toast,
  submitQuizMutation: null as MutationState<{ success?: boolean; data?: unknown }, Record<string, unknown>> | null,
  $el: null as HTMLFormElement | null,

  init() {
    this.handleQuizSubmit = this.handleQuizSubmit.bind(this);
    this.submitQuizMutation = this.query.useMutation(this.submitQuizAttempt, {
      onSuccess: (response: { success?: boolean }) => {
        if (response?.success === false) {
          this.toast.error(ERROR_MESSAGES.SUBMIT_FAILED);
          return;
        }
        window.location.reload();
      },
      onError: (error: Error) => {
        this.toast.error(convertToErrorMessage(error) || ERROR_MESSAGES.SUBMIT_FAILED);
      },
    });
  },

  handleQuizSubmit(data: Record<string, unknown>) {
    const payload = this.buildSubmitPayload(data);
    this.submitQuizMutation?.mutate(payload);
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

  async submitQuizAttempt(payload: Record<string, unknown>) {
    return wpAjaxInstance.post(endpoints.QUIZ_ATTEMPT_SUBMIT, payload);
  },

  // Validation is handled by tutorForm rules at input registration.
});

export const quizSubmissionMeta: AlpineComponentMeta = {
  name: 'quizSubmission',
  component: quizSubmission,
};
