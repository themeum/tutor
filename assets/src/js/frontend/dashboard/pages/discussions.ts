import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';
import { __ } from '@wordpress/i18n';

interface ReplyCommentPayload {
  comment_post_ID: number;
  comment_parent: number;
  comment: string;
  reply_context?: 'list' | 'single';
}

interface DeleteCommentPayload {
  comment_id: number;
  is_reply?: boolean;
}

interface QnASingleActionPayload {
  question_id: number;
  qna_action: string;
  [key: string]: string | number;
}

interface ReplyQnAPayload {
  course_id: number;
  question_id: number;
  answer: string;
  reply_context?: 'list' | 'single';
}
interface UpdateQnAPayload {
  question_id: number;
  answer: string;
}

interface DeleteQnAPayload {
  question_id: number;
  context?: 'question' | 'reply';
}

type DiscussionCardType = 'qna' | 'comment';

const FORM_ID_PREFIXES = {
  COMMENT_EDIT: 'lesson-comment-edit-',
  COMMENT_REPLY: 'lesson-comment-reply-form-',
  QNA_EDIT: 'qna-edit-',
  QNA_REPLY: 'qna-reply-form-',
};

const MODALS = {
  COMMENT_DELETE: 'tutor-comment-delete-modal',
  QNA_DELETE: 'tutor-qna-delete-modal',
};

const ELEMENT_IDS = {
  COMMENT_TEXT_PREFIX: 'tutor-lesson-comment-text-',
  QNA_TEXT_PREFIX: 'tutor-qna-text-',
  REPLIES_LIST_CONTAINER: 'tutor-discussion-replies-list',
};

const URL_PARAMS = {
  TAB: 'tab',
  ID: 'id',
  ORDER: 'order',
};

/**
 * Discussions Page Component
 * Handles Q&A, Lesson comments related actions
 */
