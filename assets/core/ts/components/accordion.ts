// Accordion Component
// Alpine.js accordion with multiple/single expand modes

import { type AccordionConfig, type AlpineAccordionData } from '../types/components';

export function createAccordion(config: AccordionConfig = {}): AlpineAccordionData {
  return {
    openItems: [] as number[],
    multiple: config.multiple || false,
    $el: undefined as HTMLElement | undefined,

    toggle(index: number): void {
      if (this.multiple) {
        if (this.openItems.includes(index)) {
          this.openItems = this.openItems.filter((i: number) => i !== index);
        } else {
          this.openItems.push(index);
        }
      } else {
        if (this.openItems.includes(index)) {
          this.openItems = config.collapsible !== false ? [] : [index];
        } else {
          this.openItems = [index];
        }
      }
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
      const triggers = this.$el?.querySelectorAll('.tutor-accordion__trigger');
      if (triggers) {
        const nextIndex = currentIndex < triggers.length - 1 ? currentIndex + 1 : 0;
        (triggers[nextIndex] as HTMLElement).focus();
      }
    },

    focusPrevious(currentIndex: number): void {
      const triggers = this.$el?.querySelectorAll('.tutor-accordion__trigger');
      if (triggers) {
        const prevIndex = currentIndex > 0 ? currentIndex - 1 : triggers.length - 1;
        (triggers[prevIndex] as HTMLElement).focus();
      }
    },

    focusFirst(): void {
      const triggers = this.$el?.querySelectorAll('.tutor-accordion__trigger');
      if (triggers && triggers.length > 0) {
        (triggers[0] as HTMLElement).focus();
      }
    },

    focusLast(): void {
      const triggers = this.$el?.querySelectorAll('.tutor-accordion__trigger');
      if (triggers && triggers.length > 0) {
        (triggers[triggers.length - 1] as HTMLElement).focus();
      }
    },
  };
}
