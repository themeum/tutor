// Quiz Attempts Page
import { type MutationState } from '@Core/ts/services/Query';
import { tutorConfig } from '@TutorShared/config/config';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import axios from 'axios';

interface RetryAttempt {
  quizID: string;
  redirectURL: string;
}

const quizAttemptsPage = () => {
  const query = window.TutorCore.query;

  return {
    query,
    deleteMutation: null as MutationState<unknown, number> | null,
    retryMutation: null as MutationState<unknown, RetryAttempt> | null,

    init() {
      this.deleteMutation = this.query.useMutation(this.deleteAttempt, {
        onSuccess: () => {
          window.TutorCore.modal.closeModal('tutor-quiz-attempt-delete-modal');
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || 'Failed to delete quiz attempt');
        },
      });

      this.retryMutation = this.query.useMutation(this.retryAttempt, {
        onSuccess: (_, payload) => {
          window.location.href = payload.redirectURL;
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || 'Failed to retry quiz attempt');
        },
      });
    },

    deleteAttempt(attemptID: number) {
      return wpAjaxInstance.post('tutor_attempt_delete', {
        id: attemptID,
      });
    },

    async handleDeleteAttempt(attemptID: number) {
      await this.deleteMutation?.mutate(attemptID);
    },

    retryAttempt(payload: RetryAttempt) {
      return axios.postForm(payload.redirectURL, {
        quiz_id: payload.quizID,
        tutor_action: 'tutor_start_quiz',
        _tutor_nonce: tutorConfig._tutor_nonce,
      });
    },

    async handleRetryAttempt(payload: RetryAttempt) {
      await this.retryMutation?.mutate(payload);
    },
  };
};

export const initializeQuizAttempts = () => {
  window.TutorComponentRegistry.register({
    type: 'component',
    meta: {
      name: 'quizAttempts',
      component: quizAttemptsPage,
    },
  });

  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
