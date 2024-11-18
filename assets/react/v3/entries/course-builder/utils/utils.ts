import { __ } from '@wordpress/i18n';
import type { UseFormReturn } from 'react-hook-form';

import { tutorConfig } from '@Config/config';
import { type Addons, VideoRegex } from '@Config/constants';
import type { PostStatus } from '@CourseBuilderServices/course';
import type { QuizForm } from '@CourseBuilderServices/quiz';
import type { ID } from '../services/curriculum';

export const getCourseId = () => {
  const params = new URLSearchParams(window.location.search);
  const courseId = params.get('course_id');
  return Number(courseId);
};

type Addon = `${Addons}`;

export const isAddonEnabled = (addon: Addon) => {
  return !!tutorConfig.addons_data.find((item) => item.base_name === addon)?.is_enabled;
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

export const determinePostStatus = (postStatus: PostStatus, postVisibility: 'private' | 'password_protected') => {
  if (postStatus === 'trash') {
    return 'trash';
  }

  if (postVisibility === 'private') {
    return 'private';
  }

  if (postStatus === 'future') {
    return 'future';
  }

  if (postVisibility === 'password_protected' && postStatus !== 'draft') {
    return 'publish';
  }

  return postStatus;
};

export const convertToSlug = (value: string) => {
  return value
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-+|-+$/g, '');
};

export const getIdWithoutPrefix = (prefix: string, id: ID) => {
  return id.toString().replace(prefix, '');
};

/**
 * Generates a thumbnail from different video sources
 * @param {string} source - Video source type ('youtube', 'vimeo', 'external_url', 'html5')
 * @param {string} url - Video URL
 * @returns {Promise<string>} - Base64 encoded thumbnail image
 */
export const generateVideoThumbnail = async (
  source: 'youtube' | 'vimeo' | 'external_url' | 'html5',
  url: string,
): Promise<string> => {
  if (source === 'youtube') {
    const match = url.match(VideoRegex.YOUTUBE);
    const videoId = match && match[7].length === 11 ? match[7] : '';

    return `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
  }

  if (source === 'vimeo') {
    try {
      const vimeoId = url.split('/').pop();
      const response = await fetch(`https://vimeo.com/api/v2/video/${vimeoId}.json`);
      const data = await response.json();
      return data[0].thumbnail_large;
    } catch (error) {
      throw new Error('Failed to get Vimeo thumbnail');
    }
  }

  if (source === 'external_url' || source === 'html5') {
    return new Promise((resolve, reject) => {
      try {
        // Create video element
        const video = document.createElement('video');
        video.muted = true;
        video.style.cssText =
          'position: fixed; left: 0; top: 0; width: 1px; height: 1px; object-fit: contain; z-index: -1;';

        // Create canvas element
        const canvas = document.createElement('canvas');

        // Track loading states
        let isMetadataLoaded = false;
        let isDataLoaded = false;
        let isSuspended = false;
        let isSeeked = false;

        const cleanup = () => {
          video.src = '';
          video.remove();
          canvas.remove();
          clearTimeout(timeoutId);
        };

        const attemptSnapshot = () => {
          if (isMetadataLoaded && isDataLoaded && isSuspended && isSeeked) {
            try {
              canvas.height = video.videoHeight;
              canvas.width = video.videoWidth;

              const ctx = canvas.getContext('2d');
              if (!ctx) {
                throw new Error('Failed to get canvas context');
              }

              ctx.drawImage(video, 0, 0);

              const thumbnail = canvas.toDataURL('image/png');
              cleanup();
              resolve(thumbnail);
            } catch (error) {
              cleanup();
              const errorMessage = error instanceof Error ? error.message : 'Unknown error occurred';
              reject(new Error(`Thumbnail generation failed: ${errorMessage}`));
            }
          }
        };

        // Setup event listeners
        video.addEventListener('loadedmetadata', () => {
          isMetadataLoaded = true;
          if (!video.currentTime || video.currentTime < 2) {
            video.currentTime = 2; // Take snapshot at 2 seconds
          }
        });

        video.addEventListener('loadeddata', () => {
          isDataLoaded = true;
          attemptSnapshot();
        });

        video.addEventListener('suspend', () => {
          isSuspended = true;
          attemptSnapshot();
        });

        video.addEventListener('seeked', () => {
          isSeeked = true;
          attemptSnapshot();
        });

        video.addEventListener('error', (error) => {
          cleanup();
          reject(new Error(`Video loading failed: ${error.message}`));
        });

        // Set timeout
        // 30 seconds is a reasonable maximum time to wait for video metadata and frame capture
        const timeoutId = setTimeout(() => {
          cleanup();
          reject(new Error('Thumbnail generation timed out'));
        }, 30000);

        // Add elements to DOM
        document.body.appendChild(video);
        document.body.appendChild(canvas);

        // Start loading the video
        video.src = url;
      } catch (error) {
        const errorMessage = error instanceof Error ? error.message : 'Unknown error occurred';
        reject(new Error(`Thumbnail generation failed: ${errorMessage}`));
      }
    });
  }

  throw new Error('Unsupported video source');
};
