import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import type { AxiosResponse } from 'axios';

import { useToast } from '@Atoms/Toast';
import type {
  QuizFeedbackMode,
  QuizLayoutView,
  QuizQuestionsOrder,
  QuizTimeLimit,
} from '@CourseBuilderComponents/modals/QuizModal';

import { tutorConfig } from '@Config/config';
import { Addons } from '@Config/constants';
import { isAddonEnabled } from '@CourseBuilderUtils/utils';
import { authApiInstance, wpAjaxInstance } from '@Utils/api';
import endpoints from '@Utils/endpoints';
import type { ErrorResponse } from '@Utils/form';
import type { ContentDripType, TutorMutationResponse } from './course';
import type { ContentType, ID } from './curriculum';

export type QuizDataStatus = 'new' | 'update' | 'no_change';

export type QuizQuestionType =
  | 'true_false'
  | 'single_choice'
  | 'multiple_choice'
  | 'open_ended'
  | 'fill_in_the_blank'
  | 'short_answer'
  | 'matching'
  | 'image_matching'
  | 'image_answering'
  | 'ordering'
  | 'h5p';

export interface QuizQuestionOption {
  _data_status: QuizDataStatus;
  is_saved: boolean;
  answer_id: ID;
  belongs_question_id: ID;
  belongs_question_type: QuizQuestionType;
  answer_title: string;
  is_correct: '0' | '1';
  image_id?: ID;
  image_url?: string;
  answer_two_gap_match: string;
  answer_view_format: string;
  answer_order: number;
}

export interface QuizQuestion {
  _data_status: QuizDataStatus;
  question_id: ID;
  question_title: string;
  question_description: string;
  question_mark: number;
  answer_explanation: string;
  question_order: number;
  question_type: QuizQuestionType;
  question_settings: {
    question_type: QuizQuestionType;
    answer_required: boolean;
    randomize_options: boolean;
    question_mark: number;
    show_question_mark: boolean;
    has_multiple_correct_answer: boolean;
    is_image_matching: boolean;
  };
  question_answers: QuizQuestionOption[];
}

interface ImportQuizPayload {
  topic_id: ID;
  csv_file: File;
}

interface QuizQuestionsForPayload extends Omit<QuizQuestion, 'question_settings' | 'answer_explanation'> {
  answer_explanation?: string;
  question_settings: {
    question_type: QuizQuestionType;
    answer_required: '0' | '1';
    randomize_options: '0' | '1';
    question_mark: number;
    show_question_mark: '0' | '1';
    has_multiple_correct_answer?: '0' | '1';
    is_image_matching?: '0' | '1';
  };
}

interface QuizResponseWithStatus extends Omit<QuizDetailsResponse, 'questions' | 'quiz_option'> {
  _data_status: QuizDataStatus;
  questions: QuizQuestionsForPayload[];
  quiz_option: Omit<QuizDetailsResponse['quiz_option'], 'content_drip_settings'> & {
    content_drip_settings?: {
      unlock_date: string;
      after_xdays_of_enroll: number;
      prerequisites: ID[];
    };
    quiz_type?: string;
  };
}
interface QuizPayload {
  course_id: ID;
  topic_id: ID;
  payload: QuizResponseWithStatus;
  deleted_question_ids?: ID[];
  deleted_answer_ids?: ID[];
}

export interface QuizDetailsResponse {
  ID: ID;
  post_title: string;
  post_content: string;
  quiz_option: {
    time_limit: {
      time_value: number;
      time_type: QuizTimeLimit;
    };
    hide_quiz_time_display: '0' | '1';
    feedback_mode: QuizFeedbackMode;
    attempts_allowed: number;
    pass_is_required: '0' | '1';
    passing_grade: number;
    max_questions_for_answer: number;
    quiz_auto_start: '0' | '1';
    question_layout_view: QuizLayoutView;
    questions_order: QuizQuestionsOrder;
    hide_question_number_overview: '0' | '1';
    short_answer_characters_limit: number;
    open_ended_answer_characters_limit: number;
    content_drip_settings: {
      unlock_date: string;
      after_xdays_of_enroll: number;
      prerequisites: [];
    };
  };
  questions: Omit<QuizQuestion, '_data_status'>[];
}
export interface QuizForm {
  ID: ID;
  _data_status: 'new' | 'update' | 'no_change';
  quiz_title: string;
  quiz_description: string;
  quiz_option: {
    time_limit: {
      time_value: number;
      time_type: QuizTimeLimit;
    };
    hide_quiz_time_display: boolean;
    feedback_mode: QuizFeedbackMode;
    attempts_allowed: number;
    pass_is_required: boolean;
    passing_grade: number;
    max_questions_for_answer: number;
    quiz_auto_start: boolean;
    question_layout_view: QuizLayoutView;
    questions_order: QuizQuestionsOrder;
    hide_question_number_overview: boolean;
    short_answer_characters_limit: number;
    open_ended_answer_characters_limit: number;
    content_drip_settings: {
      unlock_date: string;
      after_xdays_of_enroll: number;
      prerequisites: ID[];
    };
    quiz_type?: string;
  };
  questions: QuizQuestion[];
  deleted_question_ids: ID[];
  deleted_answer_ids: ID[];
}

