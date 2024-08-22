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
import type { ID } from './curriculum';

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
  | 'ordering';

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

// Define a base interface for common properties
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

interface QuizResponseWithStatus extends Omit<QuizDetailsResponse, 'questions'> {
  _data_status: QuizDataStatus;
  questions: QuizQuestionsForPayload[];
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
      prerequisites: [];
    };
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

interface QuizQuestionAnswerOrderingPayload {
  question_id: ID;
  sorted_answer_ids: ID[];
}

interface SaveQuizQuestionAnswerPayload {
  question_id: ID;
  answer_id?: ID; //only for update
  answer_title: string;
  image_id: ID;
  question_type?: QuizQuestionType;
  answer_view_format?: string;
  answer_two_gap_match?: string;
  matched_answer_title?: string; //only when question type matching or image matching
}

export const convertQuizResponseToFormData = (quiz: QuizDetailsResponse): QuizForm => {
  const convertedQuestion = (question: Omit<QuizQuestion, '_data_status'>): QuizQuestion => {
    if (question.question_settings) {
      question.question_settings.answer_required = !!Number(question.question_settings.answer_required);
      question.question_settings.show_question_mark = !!Number(question.question_settings.show_question_mark);
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
          _data_status: 'no_change',
          question_type: 'multiple_choice',

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
          _data_status: 'no_change',
          question_settings: {
            ...question.question_settings,
            has_multiple_correct_answer: !!Number(question.question_settings.has_multiple_correct_answer),
          },
        };
      }
      case 'matching': {
        return {
          ...question,
          _data_status: 'no_change',
          question_settings: {
            ...question.question_settings,
            is_image_matching: !!Number(question.question_settings.is_image_matching),
          },
        };
      }
      case 'image_matching': {
        return {
          ...question,
          _data_status: 'no_change',
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
      feedback_mode: quiz.quiz_option.feedback_mode || 'default',
      attempts_allowed: quiz.quiz_option.attempts_allowed || 0,
      pass_is_required: quiz.quiz_option.pass_is_required === '1',
      passing_grade: quiz.quiz_option.passing_grade || 0,
      max_questions_for_answer: quiz.quiz_option.max_questions_for_answer || 0,
      quiz_auto_start: quiz.quiz_option.quiz_auto_start === '1',
      question_layout_view: quiz.quiz_option.question_layout_view || '',
      questions_order: quiz.quiz_option.questions_order || 'rand',
      hide_question_number_overview: quiz.quiz_option.hide_question_number_overview === '1',
      short_answer_characters_limit: quiz.quiz_option.short_answer_characters_limit || 0,
      open_ended_answer_characters_limit: quiz.quiz_option.open_ended_answer_characters_limit || 0,
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
  quizId?: ID,
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
        content_drip_settings: formData.quiz_option.content_drip_settings,
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
      },
      questions: formData.questions.map((question) => {
        console.log('question', question);
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
                image_id: answer.image_id,
                image_url: answer.image_url,
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
    // quiz_title: formData.quiz_title,
    // quiz_description: formData.quiz_description,
    // 'quiz_option[time_limit][time_value]': formData.quiz_option.time_limit.time_value,
    // 'quiz_option[time_limit][time_type]': formData.quiz_option.time_limit.time_type,
    // 'quiz_option[feedback_mode]': formData.quiz_option.feedback_mode,
    // 'quiz_option[attempts_allowed]': formData.quiz_option.attempts_allowed,
    // 'quiz_option[passing_grade]': formData.quiz_option.passing_grade,
    // 'quiz_option[max_questions_for_answer]': formData.quiz_option.max_questions_for_answer,
    // 'quiz_option[question_layout_view]': formData.quiz_option.question_layout_view,
    // 'quiz_option[questions_order]': formData.quiz_option.questions_order,
    // 'quiz_option[short_answer_characters_limit]': formData.quiz_option.short_answer_characters_limit,
    // 'quiz_option[open_ended_answer_characters_limit]': formData.quiz_option.open_ended_answer_characters_limit,
    // 'quiz_option[hide_quiz_time_display]': formData.quiz_option.hide_quiz_time_display ? 1 : 0,
    // ...(isAddonEnabled(Addons.CONTENT_DRIP) &&
    //   contentDripType === 'unlock_sequentially' &&
    //   formData.quiz_option.feedback_mode === 'retry' && {
    //     'quiz_option[pass_is_required]': formData.quiz_option.pass_is_required ? 1 : 0,
    //   }),
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

const createQuizQuestion = (quizId: ID) => {
  return authApiInstance.post<ID, TutorMutationResponse<QuizQuestion>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_quiz_question_create',
    quiz_id: quizId,
  });
};

export const useCreateQuizQuestionMutation = () => {
  const { showToast } = useToast();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: createQuizQuestion,
    onSuccess: (response, payload) => {
      if (response.data) {
        showToast({
          message: __(response.message, 'tutor'),
          type: 'success',
        });

        queryClient.setQueryData(['Quiz', payload], (oldData: QuizDetailsResponse) => {
          const oldDataCopy = JSON.parse(JSON.stringify(oldData)) as QuizDetailsResponse;
          if (oldDataCopy) {
            return {
              ...oldDataCopy,
              questions: oldData.questions.length ? [...oldData.questions, response.data] : [response.data],
            };
          }
          return oldDataCopy;
        });
      }
    },
    onError: (error: ErrorResponse, quizId) => {
      showToast({
        message: error.response.data.message,
        type: 'danger',
      });

      queryClient.invalidateQueries({
        queryKey: ['Quiz', quizId],
      });
    },
  });
};

