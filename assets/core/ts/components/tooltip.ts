// Tooltip Component
// Alpine.js tooltip with RTL-aware positioning and accessibility

import { type AlpineTooltipData, type TooltipConfig } from '../types/components';

export function createTooltip(config: TooltipConfig = {}): AlpineTooltipData {
  return {
    visible: false,
    placement: config.placement || 'top',
    trigger: config.trigger || 'hover',
    $el: undefined as HTMLElement | undefined,

    init() {
      this.setupRTL();
      this.setupTriggers();
      this.setupAccessibility();
    },

    show(): void {
      this.visible = true;
      this.updatePosition();
    },

    hide(): void {
      this.visible = false;
    },

    handleMouseEnter(): void {
      if (this.trigger === 'hover' || !this.trigger) {
        const delay = config.delay?.show || 0;
        setTimeout(() => this.show(), delay);
      }
    },

    handleMouseLeave(): void {
      if (this.trigger === 'hover' || !this.trigger) {
        const delay = config.delay?.hide || 0;
        setTimeout(() => this.hide(), delay);
      }
    },

    handleFocus(): void {
      if (this.trigger === 'focus' || !this.trigger) {
        this.show();
      }
    },

    handleBlur(): void {
      if (this.trigger === 'focus' || !this.trigger) {
        this.hide();
      }
    },

    handleClick(): void {
      if (this.trigger === 'click') {
        if (this.visible) {
          this.hide();
        } else {
          this.show();
        }
      }
    },

    handleKeydown(event: KeyboardEvent): void {
      if (event.key === 'Escape' && this.visible) {
        this.hide();
      }
    },

    setupRTL(): void {
      const isRTL = document.dir === 'rtl' || document.documentElement.dir === 'rtl';
      if (isRTL) {
        const rtlAdaptations: Record<string, string> = {
          left: 'right',
          right: 'left',
        };
        const newPlacement = rtlAdaptations[this.placement] || this.placement;
        this.placement = newPlacement as typeof this.placement;
      }
    },

    setupTriggers(): void {
      const trigger = this.$el;
      if (!trigger) return;

      switch (this.trigger) {
        case 'hover':
          trigger.addEventListener('mouseenter', () => this.handleMouseEnter());
          trigger.addEventListener('mouseleave', () => this.handleMouseLeave());
          break;
        case 'focus':
          trigger.addEventListener('focus', () => this.handleFocus());
          trigger.addEventListener('blur', () => this.handleBlur());
          break;
        case 'click':
          trigger.addEventListener('click', () => this.handleClick());
          break;
      }

      // Always listen for keyboard events for accessibility
      trigger.addEventListener('keydown', (e: KeyboardEvent) => this.handleKeydown(e));
    },

    setupAccessibility(): void {
      const trigger = this.$el;
      const tooltip = this.$el?.querySelector('.tutor-tooltip__content');

      if (trigger && tooltip) {
        const tooltipId = `tooltip-${Date.now()}`;
        tooltip.setAttribute('id', tooltipId);
        tooltip.setAttribute('role', 'tooltip');
        trigger.setAttribute('aria-describedby', tooltipId);
      }
    },

    updatePosition(): void {
      const trigger = this.$el as HTMLElement;
      const tooltip = this.$el?.querySelector('.tutor-tooltip__content') as HTMLElement;

      if (!trigger || !tooltip) return;

      const triggerRect = trigger.getBoundingClientRect();
      const tooltipRect = tooltip.getBoundingClientRect();
      const viewport = {
        width: window.innerWidth,
        height: window.innerHeight,
      };

      let top = 0;
      let left = 0;

      switch (this.placement) {
        case 'top':
          top = triggerRect.top - tooltipRect.height - 8;
          left = triggerRect.left + (triggerRect.width - tooltipRect.width) / 2;
          break;
        case 'bottom':
          top = triggerRect.bottom + 8;
          left = triggerRect.left + (triggerRect.width - tooltipRect.width) / 2;
          break;
        case 'left':
          top = triggerRect.top + (triggerRect.height - tooltipRect.height) / 2;
          left = triggerRect.left - tooltipRect.width - 8;
          break;
        case 'right':
          top = triggerRect.top + (triggerRect.height - tooltipRect.height) / 2;
          left = triggerRect.right + 8;
          break;
      }

      // Keep tooltip within viewport
      top = Math.max(8, Math.min(top, viewport.height - tooltipRect.height - 8));
      left = Math.max(8, Math.min(left, viewport.width - tooltipRect.width - 8));

      tooltip.style.position = 'fixed';
      tooltip.style.top = `${top}px`;
      tooltip.style.left = `${left}px`;
      tooltip.style.zIndex = '9999';
    },
  };
}
