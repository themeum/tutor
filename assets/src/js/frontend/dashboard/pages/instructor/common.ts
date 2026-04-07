import { type MutationState } from '@Core/ts/services/Query';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import { convertToErrorMessage } from '@TutorShared/utils/util';

const createCourseHandler = () => {
  const { query, toast } = window.TutorCore;

  return {
    createCourseMutation: null as MutationState<unknown, unknown> | null,

    init() {
      if (this.createCourseMutation) {
        return;
      }

      this.createCourseMutation = query.useMutation(this.createCourseRequest, {
        onSuccess: (res) => {
          window.location.href = res.data;
        },
        onError: (error: Error) => {
          toast.error(convertToErrorMessage(error));
        },
      });
    },

    async createCourseRequest() {
      return wpAjaxInstance.post(endpoints.CREATE_DRAFT_COURSE, {
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
    await handler.handleCreateCourse();
    createCourseButton.classList.remove('tutor-loading');
    createCourseButton.removeAttribute('disabled');
  });
};
