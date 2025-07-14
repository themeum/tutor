import { __ } from '@wordpress/i18n';
import type { UseFormReturn } from 'react-hook-form';

import type { QuizValidationErrorType } from '@CourseBuilderContexts/QuizModalContext';
import type { QuizForm } from '@CourseBuilderServices/quiz';
import { type ID } from '@TutorShared/utils/types';

export const getCourseId = () => {
  const params = new URLSearchParams(window.location.search);
  const courseId = params.get('course_id');
  return Number(courseId);
};

export const validateQuizQuestion = (
  activeQuestionIndex: number,
  form: UseFormReturn<QuizForm>,
):
  | {
      message: string;
      type: QuizValidationErrorType;
    }
  | true => {
  if (activeQuestionIndex !== -1) {
    const currentQuestionType = form.watch(`questions.${activeQuestionIndex}.question_type`);

    if (currentQuestionType === 'h5p') {
      return true;
    }

    const answers =
      form.watch(`questions.${activeQuestionIndex}.question_answers` as 'questions.0.question_answers') || [];
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
      const isImageMatching = form.watch(
        `questions.${activeQuestionIndex}.question_settings.is_image_matching` as 'questions.0.question_settings.is_image_matching',
      );

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

export const getIdWithoutPrefix = (prefix: string, id: ID) => {
  return id.toString().replace(prefix, '');
};
