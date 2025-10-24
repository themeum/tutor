// Dropdown Component
// Alpine.js dropdown with RTL support and positioning

import { type AlpineDropdownData, type DropdownConfig } from '../types/components';

export function createDropdown(config: DropdownConfig = {}): AlpineDropdownData {
  return {
    open: false,
    placement: config.placement || 'bottom-start',
    $el: undefined as HTMLElement | undefined,
    $nextTick: undefined as ((callback: () => void) => void) | undefined,

    init() {
      this.setupRTL();
    },

    toggle(): void {
      this.open = !this.open;
    },

    close(): void {
      this.open = false;
    },

    handleClickOutside(): void {
      if (config.closeOnClickOutside !== false) {
        this.close();
      }
    },

    handleKeydown(event: KeyboardEvent): void {
      if (event.key === 'Escape') {
        this.close();
      }
    },

    setupRTL(): void {
      const isRTL = document.dir === 'rtl';
      if (isRTL && this.placement.includes('start')) {
        this.placement = this.placement.replace('start', 'end');
      } else if (isRTL && this.placement.includes('end')) {
        this.placement = this.placement.replace('end', 'start');
      }
    },
  };
}
