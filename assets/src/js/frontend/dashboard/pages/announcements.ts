import { type FormControlMethods } from '@Core/ts/components/form';
import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import { __ } from '@wordpress/i18n';

/**
 * Type Definitions
 */
interface AnnouncementFormData {
  id: number;
  course_id: string;
  title: string;
  summary: string;
  action_type: 'create' | 'update';
}

interface AnnouncementPayload extends Record<string, unknown> {
  tutor_announcement_course: string;
  tutor_announcement_title: string;
  tutor_announcement_summary: string;
  action_type: 'create' | 'update';
  announcement_id?: number;
}

interface AnnouncementResponse {
  success: boolean;
  message?: string;
  data?: unknown;
}

/**
 * Announcements Page Component
 */
const announcementsPage = () => {
  const query = window.TutorCore.query;

  return {
    query,
    deleteMutation: null as MutationState<AnnouncementResponse, number> | null,
    createUpdateMutation: null as MutationState<AnnouncementResponse, AnnouncementPayload> | null,

    formData: {
      id: 0,
      course_id: '',
      title: '',
      summary: '',
      action_type: 'create',
    } as AnnouncementFormData,

    get formTitle() {
      return this.formData.action_type === 'create'
        ? __('Create Announcement', 'tutor')
        : __('Edit Announcement', 'tutor');
    },

    get formActionText() {
      return this.formData.action_type === 'create' ? __('Publish', 'tutor') : __('Update', 'tutor');
    },

    init() {
      // Setup delete mutation
      this.deleteMutation = this.query.useMutation(this.deleteAnnouncement, {
        onSuccess: () => {
          window.TutorCore.modal.closeModal('tutor-announcement-delete-modal');
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to delete announcement', 'tutor'));
        },
      });

      // Setup create/update mutation
      this.createUpdateMutation = this.query.useMutation(this.createUpdateAnnouncement, {
        onSuccess: (response: AnnouncementResponse) => {
          window.TutorCore.modal.closeModal('tutor-announcement-form-modal');
          window.TutorCore.toast.success(response.message || __('Operation successful', 'tutor'));
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || __('Failed to save announcement', 'tutor'));
        },
      });
    },

    async deleteAnnouncement(announcementId: number) {
      const response = await wpAjaxInstance.post<AnnouncementResponse>('tutor_delete_dashboard_announcement', {
        announcement_id: announcementId,
      });
      return response.data;
    },

    async createUpdateAnnouncement(payload: AnnouncementPayload) {
      const response = await wpAjaxInstance.post<AnnouncementResponse>('tutor_announcement_create', payload);
      return response.data;
    },

    async handleDeleteAnnouncement(announcementId: number) {
      await this.deleteMutation?.mutate(announcementId);
    },

    openCreateModal() {
      this.formData.action_type = 'create';
      this.formData.id = 0;
      window.TutorCore.modal.showModal('tutor-announcement-form-modal');

      const formId = 'announcement-form';
      if (window.TutorCore.form.hasForm(formId)) {
        window.TutorCore.form.reset(formId);
      }
    },

    openEditModal(data: { id: number; title: string; summary: string; course_id: number }) {
      this.formData.id = data.id;
      this.formData.course_id = data.course_id.toString();
      this.formData.title = data.title;
      this.formData.summary = data.summary;
      this.formData.action_type = 'update';

      window.TutorCore.modal.showModal('tutor-announcement-form-modal');

      const formId = 'announcement-form';
      const formEl = document.getElementById('tutor-announcement-form');

      if (!formEl) return;

      // Manual registration fallback if auto-init hasn't fired yet
      if (!window.TutorCore.form.hasForm(formId)) {
        const alpineForm = window.Alpine.$data(formEl);
        window.TutorCore.form.register(formId, alpineForm as unknown as FormControlMethods);
      }

      if (window.TutorCore.form.hasForm(formId)) {
        const values = {
          tutor_announcement_course: String(data.course_id),
          tutor_announcement_title: data.title,
          tutor_announcement_summary: data.summary,
        };

        window.TutorCore.form.reset(formId, values);
      }
    },

    async handleFormSubmit(data: Record<string, unknown>) {
      const payload: AnnouncementPayload = {
        tutor_announcement_course: String(data.tutor_announcement_course || ''),
        tutor_announcement_title: String(data.tutor_announcement_title || ''),
        tutor_announcement_summary: String(data.tutor_announcement_summary || ''),
        action_type: this.formData.action_type,
      };

      if (this.formData.action_type === 'update') {
        payload.announcement_id = this.formData.id;
      }

      await this.createUpdateMutation?.mutate(payload);
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
