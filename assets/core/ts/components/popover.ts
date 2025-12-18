import { type AlpineComponentMeta } from '@Core/ts/types';
import { isRTL } from '@TutorShared/config/constants';

const VIEWPORT_PADDING = 8;

const PLACEMENTS = {
  TOP: 'top',
  TOP_START: 'top-start',
  TOP_END: 'top-end',
  BOTTOM: 'bottom',
  BOTTOM_START: 'bottom-start',
  BOTTOM_END: 'bottom-end',
  LEFT: 'left',
  RIGHT: 'right',
} as const;

export interface PopoverProps {
  placement?: (typeof PLACEMENTS)[keyof typeof PLACEMENTS];
  offset?: number;
  onShow?: () => void;
  onHide?: () => void;
}

export const popover = (props: PopoverProps = {}) => ({
  open: false,
  placement: props.placement || PLACEMENTS.BOTTOM_START,
  offset: props.offset ?? 4,
  actualPlacement: '',
  $el: undefined as HTMLElement | undefined,
  $refs: {} as { trigger: HTMLElement; content: HTMLElement },
  $nextTick: undefined as ((callback: () => void) => void) | undefined,
  scrollHandler: null as (() => void) | null,
  resizeHandler: null as (() => void) | null,

  init() {
    this.actualPlacement = this.getActualPlacement();
    this.setupEventListeners();

    // Add global escape key listener
    document.addEventListener('keydown', (e) => this.handleEscapeKeydown(e));
  },

  destroy() {
    this.removeEventListeners();
    document.removeEventListener('keydown', (e) => this.handleEscapeKeydown(e));
  },

  setupEventListeners() {
    this.scrollHandler = () => {
      if (this.open) {
        this.updatePosition();
      }
    };

    this.resizeHandler = () => {
      if (this.open) {
        this.updatePosition();
      }
    };

    window.addEventListener('scroll', this.scrollHandler, true);
    window.addEventListener('resize', this.resizeHandler);
  },

  removeEventListeners() {
    if (this.scrollHandler) {
      window.removeEventListener('scroll', this.scrollHandler, true);
    }
    if (this.resizeHandler) {
      window.removeEventListener('resize', this.resizeHandler);
    }
  },

  getActualPlacement() {
    if (!isRTL) return this.placement;

    const rtlAdaptations: Record<string, string> = {
      [PLACEMENTS.LEFT]: PLACEMENTS.RIGHT,
      [PLACEMENTS.RIGHT]: PLACEMENTS.LEFT,
      [PLACEMENTS.TOP_START]: PLACEMENTS.TOP_END,
      [PLACEMENTS.TOP_END]: PLACEMENTS.TOP_START,
      [PLACEMENTS.BOTTOM_START]: PLACEMENTS.BOTTOM_END,
      [PLACEMENTS.BOTTOM_END]: PLACEMENTS.BOTTOM_START,
    };

    return rtlAdaptations[this.placement] || this.placement;
  },

  show(): void {
    const content = this.$refs.content;
    if (content) {
      content.style.visibility = 'hidden';
    }

    this.open = true;

    const afterShow = () => {
      this.updatePosition();
      if (content) {
        content.style.visibility = 'visible';
      }
      if (props.onShow) {
        props.onShow();
      }
    };

    if (this.$nextTick) {
      this.$nextTick(afterShow);
    } else {
      requestAnimationFrame(afterShow);
    }
  },

  hide(): void {
    this.open = false;
    const content = this.$refs.content;
    if (content) {
      content.style.visibility = 'hidden';
    }
    if (props.onHide) {
      props.onHide();
    }
  },

  toggle(): void {
    if (this.open) {
      this.hide();
    } else {
      this.show();
    }
  },

  handleClickOutside(): void {
    if (this.open) {
      this.hide();
    }
  },

  handleEscapeKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape' && this.open) {
      event.preventDefault();
      event.stopPropagation();
      this.hide();
    }
  },

  updatePosition(): void {
    const trigger = this.$refs.trigger;
    const content = this.$refs.content;

    if (!trigger || !content) return;

    const triggerRect = trigger.getBoundingClientRect();
    const contentRect = content.getBoundingClientRect();
    const viewport = {
      width: window.innerWidth,
      height: window.innerHeight,
    };

    let top = 0;
    let left = 0;
    let actualPlacement = this.actualPlacement;

    // Calculate initial position based on placement
    const positions = this.calculatePosition(triggerRect, contentRect, actualPlacement);
    top = positions.top;
    left = positions.left;

    // Collision detection and flip if necessary
    const collisions = this.detectCollisions(top, left, contentRect, viewport);

    if (collisions.top || collisions.bottom || collisions.left || collisions.right) {
      const flippedPlacement = this.getFlippedPlacement(actualPlacement, collisions);
      if (flippedPlacement !== actualPlacement) {
        const flippedPositions = this.calculatePosition(triggerRect, contentRect, flippedPlacement);
        const flippedCollisions = this.detectCollisions(
          flippedPositions.top,
          flippedPositions.left,
          contentRect,
          viewport,
        );

        // Use flipped position if it has fewer collisions
        if (this.countCollisions(flippedCollisions) < this.countCollisions(collisions)) {
          top = flippedPositions.top;
          left = flippedPositions.left;
          actualPlacement = flippedPlacement;
        }
      }
    }

    // Final boundary adjustments
    top = Math.max(VIEWPORT_PADDING, Math.min(top, viewport.height - contentRect.height - VIEWPORT_PADDING));
    left = Math.max(VIEWPORT_PADDING, Math.min(left, viewport.width - contentRect.width - VIEWPORT_PADDING));

    // Apply positioning
    content.style.position = 'fixed';
    content.style.top = `${top}px`;
    content.style.left = `${left}px`;
    content.style.zIndex = '1060';

    // Update CSS classes for placement
    this.updatePlacementClasses(content, actualPlacement);
  },

  calculatePosition(triggerRect: DOMRect, contentRect: DOMRect, placement: string) {
    let top = 0;
    let left = 0;

    switch (placement) {
      case PLACEMENTS.TOP:
        top = triggerRect.top - contentRect.height - this.offset;
        left = triggerRect.left + (triggerRect.width - contentRect.width) / 2;
        break;
      case PLACEMENTS.TOP_START:
        top = triggerRect.top - contentRect.height - this.offset;
        left = triggerRect.left;
        break;
      case PLACEMENTS.TOP_END:
        top = triggerRect.top - contentRect.height - this.offset;
        left = triggerRect.right - contentRect.width;
        break;
      case PLACEMENTS.BOTTOM:
        top = triggerRect.bottom + this.offset;
        left = triggerRect.left + (triggerRect.width - contentRect.width) / 2;
        break;
      case PLACEMENTS.BOTTOM_START:
        top = triggerRect.bottom + this.offset;
        left = triggerRect.left;
        break;
      case PLACEMENTS.BOTTOM_END:
        top = triggerRect.bottom + this.offset;
        left = triggerRect.right - contentRect.width;
        break;
      case PLACEMENTS.LEFT:
        top = triggerRect.top + (triggerRect.height - contentRect.height) / 2;
        left = triggerRect.left - contentRect.width - this.offset;
        break;
      case PLACEMENTS.RIGHT:
        top = triggerRect.top + (triggerRect.height - contentRect.height) / 2;
        left = triggerRect.right + this.offset;
        break;
    }

    return { top, left };
  },

  detectCollisions(top: number, left: number, contentRect: DOMRect, viewport: { width: number; height: number }) {
    return {
      top: top < VIEWPORT_PADDING,
      bottom: top + contentRect.height > viewport.height - VIEWPORT_PADDING,
      left: left < VIEWPORT_PADDING,
      right: left + contentRect.width > viewport.width - VIEWPORT_PADDING,
    };
  },

  countCollisions(collisions: { top: boolean; bottom: boolean; left: boolean; right: boolean }) {
    return Object.values(collisions).filter(Boolean).length;
  },

  getFlippedPlacement(placement: string, collisions: { top: boolean; bottom: boolean; left: boolean; right: boolean }) {
    const flips: Record<string, string> = {
      [PLACEMENTS.TOP]: PLACEMENTS.BOTTOM,
      [PLACEMENTS.BOTTOM]: PLACEMENTS.TOP,
      [PLACEMENTS.LEFT]: PLACEMENTS.RIGHT,
      [PLACEMENTS.RIGHT]: PLACEMENTS.LEFT,
      [PLACEMENTS.TOP_START]: PLACEMENTS.BOTTOM_START,
      [PLACEMENTS.TOP_END]: PLACEMENTS.BOTTOM_END,
      [PLACEMENTS.BOTTOM_START]: PLACEMENTS.TOP_START,
      [PLACEMENTS.BOTTOM_END]: PLACEMENTS.TOP_END,
    };

    if (collisions.top && placement.startsWith(PLACEMENTS.TOP)) {
      return flips[placement] || PLACEMENTS.BOTTOM;
    }
    if (collisions.bottom && placement.startsWith(PLACEMENTS.BOTTOM)) {
      return flips[placement] || PLACEMENTS.TOP;
    }
    if (collisions.left && placement === PLACEMENTS.LEFT) {
      return PLACEMENTS.RIGHT;
    }
    if (collisions.right && placement === PLACEMENTS.RIGHT) {
      return PLACEMENTS.LEFT;
    }

    return placement;
  },

  updatePlacementClasses(content: HTMLElement, placement: string) {
    // Remove all placement classes
    const placementClasses = ['tutor-popover-top', 'tutor-popover-bottom', 'tutor-popover-left', 'tutor-popover-right'];
    content.classList.remove(...placementClasses);

    // Add current placement class
    const basePlacement = placement.split('-')[0];
    content.classList.add(`tutor-popover-${basePlacement}`);
  },
});

export const popoverMeta: AlpineComponentMeta<PopoverProps> = {
  name: 'popover',
  component: popover,
};
