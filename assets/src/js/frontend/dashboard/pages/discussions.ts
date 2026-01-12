import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { __ } from '@wordpress/i18n';

interface QnASingleActionPayload {
  question_id: number;
  qna_action: string;
  [key: string]: string | number;
}

/**
 * Discussions Page Component
 * Handles Q&A, Lesson comments related actions
 */
const discussionsPage = () => {
  const query = window.TutorCore.query;

  return {
    query,
    qnaSingleActionMutation: null as MutationState<unknown, unknown> | null,
    deleteQnAMutation: null as MutationState<unknown, unknown> | null,
    deleteCommentMutation: null as MutationState<unknown, unknown> | null,
    createUpdateQnAMutation: null as MutationState<unknown, unknown> | null,
    currentAction: null as string | null,
    currentQuestionId: null as number | null,
    isSolved: false,
    isImportant: false,
    isArchived: false,

    init() {
      // Q&A single action mutation (read, unread, solved, important, archived).
      this.qnaSingleActionMutation = this.query.useMutation(this.qnaSingleAction, {
        onSuccess: (response, payload: QnASingleActionPayload) => {
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
          window.TutorCore.toast.error(error.message || __('Action failed', 'tutor'));
        },
      });

      // Q&A delete mutation.
      this.deleteQnAMutation = this.query.useMutation(this.deleteQnA, {
        onSuccess: () => {
          const url = new URL(window.location.href);
          url.searchParams.delete('id');
          window.location.href = url.toString();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to delete Q&A', 'tutor'));
        },
      });

      // Lesson comment delete mutation.
      this.deleteCommentMutation = this.query.useMutation(this.deleteComment, {
        onSuccess: () => {
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to delete Comment', 'tutor'));
        },
      });

      // Q&A create/update mutation.
      this.createUpdateQnAMutation = this.query.useMutation(this.createUpdateQnA, {
        onSuccess: () => {
          window.TutorCore.toast.success(__('Reply saved successfully', 'tutor'));
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to save reply', 'tutor'));
        },
      });
    },

    qnaSingleAction(payload: QnASingleActionPayload) {
      return wpAjaxInstance.post(endpoints.QNA_SINGLE_ACTION, payload);
    },

    deleteQnA(payload: { question_id: number }) {
      return wpAjaxInstance.post(endpoints.DELETE_DASHBOARD_QNA, payload);
    },

    deleteComment(payload: { comment_id: number }) {
      return wpAjaxInstance.post(endpoints.DELETE_LESSON_COMMENT, payload);
    },

    createUpdateQnA(payload: { course_id: number; question_id: number; answer: string }) {
      return wpAjaxInstance.post(endpoints.CREATE_UPDATE_QNA, payload);
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

    async handleDeleteQnA(questionId: number) {
      await this.deleteQnAMutation?.mutate({ question_id: questionId });
    },

    async handleDeleteComment(commentId: number) {
      await this.deleteCommentMutation?.mutate({ comment_id: commentId });
    },

    async handleSaveQnAReply(data: Record<string, unknown>) {
      const answer = (data.answer as string) || '';
      const courseId = (data.course_id as number) || 0;
      const questionId = (data.question_id as number) || 0;

      await this.createUpdateQnAMutation?.mutate({
        course_id: courseId,
        question_id: questionId,
        answer: answer,
      });
    },

    handleKeydown(event: KeyboardEvent) {
      if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
        (event.target as HTMLFormElement).closest('form')?.requestSubmit();
      }
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
