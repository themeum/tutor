import { type MutationState } from '@Core/ts/services/Query';
import { convertToErrorMessage } from '@TutorShared/utils/util';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import { __ } from '@wordpress/i18n';

interface CourseCompletePayload {
  course_id: number;
}
interface CourseRetakePayload {
  course_id: number;
}

const courseCompleteHandler = () => {
  const query = window.TutorCore.query;
  const toast = window.TutorCore.toast;

  return {
    courseCompleteMutation: null as MutationState<unknown, CourseCompletePayload> | null,
    courseRetakeMutation: null as MutationState<unknown, CourseRetakePayload> | null,

    init() {
      if (this.courseCompleteMutation) {
        return;
      }

      this.courseCompleteMutation = query.useMutation(this.completeCourseRequest, {
        onSuccess: () => {
          window.TutorCore.modal.closeModal('tutor-course-complete-modal');
          window.location.reload();
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });

      this.courseRetakeMutation = query.useMutation(this.retakeCourseRequest, {
        onSuccess: (res) => {
          if (res.data?.redirect_to) {
            window.location.href = res.data.redirect_to;
          } else {
            toast.error(__('Something went wrong', 'tutor'));
          }
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });
    },

    // Complete
    async completeCourseRequest(payload: CourseCompletePayload) {
      return wpAjaxInstance.post('tutor_complete_course', payload);
    },

    async handleCourseComplete(courseId: number) {
      await this.courseCompleteMutation?.mutate({ course_id: courseId });
    },

    // Retake
    async retakeCourseRequest(payload: CourseRetakePayload) {
      return wpAjaxInstance.post('tutor_reset_course_progress', payload);
    },

    async handleCourseRetake(courseId: number) {
      await this.courseRetakeMutation?.mutate({ course_id: courseId });
    },
  };
};

export const initializeCourseCourseInfo = () => {
  window.TutorComponentRegistry.register({
    type: 'component',
    meta: {
      name: 'courseCompleteHandler',
      component: courseCompleteHandler,
    },
  });
  window.TutorComponentRegistry.initWithAlpine(window.Alpine);
};

document.addEventListener('alpine:init', initializeCourseCourseInfo);
