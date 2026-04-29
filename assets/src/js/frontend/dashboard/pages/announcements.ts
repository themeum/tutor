import { __ } from '@wordpress/i18n';

import { type FormControlMethods } from '@Core/ts/components/form';
import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';
interface AnnouncementFormData {
  id: number;
  course_id: string;
  title: string;
  summary: string;
  tutor_notify_all_students?: boolean;
  tutor_push_notify_students?: boolean;
  action_type: 'create' | 'update';
}

interface AnnouncementPayload extends Record<string, unknown> {
  tutor_announcement_course: string;
  tutor_announcement_title: string;
  tutor_announcement_summary: string;
  tutor_notify_all_students?: 'on' | 'off';
  tutor_push_notify_students?: 'on' | 'off';
  action_type: 'create' | 'update';
  announcement_id?: number;
}

interface AnnouncementResponse {
  success: boolean;
  message?: string;
  data?: unknown;
}

interface AnnouncementProps {
  formId: string;
  deleteModalId: string;
  createModalId: string;
}

const announcementsPage = ({ formId, deleteModalId, createModalId }: AnnouncementProps) => {
  const query = window.TutorCore.query;
  const modal = window.TutorCore.modal;
  const toast = window.TutorCore.toast;
  const form = window.TutorCore.form;

  const ANNOUNCEMENTS_IDS = {
    FORM: formId,
    DELETE: deleteModalId,
    CREATE: createModalId,
  };

  return {
    query,
    deleteMutation: null as MutationState<AnnouncementResponse, number> | null,
    createUpdateMutation: null as MutationState<AnnouncementResponse, AnnouncementPayload> | null,

    formData: {
      id: 0,
      course_id: '',
      title: '',
      summary: '',
      tutor_notify_all_students: true,
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
          modal.closeModal(ANNOUNCEMENTS_IDS.DELETE);
          window.location.reload();
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      // Setup create/update mutation
      this.createUpdateMutation = this.query.useMutation(this.createUpdateAnnouncement, {
        onSuccess: (response: AnnouncementResponse) => {
          modal.closeModal(ANNOUNCEMENTS_IDS.CREATE);
          toast.success(response.message || __('Operation successful', 'tutor'));
          window.location.reload();
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });
    },

    async deleteAnnouncement(announcementId: number) {
      const response = await wpAjaxInstance.post<AnnouncementResponse>(endpoints.DELETE_ANNOUNCEMENT, {
        announcement_id: announcementId,
      });
      return response.data;
    },

    async createUpdateAnnouncement(payload: AnnouncementPayload) {
      const response = await wpAjaxInstance.post<AnnouncementResponse>(endpoints.CREATE_ANNOUNCEMENT, payload);
      return response.data;
    },

    async handleDeleteAnnouncement(announcementId: number) {
      await this.deleteMutation?.mutate(announcementId);
    },

    openCreateModal() {
      this.formData.action_type = 'create';
      this.formData.id = 0;
      modal.showModal(ANNOUNCEMENTS_IDS.CREATE);

      if (form.hasForm(formId)) {
        form.reset(formId);
      }
    },

    openEditModal(data: {
      id: number;
      title: string;
      summary: string;
      course_id: number;
      tutor_notify_all_students: boolean;
    }) {
      this.formData.id = data.id;
      this.formData.course_id = data.course_id.toString();
      this.formData.title = data.title;
      this.formData.summary = data.summary;
      this.formData.action_type = 'update';

      modal.showModal(ANNOUNCEMENTS_IDS.CREATE);

      const formEl = document.getElementById(ANNOUNCEMENTS_IDS.FORM);

      if (!formEl) return;

      // Manual registration fallback if auto-init hasn't fired yet
      if (!form.hasForm(formId)) {
        const alpineForm = window.Alpine.$data(formEl);
        form.register(formId, alpineForm as unknown as FormControlMethods);
      }

      if (form.hasForm(formId)) {
        const values = {
          tutor_announcement_course: String(data.course_id),
          tutor_announcement_title: data.title,
          tutor_announcement_summary: data.summary,
          tutor_notify_all_students: true,
          tutor_push_notify_students: true,
        };

        form.setValues(formId, values);
      }
    },

    async handleFormSubmit(data: AnnouncementPayload) {
      const payload: AnnouncementPayload = {
        ...(data.tutor_notify_all_students && {
          tutor_notify_all_students: data.tutor_notify_all_students ? 'on' : 'off',
        }),
        ...(data.tutor_push_notify_students && {
          tutor_push_notify_students: data.tutor_push_notify_students ? 'on' : 'off',
        }),
        tutor_announcement_course: data.tutor_announcement_course || '',
        tutor_announcement_title: data.tutor_announcement_title || '',
        tutor_announcement_summary: data.tutor_announcement_summary || '',
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
