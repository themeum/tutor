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

export function youtubeVideoId(url: string): string | null {
  const parsed = parseUrl(url);
  if (!parsed) {
    return null;
  }

  const host = parsed.hostname.replace(/^www\./, '');

  if (host === 'youtu.be') {
    return parsed.pathname.split('/').filter(Boolean)[0] || null;
  }

  if (host !== 'youtube.com' && host !== 'm.youtube.com' && host !== 'youtube-nocookie.com') {
    return null;
  }

  if (parsed.pathname === '/watch') {
    return parsed.searchParams.get('v');
  }

  const match = parsed.pathname.match(/^\/(?:embed|v|shorts)\/([^/?#]+)/);
  return match ? match[1] : null;
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
    return id ? { id, hash } : null;
  }

  const index = parts[0] === 'video' ? 1 : 0;
  const id = parts[index];
  const hash = parts[index + 1];
  return id ? { id, hash } : null;
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
    const src = video.getAttribute('src') || source?.getAttribute('src') || '';
    if (src) {
      return {
        provider: providerFromUrl(src, host),
        src,
        poster: video.getAttribute('poster') || undefined,
      };
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
