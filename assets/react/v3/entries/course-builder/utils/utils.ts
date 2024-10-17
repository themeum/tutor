import { __ } from '@wordpress/i18n';
import type { UseFormReturn } from 'react-hook-form';

import { tutorConfig } from '@Config/config';
import type { Addons } from '@Config/constants';
import type {} from '@CourseBuilderServices/course';
import type {} from '@CourseBuilderServices/curriculum';
import type { QuizForm } from '@CourseBuilderServices/quiz';

export const getCourseId = () => {
  const params = new URLSearchParams(window.location.search);
  const courseId = params.get('course_id');
  return Number(courseId);
};

type Addon = `${Addons}`;

export const isAddonEnabled = (addon: Addon) => {
  return !!tutorConfig.addons_data.find((item) => item.name === addon)?.is_enabled;
};

export async function getVimeoVideoDuration(videoUrl: string): Promise<number | null> {
  const regExp = /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/;
  const match = videoUrl.match(regExp);
  const videoId = match ? match[5] : null;
  const jsonUrl = `http${tutorConfig.is_ssl}://vimeo.com/api/v2/video/${videoId}.xml`;

  try {
    const response = await fetch(jsonUrl);
    if (!response.ok) {
      throw new Error('Failed to fetch the video data');
    }

    const textData = await response.text();

    const parser = new DOMParser();
    const xmlDoc = parser.parseFromString(textData, 'application/xml');

    const durationElement = xmlDoc.getElementsByTagName('duration')[0];
    if (!durationElement || !durationElement.textContent) {
      return null;
    }

    const duration = Number.parseInt(durationElement.textContent, 10);
    return duration; // in seconds
  } catch (error) {
    console.error('Error fetching video duration:', error);
    return null;
  }
}

export const getExternalVideoDuration = async (videoUrl: string): Promise<number | null> => {
  const video = document.createElement('video');
  video.src = videoUrl;
  video.preload = 'metadata';

  return new Promise((resolve) => {
    video.onloadedmetadata = () => {
      resolve(video.duration);
    };
  });
};

export const convertYouTubeDurationToSeconds = (duration: string) => {
  const matches = duration.match(/PT(\d+H)?(\d+M)?(\d+S)?/);

  if (!matches) {
    return 0;
  }

  const hours = matches[1] ? Number(matches[1].replace('H', '')) : 0;
  const minutes = matches[2] ? Number(matches[2].replace('M', '')) : 0;
  const seconds = matches[3] ? Number(matches[3].replace('S', '')) : 0;

  return hours * 3600 + minutes * 60 + seconds;
};

export const covertSecondsToHMS = (seconds: number) => {
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const sec = seconds % 60;
  return { hours, minutes, seconds: sec };
};

export const validateQuizQuestion = (
  activeQuestionIndex: number,
  form: UseFormReturn<QuizForm>,
):
  | {
      message: string;
      type: 'question' | 'quiz' | 'correct_option' | 'add_option' | 'save_option';
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
            type: 'save_option',
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

export const determinePostStatus = (
  postStatus: 'trash' | 'future' | 'draft',
  postVisibility: 'private' | 'password_protected',
) => {
  if (postStatus === 'trash') {
    return 'trash';
  }

  if (postVisibility === 'private') {
    return 'private';
  }

  if (postStatus === 'future') {
    return 'future';
  }

  if (postVisibility === 'password_protected' && postStatus !== 'draft' && postStatus !== 'future') {
    return 'publish';
  }

  return postStatus;
};
