import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';
import { __ } from '@wordpress/i18n';

const COMMENT_ID_PREFIX = 'tutor-comment-';
const COMMENT_REPLY_ID_PREFIX = 'tutor-comment-reply-';
const COMMENT_REPLIES_ID_PREFIX = 'tutor-comment-replies-';
const COMMENT_REPLY_FORM_ID_PREFIX = 'lesson-comment-reply-form-';
const COMMENT_FORM_ID = 'lesson-comment-form';

const CLASSES = {
  COMMENT_ITEM: 'tutor-comment-item',
  REPLY_ITEM: 'tutor-comment-reply-item',
  REPLIES_WRAPPER: 'tutor-comment-replies',
  COMMENT_CONTENT: 'tutor-comment-content',
};

/**
 * Get comment or reply DOM ID
 *
 * @param id Comment ID
 * @param isReply Whether it's a reply
 */
const getCommentElementId = (id: number | string, isReply: boolean) => {
  return isReply ? `${COMMENT_REPLY_ID_PREFIX}${id}` : `${COMMENT_ID_PREFIX}${id}`;
};

interface ReplyCommentPayload {
  comment_post_ID: number;
  comment_parent: number;
  comment: string;
  order: string;
}

type OrderTypes = 'ASC' | 'DESC';

/**
 * Lesson Comments Component
 */
const lessonComments = (lessonId: number, initialCount: number = 0) => {
  const query = window.TutorCore.query;
  const toast = window.TutorCore.toast;
  const form = window.TutorCore.form;
  const modal = window.TutorCore.modal;

  return {
    query,
    lessonId: lessonId,
    totalComments: initialCount,
    currentPage: 1,
    loading: false,
    hasMore: true,
    currentOrder: 'DESC' as OrderTypes,
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
        onSuccess: (response) => {
          toast.success(__('Comment added successfully.', 'tutor'));
          const data = response.data;

          if (data.html) {
            if (this.currentOrder === 'DESC') {
              this.$refs.commentList.insertAdjacentHTML('afterbegin', data.html);
            } else if (!this.hasMore) {
              this.$refs.commentList.insertAdjacentHTML('beforeend', data.html);
            }
          }

          if (data.count !== undefined) {
            this.totalComments = data.count;
          }

          if (form.hasForm(COMMENT_FORM_ID)) {
            form.reset(COMMENT_FORM_ID);
          }
        },
        onError: (error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      // Lesson comment edit mutation.
      this.editCommentMutation = this.query.useMutation(this.updateComment, {
        onSuccess: (response) => {
          toast.success(__('Comment updated successfully.', 'tutor'));
          const data = response.data;
          const targetId = getCommentElementId(data.comment_id, data.is_reply);
          const targetEl = document.getElementById(targetId);

          if (targetEl && data.html) {
            targetEl.outerHTML = data.html;
          } else {
            this.reloadComments();
          }
        },
        onError: (error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      // Lesson comment delete mutation.
      this.deleteCommentMutation = this.query.useMutation(this.deleteComment, {
        onSuccess: (response) => {
          toast.success(__('Comment deleted successfully.', 'tutor'));
          modal.closeModal('delete-comment-modal');

          const data = response.data;
          const targetId = getCommentElementId(data.comment_id, data.is_reply);
          const targetEl = document.getElementById(targetId);

          if (targetEl) {
            targetEl.remove();
          }

          if (data.is_reply) {
            // Check if there are any replies left. If not, remove the replies container.
            const repliesContainer = document.getElementById(`${COMMENT_REPLIES_ID_PREFIX}${data.parent_id}`);
            if (repliesContainer && repliesContainer.querySelectorAll(`.${CLASSES.REPLY_ITEM}`).length === 0) {
              repliesContainer.remove();
            }
          }

          if (data.count !== undefined) {
            this.totalComments = data.count;
            const mainCommentsCount = this.$refs.commentList.querySelectorAll(
              `:scope > .${CLASSES.COMMENT_ITEM}`,
            ).length;
            this.hasMore = this.totalComments > mainCommentsCount;

            // If we deleted a main comment and there are more items on the server,
            // trigger a load to fill the gap.
            if (!data.is_reply && this.hasMore) {
              this.loadNextPage();
            }
          }
        },
        onError: (error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      // Lesson comment reply mutation
      this.replyCommentMutation = this.query.useMutation(this.replyComment, {
        onSuccess: (response, payload) => {
          toast.success(__('Reply saved successfully', 'tutor'));
          const data = response.data;
          const parentId = payload.comment_parent;
          const repliesWrapper = document.getElementById(`${COMMENT_REPLIES_ID_PREFIX}${parentId}`);
          const repliesList = repliesWrapper?.querySelector(`.${CLASSES.REPLIES_WRAPPER}`);

          if (data.html) {
            if (data.is_first_reply || !repliesList) {
              // Append to parent flex container if replies wrapper doesn't exist yet
              const parentComment = document.getElementById(`${COMMENT_ID_PREFIX}${parentId}`);
              const commentContent = parentComment?.querySelector(`.${CLASSES.COMMENT_CONTENT}`);
              commentContent?.insertAdjacentHTML('beforeend', data.html);
            } else {
              // Append item directly if wrapper exists
              repliesList.insertAdjacentHTML('beforeend', data.html);
            }

            // Notify Alpine to expand the replies and hide the form via custom event.
            window.dispatchEvent(new CustomEvent(TUTOR_CUSTOM_EVENTS.COMMENT_REPLIED, { detail: { parentId } }));

            // Reset the specific reply form.
            const replyFormId = `${COMMENT_REPLY_FORM_ID_PREFIX}${parentId}`;
            if (form.hasForm(replyFormId)) {
              form.reset(replyFormId);
            }
          } else {
            // Fallback to reload if something went wrong
            this.reloadComments();
          }

          if (data.count !== undefined) {
            this.totalComments = data.count;
          }
        },
        onError: (error) => {
          toast.error(convertToErrorMessage(error));
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

    handleReplyComment(data: { comment: string }, commentId: number) {
      this.replyCommentMutation?.mutate({
        comment_post_ID: this.lessonId,
        comment_parent: commentId,
        comment: data.comment,
        order: this.currentOrder,
      });
    },

    handelEditComment(data: { comment: string }, commentId: number) {
      this.editCommentMutation?.mutate({
        comment_id: commentId,
        comment: data.comment,
      });
    },

    handleChangeOrder(newOrder: OrderTypes) {
      if (this.currentOrder === newOrder) return;

      this.currentOrder = newOrder;
      this.updateURL(newOrder);
      this.reloadComments();
    },

    handleDeleteComment(payload: { commentId: number }) {
      modal.showModal('delete-comment-modal', { commentId: payload.commentId });
    },

    updateURL(order: OrderTypes) {
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
          toast.error(convertToErrorMessage(error));
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

      // Calculate offset based on main comments in DOM.
      const offset = this.$refs.commentList.querySelectorAll(`:scope > .${CLASSES.COMMENT_ITEM}`).length;

      wpAjaxInstance
        .post(endpoints.LOAD_LESSON_COMMENTS, {
          lesson_id: this.lessonId,
          offset,
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
          toast.error(convertToErrorMessage(error));
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
