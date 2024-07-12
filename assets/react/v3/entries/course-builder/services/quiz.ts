import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import type { ID } from './curriculum';
import type {
  QuizFeedbackMode,
  QuizLayoutView,
  QuizQuestionsOrder,
  QuizTimeLimit,
} from '@CourseBuilderComponents/modals/QuizModal';
import { authApiInstance } from '@Utils/api';
import type { TutorMutationResponse } from './course';
import endpoints from '@Utils/endpoints';
import type { ErrorResponse } from '@Utils/form';
import { useToast } from '@Atoms/Toast';
import { __ } from '@wordpress/i18n';
import type { AxiosResponse } from 'axios';

export type QuizQuestionType =
  | 'true_false'
  | 'multiple_choice'
  | 'open_ended'
  | 'fill_in_the_blank'
  | 'short_answer'
  | 'matching'
  | 'image_answering'
  | 'ordering';

export interface QuizQuestionOption {
  answer_id: ID;
  belongs_question_id: ID;
  belongs_question_type: QuizQuestionType;
  answer_title: string;
  is_correct?: boolean;
  image_id?: ID;
  image_url?: string;
  answer_two_gap_match?: string;
  answer_view_format?: string;
  answer_order?: number;
}

// Define a base interface for common properties
interface BaseQuizQuestion {
  question_id: ID;
  question_title: string;
  question_description: string;
  randomizeQuestion: boolean;
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
  };
}

interface TrueFalseQuizQuestion extends BaseQuizQuestion {
  question_type: 'true_false';
  question_answers: QuizQuestionOption[];
}

export interface MultipleChoiceQuizQuestion extends BaseQuizQuestion {
  question_type: 'multiple_choice';
  multipleCorrectAnswer: boolean;
  question_answers: QuizQuestionOption[];
}

interface MatchingQuizQuestion extends BaseQuizQuestion {
  question_type: 'matching';
  imageMatching: boolean;
  question_answers: QuizQuestionOption[];
}

interface ImageAnsweringQuizQuestion extends BaseQuizQuestion {
  question_type: 'image_answering';
  question_answers: QuizQuestionOption[];
}

interface FillInTheBlanksQuizQuestion extends BaseQuizQuestion {
  question_type: 'fill_in_the_blank';
  question_answers: QuizQuestionOption[];
}

export interface OrderingQuizQuestion extends BaseQuizQuestion {
  question_type: 'ordering';
  question_answers: QuizQuestionOption[];
}

interface OtherQuizQuestion extends BaseQuizQuestion {
  question_type: Exclude<
    QuizQuestionType,
    'true_false' | 'multiple_choice' | 'matching' | 'image_answering' | 'fill_in_the_blanks' | 'ordering'
  >;
}

interface ImportQuizPayload {
  topic_id: ID;
  csv_file: File;
}

interface QuizPayload {
  quiz_id?: ID; // only for update
  topic_id: ID;
  quiz_title: string;
  quiz_description: string;

  'quiz_option[time_limit][time_value]': number;
  'quiz_option[time_limit][time_type]': QuizTimeLimit;
  'quiz_option[feedback_mode]': QuizFeedbackMode;
  'quiz_option[attempts_allowed]': number;
  'quiz_option[passing_grade]': number;
  'quiz_option[max_questions_for_answer]': number;
  'quiz_option[question_layout_view]': QuizLayoutView;
  'quiz_option[questions_order]': QuizQuestionsOrder;
  'quiz_option[short_answer_characters_limit]': number;
  'quiz_option[open_ended_answer_characters_limit]': number;
  'quiz_option[hide_quiz_time_display]'?: 1 | 0;
  'quiz_option[pass_is_required]'?: 1 | 0; // when => content_drip enabled + drip settings sequential + retry mode
}

interface QuizDetailsResponse {
  post_title: string;
  post_content: string;
  quiz_option: {
    time_limit: {
      time_value: number;
      time_type: QuizTimeLimit;
    };
    feedback_mode: QuizFeedbackMode;
    attempts_allowed: number;
    passing_grade: number;
    max_questions_for_answer: number;
    question_layout_view: QuizLayoutView;
    questions_order: QuizQuestionsOrder;
    short_answer_characters_limit: number;
    open_ended_answer_characters_limit: number;
  };
  questions: QuizQuestion[];
}

export type QuizQuestion =
  | TrueFalseQuizQuestion
  | MultipleChoiceQuizQuestion
  | MatchingQuizQuestion
  | ImageAnsweringQuizQuestion
  | FillInTheBlanksQuizQuestion
  | OrderingQuizQuestion
  | OtherQuizQuestion;

