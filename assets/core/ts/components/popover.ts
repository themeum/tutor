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
  LEFT_TOP: 'left-top',
  LEFT_BOTTOM: 'left-bottom',
  RIGHT: 'right',
  RIGHT_TOP: 'right-top',
  RIGHT_BOTTOM: 'right-bottom',
} as const;

export interface PopoverProps {
  placement?: (typeof PLACEMENTS)[keyof typeof PLACEMENTS];
  offset?: number;
  onShow?: () => void;
  onHide?: () => void;
}

interface PopoverDimensions {
  width: number;
  height: number;
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
      [PLACEMENTS.LEFT_TOP]: PLACEMENTS.RIGHT_TOP,
      [PLACEMENTS.LEFT_BOTTOM]: PLACEMENTS.RIGHT_BOTTOM,
      [PLACEMENTS.RIGHT]: PLACEMENTS.LEFT,
      [PLACEMENTS.RIGHT_TOP]: PLACEMENTS.LEFT_TOP,
      [PLACEMENTS.RIGHT_BOTTOM]: PLACEMENTS.LEFT_BOTTOM,
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

  isTriggerVisible(): boolean {
    const trigger = this.$refs.trigger;
    if (!trigger) return false;

    const rect = trigger.getBoundingClientRect();
    const viewportHeight = window.innerHeight;
    const viewportWidth = window.innerWidth;

    // Check if trigger is within viewport bounds
    const isWithinViewport =
      rect.bottom > 0 && rect.top < viewportHeight && rect.right > 0 && rect.left < viewportWidth;

    if (!isWithinViewport || rect.width === 0 || rect.height === 0) {
      return false;
    }

    // Check if trigger is obscured by elements like sticky headers
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;

    const elementAtPoint = document.elementFromPoint(centerX, centerY);

    if (elementAtPoint) {
      const content = this.$refs.content;
      const isTriggerOrChild = trigger === elementAtPoint || trigger.contains(elementAtPoint);
      const isContentOrChild = content && (content === elementAtPoint || content.contains(elementAtPoint));

      if (!isTriggerOrChild && !isContentOrChild) {
        return false;
      }
    }

    return true;
  },

  updatePosition(): void {
    const trigger = this.$refs.trigger;
    const content = this.$refs.content;

    if (!trigger || !content) return;

    if (this.open && !this.isTriggerVisible()) {
      this.hide();
      return;
    }

    const triggerRect = trigger.getBoundingClientRect();
    const contentDimensions = this.getContentDimensions(content);
    const viewport = {
      width: window.innerWidth,
      height: window.innerHeight,
    };

    const placement = this.resolvePlacement(this.actualPlacement, triggerRect, contentDimensions, viewport);
    const viewportPosition = this.calculatePosition(triggerRect, contentDimensions, placement);
    const { top, left } = this.convertViewportPositionToContentPosition(content, viewportPosition);

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
    contentDimensions: PopoverDimensions,
    viewport: { width: number; height: number },
  ): string {
    const space = {
      top: triggerRect.top,
      bottom: viewport.height - triggerRect.bottom,
      left: triggerRect.left,
      right: viewport.width - triggerRect.right,
    };

    const needsVerticalFlip = {
      top: space.top < contentDimensions.height + this.offset && space.bottom > space.top,
      bottom: space.bottom < contentDimensions.height + this.offset && space.top > space.bottom,
    };

    const needsHorizontalFlip = {
      left: space.left < contentDimensions.width + this.offset && space.right > space.left,
      right: space.right < contentDimensions.width + this.offset && space.left > space.right,
    };

    if (placement.startsWith('top') && needsVerticalFlip.top) {
      return placement.replace('top', 'bottom');
    }

    if (placement.startsWith('bottom') && needsVerticalFlip.bottom) {
      return placement.replace('bottom', 'top');
    }

    if (placement.startsWith('left') && needsHorizontalFlip.left) {
      return placement.replace('left', 'right');
    }

    if (placement.startsWith('right') && needsHorizontalFlip.right) {
      return placement.replace('right', 'left');
    }

    return placement;
  },

