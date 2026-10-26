const CSS_VARIABLE_HEADER_OFFSET = '--tutor-site-header-offset';
const CSS_VARIABLE_HEADER_OVERLAY_OFFSET = '--tutor-site-header-overlay-offset';

const WP_ADMIN_BAR_ID = 'wpadminbar';
const MAX_HEADER_HEIGHT = 250;

const SITE_SHELL_ROOT_SELECTOR = '[data-tutor-learning-site-shell], [data-tutor-dashboard-site-shell]';

/**
 * Returns the lowest visible bottom edge among the site header and admin bar.
 */
const getVisibleHeaderBoundary = (elements: HTMLElement[]): number => {
  const boundary = elements.reduce((offset, element) => {
    const style = window.getComputedStyle(element);
    if (style.display === 'none' || style.visibility === 'hidden') {
      return offset;
    }

    const rect = element.getBoundingClientRect();
    if (rect.bottom <= 0 || rect.top > MAX_HEADER_HEIGHT) {
      return offset;
    }

    return Math.max(offset, rect.bottom);
  }, 0);

  return Math.min(Math.round(boundary), MAX_HEADER_HEIGHT);
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
    this.updateOffset();

    const isSticky = this.isStickyHeader(this.themeHeader);
    const hasAdminBar = Boolean(document.getElementById(WP_ADMIN_BAR_ID));

    if (isSticky && this.themeHeader && typeof ResizeObserver !== 'undefined') {
      this.resizeObserver = new ResizeObserver(() => this.scheduleOffsetUpdate());
      this.resizeObserver.observe(this.themeHeader);
    }

    window.addEventListener('resize', this.scheduleOffsetUpdate);

    if (isSticky || hasAdminBar) {
      window.addEventListener('scroll', this.scheduleOffsetUpdate, { passive: true });
    }
  }

  destroy(): void {
    this.resizeObserver?.disconnect();
    this.resizeObserver = null;

    window.removeEventListener('resize', this.scheduleOffsetUpdate);
    window.removeEventListener('scroll', this.scheduleOffsetUpdate);

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

  private isStickyHeader(header: HTMLElement | null): boolean {
    if (!header) {
      return false;
    }

    const position = window.getComputedStyle(header).position;
    if (position === 'fixed' || position === 'sticky' || position === 'absolute') {
      return true;
    }

    const className = (header.className || '').toString();
    return /sticky|transparent/i.test(className) || header.hasAttribute('data-sticky');
  }

  private getStickyHeader(header: HTMLElement | null): HTMLElement | null {
    if (!header) {
      return null;
    }

    const isHeaderBar = (el: HTMLElement): boolean => {
      const style = window.getComputedStyle(el);
      if (style.display === 'none' || style.visibility === 'hidden') {
        return false;
      }
      const rect = el.getBoundingClientRect();
      // Must have realistic header bar dimensions (reject full-screen mobile drawers/modals > 250px)
      return rect.height > 0 && rect.height <= MAX_HEADER_HEIGHT && rect.width >= window.innerWidth * 0.4;
    };

    if (this.isStickyHeader(header) && isHeaderBar(header)) {
      return header;
    }

    // Check if an inner row is fixed or sticky (e.g. Sydney, Astra)
    const children = header.querySelectorAll<HTMLElement>(
      'div, nav, header, [class*="header"], [class*="sticky"], [class*="navbar"], [class*="shfb"]',
    );
    for (let i = 0; i < children.length; i++) {
      const child = children[i];
      if (this.isStickyHeader(child) && isHeaderBar(child)) {
        return child;
      }
    }

    return null;
  }

  private scheduleOffsetUpdate = (): void => {
    if (this.animationFrame !== null) {
      return;
    }

    this.animationFrame = window.requestAnimationFrame(() => {
      this.animationFrame = null;
      this.updateOffset();
    });
  };

  private updateOffset(): void {
    const adminBar = document.getElementById(WP_ADMIN_BAR_ID);
    const stickyHeader = this.getStickyHeader(this.themeHeader);
    const elements = [stickyHeader, adminBar].filter(
      (element): element is HTMLElement => element instanceof HTMLElement,
    );
    const offset = getVisibleHeaderBoundary(elements);

    this.root.style.setProperty(CSS_VARIABLE_HEADER_OFFSET, `${offset}px`);

    // The active-quiz form is positioned below a normal-flow theme header,
    // but starts behind a fixed or stuck header. This value lets its
    // absolutely positioned question panel reserve only the part of the
    // visible boundary that overlays the Learning Area itself.
    const rootTop = this.root.getBoundingClientRect().top;
    const overlayOffset = Math.max(0, offset - Math.max(0, rootTop));
    this.root.style.setProperty(CSS_VARIABLE_HEADER_OVERLAY_OFFSET, `${overlayOffset}px`);
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
