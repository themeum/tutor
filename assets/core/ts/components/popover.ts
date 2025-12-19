import { type AlpineComponentMeta } from '@Core/ts/types';
import { isRTL } from '@TutorShared/config/constants';

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
  escapeHandler: null as ((e: KeyboardEvent) => void) | null,

  init() {
    this.actualPlacement = this.getActualPlacement();
    this.setupEventListeners();
  },

  destroy() {
    this.removeEventListeners();
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

    this.escapeHandler = (e: KeyboardEvent) => {
      this.handleEscapeKeydown(e);
    };

    window.addEventListener('scroll', this.scrollHandler, true);
    window.addEventListener('resize', this.resizeHandler);
    document.addEventListener('keydown', this.escapeHandler);
  },

  removeEventListeners() {
    if (this.scrollHandler) {
      window.removeEventListener('scroll', this.scrollHandler, true);
    }

    if (this.resizeHandler) {
      window.removeEventListener('resize', this.resizeHandler);
    }

    if (this.escapeHandler) {
      document.removeEventListener('keydown', this.escapeHandler);
      this.escapeHandler = null;
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

    const placement = this.resolvePlacement(this.actualPlacement, triggerRect, contentRect, viewport);
    const { top, left } = this.calculatePosition(triggerRect, contentRect, placement);

    // Apply positioning
    content.style.position = 'fixed';
    content.style.top = `${top}px`;
    content.style.left = `${left}px`;
    content.style.zIndex = '1060';

    // Update CSS classes for placement
    this.updatePlacementClasses(content, placement);
  },

  resolvePlacement(
    placement: string,
    triggerRect: DOMRect,
    contentRect: DOMRect,
    viewport: { width: number; height: number },
  ): string {
    const space = {
      top: triggerRect.top,
      bottom: viewport.height - triggerRect.bottom,
      left: triggerRect.left,
      right: viewport.width - triggerRect.right,
    };

    const needsVerticalFlip = {
      top: space.top < contentRect.height + this.offset && space.bottom > space.top,
      bottom: space.bottom < contentRect.height + this.offset && space.top > space.bottom,
    };

    const needsHorizontalFlip = {
      left: space.left < contentRect.width + this.offset && space.right > space.left,
      right: space.right < contentRect.width + this.offset && space.left > space.right,
    };

    if (placement.startsWith('top') && needsVerticalFlip.top) {
      return placement.replace('top', 'bottom');
    }

    if (placement.startsWith('bottom') && needsVerticalFlip.bottom) {
      return placement.replace('bottom', 'top');
    }

    if (placement === PLACEMENTS.LEFT && needsHorizontalFlip.left) {
      return PLACEMENTS.RIGHT;
    }

    if (placement === PLACEMENTS.RIGHT && needsHorizontalFlip.right) {
      return PLACEMENTS.LEFT;
    }

    return placement;
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
