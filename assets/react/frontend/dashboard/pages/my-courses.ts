import { wpAjaxInstance } from '@TutorShared/utils/api';

/**
 * My Courses Page Component
 * Handles course deletion and creation using HTTP and Query services
 */
const myCoursesPage = () => ({
  query: window.TutorCore.query,
  createMutation: null as ReturnType<typeof query.useMutation> | null,
  deleteMutation: null as ReturnType<typeof query.useMutation> | null,

  init() {
    // Setup create mutation
    this.createMutation = this.query.useMutation(this.createCourse, {
      onSuccess: (response: { status_code?: number; data?: string }) => {
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

  /**
   * Create course mutation function
   */
  createCourse() {
    return wpAjaxInstance.post('tutor_create_new_draft_course', {
      from_dashboard: true,
    });
  },

  /**
   * Delete course mutation function
   */
  deleteCourse(courseId: number) {
    return wpAjaxInstance.post('tutor_delete_dashboard_course', {
      course_id: courseId,
    });
  },

  /**
   * Handle create new course
   */
  async handleCreateCourse() {
    await this.createMutation.mutate();
  },

  /**
   * Handle delete course
   */
  async handleDeleteCourse(courseId: number) {
    await this.deleteMutation.mutate(courseId);
  },
});

/**
 * Initialize My Courses page
 */
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
