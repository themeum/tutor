import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { __ } from '@wordpress/i18n';

interface CreateQnAPayload {
  course_id: number;
  question: string;
}

interface ReplyQnAPayload {
  course_id: number;
  question_id: number;
  answer: string;
}

/**
 * Q&A Page Component
 * Handles Q&A question creation in learning area
 */
const qnaPage = () => {
  const query = window.TutorCore.query;

  return {
    query,
    createQnAMutation: null as MutationState<unknown, CreateQnAPayload> | null,
    replyQnaMutation: null as MutationState<unknown, ReplyQnAPayload> | null,
    deleteQnAMutation: null as MutationState<unknown, unknown> | null,
    focused: false,

    init() {
      this.createQnAMutation = this.query.useMutation(this.createQnA, {
        onSuccess: () => {
          window.TutorCore.toast.success(__('Question saved successfully', 'tutor'));
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to save question', 'tutor'));
        },
      });

      this.replyQnaMutation = this.query.useMutation(this.replyQna, {
        onSuccess: () => {
          window.TutorCore.toast.success(__('Reply saved successfully', 'tutor'));
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to save reply', 'tutor'));
        },
      });

      // Q&A delete mutation.
      this.deleteQnAMutation = this.query.useMutation(this.deleteQnA, {
        onSuccess: () => {
          const url = new URL(window.location.href);
          url.searchParams.delete('question_id');
          window.location.href = url.toString();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to delete Q&A', 'tutor'));
        },
      });
    },

    createQnA(payload: CreateQnAPayload) {
      return wpAjaxInstance.post(endpoints.CREATE_UPDATE_QNA, payload);
    },

    replyQna(payload: ReplyQnAPayload) {
      return wpAjaxInstance.post(endpoints.CREATE_UPDATE_QNA, payload);
    },

    deleteQnA(payload: { question_id: number }) {
      return wpAjaxInstance.post(endpoints.DELETE_DASHBOARD_QNA, payload);
    },
  };
};

export const initializeQnA = () => {
  window.TutorComponentRegistry.register({
    type: 'component',
    meta: {
      name: 'qna',
      component: qnaPage,
    },
  });
  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
