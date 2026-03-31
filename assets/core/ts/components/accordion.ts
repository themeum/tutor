import { type AlpineComponentMeta } from '@Core/ts/types';

export interface AccordionConfig {
  multiple?: boolean;
  defaultOpen?: number[];
}

export interface AlpineAccordionData {
  openItems: number[];
  multiple: boolean;
  $el?: HTMLElement;
  toggle: (index: number) => void;
  isOpen: (index: number) => boolean;
  handleKeydown: (event: KeyboardEvent, index: number) => void;
  focusNext: (currentIndex: number) => void;
  focusPrevious: (currentIndex: number) => void;
  focusFirst: () => void;
  focusLast: () => void;
}

export function createAccordion(config: AccordionConfig = {}): AlpineAccordionData {
  return {
    openItems: config.defaultOpen || ([] as number[]),
    multiple: config.multiple !== false, // Default to true if not specified
    $el: undefined as HTMLElement | undefined,

    toggle(index: number): void {
      if (this.openItems.includes(index)) {
        this.openItems = this.openItems.filter((i: number) => i !== index);
      } else {
        if (!this.multiple) {
          this.openItems = [index];
        } else {
          this.openItems.push(index);
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
