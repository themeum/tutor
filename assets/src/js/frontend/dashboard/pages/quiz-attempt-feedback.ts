import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';
import { __ } from '@wordpress/i18n';

interface QuizAttemptFeedbackProps {
  attemptId: number;
  formId: string;
}

interface QuizAttemptFeedbackPayload {
  attempt_id: number;
  feedback: string;
  review_statuses: Record<string, string>;
}

interface QuizAttemptFeedbackResponse {
  success?: boolean;
  message?: string;
  data?: unknown;
}

interface QuizAttemptSubmitResponse {
  reviewResponse: QuizAttemptFeedbackResponse | null;
  feedbackResponse: QuizAttemptFeedbackResponse;
}

const quizAttemptFeedback = ({ attemptId, formId }: QuizAttemptFeedbackProps) => {
  const query = window.TutorCore.query;
  const toast = window.TutorCore.toast;
  const getReviewStatuses = (data: Record<string, unknown>) => {
    return Object.entries(data).reduce<Record<string, string>>((acc, [key, value]) => {
      const match = key.match(/^review_statuses\[(.+)\]$/);

      if (!match) {
        return acc;
      }

      const status = String(value ?? '');

      if (!['correct', 'incorrect'].includes(status)) {
        return acc;
      }

      acc[match[1]] = status;
      return acc;
    }, {});
  };

  const getReviewStatusesPayload = (reviewStatuses: Record<string, string>) => {
    return Object.entries(reviewStatuses).reduce<Record<string, string>>((acc, [questionId, status]) => {
      acc[`review_statuses[${questionId}]`] = status;
      return acc;
    }, {});
  };

  return {
    formId,
    attemptId,
    feedbackMutation: null as MutationState<QuizAttemptSubmitResponse, QuizAttemptFeedbackPayload> | null,

    init() {
      this.feedbackMutation = query.useMutation(this.saveFeedback, {
        onSuccess: () => {
          toast.success(__('Updated', 'tutor'));
          window.location.reload();
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });
    },

    async saveFeedback(payload: QuizAttemptFeedbackPayload) {
      const reviewStatusesPayload = getReviewStatusesPayload(payload.review_statuses);
      const reviewRequest =
        Object.keys(reviewStatusesPayload).length > 0
          ? wpAjaxInstance
              .post<QuizAttemptFeedbackResponse>(endpoints.REVIEW_QUIZ_ANSWERS, {
                attempt_id: payload.attempt_id,
                ...reviewStatusesPayload,
              })
              .then((res) => res.data)
          : Promise.resolve(null);

      const feedbackRequest = wpAjaxInstance
        .post<QuizAttemptFeedbackResponse>(endpoints.INSTRUCTOR_FEEDBACK, {
          attempt_id: payload.attempt_id,
          feedback: payload.feedback,
        })
        .then((res) => res.data);

      const [reviewResponse, feedbackResponse] = await Promise.all([reviewRequest, feedbackRequest]);

      return {
        reviewResponse,
        feedbackResponse,
      };
    },

    async handleSaveFeedback(data: Record<string, unknown>) {
      await this.feedbackMutation?.mutate({
        attempt_id: this.attemptId,
        feedback: String(data.feedback ?? ''),
        review_statuses: getReviewStatuses(data),
      });
    },
  };
};

export const quizAttemptFeedbackMeta = {
  name: 'quizAttemptFeedback',
  component: quizAttemptFeedback,
};
