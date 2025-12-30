import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';

/**
 * Discussions Page Component
 * Handles Q&A, Lesson comments related actions
 */
const discussionsPage = () => {
  const query = window.TutorCore.query;

  return {
    query,
    readUnreadQnAMutation: null as MutationState<unknown, unknown> | null,
    deleteQnAMutation: null as MutationState<unknown, unknown> | null,
    deleteCommentMutation: null as MutationState<unknown, unknown> | null,

    init() {
      // Q&A read-unread mutation.
      this.readUnreadQnAMutation = this.query.useMutation(this.readUnreadQnA, {
        onSuccess: () => {
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || 'Failed to mark as read-unread');
        },
      });

      // Q&A delete mutation.
      this.deleteQnAMutation = this.query.useMutation(this.deleteQnA, {
        onSuccess: () => {
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || 'Failed to delete Q&A');
        },
      });

      // Lesson comment delete mutation.
      this.deleteCommentMutation = this.query.useMutation(this.deleteComment, {
        onSuccess: () => {
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || 'Failed to delete Comment');
        },
      });
    },

    readUnreadQnA(payload: { question_id: number; qna_action: string }) {
      return wpAjaxInstance.post('tutor_qna_single_action', payload);
    },

    deleteQnA(payload: { question_id: number }) {
      return wpAjaxInstance.post('tutor_delete_dashboard_question', payload);
    },

    deleteComment(payload: { comment_id: number }) {
      return wpAjaxInstance.post('tutor_delete_lesson_comment', payload);
    },

    async handleReadUnreadQnA(questionId: number) {
      await this.readUnreadQnAMutation?.mutate({
        context: 'frontend-dashboard-qna-table-instructor',
        question_id: questionId,
        qna_action: 'read',
      });
    },

    async handleDeleteQnA(questionId: number) {
      await this.deleteQnAMutation?.mutate({ question_id: questionId });
    },

    async handleDeleteComment(commentId: number) {
      await this.deleteCommentMutation?.mutate({ comment_id: commentId });
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
