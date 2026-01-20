import { __ } from '@wordpress/i18n';

import { type MutationState } from '@Core/ts/services/Query';

import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { type TutorMutationResponse } from '@TutorShared/utils/types';
import { convertToErrorMessage } from '@TutorShared/utils/util';

interface ReviewFormProps {
  comment_ID?: string;
  comment_post_ID: string;
  rating: string;
  comment_content: string;
}

interface ReviewPayload {
  course_id: string;
  review_id?: string;
  tutor_rating_gen_input: string;
  review: string;
}

const reviewDeleteModal = () => {
  const query = window.TutorCore.query;

  return {
    query,
    $el: null as HTMLElement | null,
    deleteReviewMutation: null as MutationState<unknown> | null,

    init() {
      if (!this.$el) {
        return;
      }

      this.deleteReviewMutation = this.query.useMutation(this.deleteReview, {
        onSuccess: (data: TutorMutationResponse<string>) => {
          window.location.reload();
          window.TutorCore.toast.success(data?.message ?? __('Review deleted successfully', 'tutor'));
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to delete review', 'tutor'));
        },
      });
    },

    async handleDeleteReview(reviewId: string) {
      await this.deleteReviewMutation?.mutate(reviewId);
    },

    async deleteReview(reviewId: string) {
      return wpAjaxInstance
        .post(endpoints.DELETE_REVIEW, {
          review_id: reviewId,
        })
        .then((res) => res.data);
    },
  };
};

const reviewCard = (id: string) => {
  const query = window.TutorCore.query;

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
    saveRatingMutation: null as MutationState<TutorMutationResponse<string>> | null,

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
        onSuccess: (data: TutorMutationResponse<string>) => {
          this.isEditMode = false;
          window.TutorCore.toast.success(data.message);
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(convertToErrorMessage(error));
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
      return wpAjaxInstance.post(endpoints.PLACE_RATING, payload).then((res) => res.data);
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

const reviewCardMeta = {
  name: 'reviewCard',
  component: reviewCard,
};

export const initializeReviews = () => {
  window.TutorComponentRegistry.registerAll({
    components: [reviewCardMeta, reviewServicesMeta],
  });

  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
