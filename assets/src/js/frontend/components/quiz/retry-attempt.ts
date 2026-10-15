import { type MutationState } from '@Core/ts/services/Query';
import type { AlpineComponentMeta } from '@Core/ts/types';

interface RetryAttemptPayload {
  quizID: string;
  redirectURL: string;
}

const quizRetryAttempt = () => {
  const { query, toast, endpoints } = window.TutorCore;
  const { wpPostForm } = window.TutorCore.api;
  const { convertToErrorMessage } = window.TutorCore.error;

  return {
    retryMutation: null as MutationState<unknown, RetryAttemptPayload> | null,

    init() {
      this.retryMutation = query.useMutation(this.retryAttempt, {
        onSuccess: (_, payload) => {
          window.location.href = payload.redirectURL;
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });
    },

    retryAttempt(payload: RetryAttemptPayload) {
      return wpPostForm(payload.redirectURL, {
        tutor_action: endpoints.START_QUIZ,
        quiz_id: payload.quizID,
      });
    },
  };
};

export const quizRetryAttemptMeta: AlpineComponentMeta = {
  name: 'quizRetryAttempt',
  component: quizRetryAttempt,
};
