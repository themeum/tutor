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
const lessonComments = (lessonId?: number) => {
  const query = window.TutorCore.query;

  return {
    query,
    lessonId: lessonId || null,
    currentPage: 1,
    loading: false,
    hasMore: true,
    currentOrder: 'DESC' as 'ASC' | 'DESC',
    isReloading: false,
    $el: null as unknown as HTMLElement,
    $refs: {} as {
      commentList: HTMLElement;
      commentItems: HTMLElement;
      loadMoreTrigger: HTMLElement;
    },
    createCommentMutation: null as MutationState<unknown, unknown> | null,
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
          window.TutorCore.toast.success(__('Comment added successfully.', 'tutor'));
          this.$refs.commentList.innerHTML = response.data.html;

          // Reset pagination state when new comment is added
          this.currentPage = 1;
          this.hasMore = true;

          const formId = 'lesson-comment-form';
          if (window.TutorCore.form.hasForm(formId)) {
            window.TutorCore.form.reset(formId);
          }
        },
        onError: (error) => {
          window.TutorCore.toast.error(convertToErrorMessage(error));
        },
      });

      // Lesson comment delete mutation.
      this.deleteCommentMutation = this.query.useMutation(this.deleteComment, {
        onSuccess: () => {
          const url = new URL(window.location.href);
          url.searchParams.delete('id');
          window.location.href = url.toString();
        },
        onError: (error) => {
          window.TutorCore.toast.error(convertToErrorMessage(error));
        },
      });

      // Lesson comment reply mutation
      this.replyCommentMutation = this.query.useMutation(this.replyComment, {
        onSuccess: (response) => {
          window.TutorCore.toast.success(__('Reply saved successfully', 'tutor'));
          this.$refs.commentList.innerHTML = response.data.html;

          // Reset pagination state when reply is added
          this.currentPage = 1;
          this.hasMore = true;
        },
        onError: (error) => {
          window.TutorCore.toast.error(convertToErrorMessage(error));
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

    handleChangeOrder(newOrder: 'ASC' | 'DESC') {
      if (this.currentOrder === newOrder) return;

      this.currentOrder = newOrder;
      this.updateURL(newOrder);
      this.reloadComments();
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
          this.$refs.commentItems.innerHTML = response.data.html;
          this.hasMore = response.data.has_more;
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
            this.$refs.commentItems.insertAdjacentHTML('beforeend', response.data.html);
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
