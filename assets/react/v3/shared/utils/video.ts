import { __ } from '@wordpress/i18n';

import { VideoRegex } from '@TutorShared/config/constants';

export async function getVimeoVideoDuration(videoUrl: string): Promise<number | null> {
  const regExp = /^.*(vimeo\.com\/)((channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/;
  const match = videoUrl.match(regExp);
  const videoId = match ? match[5] : null;
  const jsonUrl = `https://vimeo.com/api/v2/video/${videoId}.xml`;

  try {
    const response = await fetch(jsonUrl);
    if (!response.ok) {
      throw new Error(__('Failed to fetch the video data', __TUTOR_TEXT_DOMAIN__));
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
    // eslint-disable-next-line no-console
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
      throw new Error(`Failed to get Vimeo thumbnail. Error: ${error}`);
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
        video.crossOrigin = 'Anonymous';

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
                throw new Error(__('Failed to get canvas context', __TUTOR_TEXT_DOMAIN__));
              }

              ctx.drawImage(video, 0, 0);

              const thumbnail = canvas.toDataURL('image/png');
              cleanup();
              resolve(thumbnail);
            } catch (error) {
              cleanup();
              const errorMessage =
                error instanceof Error ? error.message : __('Unknown error occurred', __TUTOR_TEXT_DOMAIN__);
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
          reject(new Error(__('Thumbnail generation timed out', __TUTOR_TEXT_DOMAIN__)));
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

  throw new Error(__('Unsupported video source', __TUTOR_TEXT_DOMAIN__));
};