interface QuizUpdateQuestionPayload {
  question_id: ID;
  question_title: string;
  question_description: string;
  question_type: string;
  question_mark: number;
  answer_explanation: string;
  'question_settings[question_type]': string;
  'question_settings[answer_required]': 0 | 1;
  'question_settings[question_mark]': number;
}

export interface H5PContent {
  id: ID;
  title: string;
  content_type: string;
  user_id: ID;
  user_name: string;
  updated_at: string;
}

interface H5PContentResponse {
  output: H5PContent[];
}

export const convertQuizResponseToFormData = (quiz: QuizDetailsResponse): QuizForm => {
  const calculateQuizDataStatus = (answer: QuizQuestionOption) => {
    if (answer.image_url) {
      return answer.answer_view_format === 'text_image' ? 'no_change' : 'update';
    }

    return answer.answer_view_format === 'text' ? 'no_change' : 'update';
  };

  const convertedQuestion = (question: Omit<QuizQuestion, '_data_status'>): QuizQuestion => {
    if (question.question_settings) {
      question.question_settings.answer_required = !!Number(question.question_settings.answer_required);
      question.question_settings.show_question_mark = !!Number(question.question_settings.show_question_mark);
      question.question_settings.randomize_options = !!Number(question.question_settings.randomize_options);
    }
    question.question_answers = question.question_answers.map((answer) => ({
      ...answer,
      _data_status: 'no_change',
      is_saved: true,
    }));

    switch (question.question_type) {
      case 'single_choice': {
        return {
          ...question,
          _data_status: 'update',
          question_type: 'multiple_choice',
          question_answers: question.question_answers.map((answer) => ({
            ...answer,
            _data_status: calculateQuizDataStatus(answer),
            answer_view_format: answer.image_url ? 'text_image' : 'text',
          })),
          question_settings: {
            ...question.question_settings,
            question_type: 'multiple_choice',
            has_multiple_correct_answer: false,
          },
        };
      }
      case 'multiple_choice': {
        return {
          ...question,
          _data_status: question.question_settings.has_multiple_correct_answer ? 'no_change' : 'update',
          question_answers: question.question_answers.map((answer) => ({
            ...answer,
            _data_status: calculateQuizDataStatus(answer),
            answer_view_format: answer.image_url ? 'text_image' : 'text',
          })),
          question_settings: {
            ...question.question_settings,
            has_multiple_correct_answer: question.question_settings.has_multiple_correct_answer
              ? !!Number(question.question_settings.has_multiple_correct_answer)
              : true,
          },
        };
      }
      case 'matching': {
        return {
          ...question,
          _data_status: question.question_settings.is_image_matching ? 'no_change' : 'update',
          question_settings: {
            ...question.question_settings,
            is_image_matching: question.question_settings.is_image_matching
              ? !!Number(question.question_settings.is_image_matching)
              : false,
          },
        };
      }
      case 'image_matching': {
        return {
          ...question,
          _data_status: 'update',
          question_type: 'matching',
          question_settings: {
            ...question.question_settings,
            question_type: 'matching',
            is_image_matching: true,
          },
        };
      }
      default:
        return {
          ...question,
          _data_status: 'no_change',
        } as QuizQuestion;
    }
  };

  return {
    ID: quiz.ID,
    _data_status: 'no_change',
    quiz_title: quiz.post_title || '',
    quiz_description: quiz.post_content || '',
    quiz_option: {
      time_limit: {
        time_value: quiz.quiz_option.time_limit.time_value || 0,
        time_type: quiz.quiz_option.time_limit.time_type || 'minutes',
      },
      hide_quiz_time_display: quiz.quiz_option.hide_quiz_time_display === '1',
      feedback_mode: quiz.quiz_option.feedback_mode || 'retry',
      attempts_allowed: quiz.quiz_option.attempts_allowed || 10,
      pass_is_required: quiz.quiz_option.pass_is_required === '1',
      passing_grade: quiz.quiz_option.passing_grade || 80,
      max_questions_for_answer: quiz.quiz_option.max_questions_for_answer || 10,
      quiz_auto_start: quiz.quiz_option.quiz_auto_start === '1',
      question_layout_view: quiz.quiz_option.question_layout_view || '',
      questions_order: quiz.quiz_option.questions_order || 'rand',
      hide_question_number_overview: quiz.quiz_option.hide_question_number_overview === '1',
      short_answer_characters_limit: quiz.quiz_option.short_answer_characters_limit || 200,
      open_ended_answer_characters_limit: quiz.quiz_option.open_ended_answer_characters_limit || 500,
      content_drip_settings: quiz.quiz_option.content_drip_settings || {
        unlock_date: '',
        after_xdays_of_enroll: 0,
        prerequisites: [],
      },
    },
    questions: (quiz.questions || []).map((question) => convertedQuestion(question)),
    deleted_question_ids: [],
    deleted_answer_ids: [],
  };
};

