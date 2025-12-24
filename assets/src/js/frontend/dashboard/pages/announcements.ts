import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';

/**
 * Announcements Page Component
 * Handles announcement deletion using HTTP and Query services
 */
const announcementsPage = () => {
  const query = window.TutorCore.query;

  return {
    query,
    deleteMutation: null as MutationState<unknown, number> | null,

    init() {
      // Setup delete mutation
      this.deleteMutation = this.query.useMutation(this.deleteAnnouncement, {
        onSuccess: () => {
          window.TutorCore.modal.closeModal('tutor-announcement-delete-modal');
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || 'Failed to delete announcement');
        },
      });
    },

    deleteAnnouncement(announcementId: number) {
      return wpAjaxInstance.post('tutor_announcement_delete', {
        announcement_id: announcementId,
      });
    },

    async handleDeleteAnnouncement(announcementId: number) {
      await this.deleteMutation?.mutate(announcementId);
    },
  };
};

export const initializeAnnouncements = () => {
  window.TutorComponentRegistry.register({
    type: 'component',
    meta: {
      name: 'announcements',
      component: announcementsPage,
    },
  });
  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
