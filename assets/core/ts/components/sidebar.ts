// Sidebar Component
// Alpine.js sidebar with RTL-aware collapse/expand and responsive behavior

import { type AlpineSidebarData, type SidebarConfig } from '../types/components';

export function createSidebar(config: SidebarConfig = {}): AlpineSidebarData {
  return {
    open: false,
    overlay: config.overlay !== false,
    position: config.position || 'left',
    breakpoint: config.breakpoint || 1024,
    previousFocus: null as HTMLElement | null,
    $el: undefined as HTMLElement | undefined,
    $nextTick: undefined as ((callback: () => void) => void) | undefined,

    init() {
      this.setupRTL();
      this.setupResponsive();
      this.setupKeyboardHandling();
    },

    toggle(): void {
      this.open = !this.open;
      this.updateBodyClass();
      this.manageFocus();
    },

    close(): void {
      this.open = false;
      this.updateBodyClass();
      this.restoreFocus();
    },

    handleResize(): void {
      if (window.innerWidth >= this.breakpoint && !config.persistent) {
        this.close();
      }
    },

    handleKeydown(event: KeyboardEvent): void {
      if (event.key === 'Escape' && this.open) {
        this.close();
      }
    },

    handleBackdropClick(): void {
      if (this.overlay && this.open) {
        this.close();
      }
    },

    setupRTL(): void {
      const isRTL = document.dir === 'rtl' || document.documentElement.dir === 'rtl';
      if (isRTL) {
        // Flip position for RTL
        this.position = this.position === 'left' ? 'right' : 'left';
      }
    },

    setupResponsive(): void {
      // Listen for window resize
      window.addEventListener('resize', () => this.handleResize());

      // Initial check
      this.handleResize();
    },

    setupKeyboardHandling(): void {
      document.addEventListener('keydown', (e: KeyboardEvent) => {
        if (this.open) {
          this.handleKeydown(e);
        }
      });
    },

    updateBodyClass(): void {
      if (this.open) {
        document.body.classList.add('tutor-sidebar-open');
        if (this.overlay) {
          document.body.style.overflow = 'hidden';
        }
      } else {
        document.body.classList.remove('tutor-sidebar-open');
        document.body.style.overflow = '';
      }
    },

    manageFocus(): void {
      if (this.open) {
        // Store current focus
        this.previousFocus = document.activeElement as HTMLElement;

        // Focus first focusable element in sidebar
        this.$nextTick?.(() => {
          const sidebar = this.$el?.querySelector('.tutor-sidebar__content');
          const focusableElements = sidebar?.querySelectorAll(
            'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"]):not([disabled])',
          );

          if (focusableElements && focusableElements.length > 0) {
            (focusableElements[0] as HTMLElement).focus();
          }
        });
      }
    },

    restoreFocus(): void {
      if (this.previousFocus) {
        this.previousFocus.focus();
        this.previousFocus = null;
      }
    },

    trapFocus(event: KeyboardEvent): void {
      if (!this.open || event.key !== 'Tab') return;

      const sidebar = this.$el?.querySelector('.tutor-sidebar__content');
      if (!sidebar) return;

      const focusableElements = Array.from(
        sidebar.querySelectorAll(
          'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"]):not([disabled])',
        ),
      ) as HTMLElement[];

      if (focusableElements.length === 0) return;

      const firstElement = focusableElements[0];
      const lastElement = focusableElements[focusableElements.length - 1];

      if (event.shiftKey) {
        if (document.activeElement === firstElement) {
          event.preventDefault();
          lastElement.focus();
        }
      } else {
        if (document.activeElement === lastElement) {
          event.preventDefault();
          firstElement.focus();
        }
      }
    },
  };
}
