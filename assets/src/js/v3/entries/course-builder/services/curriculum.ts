import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';

import type { AssignmentForm } from '@CourseBuilderComponents/modals/AssignmentModal';
import type { LessonForm } from '@CourseBuilderComponents/modals/LessonModal';
import { useToast } from '@TutorShared/atoms/Toast';
import type { CourseVideo } from '@TutorShared/components/fields/FormVideoInput';

import type { ContentDripType, GoogleMeet, ZoomMeeting } from '@CourseBuilderServices/course';
import { Addons } from '@TutorShared/config/constants';
import { type WPMedia } from '@TutorShared/hooks/useWpMedia';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import type { ErrorResponse } from '@TutorShared/utils/form';
import { type ID, type TopicContentType, type TutorMutationResponse } from '@TutorShared/utils/types';
import { convertToErrorMessage, isAddonEnabled } from '@TutorShared/utils/util';

export interface Content {
  ID: ID;
  post_title: string;
  post_content: string;
  post_name: string | null;
  post_type: TopicContentType;
  total_question?: number;
  quiz_type?: 'tutor_h5p_quiz';
}

export interface Lesson extends Content {
  attachments: WPMedia[];
  thumbnail: string;
  thumbnail_id: ID;
  available_on: string;
  video: CourseVideo & {
    runtime: {
      hours: number;
      minutes: number;
      seconds: number;
    };
  };
  is_preview: boolean;
  content_drip_settings: {
    unlock_date: string;
    after_xdays_of_enroll: string;
    prerequisites: ID[];
  };
}
export interface Assignment extends Content {
  attachments: WPMedia[];
  assignment_option: {
    time_duration: {
      time: string;
      value: string;
    };
    deadline_from_start: string;
    total_mark: number;
    pass_mark: number;
    upload_files_limit: number;
    upload_file_size_limit: number;
    is_retry_allowed: '0' | '1';
    attempts_allowed: number;
  };
  content_drip_settings: {
    unlock_date: string;
    after_xdays_of_enroll: string;
    prerequisites: ID[];
  };
}

export interface ZoomLive extends Content {
  type: 'zoom';
}
export interface MeetLive extends Content {
  type: 'tutor-google-meet';
}

export interface CourseTopic {
  id: ID;
  title: string;
  summary: string;
  contents: Content[];
}

interface TopicPayload {
  topic_id?: ID;
  course_id: ID;
  title: string;
  summary: string;
}

export interface LessonPayload {
  topic_id: ID;
  lesson_id?: ID; //only for update
  title: string;
  description: string;
  thumbnail_id: ID | null;

  'video[source]'?: string;
  'video[source_video_id]'?: ID;
  'video[poster]'?: string;
  'video[source_external_url]'?: string;
  'video[source_shortcode]'?: string;
  'video[source_youtube]'?: string;
  'video[source_vimeo]'?: string;
  'video[source_embedded]'?: string;

  'video[runtime][hours]': number;
  'video[runtime][minutes]': number;
  'video[runtime][seconds]': number;

  _is_preview?: 0 | 1; //only when course preview addon enabled
  tutor_attachments: ID[];
  'content_drip_settings[unlock_date]'?: string;
  'content_drip_settings[after_xdays_of_enroll]'?: string;
  'content_drip_settings[prerequisites]'?: ID[] | string;
}

export interface AssignmentPayload {
  topic_id: ID;
  assignment_id?: ID; //only for update
  title: string;
  summary: string;
  attachments: ID[];
  'assignment_option[time_duration][time]': string;
  'assignment_option[time_duration][value]': string;
  'assignment_option[deadline_from_start]': string;
  'assignment_option[total_mark]': number;
  'assignment_option[pass_mark]': number;
  'assignment_option[upload_files_limit]': number;
  'assignment_option[upload_file_size_limit]': number;
  'assignment_option[is_retry_allowed]': '0' | '1';
  'assignment_option[attempts_allowed]'?: number;

  'content_drip_settings[unlock_date]'?: string;
  'content_drip_settings[after_xdays_of_enroll]'?: string;
  'content_drip_settings[prerequisites]'?: ID[] | string;
}

export interface ContentDuplicatePayload {
  content_id: ID;
  course_id: ID;
  content_type: 'lesson' | 'assignment' | 'answer' | 'question' | 'quiz' | 'topic';
}

export interface CourseContentOrderPayload {
  tutor_topics_lessons_sorting: {
    [order: string]: {
      topic_id: ID;
      // lesson_ids represents the order of all contents inside a topic
      lesson_ids: {
        [order: string]: ID;
      };
    };
  };
  'content_parent[parent_topic_id]'?: ID; //only for topic contents
  'content_parent[content_id]'?: ID; // only for topic contents
}

