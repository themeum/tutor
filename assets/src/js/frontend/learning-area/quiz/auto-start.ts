import axios from 'axios';

import { type MutationState } from '@Core/ts/services/Query';
import type { AlpineComponentMeta } from '@Core/ts/types';
import { tutorConfig } from '@TutorShared/config/config';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';

export interface QuizAutoStartConfig {
  quizID: number;
}

export interface StartQuizPayload {
  quizID: number;
}

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
