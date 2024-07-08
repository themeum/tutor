import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';

import { useToast } from '@Atoms/Toast';
import { authApiInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import type { AxiosResponse } from 'axios';
import type { ErrorResponse } from '@Utils/form';
import type { TutorMutationResponse } from '@CourseBuilderServices/course';

export type ID = string | number;

export type ContentType = 'tutor-google-meet' | 'tutor_zoom_meeting' | 'lesson' | 'tutor_quiz' | 'tutor_assignments';
export interface Content {
  ID: ID;
  post_title: string;
  post_content: string;
  post_name: string | null;
  post_type: ContentType;
}
export interface LessonVideo {
  source: 'youtube' | 'vimeo';
  source_video_id: string;
  source_youtube: string;
  source_vimeo: string;
  runtime: {
    hours: string;
    minutes: string;
    seconds: string;
  };
  poster: string;
}

export interface Lesson extends Content {
  type: 'lesson';
  course_id: ID;
  attachments: unknown[];
  thumbnail: boolean;
  video: LessonVideo[];
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
  type: 'assignment';
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

interface LessonPayload {
  topic_id: ID;
  lesson_id: ID; //only for update
  title: string;
  description: string;
  thumbnail_id: number;

  'video[source]': string;
  'video[source_video_id]': ID;

  'video[runtime][hours]': number;
  'video[runtime][minutes]': number;
  'video[runtime][seconds]': number;

  _is_preview: 0 | 1; //only when course preview addon enabled
  tutor_attachments: ID[];
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
      console.log(response.data);
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
  return authApiInstance.post<
    string,
    {
      success: true;
    }
  >(endpoints.ADMIN_AJAX, {
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
      if (response.success) {
        queryClient.invalidateQueries({
          queryKey: ['Topic'],
        });
        showToast({
          message: __('Topic deleted successfully', 'tutor'),
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

const saveLesson = (payload: LessonPayload) => {
  return authApiInstance.post<string, AxiosResponse<TutorMutationResponse>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_save_lesson',
    ...payload,
  });
};

export const useSaveLessonMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: saveLesson,
    onSuccess: (response) => {
      if (response.data) {
        queryClient.invalidateQueries({
          queryKey: ['Topic'],
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
  return authApiInstance.post<
    string,
    {
      success: true;
    }
  >(endpoints.ADMIN_AJAX, {
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
      if (response.success) {
        queryClient.invalidateQueries({
          queryKey: ['Topic'],
        });
        showToast({
          message: __('Lesson deleted successfully', 'tutor'),
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
