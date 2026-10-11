import { describe, expect, it } from 'vitest';

import { detectSource, providerFromUrl, vimeoVideoId, youtubeVideoId } from './providers';

describe('youtubeVideoId', () => {
  it.each([
    ['https://www.youtube.com/watch?v=abc123', 'abc123'],
    ['https://www.youtube.com/watch?x=1&v=abc123', 'abc123'],
    ['https://youtu.be/abc123?t=10', 'abc123'],
    ['https://www.youtube.com/embed/abc123?rel=0', 'abc123'],
    ['https://www.youtube-nocookie.com/embed/abc123', 'abc123'],
    ['https://www.youtube.com/shorts/abc123', 'abc123'],
    ['https://example.com/abc123', null],
    ['not a url', null],
  ])('%s -> %s', (url, expected) => {
    expect(youtubeVideoId(url)).toBe(expected);
  });
});

describe('vimeoVideoId', () => {
  it('parses plain, unlisted and player urls', () => {
    expect(vimeoVideoId('https://vimeo.com/12345')).toEqual({ id: '12345', hash: undefined });
    expect(vimeoVideoId('https://vimeo.com/12345/abcdef')).toEqual({ id: '12345', hash: 'abcdef' });
    expect(vimeoVideoId('https://player.vimeo.com/video/12345?h=abcdef')).toEqual({ id: '12345', hash: 'abcdef' });
    expect(vimeoVideoId('https://example.com/12345')).toBeNull();
  });
});

describe('providerFromUrl', () => {
  it('classifies known hosts and same/cross-origin files', () => {
    expect(providerFromUrl('https://www.youtube.com/watch?v=abc')).toBe('youtube');
    expect(providerFromUrl('https://vimeo.com/123')).toBe('vimeo');
    expect(providerFromUrl('https://cdn.example.com/a.mp4', 'lesson.test')).toBe('external');
    expect(providerFromUrl('https://lesson.test/wp-content/uploads/a.mp4', 'lesson.test')).toBe('html5');
    expect(providerFromUrl('/wp-content/uploads/a.mp4', 'lesson.test')).toBe('html5');
  });
});

describe('detectSource', () => {
  it('reads a native video element and its source', () => {
    const root = document.createElement('div');
    root.innerHTML =
      '<video class="tutorPlayer" poster="p.jpg"><source src="https://cdn.example.com/a.mp4" type="video/mp4"></video>';

    expect(detectSource(root.querySelector('video') as Element)).toEqual({
      provider: 'external',
      src: 'https://cdn.example.com/a.mp4',
      poster: 'p.jpg',
    });
  });

  it('reads a YouTube iframe embed', () => {
    const root = document.createElement('div');
    root.innerHTML =
      '<div class="plyr__video-embed tutorPlayer"><iframe src="https://www.youtube.com/embed/abc123"></iframe></div>';

    expect(detectSource(root.querySelector('.tutorPlayer') as Element)).toEqual({
      provider: 'youtube',
      src: 'https://www.youtube.com/embed/abc123',
    });
  });

  it('returns null when nothing playable is present', () => {
    const root = document.createElement('div');
    root.innerHTML = '<div class="tutorPlayer"></div>';

    expect(detectSource(root.querySelector('.tutorPlayer') as Element)).toBeNull();
  });
});
