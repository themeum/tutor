// Quiz Attempts Page
import { type MutationState } from '@Core/ts/services/Query';
import { wpPost } from '@Core/ts/utils/api';
import { convertToErrorMessage } from '@Core/ts/utils/error';

import { quizRetryAttemptMeta } from '@FrontendComponents/quiz/retry-attempt';
import { quizSummarySidebarMeta } from '@FrontendComponents/quiz/summary-sidebar';

import { quizAttemptFeedbackMeta } from './quiz-attempt-feedback';

const quizAttemptsPage = () => {
  const query = window.TutorCore.query;

  return {
    query,
    deleteMutation: null as MutationState<unknown, number> | null,

    init() {
      this.deleteMutation = this.query.useMutation(this.deleteAttempt, {
        onSuccess: () => {
          window.TutorCore.modal.closeModal('tutor-quiz-attempt-delete-modal');
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(convertToErrorMessage(error));
        },
      });
    },

    deleteAttempt(attemptID: number) {
      return wpPost('tutor_attempt_delete', {
        id: attemptID,
      });
    },

    async handleDeleteAttempt(attemptID: number) {
      await this.deleteMutation?.mutate(attemptID);
    },
  };
};

export const initializeQuizAttempts = () => {
  window.TutorComponentRegistry.registerAll({
    components: [
      {
        name: 'quizAttempts',
        component: quizAttemptsPage,
      },
      quizRetryAttemptMeta,
      quizAttemptFeedbackMeta,
      quizSummarySidebarMeta,
    ],
  });

  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
