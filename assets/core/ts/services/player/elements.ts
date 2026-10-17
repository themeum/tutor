import { createPlayer } from '@videojs/html';
import { videoFeatures } from '@videojs/html/video';

import { type PlayerProvider, type TutorPlayer } from '@Core/ts/types/player';

import { createTutorPlayer, type PlayerStore } from './Player';
import { type DetectedSource, providerFromUrl, vimeoVideoId, youtubeVideoId } from './providers';

export const VIDEO_PLAYER_TAG = 'tutor-video-player';
const VIDEO_SKIN_TAG = 'video-skin';

type DeferredMediaProvider = 'youtube' | 'vimeo';

// YouTube/Vimeo adapters drag in the embed SDKs (~120 KB raw). They are code-split and
// pulled only when a lesson actually uses that provider, keeping the HTML5/external path lean.
const DEFERRED_MEDIA_LOADERS: Record<DeferredMediaProvider, () => Promise<unknown>> = {
  youtube: () => import(/* webpackChunkName: "tutor-player-youtube" */ '@videojs/html/media/youtube-video'),
  vimeo: () => import(/* webpackChunkName: "tutor-player-vimeo" */ '@videojs/html/media/vimeo-video'),
};

const providerModulePromises = new Map<DeferredMediaProvider, Promise<unknown>>();

/** Resolves once the code-split adapter for a provider has been imported and defined. */
export function loadProviderModule(provider: DeferredMediaProvider): Promise<unknown> {
  let promise = providerModulePromises.get(provider);
  if (!promise) {
    promise = DEFERRED_MEDIA_LOADERS[provider]();
    providerModulePromises.set(provider, promise);
  }
  return promise;
}

function isDeferredProvider(provider: DetectedSource['provider']): provider is DeferredMediaProvider {
  return provider === 'youtube' || provider === 'vimeo';
}

export interface VideoPlayerElement extends HTMLElement {
  store: PlayerStore;
  /** Handle created by the element itself on connect; null once disposed. */
  player: TutorPlayer | null;
}

export interface PlayerElementHooks {
  onMount?: (player: TutorPlayer) => void;
  onDispose?: (player: TutorPlayer) => void;
}

let hooks: PlayerElementHooks = {};

/** Wired by the engine entry so the service can track self-booted elements. */
export function setPlayerElementHooks(next: PlayerElementHooks): void {
  hooks = next;
}

const { PlayerElement } = createPlayer({ features: videoFeatures });

// The vendor constructor type carries a `[key: string]` index signature from its config
// typing, which rejects any subclass member; narrow it to the shape we actually use.
interface PlayerElementBase extends HTMLElement {
  readonly store: PlayerStore;
  connectedCallback?(): void;
  destroyCallback?(): void;
}

const PlayerElementBaseClass = PlayerElement as unknown as new () => PlayerElementBase;

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

/**
 * Resolves the provider from the declared subtree: embed tags win, then the native
 * source URL decides between the site's own files and an external host.
 */
export function resolveProvider(element: Element): PlayerProvider {
  if (element.querySelector('youtube-video')) {
    return 'youtube';
  }

  if (element.querySelector('vimeo-video')) {
    return 'vimeo';
  }

  const media = element.querySelector('video');
  const src = media?.getAttribute('src') || media?.querySelector('source[src]')?.getAttribute('src') || '';
  const host = element.ownerDocument?.defaultView?.location?.hostname;

  return src ? providerFromUrl(src, host) : 'html5';
}

class TutorVideoPlayerElement extends PlayerElementBaseClass {
  player: TutorPlayer | null = null;

  connectedCallback(): void {
    // Tutor never shows the provider's own title; the lesson controller sets the
    // lesson title here for fullscreen via the same attribute.
    if (!this.hasAttribute(CONTENT_TITLE_ATTRIBUTE)) {
      this.setAttribute(CONTENT_TITLE_ATTRIBUTE, '');
    }

    // Stage-2 templates keep `controls` on the fallback media for no-JS; the skinned
    // player owns its controls.
    this.querySelector('video[controls]')?.removeAttribute('controls');

    // A declared skin's class registers after this element's (see boot.ts), so while the
    // engine script is still evaluating its shadow root is not attached yet; patch then.
    const skin = this.querySelector(VIDEO_SKIN_TAG) as HTMLElement | null;

    if (skin && !skin.shadowRoot) {
      queueMicrotask(() => applyUnsupportedControlsPatch(skin));
    } else if (skin) {
      applyUnsupportedControlsPatch(skin);
    }

    super.connectedCallback?.();

    if (this.player) {
      return;
    }

    const provider = resolveProvider(this);

    if (isDeferredProvider(provider)) {
      // Import after the player element is defined and connected: the adapter's media
      // element registers itself with this element's media context on upgrade.
      void loadProviderModule(provider);
    }

    const player = createTutorPlayer({
      element: this,
      store: this.store,
      provider,
      onDispose: () => this.#releasePlayer(),
    });

    this.player = player;
    hooks.onMount?.(player);
  }

  // The vendor base destroys itself two frames after removal (DestroyMixin), which also
  // releases the store; keep the service from holding on to the dead handle.
  destroyCallback(): void {
    super.destroyCallback?.();
    this.#releasePlayer();
  }

  #releasePlayer(): void {
    const player = this.player;
    this.player = null;

    if (player) {
      hooks.onDispose?.(player);
    }
  }
}

export function defineVideoPlayer(tagName: string = VIDEO_PLAYER_TAG): void {
  if (!customElements.get(tagName)) {
    customElements.define(tagName, TutorVideoPlayerElement);
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

export const CONTENT_TITLE_ATTRIBUTE = 'content-title';

export function createPlayerElement(source: DetectedSource, document: Document): VideoPlayerElement {
  const host = document.createElement(VIDEO_PLAYER_TAG) as VideoPlayerElement;
  const skin = document.createElement(VIDEO_SKIN_TAG);

  applyUnsupportedControlsPatch(skin);

  host.appendChild(skin);
  skin.appendChild(createMediaElement(source, document));

  return host;
}