export interface ZoomMeetingDetailsPayload {
  meeting_id: ID;
  topic_id: ID;
}

export const convertLessonDataToPayload = (
  data: LessonForm,
  lessonId: ID,
  topicId: ID,
  contentDripType: ContentDripType,
  slotFields: string[],
): LessonPayload => {
  return {
    ...(lessonId && { lesson_id: lessonId }),
    topic_id: topicId,
    title: data.title,
    description: data.description,
    thumbnail_id: data.thumbnail?.id ?? null,
    ...(data.video
      ? Object.fromEntries(
          Object.entries(data.video).map(([key, value]) => [
            `video[${key}]`,
            key === 'source' && !value ? '-1' : key === 'poster_url' && !data.video?.poster ? '' : value,
          ]),
        )
      : {}),
    'video[runtime][hours]': data.duration.hour || 0,
    'video[runtime][minutes]': data.duration.minute || 0,
    'video[runtime][seconds]': data.duration.second || 0,
    ...(isAddonEnabled(Addons.TUTOR_COURSE_PREVIEW) && { _is_preview: data.lesson_preview ? 1 : 0 }),
    tutor_attachments: (data.tutor_attachments || []).map((attachment) => attachment.id),
    ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
      contentDripType === 'unlock_by_date' && {
        'content_drip_settings[unlock_date]': data.content_drip_settings.unlock_date || '',
      }),
    ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
      contentDripType === 'specific_days' && {
        'content_drip_settings[after_xdays_of_enroll]': data.content_drip_settings.after_xdays_of_enroll || '0',
      }),
    ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
      contentDripType === 'after_finishing_prerequisites' && {
        'content_drip_settings[prerequisites]': data.content_drip_settings.prerequisites?.length
          ? data.content_drip_settings.prerequisites
          : '',
      }),
    ...Object.fromEntries(
      slotFields.map((key) => {
        return [key, data[key as keyof LessonForm] || ''];
      }),
    ),
  };
};

export const convertAssignmentDataToPayload = (
  data: AssignmentForm,
  assignmentId: ID,
  topicId: ID,
  contentDripType: ContentDripType,
  slotFields: string[],
): AssignmentPayload => {
  return {
    ...(assignmentId && { assignment_id: assignmentId }),
    topic_id: topicId,
    title: data.title,
    summary: data.summary,
    attachments: (data.attachments || []).map((attachment) => attachment.id),
    'assignment_option[time_duration][time]': data.time_duration.time,
    'assignment_option[time_duration][value]': data.time_duration.value,
    'assignment_option[deadline_from_start]': data.deadline_from_start ? '1' : '0',
    'assignment_option[total_mark]': data.total_mark,
    'assignment_option[pass_mark]': data.pass_mark,
    'assignment_option[upload_files_limit]': data.upload_files_limit,
    'assignment_option[upload_file_size_limit]': data.upload_file_size_limit,
    'assignment_option[is_retry_allowed]': data.is_retry_allowed ? '1' : '0',

    ...(data.is_retry_allowed && {
      'assignment_option[attempts_allowed]': data.attempts_allowed,
    }),
    ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
      contentDripType === 'unlock_by_date' && {
        'content_drip_settings[unlock_date]': data.content_drip_settings.unlock_date || '',
      }),
    ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
      contentDripType === 'specific_days' && {
        'content_drip_settings[after_xdays_of_enroll]': data.content_drip_settings.after_xdays_of_enroll || '0',
      }),
    ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
      contentDripType === 'after_finishing_prerequisites' && {
        'content_drip_settings[prerequisites]': data.content_drip_settings.prerequisites?.length
          ? data.content_drip_settings.prerequisites
          : '',
      }),
    ...Object.fromEntries(
      slotFields.map((key) => {
        return [key, data[key as keyof AssignmentForm] || ''];
      }),
    ),
  };
};

const getCourseTopic = (courseId: ID) => {
  return wpAjaxInstance.get<CourseTopic[]>(endpoints.GET_COURSE_CONTENTS, {
    params: { course_id: courseId },
  });
};

export const useCourseTopicQuery = (courseId: ID) => {
  return useQuery({
    queryKey: ['Topic', courseId],
    queryFn: () =>
      getCourseTopic(courseId).then((res) => {
        return res.data.map((topic) => ({
          ...topic,
          contents: topic.contents.map((content) => ({
            ...content,
            post_type: content.quiz_type ? 'tutor_h5p_quiz' : content.post_type,
          })),
        }));
      }),
    enabled: !!courseId,
  });
};

