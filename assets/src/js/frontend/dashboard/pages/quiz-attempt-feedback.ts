import { __ } from '@wordpress/i18n';

import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';

const REVIEW_STATUSES = ['correct', 'incorrect'] as const;
const REVIEW_STATUS_FIELD = 'review_statuses' as const;

type ReviewStatus = (typeof REVIEW_STATUSES)[number];
type ReviewStatusFieldName = `${typeof REVIEW_STATUS_FIELD}[${string}]`;
type ReviewStatusMap = Record<string, ReviewStatus>;
type ReviewStatusesAjaxPayload = Partial<Record<ReviewStatusFieldName, ReviewStatus>>;

interface QuizAttemptFeedbackProps {
  attemptId: number;
  formId: string;
}

interface QuizAttemptFeedbackPayload {
  attempt_id: number;
  feedback: string;
  review_statuses: ReviewStatusMap;
}

interface QuizAttemptFeedbackResponse<TData = unknown> {
  success?: boolean;
  message?: string;
  data?: TData;
}

interface QuizAttemptSubmitResponse {
  reviewResponse: QuizAttemptFeedbackResponse | null;
  feedbackResponse: QuizAttemptFeedbackResponse;
}

const quizAttemptFeedback = ({ attemptId, formId }: QuizAttemptFeedbackProps) => {
  const query = window.TutorCore.query;
  const toast = window.TutorCore.toast;
  const reviewStatusFieldPattern = new RegExp(`^${REVIEW_STATUS_FIELD}\\[[^\\]]+\\]$`);

  const getReviewStatuses = (data: Record<string, unknown>) => {
    return Object.entries(data).reduce<ReviewStatusMap>((acc, [key, value]) => {
      if (!reviewStatusFieldPattern.test(key)) {
        return acc;
      }

      if (typeof value !== 'string' || !REVIEW_STATUSES.includes(value as ReviewStatus)) {
        return acc;
      }

      const fieldName = key as ReviewStatusFieldName;
      const questionId = fieldName.slice(`${REVIEW_STATUS_FIELD}[`.length, -1);
      const reviewStatus = value as ReviewStatus;

      acc[questionId] = reviewStatus;
      return acc;
    }, {});
  };

  const getReviewStatusesPayload = (reviewStatuses: ReviewStatusMap) => {
    return Object.entries(reviewStatuses).reduce<ReviewStatusesAjaxPayload>((acc, [questionId, status]) => {
      const fieldName: ReviewStatusFieldName = `${REVIEW_STATUS_FIELD}[${questionId}]`;
      acc[fieldName] = status;
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
          toast.success(__('Quiz feedback updated successfully.', 'tutor'));
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
