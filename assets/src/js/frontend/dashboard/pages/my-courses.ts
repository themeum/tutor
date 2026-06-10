import { type MutationState } from '@Core/ts/services/Query';
import { wpPost } from '@Core/ts/utils/api';

import { type TutorMutationResponse } from '@TutorShared/utils/types';

/**
 * My Courses Page Component
 * Handles course deletion and creation using HTTP and Query services
 */
const myCoursesPage = () => {
  const query = window.TutorCore.query;

  return {
    query,
    createMutation: null as MutationState<unknown> | null,
    deleteMutation: null as MutationState<unknown, number> | null,

    init() {
      // Setup create mutation
      this.createMutation = this.query.useMutation(this.createCourse, {
        onSuccess: (response) => {
          if (response.status_code === 201 && response.data) {
            window.location.href = response.data;
          }
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || 'Failed to create course');
        },
      });

      // Setup delete mutation
      this.deleteMutation = this.query.useMutation(this.deleteCourse, {
        onSuccess: () => {
          window.TutorCore.modal.closeModal('tutor-course-delete-modal');
          window.location.reload();
        },
        onError: (error: Error) => {
          window.TutorCore.toast.error(error.message || 'Failed to delete course');
        },
      });
    },

    createCourse(payload: { from_dashboard: boolean }) {
      return wpPost<TutorMutationResponse<string>>('tutor_create_new_draft_course', payload);
    },

    deleteCourse(courseId: number) {
      return wpPost('tutor_delete_dashboard_course', {
        course_id: courseId,
      });
    },

    async handleCreateCourse() {
      await this.createMutation?.mutate({
        from_dashboard: true,
      });
    },

    async handleDeleteCourse(courseId: number) {
      await this.deleteMutation?.mutate(courseId);
    },
  };
};

export const initializeMyCourses = () => {
  window.TutorComponentRegistry.register({
    type: 'component',
    meta: {
      name: 'myCourses',
      component: myCoursesPage,
    },
  });
  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};
