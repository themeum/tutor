import { __ } from '@wordpress/i18n';

import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { type MutationState } from '@Core/ts/services/Query';

const REVIEW_STATUSES = ['correct', 'incorrect'] as const;
const REVIEW_STATUS_FIELD = 'review_statuses' as const;
const MANUAL_MARK_FIELD = 'manual_marks' as const;
const QUESTION_FEEDBACK_FIELD = 'question_feedback' as const;

type ReviewStatus = (typeof REVIEW_STATUSES)[number];
type ReviewStatusFieldName = `${typeof REVIEW_STATUS_FIELD}[${string}]`;
type ReviewStatusMap = Record<string, ReviewStatus>;
type ReviewStatusesAjaxPayload = Partial<Record<ReviewStatusFieldName, ReviewStatus>>;
type ManualMarkFieldName = `${typeof MANUAL_MARK_FIELD}[${string}]`;
type ManualMarksMap = Record<string, number>;
type ManualMarksAjaxPayload = Partial<Record<ManualMarkFieldName, number>>;
type QuestionFeedbackFieldName = `${typeof QUESTION_FEEDBACK_FIELD}[${string}]`;
type QuestionFeedbackMap = Record<string, string>;
type QuestionFeedbackAjaxPayload = Partial<Record<QuestionFeedbackFieldName, string>>;

interface QuizAttemptFeedbackProps {
  attemptId: number;
  formId: string;
}

interface QuizAttemptFeedbackPayload {
  attempt_id: number;
  feedback: string;
  review_statuses: ReviewStatusMap;
  manual_marks: ManualMarksMap;
  question_feedback: QuestionFeedbackMap;
}

interface QuizAttemptFeedbackResponse<TData = unknown> {
  success?: boolean;
  message?: string;
  data?: TData;
}