const updateQuizQuestion = (payload: QuizUpdateQuestionPayload) => {
  return authApiInstance.post<QuizUpdateQuestionPayload, TutorMutationResponse<number>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_quiz_question_update',
    ...payload,
  });
};

export const useUpdateQuizQuestionMutation = (quizId: ID) => {
  const { showToast } = useToast();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: updateQuizQuestion,
    onError: (error: ErrorResponse) => {
      showToast({
        message: error.response.data.message,
        type: 'danger',
      });

      queryClient.invalidateQueries({
        queryKey: ['Quiz', quizId],
      });
    },
  });
};

const quizQuestionSorting = (payload: { quiz_id: ID; sorted_question_ids: ID[] }) => {
  return authApiInstance.post<ID, TutorMutationResponse<number>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_quiz_question_sorting',
    ...payload,
  });
};

export const useQuizQuestionSortingMutation = () => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: quizQuestionSorting,
    onSuccess: (response) => {
      if (response.data) {
        showToast({
          message: __(response.message, 'tutor'),
          type: 'success',
        });
      }
    },
    onError: (error: ErrorResponse, payload) => {
      showToast({
        message: error.response.data.message,
        type: 'danger',
      });

      queryClient.invalidateQueries({
        queryKey: ['Quiz', payload.quiz_id],
      });
    },
  });
};

const deleteQuizQuestion = (questionId: ID) => {
  return authApiInstance.post<ID, TutorMutationResponse<number>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_quiz_question_delete',
    question_id: questionId,
  });
};

export const useDeleteQuizQuestionMutation = (quizId: ID) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: deleteQuizQuestion,
    onSuccess: (response) => {
      if (response.data) {
        showToast({
          message: __(response.message, 'tutor'),
          type: 'success',
        });

        queryClient.invalidateQueries({
          queryKey: ['Topic'],
        });

        queryClient.invalidateQueries({
          queryKey: ['Quiz', quizId],
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

const quizQuestionAnswerOrdering = (payload: QuizQuestionAnswerOrderingPayload) => {
  return authApiInstance.post<QuizQuestionAnswerOrderingPayload, TutorMutationResponse<number>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_quiz_question_answer_sorting',
    ...payload,
  });
};

export const useQuizQuestionAnswerOrderingMutation = (quizId: ID) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: quizQuestionAnswerOrdering,
    onSuccess: (response, payload) => {
      if (response.status_code === 200) {
        queryClient.setQueryData(['Quiz', quizId], (oldData: QuizDetailsResponse) => {
          const oldDataCopy = JSON.parse(JSON.stringify(oldData)) as QuizDetailsResponse;
          if (!oldDataCopy) {
            return;
          }

          return {
            ...oldDataCopy,
            questions: oldDataCopy.questions.map((question) => {
              if (String(question.question_id) !== String(payload.question_id)) {
                return question;
              }

              return {
                ...question,
                question_answers: payload.sorted_answer_ids.map((answerId, index) => {
                  const answer = question.question_answers.find((a) => String(a.answer_id) === String(answerId));
                  if (answer) {
                    return {
                      ...answer,
                      answer_order: index,
                    };
                  }
                  return answer;
                }),
              };
            }),
          };
        });
      }
    },
    onError: (error: ErrorResponse) => {
      showToast({
        message: error.response.data.message,
        type: 'danger',
      });

      queryClient.invalidateQueries({
        queryKey: ['Quiz', quizId],
      });
    },
  });
};

const saveQuizAnswer = (payload: SaveQuizQuestionAnswerPayload) => {
  return authApiInstance.post<SaveQuizQuestionAnswerPayload, TutorMutationResponse<number>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_quiz_question_answer_save',
    ...payload,
  });
};

export const useSaveQuizAnswerMutation = (quizId: ID) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: saveQuizAnswer,
    onSuccess: (response) => {
      if (response.status_code === 200 || response.status_code === 201) {
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

      queryClient.invalidateQueries({
        queryKey: ['Quiz', quizId],
      });
    },
  });
};

const deleteQuizQuestionAnswer = (answerId: ID) => {
  return authApiInstance.post<ID, TutorMutationResponse<number>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_quiz_question_answer_delete',
    answer_id: answerId,
  });
};

export const useDeleteQuizAnswerMutation = (quizId: ID) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: deleteQuizQuestionAnswer,
    onSuccess: (response) => {
      if (response.status_code === 200) {
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

      queryClient.invalidateQueries({
        queryKey: ['Quiz', quizId],
      });
    },
  });
};

const markAnswerAsCorrect = (payload: {
  answerId: ID;
  isCorrect: '1' | '0';
}) => {
  return authApiInstance.post<ID, TutorMutationResponse<number>>(endpoints.ADMIN_AJAX, {
    action: 'tutor_mark_answer_as_correct',
    answer_id: payload.answerId,
    is_correct: payload.isCorrect,
  });
};

export const useMarkAnswerAsCorrectMutation = (quizId: ID) => {
  const queryClient = useQueryClient();
  const { showToast } = useToast();

  return useMutation({
    mutationFn: markAnswerAsCorrect,
    onError: (error: ErrorResponse) => {
      showToast({
        message: error.response.data.message,
        type: 'danger',
      });

      queryClient.invalidateQueries({
        queryKey: ['Quiz', quizId],
      });
    },
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
