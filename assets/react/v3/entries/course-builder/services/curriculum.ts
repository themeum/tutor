import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import type { AxiosResponse } from 'axios';

import { useToast } from '@Atoms/Toast';
import type { Media } from '@Components/fields/FormImageInput';
import type { CourseVideo } from '@Components/fields/FormVideoInput';
import type { GoogleMeet, TutorMutationResponse, ZoomMeeting } from '@CourseBuilderServices/course';
import { authApiInstance, wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import type { ErrorResponse } from '@Utils/form';
import type { H5PContentResponse } from './quiz';

export type ID = string | number;

export type ContentType =
  | 'tutor-google-meet'
  | 'tutor_zoom_meeting'
  | 'lesson'
  | 'tutor_quiz'
  | 'tutor_assignments'
  | 'tutor_h5p_quiz';

export interface Content {
  ID: ID;
  post_title: string;
  post_content: string;
  post_name: string | null;
  post_type: ContentType;
  total_question?: number;
  quiz_type?: 'tutor_h5p_quiz';
}

export interface Lesson extends Content {
  attachments: Media[];
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
  attachments: Media[];
  assignment_option: {
    time_duration: {
      time: string;
      value: string;
    };
    total_mark: number;
    pass_mark: number;
    upload_files_limit: number;
    upload_file_size_limit: number;
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
  type: '"tutor-google-meet"';
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

  'video[source]': string;
  'video[source_video_id]': ID;
  'video[poster]': string;
  'video[source_external_url]': string;
  'video[source_shortcode]': string;
  'video[source_youtube]': string;
  'video[source_vimeo]': string;
  'video[source_embedded]': string;

  'video[runtime][hours]': number;
  'video[runtime][minutes]': number;
  'video[runtime][seconds]': number;

  _is_preview?: 0 | 1; //only when course preview addon enabled
  tutor_attachments: ID[];
  'content_drip_settings[unlock_date]'?: string;
  'content_drip_settings[after_xdays_of_enroll]'?: string;
  'content_drip_settings[prerequisites]'?: ID[];
}

export interface AssignmentPayload {
  topic_id: ID;
  assignment_id?: ID; //only for update
  title: string;
  summary: string;
  attachments: ID[];
  'assignment_option[time_duration][time]': string;
  'assignment_option[time_duration][value]': string;
  'assignment_option[total_mark]': number;
  'assignment_option[pass_mark]': number;
  'assignment_option[upload_files_limit]': number;
  'assignment_option[upload_file_size_limit]': number;

  'content_drip_settings[unlock_date]'?: string;
  'content_drip_settings[after_xdays_of_enroll]'?: string;
  'content_drip_settings[prerequisites]'?: ID[];
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

const getCourseTopic = (courseId: ID) => {
  return authApiInstance.post<string, AxiosResponse<CourseTopic[]>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_course_contents',
    course_id: courseId,
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
  return authApiInstance.post<TopicPayload, TutorMutationResponse<ID>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_save_topic',
    ...payload,
  });
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
      showToast({
        message: error.response.data.message,
        type: 'danger',
      });
    },
  });
};

const deleteTopic = (topicId: ID) => {
  return authApiInstance.post<string, TutorMutationResponse<number>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_delete_topic',
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

          return oldDataCopy.filter((topic) => topic.id !== topicId);
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({
        message: error.response.data.message,
        type: 'danger',
      });

      queryClient.invalidateQueries({
        queryKey: ['Topic'],
      });
    },
  });
};

const getLessonDetails = (lessonId: ID, topicId: ID) => {
  return authApiInstance.post<string, AxiosResponse<Lesson>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_lesson_details',
    topic_id: topicId,
    lesson_id: lessonId,
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
  return authApiInstance.post<string, AxiosResponse<TutorMutationResponse<number>>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_save_lesson',
    ...payload,
  });
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
      showToast({
        message: error.response.data.message,
        type: 'danger',
      });
    },
  });
};

const deleteContent = (lessonId: ID) => {
  return authApiInstance.post<string, TutorMutationResponse<ID>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_delete_lesson',
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
      showToast({
        message: error.response.data.message,
        type: 'danger',
      });
    },
  });
};

const updateCourseContentOrder = (payload: CourseContentOrderPayload) => {
  return authApiInstance.post<
    CourseContentOrderPayload,
    {
      success: boolean;
    }
  >(endpoints.ADMIN_AJAX, {
    action: 'tutor_update_course_content_order',
    ...payload,
  });
};

export const useUpdateCourseContentOrderMutation = () => {
  const { showToast } = useToast();

  return useMutation({
    mutationFn: updateCourseContentOrder,
    onError: (error: ErrorResponse) => {
      showToast({
        message: error.response.data.message,
        type: 'danger',
      });
    },
  });
};

const getAssignmentDetails = (assignmentId: ID, topicId: ID) => {
  return authApiInstance.post<string, AxiosResponse<Assignment>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_assignment_details',
    topic_id: topicId,
    assignment_id: assignmentId,
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
  return authApiInstance.post<string, TutorMutationResponse<number>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_assignment_save',
    ...payload,
  });
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
      showToast({
        message: error.response.data.message,
        type: 'danger',
      });
    },
  });
};

const duplicateContent = (payload: ContentDuplicatePayload) => {
  return authApiInstance.post<string, TutorMutationResponse<number>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_duplicate_content',
    ...payload,
  });
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
        message: error.response.data.message,
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
  return authApiInstance.post<string, AxiosResponse<ZoomMeeting>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_zoom_meeting_details',
    meeting_id: meetingId,
    topic_id: topicId,
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
  return authApiInstance.post<string, AxiosResponse<GoogleMeet>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_google_meet_meeting_details',
    meeting_id: meetingId,
    topic_id: topicId,
  });
};

export const useGoogleMeetDetailsQuery = (meetingId: ID, topicId: ID) => {
  return useQuery({
    queryKey: ['GoogleMeet', meetingId],
    queryFn: () => getGoogleMeetDetails(meetingId, topicId).then((res) => res.data),
    enabled: !!meetingId && !!topicId,
  });
};

const getH5PLessonContents = (search: string) => {
  return wpAjaxInstance
    .post<H5PContentResponse>(endpoints.GET_H5P_LESSON_CONTENT, {
      search_filter: search,
    })
    .then((response) => response.data);
};

export const useGetH5PLessonContentsQuery = (search: string, contentType: ContentType) => {
  return useQuery({
    queryKey: ['H5PQuizContents', search],
    queryFn: () => getH5PLessonContents(search),
    enabled: contentType === 'lesson',
  });
};
