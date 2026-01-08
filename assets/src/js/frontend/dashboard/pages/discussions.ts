import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';

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
    createUpdateQnAMutation: null as MutationState<unknown, unknown> | null,

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
          // @TODO:: Handle redirect from single page.
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

      // Q&A create/update mutation.
      this.createUpdateQnAMutation = this.query.useMutation(this.createUpdateQnA, {
        onSuccess: () => {
          window.TutorCore.toast.success('Reply saved successfully');
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || 'Failed to save reply');
        },
      });
    },

    readUnreadQnA(payload: { question_id: number; qna_action: string }) {
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

    async handleReadUnreadQnA(questionId: number, context: string) {
      await this.readUnreadQnAMutation?.mutate({
        context: context,
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

    async handleSaveQnAReply(data: Record<string, unknown>) {
      const answer = (data.answer as string) || '';
      const courseId = (data.course_id as number) || 0;
      const questionId = (data.question_id as number) || 0;

      if (!answer.trim()) {
        window.TutorCore.toast.error('Please enter a response');
        return;
      }

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
