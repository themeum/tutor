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
import { type AlpineComponentMeta } from '@Core/ts/types';

export function createAccordion(config: AccordionConfig = {}): AlpineAccordionData {
  return {
    openItems: config.defaultOpen || ([] as number[]),
    $el: undefined as HTMLElement | undefined,

    toggle(index: number): void {
      if (this.openItems.includes(index)) {
        this.openItems = this.openItems.filter((i: number) => i !== index);
      } else {
        this.openItems.push(index);
      }
      this.updateContentClasses(index);
    },

    updateContentClasses(index: number): void {
      if (!this.$el) return;

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
        content.classList.remove('tutor-accordion-content-expanded');
        content.classList.add('tutor-accordion-content-collapsed');
        content.style.height = '0px';

        const bodyElement = content.querySelector('.tutor-accordion-body') as HTMLElement;

        let naturalHeight = 0;

        if (bodyElement) {
          const bodyHeight = bodyElement.scrollHeight;

          const previousTransition = content.style.transition;
          const previousTransform = content.style.transform;
          const previousPointerEvents = content.style.pointerEvents;

          content.style.transition = 'none';
          content.style.transform = 'scaleY(0)';
          content.style.transformOrigin = 'top';
          content.style.pointerEvents = 'none';
          content.classList.remove('tutor-accordion-content-collapsed');
          content.classList.add('tutor-accordion-content-expanded');
          content.style.height = 'auto';
          void content.offsetHeight;

          const computedStyle = window.getComputedStyle(content);
          const paddingTop = parseFloat(computedStyle.paddingTop) || 0;
          const paddingBottom = parseFloat(computedStyle.paddingBottom) || 0;

          naturalHeight = bodyHeight + paddingTop + paddingBottom;
          content.style.transition = previousTransition;
          content.style.transform = previousTransform;
          content.style.transformOrigin = '';
          content.style.pointerEvents = previousPointerEvents;
          content.classList.remove('tutor-accordion-content-expanded');
          content.classList.add('tutor-accordion-content-collapsed');
          content.style.height = '0px';
        } else {
          const previousTransition = content.style.transition;
          const previousTransform = content.style.transform;
          const previousPointerEvents = content.style.pointerEvents;

          content.style.transition = 'none';
          content.style.transform = 'scaleY(0)';
          content.style.transformOrigin = 'top';
          content.style.pointerEvents = 'none';
          content.classList.remove('tutor-accordion-content-collapsed');
          content.classList.add('tutor-accordion-content-expanded');
          content.style.height = 'auto';
          naturalHeight = content.scrollHeight;
          content.classList.remove('tutor-accordion-content-expanded');
          content.classList.add('tutor-accordion-content-collapsed');
          content.style.height = '0px';
          content.style.transition = previousTransition;
          content.style.transform = previousTransform;
          content.style.transformOrigin = '';
          content.style.pointerEvents = previousPointerEvents;
        }
        content.style.setProperty('--content-height', `${naturalHeight}px`);
        requestAnimationFrame(() => {
          requestAnimationFrame(() => {
            content.classList.remove('tutor-accordion-content-collapsed');
            content.classList.add('tutor-accordion-content-expanded');
            content.style.height = '';
          });
        });
      } else {
        content.classList.add('tutor-accordion-content-collapsed');
        content.classList.remove('tutor-accordion-content-expanded');
        content.style.height = '0px';
      }
    },

    initializeClasses(): void {
      if (!this.$el) return;
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
        const originalTransition = htmlContent.style.transition;
        htmlContent.style.transition = 'none';
        htmlContent.classList.add('tutor-accordion-content-expanded');
        htmlContent.classList.remove('tutor-accordion-content-collapsed');
        htmlContent.style.height = 'auto';
        const naturalHeight = htmlContent.scrollHeight;
        htmlContent.style.transition = originalTransition;
        htmlContent.style.setProperty('--content-height', `${naturalHeight}px`);
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