  calculatePosition(triggerRect: DOMRect, contentDimensions: PopoverDimensions, placement: string) {
    let top = 0;
    let left = 0;

    switch (placement) {
      case PLACEMENTS.TOP:
        top = triggerRect.top - contentDimensions.height - this.offset;
        left = triggerRect.left + (triggerRect.width - contentDimensions.width) / 2;
        break;
      case PLACEMENTS.TOP_START:
        top = triggerRect.top - contentDimensions.height - this.offset;
        left = triggerRect.left;
        break;
      case PLACEMENTS.TOP_END:
        top = triggerRect.top - contentDimensions.height - this.offset;
        left = triggerRect.right - contentDimensions.width;
        break;
      case PLACEMENTS.BOTTOM:
        top = triggerRect.bottom + this.offset;
        left = triggerRect.left + (triggerRect.width - contentDimensions.width) / 2;
        break;
      case PLACEMENTS.BOTTOM_START:
        top = triggerRect.bottom + this.offset;
        left = triggerRect.left;
        break;
      case PLACEMENTS.BOTTOM_END:
        top = triggerRect.bottom + this.offset;
        left = triggerRect.right - contentDimensions.width;
        break;
      case PLACEMENTS.LEFT:
        top = triggerRect.top + (triggerRect.height - contentDimensions.height) / 2;
        left = triggerRect.left - contentDimensions.width - this.offset;
        break;
      case PLACEMENTS.LEFT_TOP:
        top = triggerRect.top;
        left = triggerRect.left - contentDimensions.width - this.offset;
        break;
      case PLACEMENTS.LEFT_BOTTOM:
        top = triggerRect.bottom - contentDimensions.height;
        left = triggerRect.left - contentDimensions.width - this.offset;
        break;
      case PLACEMENTS.RIGHT:
        top = triggerRect.top + (triggerRect.height - contentDimensions.height) / 2;
        left = triggerRect.right + this.offset;
        break;
      case PLACEMENTS.RIGHT_TOP:
        top = triggerRect.top;
        left = triggerRect.right + this.offset;
        break;
      case PLACEMENTS.RIGHT_BOTTOM:
        top = triggerRect.bottom - contentDimensions.height;
        left = triggerRect.right + this.offset;
        break;
    }

    return { top, left };
  },

  getContentDimensions(content: HTMLElement): PopoverDimensions {
    const rect = content.getBoundingClientRect();

    return {
      width: content.offsetWidth || rect.width,
      height: content.offsetHeight || rect.height,
    };
  },

  convertViewportPositionToContentPosition(content: HTMLElement, position: { top: number; left: number }) {
    const containingBlock = this.getFixedContainingBlock(content);

    if (!containingBlock) {
      return position;
    }

    const containingBlockRect = containingBlock.getBoundingClientRect();
    const scaleX = containingBlock.offsetWidth ? containingBlockRect.width / containingBlock.offsetWidth || 1 : 1;
    const scaleY = containingBlock.offsetHeight ? containingBlockRect.height / containingBlock.offsetHeight || 1 : 1;

    return {
      top: (position.top - containingBlockRect.top) / scaleY - containingBlock.clientTop,
      left: (position.left - containingBlockRect.left) / scaleX - containingBlock.clientLeft,
    };
  },

  getFixedContainingBlock(element: HTMLElement) {
    let parent = element.parentElement;

    while (parent && parent !== document.documentElement) {
      if (this.createsFixedContainingBlock(parent)) {
        return parent;
      }

      parent = parent.parentElement;
    }

    return null;
  },

  createsFixedContainingBlock(element: HTMLElement) {
    const style = window.getComputedStyle(element);
    const willChangeProperties = style.willChange.split(',').map((property) => property.trim());
    const containProperties = style.contain.split(' ');
    const backdropFilter =
      style.getPropertyValue('backdrop-filter') || style.getPropertyValue('-webkit-backdrop-filter');
    const contentVisibility = style.getPropertyValue('content-visibility');
    const containerType = style.getPropertyValue('container-type');

    return (
      style.transform !== 'none' ||
      style.perspective !== 'none' ||
      style.filter !== 'none' ||
      (backdropFilter !== '' && backdropFilter !== 'none') ||
      contentVisibility === 'auto' ||
      (containerType !== '' && containerType !== 'normal') ||
      willChangeProperties.some((property) => ['transform', 'perspective', 'filter'].includes(property)) ||
      containProperties.some((property) => ['layout', 'paint', 'strict', 'content'].includes(property))
    );
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
