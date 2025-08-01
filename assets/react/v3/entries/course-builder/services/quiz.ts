import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import type { AxiosResponse } from 'axios';

import type {
  QuizFeedbackMode,
  QuizLayoutView,
  QuizQuestionsOrder,
  QuizTimeLimit,
} from '@CourseBuilderComponents/modals/QuizModal';
import { useToast } from '@TutorShared/atoms/Toast';

import type { ContentDripType } from '@CourseBuilderServices/course';
import { tutorConfig } from '@TutorShared/config/config';
import { Addons } from '@TutorShared/config/constants';
import { wpAjaxInstance } from '@TutorShared/utils/api';
import endpoints from '@TutorShared/utils/endpoints';
import type { ErrorResponse } from '@TutorShared/utils/form';
import { convertedQuestion } from '@TutorShared/utils/quiz';
import {
  QuizDataStatus,
  type ID,
  type QuizQuestion,
  type QuizQuestionOption,
  type QuizQuestionType,
  type TopicContentType,
  type TutorMutationResponse,
} from '@TutorShared/utils/types';
import { convertToErrorMessage, isAddonEnabled } from '@TutorShared/utils/util';

interface ImportQuizPayload {
  topic_id: ID;
  csv_file: File;
}

interface QuizQuestionsForPayload extends Omit<QuizQuestion, 'question_settings' | 'answer_explanation'> {
  answer_explanation?: string;
  question_settings: {
    question_type: QuizQuestionType;
    answer_required: '0' | '1';
    randomize_question: '0' | '1';
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
      unlock_date?: string;
      after_xdays_of_enroll?: number;
      prerequisites?: ID[] | string;
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
  'content_drip_settings[unlock_date]'?: string;
  'content_drip_settings[after_xdays_of_enroll]'?: number;
  'content_drip_settings[prerequisites]'?: ID[] | string;
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
  _data_status: QuizDataStatus;
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

export const convertQuizResponseToFormData = (quiz: QuizDetailsResponse, slotFields: string[]): QuizForm => {
  return {
    ID: quiz.ID,
    _data_status: QuizDataStatus.NO_CHANGE,
    quiz_title: quiz.post_title ?? '',
    quiz_description: quiz.post_content ?? '',
    quiz_option: {
      time_limit: {
        time_value: quiz.quiz_option.time_limit.time_value ?? 0,
        time_type: quiz.quiz_option.time_limit.time_type ?? 'minutes',
      },
      hide_quiz_time_display: quiz.quiz_option.hide_quiz_time_display === '1',
      feedback_mode: quiz.quiz_option.feedback_mode ?? 'retry',
      attempts_allowed: quiz.quiz_option.attempts_allowed ?? 10,
      pass_is_required: quiz.quiz_option.pass_is_required === '1',
      passing_grade: quiz.quiz_option.passing_grade ?? 80,
      max_questions_for_answer: quiz.quiz_option.max_questions_for_answer ?? 10,
      quiz_auto_start: quiz.quiz_option.quiz_auto_start === '1',
      question_layout_view: quiz.quiz_option.question_layout_view || 'single_question',
      questions_order: quiz.quiz_option.questions_order ?? 'rand',
      hide_question_number_overview: quiz.quiz_option.hide_question_number_overview === '1',
      short_answer_characters_limit: quiz.quiz_option.short_answer_characters_limit ?? 200,
      open_ended_answer_characters_limit: quiz.quiz_option.open_ended_answer_characters_limit ?? 500,
      content_drip_settings: quiz.quiz_option.content_drip_settings || {
        unlock_date: '',
        after_xdays_of_enroll: 0,
        prerequisites: [],
      },
    },
    questions: (quiz.questions || []).map((question) => convertedQuestion(question)),
    deleted_question_ids: [],
    deleted_answer_ids: [],
    ...Object.fromEntries(slotFields.map((key) => [key, quiz[key as keyof QuizDetailsResponse]])),
  };
};

export const convertQuizFormDataToPayload = (
  formData: QuizForm,
  topicId: ID,
  contentDripType: ContentDripType,
  courseId: ID,
  questionsSlotFields: string[],
  settingsSlotFields: string[],
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
        max_questions_for_answer:
          isAddonEnabled(Addons.H5P_INTEGRATION) &&
          formData.questions.every((question) => question.question_type === 'h5p')
            ? formData.questions.length
            : formData.quiz_option.max_questions_for_answer,
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
          content_drip_settings: {
            ...(contentDripType === 'unlock_by_date' && {
              unlock_date: formData.quiz_option.content_drip_settings.unlock_date,
            }),
            ...(contentDripType === 'specific_days' && {
              after_xdays_of_enroll: formData.quiz_option.content_drip_settings.after_xdays_of_enroll,
            }),
            ...(contentDripType === 'after_finishing_prerequisites' && {
              prerequisites: formData.quiz_option.content_drip_settings.prerequisites?.length
                ? formData.quiz_option.content_drip_settings.prerequisites
                : '',
            }),
          },
        }),
        ...(isAddonEnabled(Addons.H5P_INTEGRATION) &&
          formData.questions.every((question) => question.question_type === 'h5p') && {
            quiz_type: 'tutor_h5p_quiz',
          }),
      },
      questions: formData.questions.map((question) => {
        return {
          _data_status: question._data_status,
          question_id: (() => {
            if (question.is_cb_question) {
              return String(question.question_id).split('-')[0];
            }
            return question.question_id;
          })(),
          ...(question.is_cb_question && {
            is_cb_question: question.is_cb_question,
          }),
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
            randomize_question: question.question_settings.randomize_question ? '1' : '0',
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
          ...Object.fromEntries(questionsSlotFields.map((key) => [key, question[key as keyof QuizQuestion]])),
        };
      }),
    },
    deleted_question_ids: formData.deleted_question_ids,
    deleted_answer_ids: formData.deleted_answer_ids,
    ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
      contentDripType === 'unlock_by_date' && {
        'content_drip_settings[unlock_date]': formData.quiz_option.content_drip_settings.unlock_date,
      }),
    ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
      contentDripType === 'specific_days' && {
        'content_drip_settings[after_xdays_of_enroll]':
          formData.quiz_option.content_drip_settings.after_xdays_of_enroll,
      }),
    ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
      contentDripType === 'after_finishing_prerequisites' && {
        'content_drip_settings[prerequisites]': formData.quiz_option.content_drip_settings.prerequisites?.length
          ? formData.quiz_option.content_drip_settings.prerequisites
          : '',
      }),
    ...Object.fromEntries(settingsSlotFields.map((key) => [key, formData[key as keyof QuizForm]])),
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
  return wpAjaxInstance.post<
    string,
    {
      data: {
        message: string;
      };
      success: boolean;
    }
  >(endpoints.QUIZ_IMPORT_DATA, payload);
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
        message: convertToErrorMessage(error),
        type: 'danger',
      });
    },
  });
};