const quizAttemptFeedback = ({ attemptId, formId }: QuizAttemptFeedbackProps) => {
  const { query, toast, endpoints, form } = window.TutorCore;
  const { wpPost } = window.TutorCore.api;
  const { convertToErrorMessage } = window.TutorCore.error;

  const reviewStatusFieldPattern = new RegExp(`^${REVIEW_STATUS_FIELD}\\[[^\\]]+\\]$`);
  const manualMarkFieldPattern = new RegExp(`^${MANUAL_MARK_FIELD}\\[[^\\]]+\\]$`);
  const questionFeedbackFieldPattern = new RegExp(`^${QUESTION_FEEDBACK_FIELD}\\[[^\\]]+\\]$`);
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
      if (!manualMarkFieldPattern.test(key)) return acc;
      if (value === '' || value === null || value === undefined) return acc;
      if (typeof value === 'string' && value.trim() === '') return acc;
      const num = Number(value);
      if (Number.isNaN(num)) return acc;
      const questionId = key.slice(`${MANUAL_MARK_FIELD}[`.length, -1);
      acc[questionId] = num;
      return acc;
    }, {});
  };

  const getManualMarksPayload = (manualMarks: ManualMarksMap) => {
    return Object.entries(manualMarks).reduce<ManualMarksAjaxPayload>((acc, [questionId, mark]) => {
      acc[`${MANUAL_MARK_FIELD}[${questionId}]` as ManualMarkFieldName] = mark;
      return acc;
    }, {});
  };

  const getQuestionFeedback = (data: Record<string, unknown>) => {
    return Object.entries(data).reduce<QuestionFeedbackMap>((acc, [key, value]) => {
      if (!questionFeedbackFieldPattern.test(key)) return acc;
      const questionId = key.slice(`${QUESTION_FEEDBACK_FIELD}[`.length, -1);
      acc[questionId] = typeof value === 'string' ? value : String(value ?? '');
      return acc;
    }, {});
  };

  const getQuestionFeedbackPayload = (questionFeedback: QuestionFeedbackMap) => {
    return Object.entries(questionFeedback).reduce<QuestionFeedbackAjaxPayload>((acc, [questionId, feedback]) => {
      acc[`${QUESTION_FEEDBACK_FIELD}[${questionId}]` as QuestionFeedbackFieldName] = feedback;
      return acc;
    }, {});
  };

  return {
    formId,
    attemptId,
    feedbackMutation: null as MutationState<QuizAttemptFeedbackResponse, QuizAttemptFeedbackPayload> | null,
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
      let questionFeedbackDirty = true;

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
        questionFeedbackDirty = Object.keys(dirtyFields ?? {}).some(
          (key) => key.startsWith(`${QUESTION_FEEDBACK_FIELD}[`) && dirtyFields[key],
        );
      }

      const payloadHasReviewUpdates = feedbackDirty || reviewStatusesDirty || manualMarksDirty || questionFeedbackDirty;

      if (!payloadHasReviewUpdates) {
        throw new Error(__('No changes to update', 'tutor'));
      }

      const requestPayload: Record<string, unknown> = {
        attempt_id: payload.attempt_id,
      };

      if (reviewStatusesDirty) {
        Object.assign(requestPayload, getReviewStatusesPayload(payload.review_statuses));
      }

      if (manualMarksDirty) {
        Object.assign(requestPayload, getManualMarksPayload(payload.manual_marks));
      }

      if (questionFeedbackDirty) {
        Object.assign(requestPayload, getQuestionFeedbackPayload(payload.question_feedback));
      }

      if (feedbackDirty) {
        requestPayload.feedback = payload.feedback;
      }

      // Single request so the graded email is sent once per Submit click.
      return wpPost<QuizAttemptFeedbackResponse>(endpoints.REVIEW_QUIZ_ANSWERS, requestPayload);
    },

    async handleSaveFeedback(data: Record<string, unknown>) {
      const mergedData = { ...data };
      const formEl = document.getElementById(this.formId) as HTMLFormElement | null;
      if (formEl) {
        const fd = new FormData(formEl);
        fd.forEach((value, key) => {
          if (!(key in mergedData) || !mergedData[key]) {
            mergedData[key] = value;
          }
        });
      }

      await this.feedbackMutation?.mutate({
        attempt_id: this.attemptId,
        feedback: String(data.feedback ?? ''),
        review_statuses: getReviewStatuses(mergedData),
        manual_marks: getManualMarks(mergedData),
        question_feedback: getQuestionFeedback(mergedData),
      });
    },
  };
};

export const quizAttemptFeedbackMeta = {
  name: 'quizAttemptFeedback',
  component: quizAttemptFeedback,
};

interface QuestionFeedbackProps {
  initialFeedback?: string;
  fieldName?: string;
  formId?: string;
}

const questionFeedback = ({
  initialFeedback = '',
  fieldName = '',
  formId = 'quiz-attempt-review-form',
}: QuestionFeedbackProps = {}) => {
  const { form } = window.TutorCore;

  return {
    expanded: false,
    feedback: String(initialFeedback || ''),
    fieldName: String(fieldName || ''),
    formId: String(formId || 'quiz-attempt-review-form'),

    toggle() {
      if (!this.expanded && form.hasForm(this.formId)) {
        form.setValue(this.formId, this.fieldName, this.feedback);
      }
      this.expanded = !this.expanded;
    },

    save() {
      if (form.hasForm(this.formId)) {
        const val = form.getValue(this.formId, this.fieldName);
        this.feedback = typeof val === 'string' ? val : String(val ?? '');
        form.setValue(this.formId, this.fieldName, this.feedback, { shouldDirty: true });
      }
      this.expanded = false;
    },

    cancel() {
      if (form.hasForm(this.formId)) {
        form.setValue(this.formId, this.fieldName, this.feedback);
      }
      this.expanded = false;
    },

    del() {
      this.feedback = '';
      if (form.hasForm(this.formId)) {
        form.setValue(this.formId, this.fieldName, '', { shouldDirty: true });
      }
      this.expanded = false;
    },
  };
};

export const questionFeedbackMeta = {
  name: 'questionFeedback',
  component: questionFeedback,
};
