// TutorCore - Main Design System Class
// Single class containing all component methods and utilities

import { createAccordion } from './components/accordion';
import { createDropdown } from './components/dropdown';
import { createFormValidation } from './components/form-validation';
import { createModal } from './components/modal';
import { createPopover } from './components/popover';
import { createSidebar } from './components/sidebar';
import { createTabs } from './components/tabs';
import { createToast } from './components/toast';
import { createTooltip } from './components/tooltip';

import type {
  AccordionConfig,
  AlpineAccordionData,
  AlpineDropdownData,
  AlpineFormValidationData,
  AlpineModalData,
  AlpinePopoverData,
  AlpineSidebarData,
  AlpineTabsData,
  AlpineToastData,
  AlpineTooltipData,
  DropdownConfig,
  FormValidationConfig,
  ModalConfig,
  PopoverConfig,
  SidebarConfig,
  TabsConfig,
  TooltipConfig,
} from './types/components';

import type { RTLUtils } from './types/alpine';

/**
 * TutorCore - Main Design System Class
 *
 * A comprehensive TypeScript-based Alpine.js component library for common UI interactions.
 * Provides interactive components with TypeScript interfaces, accessibility features,
 * and RTL (Right-to-Left) language support.
 */
export class TutorCore {
  /**
   * Utility methods for common operations and RTL support
   */
  static utils: RTLUtils & {
    generateId(): string;
    debounce<T extends (...args: unknown[]) => unknown>(func: T, wait: number): T;
    throttle<T extends (...args: unknown[]) => unknown>(func: T, limit: number): T;
    getBreakpoint(): string;
    isMobile(): boolean;
    isTablet(): boolean;
    isDesktop(): boolean;
  } = {
    /**
     * Check if the current document direction is RTL
     */
    isRTL(): boolean {
      return (
        document.dir === 'rtl' ||
        document.documentElement.dir === 'rtl' ||
        getComputedStyle(document.documentElement).direction === 'rtl'
      );
    },

    /**
     * Get the current document direction
     */
    getDirection(): 'ltr' | 'rtl' {
      return this.isRTL() ? 'rtl' : 'ltr';
    },

    /**
     * Adapt placement strings for RTL layouts
     */
    adaptPlacement(placement: string): string {
      if (!this.isRTL()) return placement;

      const adaptations: Record<string, string> = {
        left: 'right',
        right: 'left',
        start: 'end',
        end: 'start',
        'top-start': 'top-end',
        'top-end': 'top-start',
        'bottom-start': 'bottom-end',
        'bottom-end': 'bottom-start',
      };

      return adaptations[placement] || placement;
    },

    /**
     * Get start and end directions based on RTL
     */
    getStartEnd(): { start: 'left' | 'right'; end: 'left' | 'right' } {
      return this.isRTL() ? { start: 'right', end: 'left' } : { start: 'left', end: 'right' };
    },

    /**
     * Generate a unique ID for components
     */
    generateId(): string {
      return `tutor-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;
    },

    /**
     * Debounce function calls
     */
    debounce<T extends (...args: unknown[]) => unknown>(func: T, wait: number): T {
      let timeout: ReturnType<typeof setTimeout>;
      return ((...args: unknown[]) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
      }) as T;
    },

    /**
     * Throttle function calls
     */
    throttle<T extends (...args: unknown[]) => unknown>(func: T, limit: number): T {
      let inThrottle: boolean;
      return ((...args: unknown[]) => {
        if (!inThrottle) {
          func.apply(this, args);
          inThrottle = true;
          setTimeout(() => (inThrottle = false), limit);
        }
      }) as T;
    },

    /**
     * Get current breakpoint
     */
    getBreakpoint(): string {
      const width = window.innerWidth;
      if (width < 768) return 'mobile';
      if (width < 1024) return 'tablet';
      return 'desktop';
    },

    /**
     * Check if current viewport is mobile
     */
    isMobile(): boolean {
      return window.innerWidth < 768;
    },

    /**
     * Check if current viewport is tablet
     */
    isTablet(): boolean {
      return window.innerWidth >= 768 && window.innerWidth < 1024;
    },

    /**
     * Check if current viewport is desktop
     */
    isDesktop(): boolean {
      return window.innerWidth >= 1024;
    },
  };

  /**
   * Dropdown component factory method
   * Creates a dropdown with RTL-aware positioning, click-outside handling, and keyboard navigation
   */
  static dropdown(config: DropdownConfig = {}): AlpineDropdownData {
    return createDropdown(config);
  }

  /**
   * Modal component factory method
   * Creates a modal with backdrop, focus management, ESC key support, and accessibility
   */
  static modal(config: ModalConfig = {}): AlpineModalData {
    return createModal(config);
  }

  /**
   * Toast notification factory method
   * Creates a toast system with RTL-aware positioning, auto-dismiss, stacking, and multiple types
   */
  static toast(): AlpineToastData {
    return createToast();
  }

  /**
   * Tabs component factory method
   * Creates tabs with keyboard navigation, ARIA support, and smooth transitions
   */
  static tabs(config: TabsConfig = {}): AlpineTabsData {
    return createTabs(config.defaultTab);
  }

  /**
   * Accordion component factory method
   * Creates accordion with multiple/single expand modes and smooth animations
   */
  static accordion(config: AccordionConfig = {}): AlpineAccordionData {
    return createAccordion(config);
  }

  /**
   * Popover component factory method
   * Creates popover with RTL-aware dynamic positioning, trigger options, and collision detection
   */
  static popover(config: PopoverConfig = {}): AlpinePopoverData {
    return createPopover(config);
  }

  /**
   * Tooltip component factory method
   * Creates tooltip with RTL-aware positioning, hover/focus triggers, and accessibility
   */
  static tooltip(config: TooltipConfig = {}): AlpineTooltipData {
    return createTooltip(config);
  }

  /**
   * Sidebar component factory method
   * Creates sidebar with RTL-aware collapse/expand functionality and responsive behavior
   */
  static sidebar(config: SidebarConfig = {}): AlpineSidebarData {
    return createSidebar(config);
  }

  /**
   * Form validation component factory method
   * Creates form validation with real-time validation, custom rules, and error display
   */
  static formValidation(config: FormValidationConfig = {}): AlpineFormValidationData {
    return createFormValidation(config);
  }
}

// Register with Alpine.js when it's available
document.addEventListener('alpine:init', () => {
  if (window.Alpine) {
    // Register all component data functions
    window.Alpine.data('tutorDropdown', TutorCore.dropdown);
    window.Alpine.data('tutorModal', TutorCore.modal);
    window.Alpine.data('tutorToast', TutorCore.toast);
    window.Alpine.data('tutorTabs', TutorCore.tabs);
    window.Alpine.data('tutorAccordion', TutorCore.accordion);
    window.Alpine.data('tutorPopover', TutorCore.popover);
    window.Alpine.data('tutorTooltip', TutorCore.tooltip);
    window.Alpine.data('tutorSidebar', TutorCore.sidebar);
    window.Alpine.data('tutorFormValidation', TutorCore.formValidation);
  }
});

// Make TutorCore globally available
declare global {
  interface Window {
    TutorCore: typeof TutorCore;
  }
}

window.TutorCore = TutorCore;

export default TutorCore;
