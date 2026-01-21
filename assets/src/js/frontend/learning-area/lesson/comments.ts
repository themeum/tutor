import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { __ } from '@wordpress/i18n';

interface ReplyCommentPayload {
  comment_post_ID: number;
  comment_parent: number;
  comment: string;
}

/**
 * Lesson Comments Component
 */
const lessonComments = () => {
  const query = window.TutorCore.query;

  return {
    query,
    createCommentMutation: null as MutationState<unknown, unknown> | null,
    deleteCommentMutation: null as MutationState<unknown, unknown> | null,
    replyCommentMutation: null as MutationState<unknown, ReplyCommentPayload> | null,

    init() {
      // Lesson comment create mutation.
      this.createCommentMutation = this.query.useMutation(this.createComment, {
        onSuccess: () => {
          window.TutorCore.toast.success(__('Comment added successfully.', 'tutor'));
          //   const url = new URL(window.location.href);
          //   url.searchParams.delete('id');
          //   window.location.href = url.toString();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to delete Comment', 'tutor'));
        },
      });

      // Lesson comment delete mutation.
      this.deleteCommentMutation = this.query.useMutation(this.deleteComment, {
        onSuccess: () => {
          const url = new URL(window.location.href);
          url.searchParams.delete('id');
          window.location.href = url.toString();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to delete Comment', 'tutor'));
        },
      });

      // Lesson comment reply mutation
      this.replyCommentMutation = this.query.useMutation(this.replyComment, {
        onSuccess: () => {
          window.TutorCore.toast.success(__('Reply saved successfully', 'tutor'));
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to save reply', 'tutor'));
        },
      });
    },

    createComment(payload: { comment_post_ID: number; comment_parent: number }) {
      return wpAjaxInstance.post(endpoints.CREATE_LESSON_COMMENT, payload);
    },

    deleteComment(payload: { comment_id: number }) {
      return wpAjaxInstance.post(endpoints.DELETE_LESSON_COMMENT, payload);
    },

    replyComment(payload: ReplyCommentPayload) {
      return wpAjaxInstance.post(endpoints.REPLY_LESSON_COMMENT, payload);
    },

    handleKeydown(event: KeyboardEvent) {
      if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
        (event.target as HTMLFormElement).closest('form')?.requestSubmit();
      }
    },
  };
};

export const initializeLessonComments = () => {
  window.TutorComponentRegistry.register({
    type: 'component',
    meta: {
      name: 'lessonComments',
      component: lessonComments,
    },
  });
  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
