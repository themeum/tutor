import { type QuizValidationErrorType } from '@CourseBuilderContexts/QuizModalContext';
import { QuizDataStatus, type QuizQuestion } from '@TutorShared/utils/types';
import { __ } from '@wordpress/i18n';

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
        message: __('Please add an option.', 'tutor'),
        type: 'add_option',
      };
    }

    if (!isAllSaved) {
      return {
        message: __('Please finish editing all newly created options.', 'tutor'),
        type: 'save_option',
      };
    }

    if (['true_false', 'multiple_choice'].includes(currentQuestionType) && !hasCorrectAnswer) {
      return {
        message: __('Please select a correct answer.', 'tutor'),
        type: 'correct_option',
      };
    }

    if (currentQuestionType === 'matching') {
      const isImageMatching = question.question_settings.is_image_matching;

      const everyOptionHasTitle = answers.every((answer) => answer.answer_title);

      if (!everyOptionHasTitle) {
        return {
          message: __('Please add titles to all options.', 'tutor'),
          type: 'save_option',
        };
      }

      if (isImageMatching) {
        const everyOptionHasImage = answers.every((answer) => answer.image_url);
        if (!everyOptionHasImage) {
          return {
            message: __('Please add images to all options.', 'tutor'),
            type: 'question',
          };
        }
      } else {
        const everyOptionHasMatch = answers.every((answer) => answer.answer_two_gap_match);
        if (!everyOptionHasMatch) {
          return {
            message: __('Please add matched text to all options.', 'tutor'),
            type: 'save_option',
          };
        }
      }
    }
  }

  return true;
};