const discussionsPage = () => {
  const query = window.TutorCore.query;
  const form = window.TutorCore.form;
  const toast = window.TutorCore.toast;
  const modal = window.TutorCore.modal;

  return {
    query,
    deleteCommentMutation: null as MutationState<unknown, DeleteCommentPayload> | null,
    replyCommentMutation: null as MutationState<unknown, ReplyCommentPayload> | null,
    editCommentMutation: null as MutationState<unknown, { comment_id: number; comment: string }> | null,
    qnaSingleActionMutation: null as MutationState<unknown, unknown> | null,
    deleteQnAMutation: null as MutationState<unknown, unknown> | null,
    replyQnAMutation: null as MutationState<unknown, unknown> | null,
    updateQnAMutation: null as MutationState<unknown, UpdateQnAPayload> | null,
    loadQnARepliesMutation: null as MutationState<unknown, unknown> | null,
    currentAction: null as string | null,
    currentQuestionId: null as number | null,
    isSolved: false,
    isImportant: false,
    isArchived: false,
    editingId: null as number | null,
    editingFormId: null as string | null,
    replyingId: null as number | null,
    replyingCommentId: null as number | null,
    loadingReplies: false,
    repliesOrder: 'DESC',
    $nextTick: undefined as ((callback: () => void) => void) | undefined,

    init() {
      // Set initial state from URL
      const url = new URL(window.location.href);
      this.repliesOrder = url.searchParams.get(URL_PARAMS.ORDER) || 'DESC';

      // Lesson comment delete mutation.
      this.deleteCommentMutation = this.query.useMutation(this.deleteComment, {
        onSuccess: (_, payload) => {
          if (payload.is_reply) {
            toast.success(__('Reply deleted successfully', 'tutor'));
            modal.closeModal(MODALS.COMMENT_DELETE);
            this.reloadReplies();
          } else {
            const url = new URL(window.location.href);
            url.searchParams.delete(URL_PARAMS.ID);
            window.location.href = url.toString();
          }
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      // Lesson comment reply mutation
      this.replyCommentMutation = this.query.useMutation(this.replyComment, {
        onSuccess: (_, payload) => {
          toast.success(__('Reply saved successfully', 'tutor'));

          const formId = `${FORM_ID_PREFIXES.COMMENT_REPLY}${payload.comment_parent}`;
          if (form.hasForm(formId)) {
            form.reset(formId);
          }

          if (payload.reply_context === 'single') {
            this.reloadReplies();
          } else {
            this.setReplyingComment(null);
            this.updateCommentReplyCount(payload.comment_parent);
            this.highlightCard(payload.comment_parent, 'comment');
          }
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      // Lesson comment edit mutation.
      this.editCommentMutation = this.query.useMutation(this.updateComment, {
        onSuccess: (_, payload) => {
          toast.success(__('Comment updated successfully.', 'tutor'));

          const element = document.getElementById(`${ELEMENT_IDS.COMMENT_TEXT_PREFIX}${payload.comment_id}`);
          if (element) {
            element.innerHTML = payload.comment;
          }

          if (this.editingFormId && form.hasForm(this.editingFormId)) {
            form.reset(this.editingFormId);
            this.editingFormId = null;
          }

          this.editingId = null;
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      // Q&A single action mutation (read, unread, solved, important, archived).
      this.qnaSingleActionMutation = this.query.useMutation(this.qnaSingleAction, {
        onSuccess: (_, payload: QnASingleActionPayload) => {
          const action = payload.qna_action;
          if (action === 'solved') {
            this.isSolved = !this.isSolved;
          } else if (action === 'important') {
            this.isImportant = !this.isImportant;
          } else if (action === 'archived') {
            this.isArchived = !this.isArchived;
          }

          window.dispatchEvent(
            new CustomEvent('tutor-qna-action-success', {
              detail: { questionId: payload.question_id, action },
            }),
          );
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      // Q&A delete mutation.
      this.deleteQnAMutation = this.query.useMutation(this.deleteQnA, {
        onSuccess: (_, payload) => {
          if (payload.context === 'reply') {
            toast.success(__('Reply deleted successfully', 'tutor'));
            modal.closeModal(MODALS.QNA_DELETE);
            this.reloadReplies();
          } else {
            const url = new URL(window.location.href);
            url.searchParams.delete(URL_PARAMS.ID);
            window.location.href = url.toString();
          }
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      // Q&A reply mutation.
      this.replyQnAMutation = this.query.useMutation(this.replyQnA, {
        onSuccess: (_, payload) => {
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
            this.highlightCard(payload.question_id);
          }
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      // Q&A update mutation.
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
    },

    async reloadReplies(order?: string) {
      if (order) {
        this.repliesOrder = order;
      }

      const url = new URL(window.location.href);
      const commentId = parseInt(url.searchParams.get(URL_PARAMS.ID) || '0');
      const tab = url.searchParams.get(URL_PARAMS.TAB) || 'qna';

      if (!commentId) return;

      this.loadingReplies = true;
      try {
        const endpoint = tab === 'qna' ? endpoints.LOAD_QNA_REPLIES : endpoints.LOAD_COMMENT_REPLIES;
        const response = await wpAjaxInstance.post(endpoint, {
          comment_id: commentId,
          order: this.repliesOrder,
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

    deleteComment(payload: DeleteCommentPayload) {
      return wpAjaxInstance.post(endpoints.DELETE_LESSON_COMMENT, payload);
    },

    replyComment(payload: ReplyCommentPayload) {
      return wpAjaxInstance.post(endpoints.REPLY_LESSON_COMMENT, payload);
    },

    updateComment(payload: { comment_id: number; comment: string }) {
      return wpAjaxInstance.post(endpoints.UPDATE_LESSON_COMMENT, payload);
    },

    qnaSingleAction(payload: QnASingleActionPayload) {
      return wpAjaxInstance.post(endpoints.QNA_SINGLE_ACTION, payload);
    },

    deleteQnA(payload: DeleteQnAPayload) {
      return wpAjaxInstance.post(endpoints.DELETE_DASHBOARD_QNA, payload);
    },

    replyQnA(payload: ReplyQnAPayload) {
      return wpAjaxInstance.post(endpoints.CREATE_UPDATE_QNA, payload);
    },

    updateQnA(payload: UpdateQnAPayload) {
      return wpAjaxInstance.post(endpoints.UPDATE_QNA, payload);
    },

    async handleQnASingleAction(questionId: number, action: string, extras: Record<string, string> = {}) {
      this.currentAction = action;
      this.currentQuestionId = questionId;
      try {
        await this.qnaSingleActionMutation?.mutate({
          question_id: questionId,
          qna_action: action,
          ...extras,
        });
      } finally {
        this.currentAction = null;
        this.currentQuestionId = null;
      }
    },

    handleReplyComment(data: { comment: string }, commentId: number, courseId: number, context: ReplyCommentPayload['reply_context'] = 'single') {
      return this.replyCommentMutation?.mutate({
        comment: data.comment,
        comment_parent: commentId,
        comment_post_ID: courseId,
        reply_context: context,
      });
    },

    handleEditComment(data: { comment: string }, commentId: number) {
      return this.editCommentMutation?.mutate({
        comment_id: commentId,
        comment: data.comment,
      });
    },

    handleKeydown(event: KeyboardEvent) {
      if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
        (event.target as HTMLFormElement).closest('form')?.requestSubmit();
      }
    },

    setEditing(id: number | null, context = 'comment') {
      const prefix = context === 'qna' ? FORM_ID_PREFIXES.QNA_EDIT : FORM_ID_PREFIXES.COMMENT_EDIT;
      const field = context === 'qna' ? 'answer' : 'comment';

      this.editingId = id;
      const formId = id ? `${prefix}${id}` : null;
      this.editingFormId = formId;

      if (id && formId) {
        this.$nextTick?.(() => {
          if (form.hasForm(formId)) {
            form.setFocus(formId, field);
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

    setReplyingComment(id: number | null) {
      this.replyingCommentId = id;

      if (id) {
        const formId = `${FORM_ID_PREFIXES.COMMENT_REPLY}${id}`;
        this.$nextTick?.(() => {
          if (form.hasForm(formId)) {
            form.setFocus(formId, 'comment');
          }
        });
      }
    },

    toggleCommentReply(id: number) {
      if (this.replyingCommentId === id) {
        this.setReplyingComment(null);
      } else {
        this.setReplyingComment(id);
      }
    },

    updateCommentReplyCount(commentId: number) {
      // Find the reply count element and increment it
      const card = document.querySelector(`[data-comment-id="${commentId}"]`);
      if (card) {
        const countElement = card.querySelector('.tutor-discussion-card-reply-count');
        if (countElement) {
          const currentCount = parseInt(countElement.textContent || '0', 10);
          countElement.textContent = String(currentCount + 1);
        }
      }
    },

    highlightCard(id: number, type: DiscussionCardType = 'qna') {
      const attr = type === 'comment' ? 'data-comment-id' : 'data-question-id';
      const el = document.querySelector(`[${attr}="${id}"]`);
      const card = (el?.closest('.tutor-discussion-card') ?? el) as HTMLElement | null;
      if (!card) return;

      card.style.outline = '2px solid var(--tutor-color-primary, #3b82f6)';
      card.style.outlineOffset = '-2px';
      card.style.borderRadius = 'inherit';

      setTimeout(() => {
        card.style.outline = '';
        card.style.outlineOffset = '';
      }, 300);
    },

  };
};

export const initializeDiscussions = () => {
  window.TutorComponentRegistry.register({
    type: 'component',
    meta: {
      name: 'discussions',
      component: discussionsPage,
    },
  });
  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
