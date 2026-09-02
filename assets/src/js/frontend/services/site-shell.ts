const CSS_VARIABLE_HEADER_OFFSET = '--tutor-site-header-offset';
const CSS_VARIABLE_HEADER_OVERLAY_OFFSET = '--tutor-site-header-overlay-offset';

const WP_ADMIN_BAR_ID = 'wpadminbar';
const MAX_HEADER_HEIGHT = 250;

const SITE_SHELL_ROOT_SELECTOR = '[data-tutor-learning-site-shell], [data-tutor-dashboard-site-shell]';

/**
 * Returns the effective height in pixels for a valid header element.
 */
const getHeaderHeight = (element: HTMLElement | null): number => {
  if (!element || !element.isConnected) {
    return 0;
  }

  const rect = element.getBoundingClientRect();
  const height = rect.height > 0 && rect.height <= MAX_HEADER_HEIGHT ? rect.height : element.offsetHeight;
  return Math.min(Math.max(0, Math.round(height)), MAX_HEADER_HEIGHT);
};

/**
 * Checks if an element represents a visible header bar with reasonable dimensions.
 */
const isValidHeaderBar = (element: HTMLElement | null): boolean => {
  if (!element || !element.isConnected) {
    return false;
  }

  const style = window.getComputedStyle(element);
  if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
    return false;
  }

  const rect = element.getBoundingClientRect();
  // Must be visible and have realistic header bar dimensions (reject full-screen mobile drawers/modals > 250px)
  if (rect.height <= 0 || rect.width <= 0 || rect.height > MAX_HEADER_HEIGHT) {
    return false;
  }

  // Must span a reasonable width across the viewport (reject small floating buttons/widgets)
  if (rect.width < window.innerWidth * 0.4) {
    return false;
  }

  return true;
};

/**
 * Checks if a header element is configured or behaving as a sticky / fixed header.
 */
const isStickyOrFixedHeader = (element: HTMLElement | null): boolean => {
  if (!isValidHeaderBar(element)) {
    return false;
  }

  const style = window.getComputedStyle(element!);
  const pos = style.position;
  if (pos === 'fixed' || pos === 'sticky') {
    return true;
  }

  const className = (element!.className || '').toString();
  return /sticky/i.test(className) || element!.hasAttribute('data-sticky');
};

/**
 * Checks if a header element is configured as a transparent / floating overlay header.
 */
const isOverlayHeader = (element: HTMLElement | null): boolean => {
  if (!isValidHeaderBar(element)) {
    return false;
  }

  const style = window.getComputedStyle(element!);
  const pos = style.position;
  if (pos === 'fixed' || pos === 'absolute') {
    return true;
  }

  const className = (element!.className || '').toString();
  return /transparent/i.test(className);
};

/**
 * Returns the bottom coordinate of the active WordPress admin bar if visible at the top.
 */
const getAdminBarBottom = (): number => {
  const adminBar = document.getElementById(WP_ADMIN_BAR_ID);
  if (!adminBar || !adminBar.isConnected) {
    return 0;
  }

  const style = window.getComputedStyle(adminBar);
  if (style.display === 'none' || style.visibility === 'hidden') {
    return 0;
  }

  const rect = adminBar.getBoundingClientRect();
  if (rect.bottom <= 0 || rect.height <= 0) {
    return 0;
  }

  // On mobile (<= 600px), admin bar is position: absolute (scrolls with page)
  const isFixed = style.position === 'fixed' || style.position === 'sticky';
  if (!isFixed && rect.top < 0) {
    return 0;
  }

  return Math.max(0, rect.bottom);
};

/**
 * Finds the active sticky/fixed header bar element (or inner row) within the theme header.
 */
const findStickyHeaderElement = (header: HTMLElement | null): HTMLElement | null => {
  if (!header || !header.isConnected) {
    return null;
  }

  if (isStickyOrFixedHeader(header)) {
    return header;
  }

  // Check top-level rows/bars inside the header (e.g. Sydney, Astra, TutorStarter)
  const candidates = header.querySelectorAll<HTMLElement>(
    'div, nav, header, [class*="header"], [class*="sticky"], [class*="navbar"], [class*="shfb"]',
  );
  for (let i = 0; i < candidates.length; i++) {
    const candidate = candidates[i];
    if (isStickyOrFixedHeader(candidate)) {
      return candidate;
    }
  }

  return null;
};

