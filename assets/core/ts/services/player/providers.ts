import { type PlayerProvider } from '@Core/ts/types/player';

export interface DetectedSource {
  provider: PlayerProvider;
  src: string;
  poster?: string;
}

const INVALID_HOST = 'example.invalid';

const parseUrl = (url: string): URL | null => {
  try {
    return new URL(url, `https://${INVALID_HOST}`);
  } catch {
    return null;
  }
};

const YT_ID_REGEX = /^[\w-]{1,64}$/;
const VIMEO_ID_REGEX = /^\d+$/;
const VIMEO_HASH_REGEX = /^[a-z0-9]+$/i;

export function youtubeVideoId(url: string): string | null {
  const parsed = parseUrl(url);
  if (!parsed) {
    return null;
  }

  const host = parsed.hostname.replace(/^www\./, '');

  let id: string | null = null;
  if (host === 'youtu.be') {
    id = parsed.pathname.split('/').filter(Boolean)[0] || null;
  } else if (host === 'youtube.com' || host === 'm.youtube.com' || host === 'youtube-nocookie.com') {
    if (parsed.pathname === '/watch') {
      id = parsed.searchParams.get('v');
    } else {
      const match = parsed.pathname.match(/^\/(?:embed|v|shorts|live)\/([^/?#]+)/);
      id = match ? match[1] : null;
    }
  }

  return id && YT_ID_REGEX.test(id) ? id : null;
}

export function vimeoVideoId(url: string): { id: string; hash?: string } | null {
  const parsed = parseUrl(url);
  if (!parsed) {
    return null;
  }

  const host = parsed.hostname.replace(/^www\./, '');
  if (host !== 'vimeo.com' && host !== 'player.vimeo.com') {
    return null;
  }

  const parts = parsed.pathname.split('/').filter(Boolean);

  if (host === 'player.vimeo.com') {
    const index = parts.indexOf('video');
    const id = index >= 0 ? parts[index + 1] : parts[0];
    const hash = parsed.searchParams.get('h') ?? (index >= 0 ? parts[index + 2] : parts[1]);
    if (!id || !VIMEO_ID_REGEX.test(id)) {
      return null;
    }
    return hash && VIMEO_HASH_REGEX.test(hash) ? { id, hash } : { id };
  }

  const videoIdx = parts.indexOf('video');
  if (videoIdx >= 0 && parts[videoIdx + 1] && VIMEO_ID_REGEX.test(parts[videoIdx + 1])) {
    const id = parts[videoIdx + 1];
    const next = parts[videoIdx + 2];
    const hash = next && VIMEO_HASH_REGEX.test(next) ? next : undefined;
    return hash ? { id, hash } : { id };
  }

  for (let i = parts.length - 1; i >= 0; i--) {
    if (VIMEO_ID_REGEX.test(parts[i])) {
      const id = parts[i];
      const next = parts[i + 1];
      const hash = next && VIMEO_HASH_REGEX.test(next) ? next : undefined;
      return hash ? { id, hash } : { id };
    }
  }

  return null;
}

export function providerFromUrl(url: string, currentHost?: string): PlayerProvider {
  if (youtubeVideoId(url)) {
    return 'youtube';
  }

  if (vimeoVideoId(url)) {
    return 'vimeo';
  }

  const parsed = parseUrl(url);
  const host = parsed?.hostname;
  if (currentHost && host && host !== currentHost && host !== INVALID_HOST) {
    return 'external';
  }

  return 'html5';
}

export function detectSource(root: Element): DetectedSource | null {
  const host = root.ownerDocument?.defaultView?.location?.hostname;

  const video = root.matches('video') ? root : root.querySelector('video');
  if (video) {
    const source = video.querySelector('source[src]');
    const rawSrc = video.getAttribute('src') || source?.getAttribute('src') || '';
    const src = rawSrc.trim();
    if (src) {
      let protocol: string | null = null;
      try {
        const baseUri = root.ownerDocument?.baseURI;
        const parsed = new URL(src, baseUri || 'https://example.invalid');
        protocol = parsed.protocol;
      } catch {
        protocol = null;
      }

      if (protocol === 'http:' || protocol === 'https:' || protocol === 'blob:') {
        return {
          provider: providerFromUrl(src, host),
          src,
          poster: video.getAttribute('poster') || undefined,
        };
      }
    }
  }

  const iframe = root.matches('iframe') ? root : root.querySelector('iframe');
  if (iframe) {
    const src = iframe.getAttribute('src') || '';
    if (youtubeVideoId(src)) {
      return { provider: 'youtube', src };
    }
    if (vimeoVideoId(src)) {
      return { provider: 'vimeo', src };
    }
  }

  return null;
}
