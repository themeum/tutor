const CSS_VARIABLE_HEADER_OFFSET = '--tutor-site-header-offset';
const CSS_VARIABLE_HEADER_OVERLAY_OFFSET = '--tutor-site-header-overlay-offset';

const WP_ADMIN_BAR_ID = 'wpadminbar';

const SITE_SHELL_ROOT_SELECTOR = '[data-tutor-learning-site-shell], [data-tutor-dashboard-site-shell]';

/**
 * Returns the lowest visible bottom edge among the site header and admin bar.
 *
 * A normal-flow header contributes while it is visible, then naturally drops
 * out of the boundary as the document scrolls. Fixed and sticky headers keep
 * contributing because their bottom edge remains in the viewport.
 */
const getVisibleHeaderBoundary = (elements: HTMLElement[]): number => {
  return elements.reduce((offset, element) => {
    return Math.max(offset, element.getBoundingClientRect().bottom, 0);
  }, 0);
};

/**
 * A static (normal-flow) header only needs its computed position checked,
 * not its inline style, since positioning is almost always set via CSS.
 */
const isStaticPosition = (element: HTMLElement): boolean => {
  return window.getComputedStyle(element).position === 'static';
};

class LearningAreaSiteShellController {
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

    // A normal-flow (static) header naturally drops out of the visible
    // boundary once scrolled past — its contribution to updateOffset()
    // only matters on first paint, so skip the ongoing listeners/observer.
    if (this.themeHeader && isStaticPosition(this.themeHeader)) {
      return;
    }

    if (this.themeHeader && typeof ResizeObserver !== 'undefined') {
      this.resizeObserver = new ResizeObserver(() => this.scheduleOffsetUpdate());
      this.resizeObserver.observe(this.themeHeader);
    }

    window.addEventListener('resize', this.scheduleOffsetUpdate);
    window.addEventListener('scroll', this.scheduleOffsetUpdate, { passive: true });
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
      return header instanceof HTMLElement ? header : null;
    } catch {
      return null;
    }
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
    const elements = [this.themeHeader, adminBar].filter(
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
    const controller = new LearningAreaSiteShellController(root, selector);
    controller.start();
    return controller;
  });

  return () => {
    controllers.forEach((controller) => controller.destroy());
  };
};
