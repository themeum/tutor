/**
 * Keep Plyr videos fully visible in mobile landscape fullscreen.
 *
 * Plyr sizes HTML5/YouTube wrappers to a fixed 16:9 box based on width.
 * On landscape phones that box is taller than the viewport, so overflow
 * clips the top. iOS Chrome also uses a position:fixed fallback that
 * does not reflow after rotation until fullscreen is toggled again.
 */

const FULLSCREEN_REFRESH_DELAYS_MS = [0, 50, 200, 450];
const boundPlayers = new WeakSet<Plyr>();

const parseRatio = (player: Plyr): [number, number] => {
  if (typeof player.ratio === 'string' && player.ratio.includes(':')) {
    const [width, height] = player.ratio.split(':').map(Number);

    if (width > 0 && height > 0) {
      return [width, height];
    }
  }

  const video = player.elements.container?.querySelector('video');

  if (video instanceof HTMLVideoElement && video.videoWidth > 0 && video.videoHeight > 0) {
    return [video.videoWidth, video.videoHeight];
  }

  return [16, 9];
};

const getViewportSize = (): { width: number; height: number; offsetTop: number; offsetLeft: number } => {
  const visualViewport = window.visualViewport;

  if (visualViewport) {
    return {
      width: visualViewport.width,
      height: visualViewport.height,
      offsetTop: visualViewport.offsetTop,
      offsetLeft: visualViewport.offsetLeft,
    };
  }

  return {
    width: Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0),
    height: Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0),
    offsetTop: 0,
    offsetLeft: 0,
  };
};

const isPlayerFullscreen = (player: Plyr): boolean => {
  const container = player.elements.container;

  return Boolean(
    player.fullscreen?.active || container?.classList.contains('plyr--fullscreen-fallback'),
  );
};

const resetWrapperLayout = (wrapper: HTMLElement): void => {
  wrapper.style.width = '';
  wrapper.style.height = '';
  wrapper.style.maxWidth = '';
  wrapper.style.maxHeight = '';
  wrapper.style.margin = '';
  wrapper.style.aspectRatio = '';
};

const resetFallbackPosition = (container: HTMLElement): void => {
  container.style.top = '';
  container.style.left = '';
  container.style.right = '';
  container.style.bottom = '';
  container.style.width = '';
  container.style.height = '';
};

const resetYoutubeCrop = (container: HTMLElement, isFullscreen: boolean): void => {
  const embedContainer = container.querySelector('.plyr__video-embed__container');

  if (!(embedContainer instanceof HTMLElement)) {
    return;
  }

  if (isFullscreen) {
    embedContainer.style.transform = 'none';
    embedContainer.style.paddingBottom = '0';
    embedContainer.style.height = '100%';
    return;
  }

  embedContainer.style.transform = '';
  embedContainer.style.paddingBottom = '';
  embedContainer.style.height = '';
};

const containWrapperInViewport = (player: Plyr): void => {
  const wrapper = player.elements.wrapper;

  if (!wrapper) {
    return;
  }

  const { width: viewportWidth, height: viewportHeight } = getViewportSize();

  if (viewportWidth <= 0 || viewportHeight <= 0) {
    return;
  }

  const [ratioWidth, ratioHeight] = parseRatio(player);
  const viewportIsWider = viewportWidth / viewportHeight > ratioWidth / ratioHeight;

  wrapper.style.aspectRatio = `${ratioWidth} / ${ratioHeight}`;
  wrapper.style.maxWidth = '100%';
  wrapper.style.maxHeight = '100%';
  wrapper.style.margin = '0 auto';
  wrapper.style.width = viewportIsWider ? 'auto' : '100%';
  wrapper.style.height = viewportIsWider ? '100%' : 'auto';
};

const syncFallbackViewport = (container: HTMLElement): void => {
  const { width, height, offsetTop, offsetLeft } = getViewportSize();

  container.style.top = `${offsetTop}px`;
  container.style.left = `${offsetLeft}px`;
  container.style.right = 'auto';
  container.style.bottom = 'auto';
  container.style.width = `${width}px`;
  container.style.height = `${height}px`;
};

const refreshPlayerFullscreenLayout = (player: Plyr): void => {
  const container = player.elements.container;
  const wrapper = player.elements.wrapper;

  if (!container) {
    return;
  }

  const isFullscreen = isPlayerFullscreen(player);

  if (!isFullscreen) {
    if (wrapper) {
      resetWrapperLayout(wrapper);
    }

    resetFallbackPosition(container);
    resetYoutubeCrop(container, false);
    return;
  }

  if (container.classList.contains('plyr--fullscreen-fallback')) {
    syncFallbackViewport(container);
  }

  // Force a layout pass so iOS/Android pick up the post-rotation viewport.
  void container.offsetHeight;

  resetYoutubeCrop(container, true);
  containWrapperInViewport(player);
};

/**
 * Bind orientation/viewport listeners so fullscreen video is letterboxed, not cropped.
 *
 * @since 4.0.8
 *
 * @param player Plyr instance.
 * @return Cleanup function.
 */
export const bindPlyrMobileFullscreenFix = (player: Plyr | null): (() => void) => {
  if (!player || boundPlayers.has(player)) {
    return () => undefined;
  }

  boundPlayers.add(player);

  const timeoutIds: number[] = [];

  const scheduleRefresh = () => {
    timeoutIds.forEach((id) => window.clearTimeout(id));
    timeoutIds.length = 0;

    FULLSCREEN_REFRESH_DELAYS_MS.forEach((delay) => {
      timeoutIds.push(
        window.setTimeout(() => {
          requestAnimationFrame(() => refreshPlayerFullscreenLayout(player));
        }, delay),
      );
    });
  };

  const onViewportChange = () => {
    if (isPlayerFullscreen(player)) {
      scheduleRefresh();
    }
  };

  player.on('enterfullscreen', scheduleRefresh);
  player.on('exitfullscreen', scheduleRefresh);
  window.addEventListener('orientationchange', onViewportChange);
  window.addEventListener('resize', onViewportChange);
  window.visualViewport?.addEventListener('resize', onViewportChange);
  document.addEventListener('fullscreenchange', onViewportChange);
  document.addEventListener('webkitfullscreenchange', onViewportChange);

  return () => {
    timeoutIds.forEach((id) => window.clearTimeout(id));
    boundPlayers.delete(player);
    player.off('enterfullscreen', scheduleRefresh);
    player.off('exitfullscreen', scheduleRefresh);
    window.removeEventListener('orientationchange', onViewportChange);
    window.removeEventListener('resize', onViewportChange);
    window.visualViewport?.removeEventListener('resize', onViewportChange);
    document.removeEventListener('fullscreenchange', onViewportChange);
    document.removeEventListener('webkitfullscreenchange', onViewportChange);
  };
};
