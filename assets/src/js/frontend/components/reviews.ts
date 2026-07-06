import { __ } from '@wordpress/i18n';

import { type MutationState } from '@Core/ts/services/Query';
import { type AjaxResponse } from '@Core/ts/types';

interface ReviewFormProps {
  comment_ID?: string;
  comment_post_ID: string;
  rating: string;
  comment_content: string;
  clear_review_popup_data?: boolean;
}

interface ReviewPayload {
  course_id: string;
  review_id?: string;
  tutor_rating_gen_input: string;
  review: string;
}

const reviewDeleteModal = () => {
  const { query, endpoints, toast } = window.TutorCore;
  const { wpPost } = window.TutorCore.api;

  return {
    query,
    $el: null as HTMLElement | null,
    deleteReviewMutation: null as MutationState<unknown> | null,

    init() {
      if (!this.$el) {
        return;
      }

      this.deleteReviewMutation = this.query.useMutation(this.deleteReview, {
        onSuccess: (data) => {
          window.location.reload();
          toast.success(data?.message ?? __('Review deleted successfully', 'tutor'));
        },
        onError: (error: Error) => {
          toast.error(error.message || __('Failed to delete review', 'tutor'));
        },
      });
    },

    async handleDeleteReview(reviewId: string) {
      await this.deleteReviewMutation?.mutate(reviewId);
    },

    async deleteReview(reviewId: string) {
      return wpPost<AjaxResponse<string>>(endpoints.DELETE_REVIEW, {
        review_id: reviewId,
      });
    },
  };
};

const reviewModal = () => {
  const { query, modal, toast, endpoints } = window.TutorCore;
  const { wpPost } = window.TutorCore.api;
  const { convertToErrorMessage } = window.TutorCore.error;

  return {
    query,
    saveRatingMutation: null as MutationState<AjaxResponse<string>> | null,
    clear_review_popup_data: false as boolean,

    init() {
      this.saveRatingMutation = this.query.useMutation(this.saveRating, {
        onSuccess: async (data, variables) => {
          if (this.clear_review_popup_data) {
            await this.clearReviewPopupData(variables.course_id);
          }
          modal.closeModal('create-review-modal');
          toast.success(data.message);
          window.location.reload();
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });
    },

    async handleReviewSubmit(data: ReviewFormProps) {
      this.clear_review_popup_data = !!data.clear_review_popup_data;
      const payload = this.convertFormDataToPayload(data);
      await this.saveRatingMutation?.mutate(payload);
    },

    async saveRating(payload: ReviewPayload) {
      return wpPost<AjaxResponse<string>>(endpoints.PLACE_RATING, payload);
    },

    async clearReviewPopupData(courseId: string | number) {
      return wpPost(endpoints.CLEAR_REVIEW_POPUP_DATA, {
        course_id: courseId,
      });
    },

    convertFormDataToPayload(data: ReviewFormProps): ReviewPayload {
      return {
        ...(data.comment_ID && { comment_id: data.comment_ID }),
        course_id: data.comment_post_ID,
        tutor_rating_gen_input: data.rating,
        review: data.comment_content,
      };
    },
  };
};

const reviewCard = (id: string) => {
  const { query, endpoints, toast } = window.TutorCore;
  const { wpPost } = window.TutorCore.api;
  const { convertToErrorMessage } = window.TutorCore.error;

  return {
    query,
    id,
    isEditMode: false,
    $el: null as HTMLElement | null,
    $refs: {} as {
      edit: HTMLButtonElement;
      delete: HTMLButtonElement;
      cancel: HTMLButtonElement;
    },
    saveRatingMutation: null as MutationState<AjaxResponse<string>> | null,

    handlers: {} as { [key: string]: EventListener },

    init() {
      if (!this.$el) {
        return;
      }

      // Bind handlers once to maintain stable references for cleanup
      this.handlers.toggleEditMode = () => this.toggleEditMode();

      this.$refs.edit?.addEventListener('click', this.handlers.toggleEditMode);
      this.$refs.cancel?.addEventListener('click', this.handlers.toggleEditMode);
      this.$refs.delete?.addEventListener('click', this.handlers.onDeleteButtonClick);

      this.saveRatingMutation = this.query.useMutation(this.saveRating, {
        onSuccess: (data) => {
          this.isEditMode = false;
          toast.success(data.message);
          window.location.reload();
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });
    },

    destroy() {
      this.$refs.edit?.removeEventListener('click', this.handlers.toggleEditMode);
      this.$refs.cancel?.removeEventListener('click', this.handlers.toggleEditMode);
      this.$refs.delete?.removeEventListener('click', this.handlers.onDeleteButtonClick);
    },

    async handleReviewSubmit(data: ReviewFormProps) {
      const payload = this.convertFormDataToPayload(data);
      await this.saveRatingMutation?.mutate(payload);
    },

    async saveRating(payload: ReviewPayload) {
      return wpPost<AjaxResponse<string>>(endpoints.PLACE_RATING, payload);
    },

    convertFormDataToPayload(data: ReviewFormProps): ReviewPayload {
      return {
        ...(data.comment_ID && { comment_id: data.comment_ID }),
        course_id: data.comment_post_ID,
        tutor_rating_gen_input: data.rating,
        review: data.comment_content,
      };
    },

    toggleEditMode() {
      this.isEditMode = !this.isEditMode;
    },
  };
};

const reviewServicesMeta = {
  name: 'reviewDeleteModal',
  component: reviewDeleteModal,
};

const reviewModalMeta = {
  name: 'reviewModal',
  component: reviewModal,
};

const reviewCardMeta = {
  name: 'reviewCard',
  component: reviewCard,
};

export const initializeReviews = () => {
  window.TutorComponentRegistry.registerAll({
    components: [reviewCardMeta, reviewServicesMeta, reviewModalMeta],
  });

  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
