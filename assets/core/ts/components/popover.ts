// Popover Component
// Alpine.js popover with RTL-aware dynamic positioning and collision detection

import { type AlpinePopoverData, type PopoverConfig } from '../types/components';

export function createPopover(config: PopoverConfig = {}): AlpinePopoverData {
  return {
    open: false,
    placement: config.placement || 'top',
    trigger: config.trigger || 'click',
    $el: undefined as HTMLElement | undefined,

    init() {
      this.setupRTL();
      this.setupTriggers();
    },

    show(): void {
      this.open = true;
      this.updatePosition();
    },

    hide(): void {
      this.open = false;
    },

    toggle(): void {
      if (this.open) {
        this.hide();
      } else {
        this.show();
      }
    },

    handleClickOutside(): void {
      if (this.trigger !== 'manual' && this.open) {
        this.hide();
      }
    },

    handleMouseEnter(): void {
      if (this.trigger === 'hover') {
        const delay = config.delay?.show || 0;
        setTimeout(() => this.show(), delay);
      }
    },

    handleMouseLeave(): void {
      if (this.trigger === 'hover') {
        const delay = config.delay?.hide || 0;
        setTimeout(() => this.hide(), delay);
      }
    },

    handleFocus(): void {
      if (this.trigger === 'focus') {
        this.show();
      }
    },

    handleBlur(): void {
      if (this.trigger === 'focus') {
        this.hide();
      }
    },

    handleKeydown(event: KeyboardEvent): void {
      if (event.key === 'Escape' && this.open) {
        this.hide();
      }
    },

    setupRTL(): void {
      const isRTL = document.dir === 'rtl' || document.documentElement.dir === 'rtl';
      if (isRTL) {
        const rtlAdaptations: Record<string, string> = {
          left: 'right',
          right: 'left',
          'top-start': 'top-end',
          'top-end': 'top-start',
          'bottom-start': 'bottom-end',
          'bottom-end': 'bottom-start',
        };
        const newPlacement = rtlAdaptations[this.placement] || this.placement;
        this.placement = newPlacement as typeof this.placement;
      }
    },

    setupTriggers(): void {
      const trigger = this.$el?.querySelector('.tutor-popover__trigger');
      if (!trigger) return;

      switch (this.trigger) {
        case 'click':
          trigger.addEventListener('click', () => this.toggle());
          break;
        case 'hover':
          trigger.addEventListener('mouseenter', () => this.handleMouseEnter());
          trigger.addEventListener('mouseleave', () => this.handleMouseLeave());
          break;
        case 'focus':
          trigger.addEventListener('focus', () => this.handleFocus());
          trigger.addEventListener('blur', () => this.handleBlur());
          break;
      }
    },

    updatePosition(): void {
      // Position calculation logic
      const trigger = this.$el?.querySelector('.tutor-popover__trigger') as HTMLElement;
      const content = this.$el?.querySelector('.tutor-popover__content') as HTMLElement;

      if (!trigger || !content) return;

      const triggerRect = trigger.getBoundingClientRect();
      const contentRect = content.getBoundingClientRect();
      const viewport = {
        width: window.innerWidth,
        height: window.innerHeight,
      };

      // Basic positioning logic - can be enhanced with more sophisticated collision detection
      let top = 0;
      let left = 0;

      switch (this.placement) {
        case 'top':
          top = triggerRect.top - contentRect.height - (config.offset || 8);
          left = triggerRect.left + (triggerRect.width - contentRect.width) / 2;
          break;
        case 'bottom':
          top = triggerRect.bottom + (config.offset || 8);
          left = triggerRect.left + (triggerRect.width - contentRect.width) / 2;
          break;
        case 'left':
          top = triggerRect.top + (triggerRect.height - contentRect.height) / 2;
          left = triggerRect.left - contentRect.width - (config.offset || 8);
          break;
        case 'right':
          top = triggerRect.top + (triggerRect.height - contentRect.height) / 2;
          left = triggerRect.right + (config.offset || 8);
          break;
      }

      // Ensure popover stays within viewport
      top = Math.max(8, Math.min(top, viewport.height - contentRect.height - 8));
      left = Math.max(8, Math.min(left, viewport.width - contentRect.width - 8));

      content.style.position = 'fixed';
      content.style.top = `${top}px`;
      content.style.left = `${left}px`;
    },
  };
}
