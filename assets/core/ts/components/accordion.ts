// Accordion Component
// Alpine.js accordion with multiple/single expand modes

export interface AccordionConfig {
  multiple?: boolean;
  defaultOpen?: number[];
}
export interface AlpineAccordionData {
  openItems: number[];
  $el?: HTMLElement;
  toggle: (index: number) => void;
  isOpen: (index: number) => boolean;
  updateContentClasses: (index: number) => void;
  initializeClasses: () => void;
  recalculateHeights: () => void;
  handleKeydown: (event: KeyboardEvent, index: number) => void;
  focusNext: (currentIndex: number) => void;
  focusPrevious: (currentIndex: number) => void;
  focusFirst: () => void;
  focusLast: () => void;
}
import { type AlpineComponentMeta } from '@Core/types';

export function createAccordion(config: AccordionConfig = {}): AlpineAccordionData {
  return {
    openItems: config.defaultOpen || ([] as number[]),
    $el: undefined as HTMLElement | undefined,

    toggle(index: number): void {
      // Multi-expand behavior only
      if (this.openItems.includes(index)) {
        this.openItems = this.openItems.filter((i: number) => i !== index);
      } else {
        this.openItems.push(index);
      }
      this.updateContentClasses(index);
    },

    updateContentClasses(index: number): void {
      if (!this.$el) return;

      // Try multiple selection methods
      let content = this.$el.querySelector(`#tutor-acc-panel-${index}`) as HTMLElement;

      if (!content) {
        content = document.getElementById(`tutor-acc-panel-${index}`) as HTMLElement;
      }

      if (!content) {
        const contents = this.$el.querySelectorAll('.tutor-accordion-content');
        content = contents[index] as HTMLElement;
      }

      if (!content) return;

      if (this.isOpen(index)) {
        // Expand: apply class and explicit height to force visual update
        content.classList.add('tutor-accordion-content-expanded');
        content.classList.remove('tutor-accordion-content-collapsed');

        const previousTransition = content.style.transition;
        content.style.transition = 'none';
        content.style.height = 'auto';
        const naturalHeight = content.scrollHeight;
        content.style.transition = previousTransition;

        content.style.setProperty('--content-height', `${naturalHeight}px`);
        content.style.height = `${naturalHeight}px`;
      } else {
        // Collapse: class plus inline height 0
        content.classList.add('tutor-accordion-content-collapsed');
        content.classList.remove('tutor-accordion-content-expanded');
        content.style.height = '0px';
      }
    },

    initializeClasses(): void {
      if (!this.$el) return;

      // Small delay to ensure DOM is fully rendered
      setTimeout(() => {
        this.recalculateHeights();
        const contents = this.$el!.querySelectorAll('.tutor-accordion-content');
        contents.forEach((content, index) => {
          const htmlContent = content as HTMLElement;

          if (this.isOpen(index)) {
            htmlContent.classList.add('tutor-accordion-content-expanded');
            htmlContent.classList.remove('tutor-accordion-content-collapsed');
          } else {
            htmlContent.classList.add('tutor-accordion-content-collapsed');
            htmlContent.classList.remove('tutor-accordion-content-expanded');
          }
        });
      }, 50); // Increased delay slightly
    },

    recalculateHeights(): void {
      if (!this.$el) return;

      const contents = this.$el.querySelectorAll('.tutor-accordion-content');
      contents.forEach((content) => {
        const htmlContent = content as HTMLElement;

        // Measure natural height
        const originalTransition = htmlContent.style.transition;
        htmlContent.style.transition = 'none';
        htmlContent.classList.add('tutor-accordion-content-expanded');
        htmlContent.classList.remove('tutor-accordion-content-collapsed');
        htmlContent.style.height = 'auto';
        const naturalHeight = htmlContent.scrollHeight;
        htmlContent.style.transition = originalTransition;

        htmlContent.style.setProperty('--content-height', `${naturalHeight}px`);

        // Restore collapsed state if needed
        if (!this.isOpen(Array.from(contents).indexOf(content))) {
          htmlContent.classList.add('tutor-accordion-content-collapsed');
          htmlContent.classList.remove('tutor-accordion-content-expanded');
          htmlContent.style.height = '0px';
        } else {
          htmlContent.style.height = `${naturalHeight}px`;
        }
      });
    },

    isOpen(index: number): boolean {
      return this.openItems.includes(index);
    },

    handleKeydown(event: KeyboardEvent, index: number): void {
      switch (event.key) {
        case 'Enter':
        case ' ':
          event.preventDefault();
          this.toggle(index);
          break;
        case 'ArrowDown':
          event.preventDefault();
          this.focusNext(index);
          break;
        case 'ArrowUp':
          event.preventDefault();
          this.focusPrevious(index);
          break;
        case 'Home':
          event.preventDefault();
          this.focusFirst();
          break;
        case 'End':
          event.preventDefault();
          this.focusLast();
          break;
      }
    },

    focusNext(currentIndex: number): void {
      const triggers = this.$el?.querySelectorAll('.tutor-accordion-trigger');
      if (triggers) {
        const nextIndex = currentIndex < triggers.length - 1 ? currentIndex + 1 : 0;
        (triggers[nextIndex] as HTMLElement).focus();
      }
    },

    focusPrevious(currentIndex: number): void {
      const triggers = this.$el?.querySelectorAll('.tutor-accordion-trigger');
      if (triggers) {
        const prevIndex = currentIndex > 0 ? currentIndex - 1 : triggers.length - 1;
        (triggers[prevIndex] as HTMLElement).focus();
      }
    },

    focusFirst(): void {
      const triggers = this.$el?.querySelectorAll('.tutor-accordion-trigger');
      if (triggers && triggers.length > 0) {
        (triggers[0] as HTMLElement).focus();
      }
    },

    focusLast(): void {
      const triggers = this.$el?.querySelectorAll('.tutor-accordion-trigger');
      if (triggers && triggers.length > 0) {
        (triggers[triggers.length - 1] as HTMLElement).focus();
      }
    },
  };
}

export const accordionMeta: AlpineComponentMeta<AccordionConfig> = {
  name: 'accordion',
  component: createAccordion,
};
