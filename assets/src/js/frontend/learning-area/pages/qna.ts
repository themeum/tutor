import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { __ } from '@wordpress/i18n';

interface CreateQnaPayload {
  course_id: number;
  question: string;
}

interface ReplyQnaPayload {
  course_id: number;
  question_id: number;
  answer: string;
}

interface DeleteQnaPayload {
  question_id: number;
  context: string;
}

/**
 * Q&A Page Component
 * Handles Q&A question creation in learning area
 */
const qnaPage = () => {
  const query = window.TutorCore.query;

  return {
    query,
    createQnaMutation: null as MutationState<unknown, CreateQnaPayload> | null,
    replyQnaMutation: null as MutationState<unknown, ReplyQnaPayload> | null,
    deleteQnaMutation: null as MutationState<unknown, unknown> | null,
    focused: false,

    init() {
      this.createQnaMutation = this.query.useMutation(this.createQnA, {
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
      this.deleteQnaMutation = this.query.useMutation(this.deleteQnA, {
        onSuccess: (result, payload) => {
          if (payload?.context === 'question') {
            window.TutorCore.toast.success(__('Question deleted successfully', 'tutor'));
            const url = new URL(window.location.href);
            url.searchParams.delete('question_id');
            window.location.href = url.toString();
          } else if (payload?.context === 'reply') {
            window.TutorCore.toast.success(__('Reply deleted successfully', 'tutor'));
            window.location.reload();
          } else {
            window.location.reload();
          }
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to delete Q&A', 'tutor'));
        },
      });
    },

    createQnA(payload: CreateQnaPayload) {
      return wpAjaxInstance.post(endpoints.CREATE_UPDATE_QNA, payload);
    },

    replyQna(payload: ReplyQnaPayload) {
      return wpAjaxInstance.post(endpoints.CREATE_UPDATE_QNA, payload);
    },

    deleteQnA(payload: DeleteQnaPayload) {
      return wpAjaxInstance.post(endpoints.DELETE_DASHBOARD_QNA, payload);
    },
  };
};

export const initializeQna = () => {
  window.TutorComponentRegistry.register({
    type: 'component',
    meta: {
      name: 'qna',
      component: qnaPage,
    },
  });
  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
