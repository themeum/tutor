import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';

interface CourseCreateResponse {
  status_code?: number;
  data?: string;
}

/**
 * My Courses Page Component
 * Handles course deletion and creation using HTTP and Query services
 */
const myCoursesPage = () => {
  const query = window.TutorCore.query;

  return {
    query,
    createMutation: null as MutationState<CourseCreateResponse> | null,
    deleteMutation: null as MutationState<unknown, number> | null,

    init() {
      // Setup create mutation
      this.createMutation = this.query.useMutation(this.createCourse, {
        onSuccess: (response: CourseCreateResponse) => {
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
      return wpAjaxInstance.post('tutor_create_new_draft_course', payload);
    },

    deleteCourse(courseId: number) {
      return wpAjaxInstance.post('tutor_delete_dashboard_course', {
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
