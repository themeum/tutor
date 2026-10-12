import { createPlayer } from '@videojs/html';
import { videoFeatures } from '@videojs/html/video';

import { type PlayerStore } from './Player';
import { type DetectedSource, vimeoVideoId, youtubeVideoId } from './providers';

export const VIDEO_PLAYER_TAG = 'tutor-video-player';
const VIDEO_SKIN_TAG = 'video-skin';

export interface VideoPlayerElement extends HTMLElement {
  store: PlayerStore;
}

const { PlayerElement } = createPlayer({ features: videoFeatures });

const HIDE_UNSUPPORTED_CONTROLS_CSS = `
  media-cast-button[data-availability="unavailable"],
  media-cast-button[data-availability="unsupported"],
  media-airplay-button[data-availability="unavailable"],
  media-airplay-button[data-availability="unsupported"],
  media-pip-button[data-availability="unavailable"],
  media-pip-button[data-availability="unsupported"],
  [data-availability="unavailable"],
  [data-availability="unsupported"],
  [data-hidden] {
    display: none !important;
  }
`;

export function applyUnsupportedControlsPatch(skin: HTMLElement): void {
  const root = skin.shadowRoot;
  if (!root) {
    return;
  }

  if ('adoptedStyleSheets' in root && Array.isArray(root.adoptedStyleSheets) && typeof CSSStyleSheet !== 'undefined') {
    try {
      const sheet = new CSSStyleSheet();
      sheet.replaceSync(HIDE_UNSUPPORTED_CONTROLS_CSS);
      root.adoptedStyleSheets = [...root.adoptedStyleSheets, sheet];
      return;
    } catch {
      // Fall through to <style> injection
    }
  }

  const doc = root.ownerDocument || skin.ownerDocument || document;
  const style = doc.createElement('style');
  style.textContent = HIDE_UNSUPPORTED_CONTROLS_CSS;
  root.appendChild(style);
}

export function patchVideoSkinStyles(): void {
  const VideoSkin = customElements.get(VIDEO_SKIN_TAG) as
    | (CustomElementConstructor & { styles?: CSSStyleSheet | string })
    | undefined;

  if (!VideoSkin) {
    return;
  }

  if (typeof CSSStyleSheet !== 'undefined' && VideoSkin.styles instanceof CSSStyleSheet) {
    try {
      VideoSkin.styles.insertRule(
        `media-cast-button[data-availability="unavailable"],
         media-cast-button[data-availability="unsupported"],
         media-airplay-button[data-availability="unavailable"],
         media-airplay-button[data-availability="unsupported"],
         media-pip-button[data-availability="unavailable"],
         media-pip-button[data-availability="unsupported"],
         [data-availability="unavailable"],
         [data-availability="unsupported"],
         [data-hidden] { display: none !important; }`,
        VideoSkin.styles.cssRules.length,
      );
    } catch {
      // Ignored
    }
  }
}

export function defineVideoPlayer(tagName: string = VIDEO_PLAYER_TAG): void {
  if (!customElements.get(tagName)) {
    customElements.define(tagName, PlayerElement);
  }
  patchVideoSkinStyles();
}

function buildMediaSource(source: DetectedSource): { tagName: string; src: string } {
  if (source.provider === 'youtube') {
    const id = youtubeVideoId(source.src) ?? source.src;
    return { tagName: 'youtube-video', src: `https://www.youtube.com/watch?v=${id}` };
  }

  if (source.provider === 'vimeo') {
    const parsed = vimeoVideoId(source.src) ?? { id: source.src };
    const src = parsed.hash ? `https://vimeo.com/${parsed.id}/${parsed.hash}` : `https://vimeo.com/${parsed.id}`;
    return { tagName: 'vimeo-video', src };
  }

  return { tagName: 'video', src: source.src };
}

export function createMediaElement(source: DetectedSource, document: Document): Element {
  const { tagName, src } = buildMediaSource(source);
  const element = document.createElement(tagName);

  element.setAttribute('src', src);

  if (source.poster && tagName === 'video') {
    element.setAttribute('poster', source.poster);
  }

  element.setAttribute('playsinline', '');

  return element;
}

export function createPlayerElement(source: DetectedSource, document: Document): VideoPlayerElement {
  const host = document.createElement(VIDEO_PLAYER_TAG) as VideoPlayerElement;
  const skin = document.createElement(VIDEO_SKIN_TAG);

  applyUnsupportedControlsPatch(skin);

  skin.appendChild(createMediaElement(source, document));
  host.appendChild(skin);

  return host;
}

export function getStore(element: VideoPlayerElement): PlayerStore | null {
  return element.store ?? null;
}