export const convertQuizFormDataToPayload = (
  formData: QuizForm,
  topicId: ID,
  contentDripType: ContentDripType,
  courseId: ID,
): QuizPayload => {
  return {
    course_id: courseId,
    topic_id: topicId,
    payload: {
      ID: formData.ID,
      _data_status: formData._data_status,
      post_title: formData.quiz_title,
      post_content: formData.quiz_description,
      quiz_option: {
        attempts_allowed: formData.quiz_option.attempts_allowed,
        feedback_mode: formData.quiz_option.feedback_mode,
        hide_question_number_overview: formData.quiz_option.hide_question_number_overview ? '1' : '0',
        hide_quiz_time_display: formData.quiz_option.hide_quiz_time_display ? '1' : '0',
        max_questions_for_answer: formData.quiz_option.max_questions_for_answer,
        open_ended_answer_characters_limit: formData.quiz_option.open_ended_answer_characters_limit,
        pass_is_required: formData.quiz_option.pass_is_required ? '1' : '0',
        passing_grade: formData.quiz_option.passing_grade,
        question_layout_view: formData.quiz_option.question_layout_view,
        questions_order: formData.quiz_option.questions_order,
        quiz_auto_start: formData.quiz_option.quiz_auto_start ? '1' : '0',
        short_answer_characters_limit: formData.quiz_option.short_answer_characters_limit,
        time_limit: {
          time_type: formData.quiz_option.time_limit.time_type,
          time_value: formData.quiz_option.time_limit.time_value,
        },
        ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
          contentDripType === 'unlock_sequentially' &&
          formData.quiz_option.feedback_mode === 'retry' && {
            pass_is_required: formData.quiz_option.pass_is_required ? '1' : '0',
          }),
        ...(isAddonEnabled(Addons.CONTENT_DRIP) && {
          content_drip_settings: formData.quiz_option.content_drip_settings,
        }),
        ...(isAddonEnabled(Addons.H5P_Integration) &&
          formData.questions.every((question) => question.question_type === 'h5p') && {
            quiz_type: 'tutor_h5p_quiz',
          }),
      },
      questions: formData.questions.map((question) => {
        return {
          _data_status: question._data_status,
          question_id: question.question_id,
          question_title: question.question_title,
          question_description: question.question_description,
          question_mark: question.question_settings.question_mark,
          ...(!!tutorConfig.tutor_pro_url && {
            answer_explanation: question.answer_explanation,
          }),
          question_type: question.question_type,
          question_order: question.question_order,
          question_settings: {
            answer_required: question.question_settings.answer_required ? '1' : '0',
            question_mark: question.question_settings.question_mark,
            question_type: question.question_type as QuizQuestionType,
            randomize_options: question.question_settings.randomize_options ? '1' : '0',
            show_question_mark: question.question_settings.show_question_mark ? '1' : '0',
            ...(question.question_type === 'multiple_choice' && {
              has_multiple_correct_answer: question.question_settings.has_multiple_correct_answer ? '1' : '0',
            }),
            ...(question.question_type === 'matching' && {
              is_image_matching: question.question_settings.is_image_matching ? '1' : '0',
            }),
          },
          question_answers: question.question_answers.map(
            (answer) =>
              ({
                _data_status: answer._data_status,
                answer_id: answer.answer_id,
                belongs_question_id: question.question_id,
                belongs_question_type: question.question_type,
                answer_title: answer.answer_title,
                is_correct: answer.is_correct,
                image_id:
                  question.question_type === 'matching' && !question.question_settings.is_image_matching
                    ? ''
                    : answer.image_id,
                image_url:
                  question.question_type === 'matching' && !question.question_settings.is_image_matching
                    ? ''
                    : answer.image_url,
                answer_two_gap_match: answer.answer_two_gap_match,
                answer_view_format: answer.answer_view_format,
                answer_order: answer.answer_order,
              }) as QuizQuestionOption,
          ),
        };
      }),
    },
    deleted_question_ids: formData.deleted_question_ids,
    deleted_answer_ids: formData.deleted_answer_ids,
  };
};

