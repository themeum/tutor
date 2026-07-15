import { type MutationState } from '@Core/ts/services/Query';
import { type AjaxResponse } from '@Core/ts/types';

const UTC_DATE_CLASS = 'tutor-utc-date-time'; // existing combined format
const UTC_DATETIME_WRAPPER_CLASS = 'tutor-utc-datetime';
const UTC_DATETIME_DATE_CLASS = 'tutor-utc-datetime-date';
const UTC_DATETIME_TIME_CLASS = 'tutor-utc-datetime-time';
const UTC_DATETIME_ATTRIBUTE = 'data-utc-datetime';

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

const parseUTCDate = (value: string): Date | null => {
  // Safari does not support format 0000-00-00, instead it needs 0000/00/00
  const dateString = value.trim().replace(/-/g, '/');
  const parsed = new Date(`${dateString} UTC`);
  return parsed.toString() !== 'Invalid Date' ? parsed : null;
};

const convertUTCTime = () => {
  if (!wp.date) {
    return;
  }

  const settings = wp.date.getSettings();
  const dateFormat = settings.formats.date;
  const timeFormat = settings.formats.time;
  const dateTimeFormat = `${dateFormat}, ${timeFormat}`;
  const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;

  // Combined date + time in a single node
  document.querySelectorAll('.' + UTC_DATE_CLASS).forEach((el) => {
    const localDateTime = parseUTCDate(el.textContent ?? '');
    if (localDateTime) {
      el.textContent = wp.date.dateI18n(dateTimeFormat, localDateTime, timeZone);
    }
  });

  // Separate date / time nodes sharing a wrapper with the raw UTC value
  document.querySelectorAll('.' + UTC_DATETIME_WRAPPER_CLASS).forEach((wrapper) => {
    const rawUTC = wrapper.getAttribute(UTC_DATETIME_ATTRIBUTE);
    if (!rawUTC) {
      return;
    }

    const localDateTime = parseUTCDate(rawUTC);
    if (!localDateTime) {
      return;
    }

    const dateEl = wrapper.querySelector('.' + UTC_DATETIME_DATE_CLASS);
    const timeEl = wrapper.querySelector('.' + UTC_DATETIME_TIME_CLASS);

    if (dateEl) {
      dateEl.textContent = wp.date.dateI18n(dateFormat, localDateTime, timeZone);
    }
    if (timeEl) {
      timeEl.textContent = wp.date.dateI18n(timeFormat, localDateTime, timeZone);
    }
  });
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