// const mockQuizQuestions: QuizQuestion[] = [
//   {
//     question_id: '3',
//     question_type: 'multiple-choice',
//     question_title: 'Literally I am multiple choice don’t you see?',
//     question_description: 'This is a multiple choice question',
//     options: [
//       { ID: '1', title: 'Option 1', isCorrect: true },
//       { ID: '2', title: 'Option 2', isCorrect: false },
//       { ID: '3', title: 'Option 3', isCorrect: false },
//       { ID: '4', title: 'Option 4', isCorrect: false },
//     ],
//     answerRequired: false,
//     question_mark: 1,
//     randomizeQuestion: false,
//     showQuestionMark: false,
//     multipleCorrectAnswer: false,
//     answer_explanation: 'This is the answer explanation',
//   },
//   {
//     question_id: '1',
//     question_type: 'true-false',
//     question_title: 'Trust me I am True / False',
//     question_description: 'This is a true false question',
//     options: [
//       { ID: '1', title: 'True', isCorrect: true },
//       { ID: '2', title: 'False', isCorrect: false },
//     ],
//     answerRequired: false,
//     question_mark: 1,
//     randomizeQuestion: false,
//     showQuestionMark: true,
//     answer_explanation: '',
//   },
//   {
//     question_id: '4',
//     question_type: 'open-ended',
//     question_title: 'Write an essay dude, I am open endeeed!',
//     question_description: 'This is an open ended question',
//     answerRequired: false,
//     question_mark: 1,
//     randomizeQuestion: false,
//     showQuestionMark: true,
//     answer_explanation: 'This is the answer explanation',
//   },
//   {
//     question_id: '5',
//     question_type: 'fill-in-the-blanks',
//     question_title: 'Nature never keep spaces empty, fill in the Blanks!',
//     question_description: 'This is a fill in the blanks question',
//     answerRequired: false,
//     question_mark: 1,
//     randomizeQuestion: false,
//     showQuestionMark: true,
//     options: [{ ID: '1', title: 'Fill the {dash} in time', fillinTheBlanksCorrectAnswer: ['gap'] }],
//     answer_explanation: 'This is the answer explanation',
//   },
//   {
//     question_id: '6',
//     question_type: 'short-answer',
//     question_title: 'Keep it short!',
//     question_description: 'This is a short answer question',
//     answerRequired: false,
//     question_mark: 1,
//     randomizeQuestion: false,
//     showQuestionMark: true,
//     answer_explanation: 'This is the answer explanation',
//   },
//   {
//     question_id: '7',
//     question_type: 'matching',
//     question_title: 'Matching matching matching',
//     question_description: 'This is a matching question',
//     answerRequired: false,
//     question_mark: 1,
//     randomizeQuestion: false,
//     showQuestionMark: true,
//     imageMatching: false,
//     options: [
//       { ID: '1', title: 'Option 1', matchedTitle: 'Matched Option 1', isCorrect: true },
//       { ID: '2', title: 'Option 2', matchedTitle: 'Matched Option 2', isCorrect: false },
//       { ID: '3', title: 'Option 3', matchedTitle: 'Matched Option 3', isCorrect: false },
//       { ID: '4', title: 'Option 4', matchedTitle: 'Matched Option 4', isCorrect: false },
//     ],
//     answer_explanation: 'This is the answer explanation',
//   },
//   {
//     question_id: '9',
//     question_type: 'image-answering',
//     question_title: 'Image answering is not that bad',
//     question_description: 'This is an image answering question',
//     answerRequired: false,
//     question_mark: 1,
//     randomizeQuestion: false,
//     showQuestionMark: true,
//     options: [
//       { ID: '1', title: 'Option 1', isCorrect: true },
//       { ID: '2', title: 'Option 2', isCorrect: false },
//       { ID: '3', title: 'Option 3', isCorrect: false },
//       { ID: '4', title: 'Option 4', isCorrect: false },
//     ],
//     answer_explanation: 'This is the answer explanation',
//   },
//   {
//     question_id: '10',
//     question_type: 'ordering',
//     question_title: 'Order is not chaos!',
//     question_description: 'This is an ordering question',
//     options: [
//       { ID: '1', title: 'Option 5', isCorrect: true },
//       { ID: '2', title: 'Option 6', isCorrect: false },
//       { ID: '3', title: 'Option 7', isCorrect: false },
//       { ID: '4', title: 'Option 8', isCorrect: false },
//     ],
//     answerRequired: false,
//     question_mark: 1,
//     randomizeQuestion: false,
//     showQuestionMark: true,
//     answer_explanation: 'This is the answer explanation',
//   },
// ];

// const getQuizQuestions = () => {
//   return Promise.resolve({ data: mockQuizQuestions });
// };

// export const useGetQuizQuestionsQuery = () => {
//   return useQuery({
//     queryKey: ['GetQuizQuestions'],
//     queryFn: () => getQuizQuestions().then((response) => response.data),
//   });
// };

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
      console.log(response);
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

      if (!response.success) {
        showToast({
          message: __('Something went wrong.', 'tutor'),
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

const saveQuiz = (payload: QuizPayload) => {
  return authApiInstance.post<QuizPayload, TutorMutationResponse>(endpoints.ADMIN_AJAX, {
    action: 'tutor_quiz_save',
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
    queryKey: ['GetQuizDetails', quizId],
    queryFn: () => getQuizDetails(quizId).then((response) => response.data),
  });
};
