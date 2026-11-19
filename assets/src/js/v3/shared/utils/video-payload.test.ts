import { describe, expect, it } from 'vitest';

import type { CourseVideo } from '@TutorShared/components/fields/FormVideoInput';

import { videoMetaToFormEntries } from './video-payload';

const baseVideo: CourseVideo = {
  source: 'html5',
  source_video_id: '12',
  poster: '34',
  poster_url: 'https://example.com/poster.jpg',
  source_html5: 'https://example.com/video.mp4',
  source_external_url: '',
  source_shortcode: '',
  source_youtube: '',
  source_vimeo: '',
  source_embedded: '',
};

describe('videoMetaToFormEntries', () => {
  it('flattens plain video fields and drops empty poster_url', () => {
    const entries = videoMetaToFormEntries({ ...baseVideo, poster: '', poster_url: 'https://example.com/1.jpg' });

    expect(entries['video[source]']).toBe('html5');
    expect(entries['video[poster_url]']).toBe('');
  });

  it('expands subtitle tracks into nested keys', () => {
    const entries = videoMetaToFormEntries({
      ...baseVideo,
      subtitles: [
        { attachment_id: 56, srclang: 'en', label: 'English', default: '1' },
        { attachment_id: 78, srclang: 'de', label: 'Deutsch', default: '0' },
      ],
    });

    expect(entries['video[subtitles][0][attachment_id]']).toBe('56');
    expect(entries['video[subtitles][0][srclang]']).toBe('en');
    expect(entries['video[subtitles][0][label]']).toBe('English');
    expect(entries['video[subtitles][0][default]']).toBe('1');
    expect(entries['video[subtitles][1][attachment_id]']).toBe('78');
    expect(entries['video[subtitles]']).toBeUndefined();
  });

  it('uses the empty source marker only when asked', () => {
    expect(videoMetaToFormEntries({ ...baseVideo, source: '' })['video[source]']).toBe('');
    expect(videoMetaToFormEntries({ ...baseVideo, source: '' }, '-1')['video[source]']).toBe('-1');
  });
});
