import { describe, expect, it } from 'vitest';

import { applyUnsupportedControlsPatch, createMediaElement, createPlayerElement } from './elements';

describe('elements', () => {
  it('creates video element for html5 source', () => {
    const el = createMediaElement(
      { provider: 'html5', src: 'https://test.com/a.mp4', poster: 'https://test.com/p.jpg' },
      document,
    );
    expect(el.tagName.toLowerCase()).toBe('video');
    expect(el.getAttribute('src')).toBe('https://test.com/a.mp4');
    expect(el.getAttribute('poster')).toBe('https://test.com/p.jpg');
    expect(el.hasAttribute('playsinline')).toBe(true);
  });

  it('creates youtube-video element for youtube source', () => {
    const el = createMediaElement(
      { provider: 'youtube', src: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' },
      document,
    );
    expect(el.tagName.toLowerCase()).toBe('youtube-video');
    expect(el.getAttribute('src')).toBe('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
  });

  it('creates vimeo-video element for vimeo source', () => {
    const el = createMediaElement({ provider: 'vimeo', src: 'https://vimeo.com/123456789' }, document);
    expect(el.tagName.toLowerCase()).toBe('vimeo-video');
    expect(el.getAttribute('src')).toBe('https://vimeo.com/123456789');
  });

  it('patches shadow root with styles to hide unsupported controls', () => {
    const host = document.createElement('div');
    const shadow = host.attachShadow({ mode: 'open' });
    applyUnsupportedControlsPatch(host);

    const hasAdopted = shadow.adoptedStyleSheets?.some((s) => {
      const rules = Array.from(s.cssRules || [])
        .map((r) => r.cssText)
        .join(' ');
      return rules.includes('media-cast-button') || rules.includes('media-pip-button');
    });
    const style = shadow.querySelector('style');
    const hasStyle = style && style.textContent?.includes('media-cast-button');

    expect(Boolean(hasAdopted || hasStyle)).toBe(true);
  });

  it('creates player element tree with host and skin', () => {
    const player = createPlayerElement({ provider: 'html5', src: 'https://test.com/a.mp4' }, document);
    expect(player.tagName.toLowerCase()).toBe('tutor-video-player');
    const skin = player.querySelector('video-skin');
    expect(skin).not.toBeNull();
    const video = skin?.querySelector('video');
    expect(video).not.toBeNull();
    expect(video?.getAttribute('src')).toBe('https://test.com/a.mp4');
  });
});