class SiteShellController {
  private animationFrame: number | null = null;
  private resizeObserver: ResizeObserver | null = null;
  private themeHeader: HTMLElement | null = null;

  constructor(
    private readonly root: HTMLElement,
    private readonly themeHeaderSelector: string,
  ) {}

  start(): void {
    this.themeHeader = this.findThemeHeader();
    this.updateOffset(true);

    if (this.themeHeader && typeof ResizeObserver !== 'undefined') {
      this.resizeObserver = new ResizeObserver(() => this.scheduleOffsetUpdate(true));
      this.resizeObserver.observe(this.themeHeader);
    }

    window.addEventListener('resize', this.scheduleResizeUpdate);
    window.addEventListener('scroll', this.scheduleScrollUpdate, { passive: true });
  }

  destroy(): void {
    this.resizeObserver?.disconnect();
    this.resizeObserver = null;

    window.removeEventListener('resize', this.scheduleResizeUpdate);
    window.removeEventListener('scroll', this.scheduleScrollUpdate);

    if (this.animationFrame !== null) {
      window.cancelAnimationFrame(this.animationFrame);
      this.animationFrame = null;
    }

    this.themeHeader = null;
  }

  private findThemeHeader(): HTMLElement | null {
    if (!this.themeHeaderSelector) {
      return null;
    }

    try {
      const header = document.querySelector(this.themeHeaderSelector);
      return header instanceof HTMLElement && !this.root.contains(header) ? header : null;
    } catch {
      return null;
    }
  }

  private scheduleResizeUpdate = (): void => {
    this.scheduleOffsetUpdate(true);
  };

  private scheduleScrollUpdate = (): void => {
    this.scheduleOffsetUpdate(false);
  };

  private scheduleOffsetUpdate = (isResize: boolean): void => {
    if (this.animationFrame !== null) {
      return;
    }

    this.animationFrame = window.requestAnimationFrame(() => {
      this.animationFrame = null;
      this.updateOffset(isResize);
    });
  };

  private updateOffset(isResize = false): void {
    const adminBarBottom = getAdminBarBottom();
    const stickyHeader = findStickyHeaderElement(this.themeHeader);
    const headerHeight = getHeaderHeight(stickyHeader);

    // For sticky/fixed theme headers, the offset below the header is adminBarBottom + headerHeight
    const totalHeaderOffset = stickyHeader ? adminBarBottom + headerHeight : adminBarBottom;
    const clampedOffset = Math.min(Math.max(0, Math.round(totalHeaderOffset)), MAX_HEADER_HEIGHT);
    this.root.style.setProperty(CSS_VARIABLE_HEADER_OFFSET, `${clampedOffset}px`);

    // Overlay offset sets initial reserve padding-top on load/resize without mutating during scroll
    if (isResize) {
      let overlayOffset = 0;
      if (this.themeHeader && isOverlayHeader(this.themeHeader)) {
        const themeHeaderHeight = getHeaderHeight(this.themeHeader);
        const rootTop = this.root.getBoundingClientRect().top;
        overlayOffset = Math.max(0, adminBarBottom + themeHeaderHeight - Math.max(0, rootTop));
      }

      const clampedOverlayOffset = Math.min(Math.max(0, Math.round(overlayOffset)), MAX_HEADER_HEIGHT);
      this.root.style.setProperty(CSS_VARIABLE_HEADER_OVERLAY_OFFSET, `${clampedOverlayOffset}px`);
    }
  }
}

/**
 * Initializes all Tutor site-shell roots found in the current DOM.
 *
 * Returns a disposer that tears down every controller it created (removes
 * listeners, disconnects observers, cancels pending rAFs). Callers are
 * responsible for invoking it when the roots are removed or re-initialized,
 * to avoid duplicate listeners/observers stacking up across repeated calls.
 */
export const initializeSiteShell = (): (() => void) => {
  const controllers = Array.from(document.querySelectorAll<HTMLElement>(SITE_SHELL_ROOT_SELECTOR)).map((root) => {
    const selector = root.dataset.tutorThemeHeaderSelector || '';
    const controller = new SiteShellController(root, selector);
    controller.start();
    return controller;
  });

  return () => {
    controllers.forEach((controller) => controller.destroy());
  };
};
