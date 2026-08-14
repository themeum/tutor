const CSS_VARIABLE = '--tutor-site-header-offset';

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
    const adminBar = document.getElementById('wpadminbar');
    const elements = [this.themeHeader, adminBar].filter(
      (element): element is HTMLElement => element instanceof HTMLElement,
    );
    const offset = getVisibleHeaderBoundary(elements);

    this.root.style.setProperty(CSS_VARIABLE, `${offset}px`);

    // The active-quiz form is positioned below a normal-flow theme header,
    // but starts behind a fixed or stuck header. This value lets its
    // absolutely positioned question panel reserve only the part of the
    // visible boundary that overlays the Learning Area itself.
    const rootTop = this.root.getBoundingClientRect().top;
    const overlayOffset = Math.max(0, offset - Math.max(0, rootTop));
    this.root.style.setProperty('--tutor-site-header-overlay-offset', `${overlayOffset}px`);
  }
}

/**
 * Initializes all learning-area site-shell roots found in the current DOM.
 *
 * Returns a disposer that tears down every controller it created (removes
 * listeners, disconnects observers, cancels pending rAFs). Callers are
 * responsible for invoking it when the roots are removed or re-initialized,
 * to avoid duplicate listeners/observers stacking up across repeated calls.
 */
export const initializeSiteShell = (): (() => void) => {
  const controllers = Array.from(document.querySelectorAll<HTMLElement>('[data-tutor-learning-site-shell]')).map(
    (root) => {
      const selector = root.dataset.tutorThemeHeaderSelector || '';
      const controller = new LearningAreaSiteShellController(root, selector);
      controller.start();
      return controller;
    },
  );

  return () => {
    controllers.forEach((controller) => controller.destroy());
  };
};
