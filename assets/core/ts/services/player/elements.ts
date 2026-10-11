import { createPlayer } from '@videojs/html';
import { videoFeatures } from '@videojs/html/video';

import { type PlayerStore } from './Player';
import { type DetectedSource, vimeoVideoId, youtubeVideoId } from './providers';

export const VIDEO_PLAYER_TAG = 'tutor-video-player';
export const VIDEO_SKIN_TAG = 'video-skin';

export interface VideoPlayerElement extends HTMLElement {
  store: PlayerStore;
}

const { PlayerElement } = createPlayer({ features: videoFeatures });

export function defineVideoPlayer(tagName: string = VIDEO_PLAYER_TAG): void {
  if (!customElements.get(tagName)) {
    customElements.define(tagName, PlayerElement);
  }
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

  skin.appendChild(createMediaElement(source, document));
  host.appendChild(skin);

  return host;
}

export function isVideoPlayerElement(element: Element): element is VideoPlayerElement {
  return element.tagName.toLowerCase() === VIDEO_PLAYER_TAG;
}

export function getStore(element: VideoPlayerElement): PlayerStore | null {
  return element.store ?? null;
}
