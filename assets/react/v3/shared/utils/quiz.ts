import {
  QuizDataStatus,
  type QuizQuestion,
  type QuizQuestionOption,
  type QuizValidationErrorType,
} from '@TutorShared/utils/types';
import { __ } from '@wordpress/i18n';
import { normalizeLineEndings } from './util';

export const calculateQuizDataStatus = (dataStatus: QuizDataStatus, currentStatus: QuizDataStatus) => {
  if (currentStatus === dataStatus) {
    return null;
  }

  if (dataStatus === QuizDataStatus.NEW) {
    return QuizDataStatus.NEW;
  }

  if (
    (dataStatus === QuizDataStatus.UPDATE || dataStatus === QuizDataStatus.NO_CHANGE) &&
    currentStatus === QuizDataStatus.UPDATE
  ) {
    return QuizDataStatus.UPDATE;
  }

  return QuizDataStatus.NO_CHANGE;
};

export const validateQuizQuestion = (
  question: QuizQuestion,
):
  | {
      message: string;
      type: QuizValidationErrorType;
    }
  | true => {
  if (question) {
    const currentQuestionType = question.question_type;

    if (currentQuestionType === 'h5p') {
      return true;
    }

    const answers = question.question_answers || [];
    const isAllSaved = answers.every((answer) => answer.is_saved);
    const hasCorrectAnswer = answers.some((answer) => answer.is_correct === '1');

    if (answers.length === 0 && currentQuestionType !== 'open_ended' && currentQuestionType !== 'short_answer') {
      return {
        message: __('Please add an option.', __TUTOR_TEXT_DOMAIN__),
        type: 'add_option',
      };
    }

    if (!isAllSaved) {
      return {
        message: __('Please finish editing all newly created options.', __TUTOR_TEXT_DOMAIN__),
        type: 'save_option',
      };
    }

    if (['true_false', 'multiple_choice'].includes(currentQuestionType) && !hasCorrectAnswer) {
      return {
        message: __('Please select a correct answer.', __TUTOR_TEXT_DOMAIN__),
        type: 'correct_option',
      };
    }

    if (currentQuestionType === 'matching') {
      const isImageMatching = question.question_settings.is_image_matching;

      const everyOptionHasTitle = answers.every((answer) => answer.answer_title);

      if (!everyOptionHasTitle) {
        return {
          message: __('Please add titles to all options.', __TUTOR_TEXT_DOMAIN__),
          type: 'save_option',
        };
      }

      if (isImageMatching) {
        const everyOptionHasImage = answers.every((answer) => answer.image_url);
        if (!everyOptionHasImage) {
          return {
            message: __('Please add images to all options.', __TUTOR_TEXT_DOMAIN__),
            type: 'question',
          };
        }
      } else {
        const everyOptionHasMatch = answers.every((answer) => answer.answer_two_gap_match);
        if (!everyOptionHasMatch) {
          return {
            message: __('Please add matched text to all options.', __TUTOR_TEXT_DOMAIN__),
            type: 'save_option',
          };
        }
      }
    }
  }

  return true;
};

export const convertedQuestion = (question: Omit<QuizQuestion, '_data_status'>): QuizQuestion => {
  const calculateQuizDataStatus = (answer: QuizQuestionOption) => {
    if (answer.image_url) {
      return answer.answer_view_format === 'text_image' ? QuizDataStatus.NO_CHANGE : QuizDataStatus.UPDATE;
    }

    return answer.answer_view_format === 'text' ? QuizDataStatus.NO_CHANGE : QuizDataStatus.UPDATE;
  };

  if (question.question_settings) {
    question.question_settings.answer_required = !!Number(question.question_settings.answer_required);
    question.question_settings.show_question_mark = !!Number(question.question_settings.show_question_mark);
    question.question_settings.randomize_question = !!Number(question.question_settings.randomize_question);
  }
  question.question_answers = question.question_answers.map((answer) => ({
    ...answer,
    _data_status: calculateQuizDataStatus(answer),
    is_saved: true,
    answer_view_format: answer.image_url ? 'text_image' : 'text',
  }));
  question.question_description = normalizeLineEndings(question.question_description) || '';
  question.answer_explanation =
    question.answer_explanation === '<p><br data-mce-bogus="1"></p>'
      ? ''
      : normalizeLineEndings(question.answer_explanation) || '';

  switch (question.question_type) {
    case 'single_choice': {
      return {
        ...question,
        _data_status: QuizDataStatus.UPDATE,
        question_type: 'multiple_choice',
        question_answers: question.question_answers.map((answer) => ({
          ...answer,
          _data_status: QuizDataStatus.UPDATE,
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
        _data_status: question.question_settings.has_multiple_correct_answer
          ? QuizDataStatus.NO_CHANGE
          : QuizDataStatus.UPDATE,
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
        _data_status: question.question_settings.is_image_matching ? QuizDataStatus.NO_CHANGE : QuizDataStatus.UPDATE,
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
        _data_status: QuizDataStatus.UPDATE,
        question_type: 'matching',
        question_answers: question.question_answers.map((answer) => ({
          ...answer,
          _data_status: QuizDataStatus.UPDATE,
        })),
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
        _data_status: QuizDataStatus.NO_CHANGE,
      } as QuizQuestion;
  }
};
