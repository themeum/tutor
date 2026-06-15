import { type MutationState } from '@Core/ts/services/Query';
import { type AjaxResponse } from '@Core/ts/types';

const createCourseHandler = () => {
  const { query, toast, endpoints } = window.TutorCore;
  const { wpPost } = window.TutorCore.api;
  const { convertToErrorMessage } = window.TutorCore.error;

  return {
    createCourseMutation: null as MutationState<AjaxResponse<string>, unknown> | null,

    init() {
      if (this.createCourseMutation) {
        return;
      }

      this.createCourseMutation = query.useMutation(this.createCourseRequest, {
        onSuccess: (res) => {
          window.location.href = res.data ?? '';
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });
    },

    async createCourseRequest() {
      return wpPost<AjaxResponse<string>>(endpoints.CREATE_DRAFT_COURSE, {
        from_dashboard: true,
      });
    },

    async handleCreateCourse() {
      await this.createCourseMutation?.mutate({});
    },
  };
};

export const initializeCommon = () => {
  const handler = createCourseHandler();
  handler.init();

  const createCourseButton = document.querySelector('.tutor-create-new-course');
  createCourseButton?.addEventListener('click', async (e) => {
    e.preventDefault();
    createCourseButton.classList.add('tutor-loading');
    createCourseButton.setAttribute('disabled', 'true');
    const target = e.target as HTMLElement;
    target.innerHTML = 'Creating...';
    try {
      await handler.handleCreateCourse();
    } finally {
      createCourseButton.classList.remove('tutor-loading');
      createCourseButton.removeAttribute('disabled');
    }
  });
};
