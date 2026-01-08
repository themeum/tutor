// Quiz Attempts Page
import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';

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
          window.TutorCore.toast.error(error.message || 'Failed to delete quiz attempt');
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
