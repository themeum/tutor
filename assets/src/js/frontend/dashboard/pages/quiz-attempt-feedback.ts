import { __ } from '@wordpress/i18n';

import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { type MutationState } from '@Core/ts/services/Query';

const REVIEW_STATUSES = ['correct', 'incorrect'] as const;
const REVIEW_STATUS_FIELD = 'review_statuses' as const;
const MANUAL_MARK_FIELD = 'manual_marks' as const;

type ReviewStatus = (typeof REVIEW_STATUSES)[number];
type ReviewStatusFieldName = `${typeof REVIEW_STATUS_FIELD}[${string}]`;
type ReviewStatusMap = Record<string, ReviewStatus>;
type ReviewStatusesAjaxPayload = Partial<Record<ReviewStatusFieldName, ReviewStatus>>;
type ManualMarkFieldName = `${typeof MANUAL_MARK_FIELD}[${string}]`;
type ManualMarksMap = Record<string, number>;
type ManualMarksAjaxPayload = Partial<Record<ManualMarkFieldName, number>>;

interface QuizAttemptFeedbackProps {
  attemptId: number;
  formId: string;
}

interface QuizAttemptFeedbackPayload {
  attempt_id: number;
  feedback: string;
  review_statuses: ReviewStatusMap;
  manual_marks: ManualMarksMap;
}

interface QuizAttemptFeedbackResponse<TData = unknown> {
  success?: boolean;
  message?: string;
  data?: TData;
}

interface QuizAttemptSubmitResponse {
  reviewResponse: QuizAttemptFeedbackResponse | null;
  feedbackResponse: QuizAttemptFeedbackResponse | null;
}

