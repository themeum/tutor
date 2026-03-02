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
  reply_context?: 'list' | 'single';
}

interface DeleteQnaPayload {
  question_id: number;
  context: string;
}

const FORM_ID_PREFIXES = {
  QNA_FORM: 'learning-area-qna-form',
  QNA_EDIT: 'qna-edit-',
  QNA_REPLY: 'qna-reply-form-',
};

const ELEMENT_IDS = {
  QNA_TEXT_PREFIX: 'tutor-qna-text-',
  QNA_LIST_CONTAINER: 'tutor-discussion-list',
  REPLIES_LIST_CONTAINER: 'tutor-discussion-replies-list',
};

const MODALS = {
  QNA_DELETE: 'tutor-qna-delete-modal',
};

const URL_PARAMS = {
  QUESTION_ID: 'question_id',
  ORDER: 'order',
};

/**
 * Q&A Page Component
 * Handles Q&A related action in learning area
 */
const qnaPage = () => {
  const query = window.TutorCore.query;
  const form = window.TutorCore.form;
  const modal = window.TutorCore.modal;
  const toast = window.TutorCore.toast;

  return {
    query,
    createQnAMutation: null as MutationState<unknown, CreateQnaPayload> | null,
    updateQnAMutation: null as MutationState<unknown, UpdateQnAPayload> | null,
    replyQnAMutation: null as MutationState<unknown, ReplyQnaPayload> | null,
    deleteQnAMutation: null as MutationState<unknown, unknown> | null,
    editingId: null as number | null,
    editingFormId: null as string | null,
    replyingId: null as number | null,
    loadingReplies: false,
    repliesOrder: 'DESC',
    $nextTick: undefined as ((callback: () => void) => void) | undefined,

    init() {
      this.createQnAMutation = this.query.useMutation(this.createQnA, {
        onSuccess: () => {
          toast.success(__('Question saved successfully', 'tutor'));

          const formId = FORM_ID_PREFIXES.QNA_FORM;
          if (form.hasForm(formId)) {
            form.reset(formId);
          }

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
          toast.error(convertToErrorMessage(error));
        },
      });

      this.replyQnAMutation = this.query.useMutation(this.replyQnA, {
        onSuccess: (data, payload) => {
          toast.success(__('Reply saved successfully', 'tutor'));
          
          const formId = `${FORM_ID_PREFIXES.QNA_REPLY}${payload.question_id}`;
          if (form.hasForm(formId)) {
            form.reset(formId);
          }

          if (payload.reply_context === 'single') {
            this.reloadReplies();
          } else {
            this.setReplying(null);
            this.updateReplyCount(payload.question_id);
          }
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.deleteQnAMutation = this.query.useMutation(this.deleteQnA, {
        onSuccess: (_, payload) => {
          if (payload.context === 'reply') {
            toast.success(__('Reply deleted successfully', 'tutor'));
            modal.closeModal(MODALS.QNA_DELETE);
            this.reloadReplies();
          } else {
            toast.success(__('Question deleted successfully', 'tutor'));
            modal.closeModal(MODALS.QNA_DELETE);

            // Reload the page.
            const url = new URL(window.location.href);
            url.searchParams.delete(URL_PARAMS.QUESTION_ID);
            window.location.href = url.toString();
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

    replyQnA(payload: ReplyQnaPayload) {
      return wpAjaxInstance.post(endpoints.CREATE_UPDATE_QNA, payload);
    },

    deleteQnA(payload: DeleteQnaPayload) {
      return wpAjaxInstance.post(endpoints.DELETE_DASHBOARD_QNA, payload);
    },

    async reloadReplies(order?: string) {
      if (order) {
        this.repliesOrder = order;
      }

      const url = new URL(window.location.href);
      const commentId = parseInt(url.searchParams.get(URL_PARAMS.QUESTION_ID) || '0');

      if (!commentId) return;

      this.loadingReplies = true;
      try {
        const response = await wpAjaxInstance.post(endpoints.LOAD_QNA_REPLIES, {
          comment_id: commentId,
          order: this.repliesOrder,
          context: 'learning-area',
        });

        const container = document.getElementById(ELEMENT_IDS.REPLIES_LIST_CONTAINER);
        if (container && typeof response.data?.html === 'string') {
          container.innerHTML = response.data.html;

          // Update URL without reload
          const url = new URL(window.location.href);
          url.searchParams.set(URL_PARAMS.ORDER, this.repliesOrder);
          window.history.pushState({}, '', url.toString());
        }
      } catch (error) {
        // eslint-disable-next-line no-console
        console.error('Failed to reload replies:', error);
      } finally {
        this.loadingReplies = false;
      }
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

    setReplying(id: number | null) {
      this.replyingId = id;

      if (id) {
        const formId = `${FORM_ID_PREFIXES.QNA_REPLY}${id}`;
        this.$nextTick?.(() => {
          if (form.hasForm(formId)) {
            form.setFocus(formId, 'answer');
          }
        });
      }
    },

    toggleReply(id: number) {
      if (this.replyingId === id) {
        this.setReplying(null);
      } else {
        this.setReplying(id);
      }
    },

    updateReplyCount(questionId: number) {
      // Find the reply count element and increment it
      const card = document.querySelector(`[data-question-id="${questionId}"]`);
      if (card) {
        const countElement = card.querySelector('.tutor-discussion-card-reply-count');
        if (countElement) {
          const currentCount = parseInt(countElement.textContent || '0', 10);
          countElement.textContent = String(currentCount + 1);
        }
      }
    },

    handleKeydown(event: KeyboardEvent) {
      if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
        (event.target as HTMLFormElement).closest('form')?.requestSubmit();
      }
    },
  };
};

export const initializeQna = () => {
  window.TutorComponentRegistry.register({
    type: 'component',
    meta: {
      name: 'QnA',
      component: qnaPage,
    },
  });
  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
