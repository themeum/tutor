import { type MutationState } from '@Core/ts/services/Query';
import { convertToErrorMessage } from '@TutorShared/utils/util';
import { wpAjaxInstance } from '@TutorShared/utils/api';

interface CourseCompletePayload {
  course_id: number;
}

const courseCompleteHandler = () => {
  const query = window.TutorCore.query;
  const toast = window.TutorCore.toast;

  return {
    courseCompleteMutation: null as MutationState<unknown, CourseCompletePayload> | null,

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
    },

    async completeCourseRequest(payload: CourseCompletePayload) {
      return wpAjaxInstance.post('tutor_complete_course', payload);
    },

    async handleCourseComplete(courseId: number) {
      await this.courseCompleteMutation?.mutate({ course_id: courseId });
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
