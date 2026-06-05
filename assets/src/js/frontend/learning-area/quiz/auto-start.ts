import { type MutationState } from '@Core/ts/services/Query';
import type { AlpineComponentMeta } from '@Core/ts/types';
import { convertToErrorMessage } from '@Core/ts/utils/error';
import { tutorConfig } from '@TutorShared/config/config';
import endpoints from '@TutorShared/utils/endpoints';

import { wpPostForm } from '@Core/ts/utils/api';
import { QUIZ_EVENTS } from './constants';

export interface QuizAutoStartConfig {
  quizID: number;
  autoStart?: boolean;
  autoStartModalId?: string;
  countdownSeconds?: number;
}

export interface StartQuizPayload {
  quizID: number;
}

const quizAutoStart = (config: QuizAutoStartConfig) => {
  const query = window.TutorCore.query;
  const toast = window.TutorCore.toast;
  const modal = window.TutorCore.modal;
  const autoStartEvent = QUIZ_EVENTS.AUTO_START_COMPLETE;

  return {
    quizID: config.quizID,
    autoStart:
      typeof config.autoStart === 'boolean' ? config.autoStart : Number(tutorConfig.quiz_options?.quiz_auto_start) > 0,
    autoStartModalId: config.autoStartModalId ?? '',
    countdownSeconds: Number(config.countdownSeconds) || 5,
    isCountdownActive: false,
    autoStartListener: null as ((event: Event) => void) | null,
    startQuizMutation: null as MutationState<unknown, StartQuizPayload> | null,

    init() {
      this.autoStartListener = () => {
        if (!this.isCountdownActive) {
          return;
        }
        this.isCountdownActive = false;
        this.startQuizMutation?.mutate({ quizID: this.quizID });
      };

      document.addEventListener(autoStartEvent, this.autoStartListener);

      this.startQuizMutation = query.useMutation(this.startQuiz, {
        onSuccess: () => {
          window.location.reload();
        },
        onError: (error: Error) => {
          if (this.autoStartModalId) {
            modal?.closeModal?.(this.autoStartModalId);
          }
          toast.error(convertToErrorMessage(error));
        },
      });

      if (!this.autoStart) {
        return;
      }

      this.startAutoStartCountdown();
    },

    handleStartQuiz() {
      this.startQuizMutation?.mutate({ quizID: this.quizID });
    },

    startAutoStartCountdown() {
      if (!this.autoStartModalId) {
        this.startQuizMutation?.mutate({ quizID: this.quizID });
        return;
      }

      if (this.isCountdownActive) {
        return;
      }

      this.isCountdownActive = true;

      window.setTimeout(() => {
        modal?.showModal?.(this.autoStartModalId);
      }, 0);
    },

    startQuiz(payload: StartQuizPayload) {
      return wpPostForm(window.location.href, {
        tutor_action: endpoints.START_QUIZ,
        quiz_id: payload.quizID,
      });
    },

    destroy() {
      if (this.autoStartListener) {
        document.removeEventListener(autoStartEvent, this.autoStartListener);
      }
    },
  };
};

export const quizAutoStartMeta: AlpineComponentMeta = {
  name: 'quizAutoStart',
  component: quizAutoStart,
};
