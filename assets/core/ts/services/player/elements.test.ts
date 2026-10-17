import { afterEach, describe, expect, it, vi } from 'vitest';

import {
  applyUnsupportedControlsPatch,
  createMediaElement,
  createPlayerElement,
  defineVideoPlayer,
  resolveProvider,
  setPlayerElementHooks,
  type VideoPlayerElement,
} from './elements';

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

describe('resolveProvider', () => {
  const container = (html: string): HTMLElement => {
    const host = document.createElement('div');
    host.innerHTML = html;
    return host;
  };

  it('reads embed providers from their tags', () => {
    expect(resolveProvider(container('<youtube-video src="https://www.youtube.com/watch?v=a"></youtube-video>'))).toBe(
      'youtube',
    );
    expect(resolveProvider(container('<vimeo-video src="https://vimeo.com/1"></vimeo-video>'))).toBe('vimeo');
  });

  it('classifies native video by source host', () => {
    expect(resolveProvider(container('<video src="https://cdn.other.test/a.mp4"></video>'))).toBe('external');
    expect(resolveProvider(container('<video src="/uploads/a.mp4"></video>'))).toBe('html5');
    expect(resolveProvider(container('<video><source src="/uploads/a.mp4"></video>'))).toBe('html5');
  });

  it('falls back to html5 without media', () => {
    expect(resolveProvider(container(''))).toBe('html5');
  });
});

describe('tutor-video-player element', () => {
  afterEach(() => {
    document.body.innerHTML = '';
    setPlayerElementHooks({});
  });

  it('boots a handle on connect and releases it on dispose', () => {
    defineVideoPlayer();
    const onMount = vi.fn();
    const onDispose = vi.fn();
    setPlayerElementHooks({ onMount, onDispose });

    const host = createPlayerElement({ provider: 'html5', src: 'https://test.com/a.mp4' }, document);
    document.body.appendChild(host);

    expect(host.player).not.toBeNull();
    expect(host.getAttribute('content-title')).toBe('');
    expect(onMount).toHaveBeenCalledWith(host.player);

    const player = host.player;
    player?.dispose();

    expect(host.player).toBeNull();
    expect(onDispose).toHaveBeenCalledWith(player);
  });

  it('removes the no-JS controls fallback on connect', () => {
    defineVideoPlayer();
    const host = createPlayerElement({ provider: 'html5', src: 'https://test.com/a.mp4' }, document);
    host.querySelector('video')?.setAttribute('controls', '');

    document.body.appendChild(host);

    expect(host.querySelector('video')?.hasAttribute('controls')).toBe(false);

    host.player?.dispose();
  });

  it('releases the handle when the vendor base destroys the element', () => {
    defineVideoPlayer();
    const onDispose = vi.fn();
    setPlayerElementHooks({ onDispose });

    const host = createPlayerElement({ provider: 'html5', src: 'https://test.com/a.mp4' }, document);
    document.body.appendChild(host);
    const player = host.player;

    (host as unknown as { destroy(): void }).destroy();

    expect(host.player).toBeNull();
    expect(onDispose).toHaveBeenCalledWith(player);
  });

  it('does not boot twice when the element reconnects', () => {
    defineVideoPlayer();
    const onMount = vi.fn();
    setPlayerElementHooks({ onMount });

    const host = createPlayerElement({ provider: 'html5', src: 'https://test.com/a.mp4' }, document);
    document.body.appendChild(host);
    const player = host.player;

    document.body.removeChild(host);
    document.body.appendChild(host);

    expect(host.player).toBe(player);
    expect(onMount).toHaveBeenCalledTimes(1);

    player?.dispose();
  });

  // Defines `video-skin`, so keep this test last in the file.
  it('patches a declared skin whose class registers after the host', async () => {
    defineVideoPlayer();

    const host = document.createElement('tutor-video-player') as unknown as VideoPlayerElement;
    host.innerHTML = '<video-skin></video-skin>';
    document.body.appendChild(host);

    const skin = host.querySelector('video-skin') as HTMLElement;
    expect(skin.shadowRoot).toBeNull();

    customElements.define(
      'video-skin',
      class extends HTMLElement {
        connectedCallback(): void {
          this.attachShadow({ mode: 'open' });
        }
      },
    );

    await Promise.resolve();

    const root = skin.shadowRoot;
    const patched = Boolean(root?.querySelector('style')) || (root?.adoptedStyleSheets?.length ?? 0) > 0;
    expect(patched).toBe(true);

    host.player?.dispose();
  });
});