const saveTopic = (payload: TopicPayload) => {
  return wpAjaxInstance.post<TopicPayload, TutorMutationResponse<ID>>(endpoints.SAVE_TOPIC, payload);
};

export const useSaveTopicMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: saveTopic,
    onSuccess: (response) => {
      if (response.data) {
        showToast({
          message: __('Topic saved successfully', 'tutor'),
          type: 'success',
        });
        queryClient.invalidateQueries({
          queryKey: ['Topic'],
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const deleteTopic = (topicId: ID) => {
  return wpAjaxInstance.post<string, TutorMutationResponse<number>>(endpoints.DELETE_TOPIC, {
    topic_id: topicId,
  });
};

export const useDeleteTopicMutation = (courseId: ID) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: deleteTopic,
    onSuccess: (response, topicId) => {
      if (response.status_code === 200) {
        showToast({
          message: __(response.message, 'tutor'),
          type: 'success',
        });

        queryClient.setQueryData(['Topic', courseId], (oldData: CourseTopic[]) => {
          const oldDataCopy = JSON.parse(JSON.stringify(oldData)) as CourseTopic[];

          return oldDataCopy.filter((topic) => String(topic.id) !== String(topicId));
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });

      queryClient.invalidateQueries({
        queryKey: ['Topic'],
      });
    },
  });
};

const getLessonDetails = (lessonId: ID, topicId: ID) => {
  return wpAjaxInstance.get<Lesson>(endpoints.GET_LESSON_DETAILS, {
    params: { topic_id: topicId, lesson_id: lessonId },
  });
};

export const useLessonDetailsQuery = (lessonId: ID, topicId: ID) => {
  return useQuery({
    queryKey: ['Lesson', lessonId, topicId],
    queryFn: () => getLessonDetails(lessonId, topicId).then((res) => res.data),
    enabled: !!lessonId && !!topicId,
  });
};

const saveLesson = (payload: LessonPayload) => {
  return wpAjaxInstance.post<TutorMutationResponse<number>>(endpoints.SAVE_LESSON, payload);
};

export const useSaveLessonMutation = (courseId: ID) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: (payload: LessonPayload) => saveLesson(payload),
    onSuccess: (response, payload) => {
      if (response.data) {
        queryClient.invalidateQueries({
          queryKey: ['Topic', courseId],
        });
        queryClient.invalidateQueries({
          queryKey: ['Lesson', payload.lesson_id, payload.topic_id],
        });

        showToast({
          message: __('Lesson saved successfully', 'tutor'),
          type: 'success',
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const deleteContent = (lessonId: ID) => {
  return wpAjaxInstance.post<string, TutorMutationResponse<ID>>(endpoints.DELETE_TOPIC_CONTENT, {
    lesson_id: lessonId,
  });
};

export const useDeleteContentMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: deleteContent,
    onSuccess: (response) => {
      if (response.status_code === 200) {
        showToast({
          message: __(response.message, 'tutor'),
          type: 'success',
        });

        queryClient.invalidateQueries({
          queryKey: ['Topic'],
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const updateCourseContentOrder = (payload: CourseContentOrderPayload) => {
  return wpAjaxInstance.post<
    CourseContentOrderPayload,
    {
      success: boolean;
    }
  >(endpoints.UPDATE_COURSE_CONTENT_ORDER, payload);
};

export const useUpdateCourseContentOrderMutation = () => {
  const { showToast } = useToast();

  return useMutation({
    mutationFn: updateCourseContentOrder,
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const getAssignmentDetails = (assignmentId: ID, topicId: ID) => {
  return wpAjaxInstance.get<Assignment>(endpoints.GET_ASSIGNMENT_DETAILS, {
    params: { topic_id: topicId, assignment_id: assignmentId },
  });
};

export const useAssignmentDetailsQuery = (assignmentId: ID, topicId: ID) => {
  return useQuery({
    queryKey: ['Assignment', assignmentId, topicId],
    queryFn: () => getAssignmentDetails(assignmentId, topicId).then((res) => res.data),
    enabled: !!assignmentId && !!topicId,
  });
};

const saveAssignment = (payload: AssignmentPayload) => {
  return wpAjaxInstance.post<string, TutorMutationResponse<number>>(endpoints.SAVE_ASSIGNMENT, payload);
};

export const useSaveAssignmentMutation = (courseId: ID) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: (payload: AssignmentPayload) => saveAssignment(payload),
    onSuccess: (response, payload) => {
      if (response.status_code === 200 || response.status_code === 201) {
        queryClient.invalidateQueries({
          queryKey: ['Topic', Number(courseId)],
        });
        queryClient.invalidateQueries({
          queryKey: ['Assignment', payload.assignment_id, payload.topic_id],
        });

        showToast({
          message: __(response.message, 'tutor'),
          type: 'success',
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

const duplicateContent = (payload: ContentDuplicatePayload) => {
  return wpAjaxInstance.post<string, TutorMutationResponse<number>>(endpoints.DUPLICATE_CONTENT, payload);
};

/**
 *
 * @param quizId pass when duplicating 'answer'
 * @returns useMutation
 */
export const useDuplicateContentMutation = (quizId?: ID) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: duplicateContent,
    onSuccess: (response, payload) => {
      if (response.status_code === 200 || response.status_code === 201) {
        showToast({
          message: __(response.message, 'tutor'),
          type: 'success',
        });

        if (['lesson', 'assignment', 'quiz', 'topic'].includes(payload.content_type)) {
          queryClient.invalidateQueries({
            queryKey: ['Topic'],
          });
          return;
        }

        if (['question'].includes(payload.content_type)) {
          queryClient.invalidateQueries({
            queryKey: ['Quiz', quizId],
          });
          return;
        }
      }
    },
    onError: (error: ErrorResponse, payload) => {
      showToast({
        message: convertToErrorMessage(error),
        type: 'danger',
      });

      if (['answer'].includes(payload.content_type)) {
        queryClient.invalidateQueries({
          queryKey: ['Quiz', quizId],
        });
      }
    },
  });
};

const getZoomMeetingDetails = (meetingId: ID, topicId: ID) => {
  return wpAjaxInstance.get<ZoomMeeting>(endpoints.GET_ZOOM_MEETING_DETAILS, {
    params: { meeting_id: meetingId, topic_id: topicId },
  });
};

export const useZoomMeetingDetailsQuery = (meetingId: ID, topicId: ID) => {
  return useQuery({
    queryKey: ['ZoomMeeting', meetingId],
    queryFn: () => getZoomMeetingDetails(meetingId, topicId).then((res) => res.data),
    enabled: !!meetingId && !!topicId,
  });
};

const getGoogleMeetDetails = (meetingId: ID, topicId: ID) => {
  return wpAjaxInstance.get<GoogleMeet>(endpoints.GET_GOOGLE_MEET_DETAILS, {
    params: { meeting_id: meetingId, topic_id: topicId },
  });
};

export const useGoogleMeetDetailsQuery = (meetingId: ID, topicId: ID) => {
  return useQuery({
    queryKey: ['GoogleMeet', meetingId],
    queryFn: () => getGoogleMeetDetails(meetingId, topicId).then((res) => res.data),
    enabled: !!meetingId && !!topicId,
  });
};

interface AddContentBankContentToCoursePayload {
  course_id: ID;
  topic_id: ID;
  content_ids: ID[];
  next_content_order: number;
}

const addContentBankContentToCourse = (payload: AddContentBankContentToCoursePayload) => {
  return wpAjaxInstance.post<AddContentBankContentToCoursePayload, TutorMutationResponse<ID[]>>(
    endpoints.ADD_CONTENT_BANK_CONTENT_TO_COURSE,
    payload,
  );
};

export const useAddContentBankContentToCourseMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: addContentBankContentToCourse,
    onSuccess: (response, payload) => {
      if (response.status_code === 200) {
        showToast({
          message: __(response.message, 'tutor'),
          type: 'success',
        });

        queryClient.invalidateQueries({
          queryKey: ['Topic', payload.course_id],
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};

interface DeleteContentBankContentPayload {
  topicId: ID;
  contentId: ID;
}

const deleteContentBankContent = ({ topicId, contentId }: DeleteContentBankContentPayload) => {
  return wpAjaxInstance.post<string, TutorMutationResponse<DeleteContentBankContentPayload>>(
    endpoints.DELETE_CONTENT_BANK_CONTENT_FROM_COURSE,
    {
      topic_id: topicId,
      content_id: contentId,
    },
  );
};

export const useDeleteContentBankContentMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: deleteContentBankContent,
    onSuccess: (response) => {
      if (response.status_code === 200) {
        showToast({
          message: __(response.message, 'tutor'),
          type: 'success',
        });

        queryClient.invalidateQueries({
          queryKey: ['Topic'],
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({ type: 'danger', message: convertToErrorMessage(error) });
    },
  });
};
