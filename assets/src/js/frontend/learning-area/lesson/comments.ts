import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';
import { __ } from '@wordpress/i18n';

interface ReplyCommentPayload {
  comment_post_ID: number;
  comment_parent: number;
  comment: string;
}

/**
 * Lesson Comments Component
 */
const lessonComments = (lessonId?: number, initialCount: number = 0) => {
  const query = window.TutorCore.query;

  return {
    query,
    lessonId: lessonId || null,
    totalComments: initialCount,
    currentPage: 1,
    loading: false,
    hasMore: true,
    currentOrder: 'DESC' as 'ASC' | 'DESC',
    isReloading: false,
    $el: null as unknown as HTMLElement,
    $refs: {} as {
      commentList: HTMLElement;
      loadMoreTrigger: HTMLElement;
    },
    createCommentMutation: null as MutationState<unknown, unknown> | null,
    editCommentMutation: null as MutationState<unknown, unknown> | null,
    deleteCommentMutation: null as MutationState<unknown, unknown> | null,
    replyCommentMutation: null as MutationState<unknown, ReplyCommentPayload> | null,

    init() {
      // Initialize order from URL.
      const url = new URL(window.location.href);
      const orderParam = url.searchParams.get('order');
      this.currentOrder = orderParam === 'ASC' ? 'ASC' : 'DESC';

      this.initInfiniteScroll();

      // Lesson comment create mutation.
      this.createCommentMutation = this.query.useMutation(this.createComment, {
        onSuccess: () => {
          window.TutorCore.toast.success(__('Comment added successfully.', 'tutor'));
          this.reloadComments();

          const formId = 'lesson-comment-form';
          if (window.TutorCore.form.hasForm(formId)) {
            window.TutorCore.form.reset(formId);
          }
        },
        onError: (error) => {
          window.TutorCore.toast.error(convertToErrorMessage(error));
        },
      });

      // Lesson comment edit mutation.
      this.editCommentMutation = this.query.useMutation(this.updateComment, {
        onSuccess: (response) => {
          window.TutorCore.toast.success(__('Comment updated successfully.', 'tutor'));
          const data = response.data;
          const targetId = data.is_reply
            ? `tutor-comment-reply-${data.comment_id}`
            : `tutor-comment-${data.comment_id}`;
          const targetEl = document.getElementById(targetId);

          if (targetEl && data.html) {
            if (data.is_reply) {
              // If it's a reply, we refresh the whole replies container to keep counts/UI in sync.
              const repliesContainer = document.getElementById(`tutor-comment-replies-${data.parent_id}`);
              if (repliesContainer) {
                repliesContainer.outerHTML = data.html;
              }
            } else {
              targetEl.outerHTML = data.html;
            }
          } else {
            this.reloadComments();
          }
        },
        onError: (error) => {
          window.TutorCore.toast.error(convertToErrorMessage(error));
        },
      });

      // Lesson comment delete mutation.
      this.deleteCommentMutation = this.query.useMutation(this.deleteComment, {
        onSuccess: (response) => {
          window.TutorCore.toast.success(__('Comment deleted successfully.', 'tutor'));
          window.TutorCore.modal.closeModal('delete-comment-modal');

          const data = response.data;
          const targetId = data.is_reply
            ? `tutor-comment-reply-${data.comment_id}`
            : `tutor-comment-${data.comment_id}`;
          const targetEl = document.getElementById(targetId);

          if (targetEl) {
            targetEl.remove();
          }

          if (data.is_reply) {
            // Check if there are any replies left. If not, remove the replies container.
            const repliesContainer = document.getElementById(`tutor-comment-replies-${data.parent_id}`);
            if (repliesContainer && repliesContainer.querySelectorAll('.tutor-comment-reply-item').length === 0) {
              repliesContainer.remove();
            }
          }

          if (data.count !== undefined) {
            this.totalComments = data.count;
          }
        },
        onError: (error) => {
          window.TutorCore.toast.error(convertToErrorMessage(error));
        },
      });

      // Lesson comment reply mutation
      this.replyCommentMutation = this.query.useMutation(this.replyComment, {
        onSuccess: (response, payload) => {
          window.TutorCore.toast.success(__('Reply saved successfully', 'tutor'));
          const data = response.data;
          const parentId = payload.comment_parent;
          const repliesContainer = document.getElementById(`tutor-comment-replies-${parentId}`);

          if (data.html) {
            if (repliesContainer) {
              repliesContainer.outerHTML = data.html;
            } else {
              // Append to parent flex container if replies wrapper doesn't exist yet
              const parentComment = document.getElementById(`tutor-comment-${parentId}`);
              const commentContent = parentComment?.querySelector('.tutor-comment-content');
              commentContent?.insertAdjacentHTML('beforeend', data.html);
            }

            // Notify Alpine to expand the replies and hide the form via custom event.
            window.dispatchEvent(new CustomEvent('tutor:comment:replied', { detail: { parentId } }));

            // Reset the specific reply form.
            const replyFormId = `lesson-comment-reply-form-${parentId}`;
            if (window.TutorCore.form.hasForm(replyFormId)) {
              window.TutorCore.form.reset(replyFormId);
            }
          } else {
            // Fallback to reload if container not found
            this.reloadComments();
          }

          if (data.count !== undefined) {
            this.totalComments = data.count;
          }
        },
        onError: (error) => {
          window.TutorCore.toast.error(convertToErrorMessage(error));
        },
      });
    },

    createComment(payload: { comment_post_ID: number; comment_parent: number }) {
      return wpAjaxInstance.post(endpoints.CREATE_LESSON_COMMENT, payload);
    },

    updateComment(payload: { comment_id: number; comment: string }) {
      return wpAjaxInstance.post(endpoints.UPDATE_LESSON_COMMENT, payload);
    },

    deleteComment(payload: { comment_id: number }) {
      return wpAjaxInstance.post(endpoints.DELETE_LESSON_COMMENT, payload);
    },

    replyComment(payload: ReplyCommentPayload) {
      return wpAjaxInstance.post(endpoints.REPLY_LESSON_COMMENT, payload);
    },

    handleChangeOrder(newOrder: 'ASC' | 'DESC') {
      if (this.currentOrder === newOrder) return;

      this.currentOrder = newOrder;
      this.updateURL(newOrder);
      this.reloadComments();
    },

    handleDeleteComment(payload: { commentId: number }) {
      window.TutorCore.modal.showModal('delete-comment-modal', { commentId: payload.commentId });
    },

    updateURL(order: 'ASC' | 'DESC') {
      const url = new URL(window.location.href);
      url.searchParams.set('order', order);
      window.history.pushState({}, '', url);
    },

    reloadComments() {
      this.isReloading = true;
      this.currentPage = 1;
      this.hasMore = true;

      wpAjaxInstance
        .post(endpoints.LOAD_LESSON_COMMENTS, {
          lesson_id: this.lessonId,
          current_page: 1,
          order: this.currentOrder,
        })
        .then((response) => {
          // Replace entire comment list.
          this.$refs.commentList.innerHTML = response.data.html;
          this.hasMore = response.data.has_more;

          if (response.data.count !== undefined) {
            this.totalComments = response.data.count;
          }
        })
        .catch((error) => {
          window.TutorCore.toast.error(convertToErrorMessage(error));
        })
        .finally(() => {
          this.isReloading = false;
        });
    },

    loadNextPage() {
      if (!this.lessonId || this.loading || !this.hasMore) {
        return;
      }

      this.loading = true;

      wpAjaxInstance
        .post(endpoints.LOAD_LESSON_COMMENTS, {
          lesson_id: this.lessonId,
          current_page: this.currentPage + 1,
          order: this.currentOrder,
        })
        .then((response) => {
          if (response.data.has_more !== undefined) {
            this.hasMore = response.data.has_more;
          }

          if (response.data.html?.trim()) {
            this.currentPage++;
            this.$refs.commentList.insertAdjacentHTML('beforeend', response.data.html);
          }
        })
        .catch((error) => {
          window.TutorCore.toast.error(convertToErrorMessage(error));
        })
        .finally(() => {
          this.loading = false;
        });
    },

    handleKeydown(event: KeyboardEvent) {
      if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
        (event.target as HTMLFormElement).closest('form')?.requestSubmit();
      }
    },

    initInfiniteScroll() {
      const observer = new IntersectionObserver(
        ([entry]) => {
          if (entry.isIntersecting && this.hasMore && !this.loading) {
            this.loadNextPage();
          }
        },
        { rootMargin: '200px' },
      );

      observer.observe(this.$refs.loadMoreTrigger);
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
