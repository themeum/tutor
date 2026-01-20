// Tooltip Component

import { type AlpineComponentMeta } from '@Core/ts/types';
import { isRTL } from '@TutorShared/config/constants';

export interface TooltipProps {
  placement?: 'top' | 'bottom' | 'start' | 'end';
  trigger?: 'hover' | 'focus' | 'click';
  size?: 'small' | 'large';
  arrow?: 'start' | 'center' | 'end';
  offset?: number;
  delay?: {
    show?: number;
    hide?: number;
  };
}

export const tooltip = (props: TooltipProps = {}) => ({
  open: false,
  placement: props.placement || 'top',
  trigger: props.trigger || 'hover',
  size: props.size || 'small',
  arrow: props.arrow || 'start',
  offset: props.offset ?? 8,
  delay: props.delay || { show: 0, hide: 0 },
  actualPlacement: '',
  $nextTick: undefined as ((callback: () => void) => void) | undefined,
  $el: undefined as HTMLElement | undefined,
  $refs: {} as { trigger: HTMLElement; content: HTMLElement },

  scrollHandler: null as (() => void) | null,
  resizeHandler: null as (() => void) | null,

  init() {
    this.actualPlacement = this.getActualPlacement();
    this.setupAccessibility();
    this.setupTriggers();
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
    return this.placement;
  },

  setupTriggers() {
    // If trigger is not explicitly defined via x-ref, use $el as trigger
    const trigger = this.$refs.trigger || this.$el;
    if (!trigger) return;

    if (this.trigger === 'hover') {
      trigger.addEventListener('mouseenter', () => this.showWithDelay());
      trigger.addEventListener('mouseleave', () => this.hideWithDelay());
    } else if (this.trigger === 'focus') {
      trigger.addEventListener('focus', () => this.show());
      trigger.addEventListener('blur', () => this.hide());
    } else if (this.trigger === 'click') {
      trigger.addEventListener('click', () => this.toggle());
    }

    trigger.addEventListener('keydown', (e: KeyboardEvent) => {
      if (e.key === 'Escape' && this.open) {
        this.hide();
      }
    });
  },

  showWithDelay() {
    const delay = this.delay.show || 0;
    if (delay) {
      setTimeout(() => {
        if (!this.open) this.show();
      }, delay);
    } else {
      this.show();
    }
  },

  hideWithDelay() {
    const delay = this.delay.hide || 0;
    if (delay) {
      setTimeout(() => {
        if (this.open) this.hide();
      }, delay);
    } else {
      this.hide();
    }
  },

  show() {
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
    };

    if (this.$nextTick) {
      this.$nextTick(afterShow);
    } else {
      requestAnimationFrame(afterShow);
    }
  },

  hide() {
    this.open = false;
    const content = this.$refs.content;
    if (content) {
      content.style.visibility = 'hidden';
    }
  },

  toggle() {
    if (this.open) {
      this.hide();
    } else {
      this.show();
    }
  },

  setupAccessibility() {
    const trigger = this.$refs.trigger || this.$el;
    const content = this.$refs.content;

    if (trigger && content) {
      const tooltipId = `tooltip-${Date.now()}`;
      content.setAttribute('id', tooltipId);
      content.setAttribute('role', 'tooltip');
      trigger.setAttribute('aria-describedby', tooltipId);
    }
  },

  updatePosition() {
    const trigger = this.$refs.trigger || (this.$el as HTMLElement);
    const content = this.$refs.content as HTMLElement;

    if (!trigger || !content) return;

    // Ensure fixed position before measurement to avoid container constraints
    content.style.position = 'fixed';

    // Temporarily reset transforms/transitions for accurate measurement
    const originalTransform = content.style.transform;
    const originalTransition = content.style.transition;
    content.style.transform = 'none';
    content.style.transition = 'none';

    // Force layout if display is none (though it should be open now)
    const originalDisplay = content.style.display;
    if (window.getComputedStyle(content).display === 'none') {
      content.style.display = 'block';
    }

    const triggerRect = trigger.getBoundingClientRect();
    const contentWidth = content.offsetWidth;
    const contentHeight = content.offsetHeight;

    // Restore styles
    content.style.display = originalDisplay;
    content.style.transform = originalTransform;
    content.style.transition = originalTransition;

    // If measurement failed (0 size), retry on next frame
    if (contentWidth === 0 && contentHeight === 0 && this.open) {
      requestAnimationFrame(() => this.updatePosition());
      return;
    }

    const viewport = {
      width: window.innerWidth,
      height: window.innerHeight,
    };

    let top = 0;
    let left = 0;

    const placement = this.actualPlacement;

    switch (placement) {
      case 'top':
        top = triggerRect.top - contentHeight - this.offset;
        left = triggerRect.left + (triggerRect.width - contentWidth) / 2;
        break;
      case 'bottom':
        top = triggerRect.bottom + this.offset;
        left = triggerRect.left + (triggerRect.width - contentWidth) / 2;
        break;
      case 'start':
        top = triggerRect.top + (triggerRect.height - contentHeight) / 2;
        if (!isRTL) {
          left = triggerRect.left - contentWidth - this.offset;
        } else {
          left = triggerRect.right + this.offset;
        }
        break;
      case 'end':
        top = triggerRect.top + (triggerRect.height - contentHeight) / 2;
        if (!isRTL) {
          left = triggerRect.right + this.offset;
        } else {
          left = triggerRect.left - contentWidth - this.offset;
        }
        break;
    }

    // Keep within viewport
    top = Math.max(8, Math.min(top, viewport.height - contentHeight - 8));
    left = Math.max(8, Math.min(left, viewport.width - contentWidth - 8));

    content.style.position = 'fixed';
    content.style.top = `${top}px`;
    content.style.left = `${left}px`;
    content.style.zIndex = '1070';

    this.updatePlacementClasses(content, placement);
  },

  updatePlacementClasses(content: HTMLElement, placement: string) {
    const placementClasses = ['tutor-tooltip-top', 'tutor-tooltip-bottom', 'tutor-tooltip-start', 'tutor-tooltip-end'];
    const sizeClasses = ['tutor-tooltip-large'];
    const arrowClasses = ['tutor-tooltip-arrow-start', 'tutor-tooltip-arrow-center', 'tutor-tooltip-arrow-end'];

    content.classList.remove(...placementClasses, ...sizeClasses, ...arrowClasses);

    content.classList.add(`tutor-tooltip-${placement}`);

    if (this.size === 'large') {
      content.classList.add('tutor-tooltip-large');
    }

    content.classList.add(`tutor-tooltip-arrow-${this.arrow}`);
  },
});

export const tooltipMeta: AlpineComponentMeta<TooltipProps> = {
  name: 'tooltip',
  component: tooltip,
};