const exportQuiz = (quizId: ID) => {
  return wpAjaxInstance.post<
    string,
    {
      data: {
        title: string;
        output_quiz_data: unknown[][];
      };
      success: boolean;
    }
  >(endpoints.QUIZ_EXPORT_DATA, {
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
        message: convertToErrorMessage(error),
        type: 'danger',
      });
    },
  });
};

const saveQuiz = (payload: QuizPayload) => {
  return wpAjaxInstance.post<QuizPayload, TutorMutationResponse<QuizDetailsResponse>>(endpoints.SAVE_QUIZ, payload);
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
        message: convertToErrorMessage(error),
        type: 'danger',
      });
    },
  });
};

const getQuizDetails = (quizId: ID) => {
  return wpAjaxInstance.get<QuizDetailsResponse>(endpoints.GET_QUIZ_DETAILS, {
    params: { quiz_id: quizId },
  });
};

export const useGetQuizDetailsQuery = (quizId: ID) => {
  return useQuery({
    queryKey: ['Quiz', quizId],
    queryFn: () => getQuizDetails(quizId).then((response) => response.data),
    enabled: !!quizId,
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

export const useGetH5PQuizContentByIdQuery = (id: ID, contentType: TopicContentType) => {
  return useQuery({
    queryKey: ['H5PQuizContent', id],
    queryFn: () => getH5PQuizContentById(id),
    enabled: !!id && contentType === 'tutor_h5p_quiz',
  });
};

const deleteQuiz = (quizId: ID) => {
  return wpAjaxInstance.post<string, TutorMutationResponse<ID>>(endpoints.DELETE_QUIZ, {
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
        message: convertToErrorMessage(error),
        type: 'danger',
      });
    },
  });
};
