import { type MutationState } from '@Core/ts/services/Query';
import { wpPost } from '@Core/ts/utils/api';
import { convertToErrorMessage } from '@Core/ts/utils/error';

import endpoints from '@TutorShared/utils/endpoints';
import type { TutorMutationResponse } from '@TutorShared/utils/types';

const createCourseHandler = () => {
  const { query, toast } = window.TutorCore;

  return {
    createCourseMutation: null as MutationState<TutorMutationResponse<string>, unknown> | null,

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
      return wpPost<TutorMutationResponse<string>>(endpoints.CREATE_DRAFT_COURSE, {
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
    createCourseButton.classList.add(...['tutor-loading', 'tutor-btn-loading']);
    createCourseButton.setAttribute('disabled', 'true');
    const target = e.target as HTMLElement;
    target.innerHTML = 'Creating...';
    try {
      await handler.handleCreateCourse();
    } finally {
      createCourseButton.classList.remove(...['tutor-loading', 'tutor-btn-loading']);
      createCourseButton.removeAttribute('disabled');
    }
  });
};