export const convertQuizQuestionFormDataToPayloadForUpdate = (data: QuizQuestion): QuizUpdateQuestionPayload => {
  return {
    question_id: data.question_id,
    question_title: data.question_title,
    question_description: data.question_description,
    question_type: data.question_type,
    question_mark: data.question_mark,
    answer_explanation: data.answer_explanation,
    'question_settings[question_type]': data.question_type,
    'question_settings[answer_required]': data.question_settings.answer_required ? 1 : 0,
    'question_settings[question_mark]': data.question_mark,
  };
};

const importQuiz = (payload: ImportQuizPayload) => {
  return authApiInstance.post<
    string,
    {
      data: {
        message: string;
      };
      success: boolean;
    }
  >(endpoints.ADMIN_AJAX, {
    action: 'quiz_import_data',
    ...payload,
  });
};

export const useImportQuizMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: importQuiz,
    onSuccess: (response) => {
      if (response.success) {
        queryClient.invalidateQueries({
          queryKey: ['Topic'],
        });
        showToast({
          message: __('Quiz imported successfully', 'tutor'),
          type: 'success',
        });
      } else {
        showToast({
          message: response.data.message,
          type: 'danger',
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

const exportQuiz = (quizId: ID) => {
  return authApiInstance.post<
    string,
    {
      data: {
        title: string;
        output_quiz_data: unknown[][];
      };
      success: boolean;
    }
  >(endpoints.ADMIN_AJAX, {
    action: 'quiz_export_data',
    quiz_id: quizId,
  });
};

export const useExportQuizMutation = () => {
  const { showToast } = useToast();

  return useMutation({
    mutationFn: exportQuiz,
    onSuccess: (response) => {
      if (!response.success) {
        showToast({
          message: __('Something went wrong.', 'tutor'),
          type: 'danger',
        });
        return;
      }

      let csvContent = '';
      for (const rowArray of response.data.output_quiz_data) {
        const row = rowArray.join(',');
        csvContent += `${row}\r\n`;
      }
      const blob = new Blob([csvContent], {
        type: 'text/csv',
      });
      const csvUrl = window.webkitURL.createObjectURL(blob);
      const link = document.createElement('a');
      link.setAttribute('href', csvUrl);
      link.setAttribute('download', `tutor-quiz-${response.data.title}.csv`);
      document.body.appendChild(link);
      link.click();
    },
    onError: (error: ErrorResponse) => {
      showToast({
        message: error.response.data.message,
        type: 'danger',
      });
    },
  });
};

const saveQuiz = (payload: QuizPayload) => {
  return wpAjaxInstance.post<QuizPayload, TutorMutationResponse<QuizDetailsResponse>>(endpoints.SAVE_QUIZ, {
    action: 'tutor_quiz_builder_save',
    ...payload,
  });
};

export const useSaveQuizMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: saveQuiz,
    onSuccess: (response) => {
      if (response.data) {
        queryClient.setQueryData(['Quiz', response.data.ID], response.data);

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

const getQuizDetails = (quizId: ID) => {
  return authApiInstance.post<ID, AxiosResponse<QuizDetailsResponse>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_quiz_details',
    quiz_id: quizId,
  });
};

export const useGetQuizDetailsQuery = (quizId: ID) => {
  return useQuery({
    queryKey: ['Quiz', quizId],
    queryFn: () => getQuizDetails(quizId).then((response) => response.data),
    enabled: !!quizId,
  });
};

export const calculateQuizDataStatus = (dataStatus: QuizDataStatus, currentStatus: QuizDataStatus) => {
  if (currentStatus === dataStatus) {
    return null;
  }

  if (dataStatus === 'new') {
    return 'new';
  }

  if ((dataStatus === 'update' || dataStatus === 'no_change') && currentStatus === 'update') {
    return 'update';
  }

  return 'no_change';
};

const getH5PQuizContents = () => {
  return wpAjaxInstance.get<H5PContentResponse>(endpoints.GET_H5P_QUIZ_CONTENT).then((response) => response.data);
};

export const useGetH5PQuizContentsQuery = () => {
  return useQuery({
    queryKey: ['H5PQuizContents'],
    queryFn: getH5PQuizContents,
  });
};

const getH5PQuizContentById = (id: ID) => {
  return wpAjaxInstance
    .post<
      ID,
      AxiosResponse<{
        output: string;
      }>
    >(endpoints.GET_H5P_QUIZ_CONTENT_BY_ID, {
      content_id: id,
    })
    .then((response) => response.data);
};

export const useGetH5PQuizContentByIdQuery = (id: ID, contentType: ContentType) => {
  return useQuery({
    queryKey: ['H5PQuizContent', id],
    queryFn: () => getH5PQuizContentById(id),
    enabled: !!id && contentType === 'tutor_h5p_quiz',
  });
};

const deleteQuiz = (quizId: ID) => {
  return authApiInstance.post<string, TutorMutationResponse<ID>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_quiz_delete',
    quiz_id: quizId,
  });
};

export const useDeleteQuizMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: deleteQuiz,
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
