import type { CourseVideo, SubtitleTrack } from '@TutorShared/components/fields/FormVideoInput';

const subtitleFields = ['attachment_id', 'srclang', 'label', 'default'] as const;

/**
 * Flatten video meta into the form keys the PHP save path reads.
 *
 * Subtitle tracks expand to nested keys. Sending them as a JSON blob would be mangled
 * by sanitize_array/wp_unslash.
 */
export const videoMetaToFormEntries = (video?: CourseVideo | null, emptySource?: string) => {
  const entries: Record<`video[${string}]`, string> = {};

  if (!video) {
    return entries;
  }

  for (const [key, value] of Object.entries(video)) {
    if (key === 'subtitles') {
      (Array.isArray(value) ? (value as SubtitleTrack[]) : []).forEach((track, index) => {
        for (const field of subtitleFields) {
          const fieldValue = track[field];

          if (fieldValue !== undefined && fieldValue !== '') {
            entries[`video[subtitles][${index}][${field}]`] = String(fieldValue);
          }
        }
      });
      continue;
    }

    if (typeof value !== 'string') {
      continue;
    }

    if (key === 'source' && !value && emptySource !== undefined) {
      entries[`video[${key}]`] = emptySource;
    } else if (key === 'poster_url' && !video.poster) {
      entries[`video[${key}]`] = '';
    } else {
      entries[`video[${key}]`] = value;
    }
  }

  return entries;
};
