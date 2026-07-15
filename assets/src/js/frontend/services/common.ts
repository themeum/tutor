import { type MutationState } from '@Core/ts/services/Query';
import { type AjaxResponse } from '@Core/ts/types';

const UTC_DATE_CLASS = 'tutor-utc-date-time';

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

const convertUTCTime = () => {
  const utcDateTimes = document.querySelectorAll('.' + UTC_DATE_CLASS);
  if (utcDateTimes.length > 0 && wp.date) {
    const settings = wp.date.getSettings();
    const dateFormat = settings.formats.date;
    const timeFormat = settings.formats.time;
    const format = `${dateFormat}, ${timeFormat}`;

    utcDateTimes.forEach((utcDateTime) => {
      const textContent = utcDateTime.textContent?.trim() ?? '';

      if (!textContent) {
        return;
      }
      // Safari does not support format 0000-00-00, instead it needs 0000/00/00
      const dateString = textContent.replace(/-/g, '/');
      const localDateTime = new Date(`${dateString} UTC`);

      if (localDateTime.toString() !== 'Invalid Date') {
        utcDateTime.textContent = wp.date.dateI18n(
          format,
          localDateTime,
          Intl.DateTimeFormat().resolvedOptions().timeZone,
        );
      }
    });
  }
};

export const initializeCommon = () => {
  const handler = createCourseHandler();
  handler.init();
  convertUTCTime();

  const createCourseButtons = document.querySelectorAll('.tutor-create-new-course');
  createCourseButtons.forEach((button) => {
    button.addEventListener('click', async (e) => {
      e.preventDefault();
      button.classList.add(...['tutor-loading', 'tutor-btn-loading']);
      button.setAttribute('disabled', 'true');
      const target = e.target as HTMLElement;
      target.innerHTML = 'Creating...';
      try {
        await handler.handleCreateCourse();
      } finally {
        button.classList.remove(...['tutor-loading', 'tutor-btn-loading']);
        button.removeAttribute('disabled');
      }
    });
  });
};
