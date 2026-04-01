import axios from 'axios';

import { type MutationState } from '@Core/ts/services/Query';
import type { AlpineComponentMeta } from '@Core/ts/types';

import { tutorConfig } from '@TutorShared/config/config';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';

interface RetryAttemptPayload {
  quizID: string;
  redirectURL: string;
}

const quizRetryAttempt = () => {
  const query = window.TutorCore.query;
  const toast = window.TutorCore.toast;

  return {
    retryMutation: null as MutationState<unknown, RetryAttemptPayload> | null,

    init() {
      if (this.retryMutation) {
        return;
      }

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
      return axios.postForm(payload.redirectURL, {
        quiz_id: payload.quizID,
        tutor_action: endpoints.START_QUIZ,
        _tutor_nonce: tutorConfig._tutor_nonce,
      });
    },
  };
};

export const quizRetryAttemptMeta: AlpineComponentMeta = {
  name: 'quizRetryAttempt',
  component: quizRetryAttempt,
};