const quizAttemptFeedback = ({ attemptId, formId }: QuizAttemptFeedbackProps) => {
  const { query, toast, endpoints, form } = window.TutorCore;
  const { wpPost } = window.TutorCore.api;
  const { convertToErrorMessage } = window.TutorCore.error;

  const reviewStatusFieldPattern = new RegExp(`^${REVIEW_STATUS_FIELD}\\[[^\\]]+\\]$`);
  const manualMarkFieldPattern = new RegExp(`^${MANUAL_MARK_FIELD}\\[[^\\]]+\\]$`);
  let isProgrammaticReload = false;

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

  const getManualMarks = (data: Record<string, unknown>) => {
    return Object.entries(data).reduce<ManualMarksMap>((acc, [key, value]) => {
      if (!manualMarkFieldPattern.test(key) || Number.isNaN(Number(value))) return acc;
      const questionId = key.slice(`${MANUAL_MARK_FIELD}[`.length, -1);
      acc[questionId] = Number(value);
      return acc;
    }, {});
  };

  const getManualMarksPayload = (manualMarks: ManualMarksMap) => {
    return Object.entries(manualMarks).reduce<ManualMarksAjaxPayload>((acc, [questionId, mark]) => {
      acc[`${MANUAL_MARK_FIELD}[${questionId}]` as ManualMarkFieldName] = mark;
      return acc;
    }, {});
  };

  return {
    formId,
    attemptId,
    feedbackMutation: null as MutationState<QuizAttemptSubmitResponse, QuizAttemptFeedbackPayload> | null,
    _destroy: () => {},

    init() {
      this.feedbackMutation = query.useMutation(this.saveFeedback, {
        onSuccess: () => {
          toast.success(__('Quiz feedback updated successfully.', 'tutor'));
          isProgrammaticReload = true;
          window.location.reload();
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      if (this.formId) {
        document.dispatchEvent(
          new CustomEvent(TUTOR_CUSTOM_EVENTS.FORM_REGISTER, {
            detail: { id: this.formId, instance: this },
          }),
        );
      }

      const handler = (event: Event) => {
        if (isProgrammaticReload) {
          return false;
        }
        if (form.hasForm(formId) && form.getFormState(formId).isDirty) {
          event.preventDefault();
        }
      };

      window.addEventListener('beforeunload', handler);

      this._destroy = () => {
        isProgrammaticReload = true;
        window.removeEventListener('beforeunload', handler);
      };
    },

    destroy() {
      this._destroy?.();
    },

    async saveFeedback(payload: QuizAttemptFeedbackPayload) {
      let feedbackDirty = true;
      let reviewStatusesDirty = true;
      let manualMarksDirty = true;

      if (form.hasForm(formId)) {
        const formState = form.getFormState(formId);
        const dirtyFields = formState.dirtyFields;
        feedbackDirty = !!dirtyFields?.feedback;
        reviewStatusesDirty = Object.keys(dirtyFields ?? {}).some(
          (key) => key.startsWith(`${REVIEW_STATUS_FIELD}[`) && dirtyFields[key],
        );
        manualMarksDirty = Object.keys(dirtyFields ?? {}).some(
          (key) => key.startsWith(`${MANUAL_MARK_FIELD}[`) && dirtyFields[key],
        );
      }

      const reviewStatusesPayload = getReviewStatusesPayload(payload.review_statuses);
      const manualMarksPayload = getManualMarksPayload(payload.manual_marks);
      const reviewRequest =
        (reviewStatusesDirty || manualMarksDirty) &&
        (Object.keys(reviewStatusesPayload).length > 0 || Object.keys(manualMarksPayload).length > 0)
          ? wpPost<QuizAttemptFeedbackResponse>(endpoints.REVIEW_QUIZ_ANSWERS, {
              attempt_id: payload.attempt_id,
              ...reviewStatusesPayload,
              ...manualMarksPayload,
            })
          : Promise.resolve(null);

      const feedbackRequest = feedbackDirty
        ? wpPost<QuizAttemptFeedbackResponse>(endpoints.INSTRUCTOR_FEEDBACK, {
            attempt_id: payload.attempt_id,
            feedback: payload.feedback,
          })
        : Promise.resolve(null);

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
        manual_marks: getManualMarks(data),
      });
    },
  };
};

export const quizAttemptFeedbackMeta = {
  name: 'quizAttemptFeedback',
  component: quizAttemptFeedback,
};

interface QuestionFeedbackProps {
  attemptId: number;
  attemptAnswerId: number;
  feedback: string;
}

const questionFeedback = ({ attemptId, attemptAnswerId, feedback }: QuestionFeedbackProps) => {
  const { toast, endpoints } = window.TutorCore;
  const { wpPost } = window.TutorCore.api;

  return {
    attemptId,
    attemptAnswerId,
    feedback: String(feedback || ''),
    draft: String(feedback || ''),
    expanded: false,
    editing: false,
    saving: false,

    toggle() {
      if (!this.feedback) {
        this.expanded = !this.expanded;
        this.editing = true;
      } else if (!this.editing) {
        this.expanded = !this.expanded;
        this.editing = true;
      } else {
        this.expanded = false;
        this.editing = false;
        this.draft = this.feedback;
      }
    },

    async save() {
      if (!this.attemptId || !this.attemptAnswerId || this.saving) return;

      this.saving = true;
      try {
        const response = await wpPost<QuizAttemptFeedbackResponse>(endpoints.SAVE_QUESTION_FEEDBACK, {
          attempt_id: this.attemptId,
          attempt_answer_id: this.attemptAnswerId,
          feedback: this.draft,
        });

        if (!response.success) {
          toast.error(response.message || __('Could not save feedback.', 'tutor'));
          return;
        }

        this.feedback = this.draft;
        this.editing = false;
        this.expanded = false;
        toast.success(__('Feedback saved successfully.', 'tutor'));
      } catch {
        toast.error(__('Could not save feedback.', 'tutor'));
      } finally {
        this.saving = false;
      }
    },

    async del() {
      if (!this.attemptId || !this.attemptAnswerId || this.saving) return;

      this.saving = true;
      try {
        const response = await wpPost<QuizAttemptFeedbackResponse>(endpoints.DELETE_QUESTION_FEEDBACK, {
          attempt_id: this.attemptId,
          attempt_answer_id: this.attemptAnswerId,
        });

        if (!response.success) {
          toast.error(response.message || __('Could not delete feedback.', 'tutor'));
          return;
        }

        this.feedback = '';
        this.draft = '';
        this.editing = false;
        this.expanded = false;
        toast.success(__('Feedback deleted successfully.', 'tutor'));
      } catch {
        toast.error(__('Could not delete feedback.', 'tutor'));
      } finally {
        this.saving = false;
      }
    },
  };
};

export const questionFeedbackMeta = {
  name: 'questionFeedback',
  component: questionFeedback,
};
