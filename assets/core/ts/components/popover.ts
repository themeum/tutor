import { type AlpineComponentMeta } from '@Core/types';
import { isRTL } from '@TutorShared/config/constants';

export interface PopoverProps {
  placement?: 'top' | 'bottom' | 'left' | 'right' | 'top-start' | 'top-end' | 'bottom-start' | 'bottom-end';
  offset?: number;
  onShow?: () => void;
  onHide?: () => void;
}

export const popover = (props: PopoverProps = {}) => ({
  open: false,
  placement: props.placement || 'bottom-start',
  offset: props.offset || 4,
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
      left: 'right',
      right: 'left',
      'top-start': 'top-end',
      'top-end': 'top-start',
      'bottom-start': 'bottom-end',
      'bottom-end': 'bottom-start',
    };

    return rtlAdaptations[this.placement] || this.placement;
  },

  show(): void {
    this.open = true;
    if (this.$nextTick) {
      this.$nextTick(() => {
        this.updatePosition();
        if (props.onShow) {
          props.onShow();
        }
      });
    } else {
      // Fallback for when $nextTick is not available
      setTimeout(() => {
        this.updatePosition();
        if (props.onShow) {
          props.onShow();
        }
      }, 0);
    }
  },

  hide(): void {
    this.open = false;
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
    const padding = 8;
    top = Math.max(padding, Math.min(top, viewport.height - contentRect.height - padding));
    left = Math.max(padding, Math.min(left, viewport.width - contentRect.width - padding));

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
      case 'top':
        top = triggerRect.top - contentRect.height - this.offset;
        left = triggerRect.left + (triggerRect.width - contentRect.width) / 2;
        break;
      case 'top-start':
        top = triggerRect.top - contentRect.height - this.offset;
        left = triggerRect.left;
        break;
      case 'top-end':
        top = triggerRect.top - contentRect.height - this.offset;
        left = triggerRect.right - contentRect.width;
        break;
      case 'bottom':
        top = triggerRect.bottom + this.offset;
        left = triggerRect.left + (triggerRect.width - contentRect.width) / 2;
        break;
      case 'bottom-start':
        top = triggerRect.bottom + this.offset;
        left = triggerRect.left;
        break;
      case 'bottom-end':
        top = triggerRect.bottom + this.offset;
        left = triggerRect.right - contentRect.width;
        break;
      case 'left':
        top = triggerRect.top + (triggerRect.height - contentRect.height) / 2;
        left = triggerRect.left - contentRect.width - this.offset;
        break;
      case 'right':
        top = triggerRect.top + (triggerRect.height - contentRect.height) / 2;
        left = triggerRect.right + this.offset;
        break;
    }

    return { top, left };
  },

  detectCollisions(top: number, left: number, contentRect: DOMRect, viewport: { width: number; height: number }) {
    const padding = 8;
    return {
      top: top < padding,
      bottom: top + contentRect.height > viewport.height - padding,
      left: left < padding,
      right: left + contentRect.width > viewport.width - padding,
    };
  },

  countCollisions(collisions: { top: boolean; bottom: boolean; left: boolean; right: boolean }) {
    return Object.values(collisions).filter(Boolean).length;
  },

  getFlippedPlacement(placement: string, collisions: { top: boolean; bottom: boolean; left: boolean; right: boolean }) {
    const flips: Record<string, string> = {
      top: 'bottom',
      bottom: 'top',
      left: 'right',
      right: 'left',
      'top-start': 'bottom-start',
      'top-end': 'bottom-end',
      'bottom-start': 'top-start',
      'bottom-end': 'top-end',
    };

    if (collisions.top && placement.startsWith('top')) {
      return flips[placement] || 'bottom';
    }
    if (collisions.bottom && placement.startsWith('bottom')) {
      return flips[placement] || 'top';
    }
    if (collisions.left && placement === 'left') {
      return 'right';
    }
    if (collisions.right && placement === 'right') {
      return 'left';
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
