import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';
import { __ } from '@wordpress/i18n';
import { type AxiosError } from 'axios';

interface CourseCompletePayload {
  course_id: number;
}
interface CourseRetakePayload {
  course_id: number;
}

/**
 * Common handlers for course completion and retake.
 * These are globally registered in the learning area.
 */
export const courseCompleteHandler = () => {
  const { query, toast, modal } = window.TutorCore;

  return {
    courseCompleteMutation: null as MutationState<unknown, CourseCompletePayload> | null,
    courseRetakeMutation: null as MutationState<unknown, CourseRetakePayload> | null,

    init() {
      if (this.courseCompleteMutation) {
        return;
      }

      this.courseCompleteMutation = query.useMutation(this.completeCourseRequest, {
        onSuccess: () => {
          modal.closeModal('tutor-course-complete-modal');
          window.location.reload();
        },
        onError: (error: AxiosError) => {
          toast.error(convertToErrorMessage(error));
          if (!error || !error.response || !error.response.data) {
            window.location.reload();
          }
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
      return wpAjaxInstance.post(endpoints.TUTOR_COMPLETE_COURSE, payload);
    },

    async handleCourseComplete(courseId: number) {
      await this.courseCompleteMutation?.mutate({ course_id: courseId });
    },

    // Retake
    async retakeCourseRequest(payload: CourseRetakePayload) {
      return wpAjaxInstance.post(endpoints.RESET_COURSE_PROGRESS, payload);
    },

    async handleCourseRetake(courseId: number) {
      await this.courseRetakeMutation?.mutate({ course_id: courseId });
    },
  };
};

export const initializeCommon = () => {
  if (window.TutorComponentRegistry) {
    window.TutorComponentRegistry.register({
      type: 'component',
      meta: {
        name: 'courseCompleteHandler',
        component: courseCompleteHandler,
      },
    });
  }
};
