import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import type { AxiosResponse } from 'axios';

import { useToast } from '@Atoms/Toast';
import { authApiInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import type { ErrorResponse } from '@Utils/form';
import type {
  GoogleMeet,
  PrerequisiteCourses,
  TutorMutationResponse,
  ZoomMeeting,
} from '@CourseBuilderServices/course';
import type { CourseVideo } from '@Components/fields/FormVideoInput';
import type { Media } from '@Components/fields/FormImageInput';

export type ID = string | number;

export type ContentType = 'tutor-google-meet' | 'tutor_zoom_meeting' | 'lesson' | 'tutor_quiz' | 'tutor_assignments';
export interface Content {
  ID: ID;
  post_title: string;
  post_content: string;
  post_name: string | null;
  post_type: ContentType;
}

export interface Lesson extends Content {
  attachments: Media[];
  thumbnail: string;
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
    course_prerequisites: PrerequisiteCourses[];
  };
}
export type QuestionType = 'single_choice';
export interface QuestionSetting {
  question_type: QuestionType;
  answer_required: boolean;
  randomize_question: boolean;
  question_mark: number;
  show_question_mark: boolean;
}
export interface QuestionAnswer {
  answer_id: ID;
  answer_title: string;
  is_correct: boolean;
}
export interface QuizQuestion {
  question_id: ID;
  question_title: string;
  question_description: string;
  question_type: QuestionType;
  question_mark: number;
  question_settings: QuestionSetting;
  question_answers: QuestionAnswer[];
}

export interface Quiz extends Content {
  type: 'quiz';
  questions: QuizQuestion[];
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
    course_prerequisites: PrerequisiteCourses[];
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
  thumbnail_id: ID;

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
    queryFn: () => getCourseTopic(courseId).then((res) => res.data),
    enabled: !!courseId,
  });
};

const saveTopic = (payload: TopicPayload) => {
  return authApiInstance.post<string, AxiosResponse<TutorMutationResponse>>(endpoints.ADMIN_AJAX, {
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
        queryClient.invalidateQueries({
          queryKey: ['Topic'],
        });
        showToast({
          message: __('Topic saved successfully', 'tutor'),
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

const deleteTopic = (topicId: ID) => {
  return authApiInstance.post<string, TutorMutationResponse>(endpoints.ADMIN_AJAX, {
    action: 'tutor_delete_topic',
    topic_id: topicId,
  });
};

export const useDeleteTopicMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: deleteTopic,
    onSuccess: (response) => {
      if (response.status_code === 200) {
        queryClient.invalidateQueries({
          queryKey: ['Topic'],
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

const getLessonDetails = (topicId: ID, lessonId: ID) => {
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
  return authApiInstance.post<string, AxiosResponse<TutorMutationResponse>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_save_lesson',
    ...payload,
  });
};

export const useSaveLessonMutation = ({
  courseId,
  topicId,
  lessonId,
}: {
  courseId: ID;
  topicId: ID;
  lessonId: ID;
}) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: (payload: LessonPayload) => saveLesson(payload),
    onSuccess: (response) => {
      if (response.data) {
        queryClient.invalidateQueries({
          queryKey: ['Topic', courseId],
        });
        queryClient.invalidateQueries({
          queryKey: ['Lesson', topicId, lessonId],
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

const deleteLesson = (lessonId: ID) => {
  return authApiInstance.post<string, TutorMutationResponse>(endpoints.ADMIN_AJAX, {
    action: 'tutor_delete_lesson',
    lesson_id: lessonId,
  });
};

export const useDeleteLessonMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: deleteLesson,
    onSuccess: (response) => {
      if (response.status_code === 200) {
        queryClient.invalidateQueries({
          queryKey: ['Topic'],
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

const getAssignmentDetails = (topicId: ID, assignmentId: ID) => {
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
  return authApiInstance.post<string, TutorMutationResponse>(endpoints.ADMIN_AJAX, {
    action: 'tutor_assignment_save',
    ...payload,
  });
};

export const useSaveAssignmentMutation = ({
  courseId,
  topicId,
  assignmentId,
}: {
  courseId: ID;
  topicId: ID;
  assignmentId: ID;
}) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: (payload: AssignmentPayload) => saveAssignment(payload),
    onSuccess: (response) => {
      if (response.status_code === 200) {
        queryClient.invalidateQueries({
          queryKey: ['Topic', Number(courseId)],
        });
        queryClient.invalidateQueries({
          queryKey: ['Assignment', topicId, assignmentId],
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
