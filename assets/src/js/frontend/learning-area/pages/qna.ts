import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';
import { __ } from '@wordpress/i18n';

interface CreateQnaPayload {
  course_id: number;
  question: string;
}

interface UpdateQnAPayload {
  question_id: number;
  answer: string;
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

const FORM_ID_PREFIXES = {
  QNA_EDIT: 'qna-edit-',
  QNA_REPLY: 'qna-reply-form-',
};

const ELEMENT_IDS = {
  QNA_TEXT_PREFIX: 'tutor-qna-text-',
  REPLIES_LIST_CONTAINER: 'tutor-discussion-replies-list',
};

/**
 * Q&A Page Component
 * Handles Q&A related action in learning area
 */
const qnaPage = () => {
  const query = window.TutorCore.query;
  const form = window.TutorCore.form;
  const toast = window.TutorCore.toast;

  return {
    query,
    createQnaMutation: null as MutationState<unknown, CreateQnaPayload> | null,
    updateQnAMutation: null as MutationState<unknown, UpdateQnAPayload> | null,
    replyQnaMutation: null as MutationState<unknown, ReplyQnaPayload> | null,
    deleteQnaMutation: null as MutationState<unknown, unknown> | null,
    editingId: null as number | null,
    editingFormId: null as string | null,
    $nextTick: undefined as ((callback: () => void) => void) | undefined,

    init() {
      this.createQnaMutation = this.query.useMutation(this.createQnA, {
        onSuccess: () => {
          toast.success(__('Question saved successfully', 'tutor'));
          window.location.reload();
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.updateQnAMutation = this.query.useMutation(this.updateQnA, {
        onSuccess: (_, payload) => {
          toast.success(__('Updated successfully', 'tutor'));

          // Update DOM directly for immediate feedback
          const element = document.getElementById(`${ELEMENT_IDS.QNA_TEXT_PREFIX}${payload.question_id}`);
          if (element) {
            element.innerHTML = payload.answer;
          }

          if (this.editingId === payload.question_id) {
            this.setEditing(null);
          }
        },
        onError: (error: Error) => {
          toast.error(error.message || __('Failed to update', 'tutor'));
        },
      });

      this.replyQnaMutation = this.query.useMutation(this.replyQna, {
        onSuccess: () => {
          toast.success(__('Reply saved successfully', 'tutor'));
          window.location.reload();
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.deleteQnaMutation = this.query.useMutation(this.deleteQnA, {
        onSuccess: (result, payload) => {
          if (payload?.context === 'question') {
            toast.success(__('Question deleted successfully', 'tutor'));
            const url = new URL(window.location.href);
            url.searchParams.delete('question_id');
            window.location.href = url.toString();
          } else if (payload?.context === 'reply') {
            toast.success(__('Reply deleted successfully', 'tutor'));
            window.location.reload();
          } else {
            window.location.reload();
          }
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });
    },

    createQnA(payload: CreateQnaPayload) {
      return wpAjaxInstance.post(endpoints.CREATE_UPDATE_QNA, payload);
    },

    updateQnA(payload: UpdateQnAPayload) {
      return wpAjaxInstance.post(endpoints.UPDATE_QNA, payload);
    },

    replyQna(payload: ReplyQnaPayload) {
      return wpAjaxInstance.post(endpoints.CREATE_UPDATE_QNA, payload);
    },

    deleteQnA(payload: DeleteQnaPayload) {
      return wpAjaxInstance.post(endpoints.DELETE_DASHBOARD_QNA, payload);
    },

    setEditing(id: number | null) {
      this.editingId = id;
      const formId = id ? `${FORM_ID_PREFIXES.QNA_EDIT}${id}` : null;
      this.editingFormId = formId;

      if (id && formId) {
        this.$nextTick?.(() => {
          if (form.hasForm(formId)) {
            form.setFocus(formId, 'answer');
          }
        });
      }
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
