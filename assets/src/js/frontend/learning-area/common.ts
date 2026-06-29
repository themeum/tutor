import { __ } from '@wordpress/i18n';

import { type MutationState } from '@Core/ts/services/Query';
import { type AjaxResponse } from '@Core/ts/types';

interface CourseCompletePayload {
  course_id: number;
}
interface CourseRetakePayload {
  course_id: number;
  context: string;
}

/**
 * Common handlers for course completion and retake.
 * These are globally registered in the learning area.
 */
export const courseCompleteHandler = () => {
  const { query, toast, modal, endpoints } = window.TutorCore;
  const { wpPost } = window.TutorCore.api;
  const { convertToErrorMessage } = window.TutorCore.error;

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
        onError: (error) => {
          toast.error(convertToErrorMessage(error));
          if (error.message?.includes('HTTP')) {
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
      return wpPost(endpoints.TUTOR_COMPLETE_COURSE, payload);
    },

    async handleCourseComplete(courseId: number) {
      await this.courseCompleteMutation?.mutate({ course_id: courseId });
    },

    // Retake
    async retakeCourseRequest(payload: CourseRetakePayload) {
      return wpPost<AjaxResponse<{ redirect_to: string }>>(endpoints.RESET_COURSE_PROGRESS, payload);
    },

    async handleCourseRetake(courseId: number) {
      await this.courseRetakeMutation?.mutate({ course_id: courseId, context: 'learning-area' });
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
