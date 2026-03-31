import { type AlpineComponentMeta } from '@Core/ts/types';

export interface StepperOption {
  label: string;
  value: string;
  disabled?: boolean;
  icon?: string;
}

export interface StepperDropdownProps {
  options: StepperOption[];
  value?: string;
  placeholder?: string;
  disabled?: boolean;
  name?: string; // Form field name for the hidden input
  onChange?: (value: string) => void;
}

export const stepperDropdown = (props: StepperDropdownProps) => {
  return {
    options: props.options || [],
    isOpen: false,
    highlightedIndex: -1,
    value: props.value ?? '',
    placeholder: props.placeholder || '',
    disabled: props.disabled || false,
    dropdownPosition: 'bottom' as 'top' | 'bottom',
    name: props.name || '',

    init() {
      const $el = (this as unknown as { $el: HTMLElement }).$el;
      $el.classList.add('tutor-stepper-dropdown');
      this.syncHiddenInput();

      // Watch for isOpen changes and recalculate position
      const $watch = (this as unknown as { $watch?: (key: string, callback: (value: unknown) => void) => void }).$watch;
      if ($watch) {
        $watch('isOpen', (value: unknown) => {
          if (value === true) {
            // Use $nextTick to ensure DOM is ready after Alpine renders the menu
            const $nextTick = (this as unknown as { $nextTick?: (callback: () => void) => void }).$nextTick;
            if ($nextTick) {
              $nextTick(() => {
                this.dropdownPosition = this.calculateDropdownPosition();
                this.updateDropdownClasses();
              });
            } else {
              setTimeout(() => {
                this.dropdownPosition = this.calculateDropdownPosition();
                this.updateDropdownClasses();
              }, 50);
            }
          }
        });
      }
    },

    calculateDropdownPosition() {
      const $el = (this as unknown as { $el: HTMLElement }).$el;
      let controlElement: HTMLElement;

      if ($el.classList.contains('tutor-stepper-dropdown-control')) {
        controlElement = $el;
      } else {
        controlElement = $el.querySelector('.tutor-stepper-dropdown-control') as HTMLElement;
      }

      if (!controlElement) {
        return 'bottom';
      }

      const rect = controlElement.getBoundingClientRect();
      const viewportHeight = window.innerHeight;
      const spaceBelow = viewportHeight - rect.bottom;
      const spaceAbove = rect.top;

      const estimatedDropdownHeight = Math.min(280, this.options.length * 44) + 20; // ~44px per option + buffer

      if (spaceBelow < estimatedDropdownHeight && spaceAbove >= estimatedDropdownHeight) {
        return 'top';
      }

      return 'bottom';
    },

    updateDropdownClasses() {
      const $el = (this as unknown as { $el: HTMLElement }).$el;

      let menuElement: HTMLElement | null = null;

      if ($el.classList.contains('tutor-stepper-dropdown-control')) {
        const container = $el.closest('.tutor-stepper-dropdown');
        if (container) {
          menuElement = container.querySelector('.tutor-stepper-dropdown-menu') as HTMLElement;
        } else {
          menuElement = $el.nextElementSibling?.classList.contains('tutor-stepper-dropdown-menu')
            ? ($el.nextElementSibling as HTMLElement)
            : null;
        }
      } else {
        menuElement = $el.querySelector('.tutor-stepper-dropdown-menu') as HTMLElement;
      }

      if (!menuElement) {
        return;
      }

      menuElement.classList.remove('tutor-stepper-dropdown-menu-top', 'tutor-stepper-dropdown-menu-bottom');

      const newClass =
        this.dropdownPosition === 'top' ? 'tutor-stepper-dropdown-menu-top' : 'tutor-stepper-dropdown-menu-bottom';
      menuElement.classList.add(newClass);
    },

    toggle() {
      if (this.disabled) return;
      this.isOpen = !this.isOpen;
      if (this.isOpen) {
        this.openDropdown();
      }
    },

    open() {
      if (this.disabled) return;
      this.isOpen = true;
      this.openDropdown();
    },

    openDropdown() {
      const selectedIndex = this.options.findIndex((o) => o.value === this.value && !o.disabled);
      this.highlightedIndex = selectedIndex >= 0 ? selectedIndex : this.nextEnabledIndex(0);

      // Use $nextTick to ensure DOM is ready before calculating position
      const $nextTick = (this as unknown as { $nextTick?: (callback: () => void) => void }).$nextTick;
      if ($nextTick) {
        $nextTick(() => {
          this.dropdownPosition = this.calculateDropdownPosition();
          this.updateDropdownClasses();
          this.scrollToHighlighted();
        });
      } else {
        // Fallback to setTimeout if $nextTick is not available
        setTimeout(() => {
          this.dropdownPosition = this.calculateDropdownPosition();
          this.updateDropdownClasses();
          this.scrollToHighlighted();
        }, 50);
      }
    },

    close() {
      this.isOpen = false;
    },

    isSelected(option: StepperOption): boolean {
      return this.value === option.value;
    },

    get selectedLabel(): string {
      const match = this.options.find((o) => o.value === this.value);
      return match ? match.label : this.placeholder;
    },

    get selectedOption(): StepperOption | null {
      const match = this.options.find((o) => o.value === this.value);
      return match || null;
    },

    syncHiddenInput() {
      if (!this.name) return;
      const $el = (this as unknown as { $el: HTMLElement }).$el;
      let hiddenInput = $el.querySelector(`input[type="hidden"][name="${this.name}"]`) as HTMLInputElement | null;

      if (!hiddenInput) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = this.name;
        $el.appendChild(hiddenInput);
      }

      hiddenInput.value = this.value;
    },

    get currentIndex(): number {
      return this.options.findIndex((o) => o.value === this.value && !o.disabled);
    },

    canIncrement(): boolean {
      const currentIdx = this.currentIndex;
      if (currentIdx === -1) return this.options.length > 0;
      return currentIdx < this.options.length - 1;
    },

    canDecrement(): boolean {
      const currentIdx = this.currentIndex;
      if (currentIdx === -1) return false;
      return currentIdx > 0;
    },

    increment() {
      if (this.disabled) return;

      const currentIdx = this.currentIndex;
      let nextIdx: number;

      if (currentIdx === -1) {
        // No selection, go to first enabled option
        nextIdx = this.nextEnabledIndex(0);
      } else {
        // Find next enabled option
        nextIdx = this.nextEnabledIndex(currentIdx + 1);
      }

      if (nextIdx >= 0 && nextIdx < this.options.length) {
        const option = this.options[nextIdx];
        if (option && !option.disabled) {
          this.value = option.value;
          this.syncHiddenInput();
          if (props.onChange) {
            props.onChange(option.value);
          }
        }
      }
    },

    decrement() {
      if (this.disabled) return;

      const currentIdx = this.currentIndex;
      if (currentIdx === -1) return;

      // Find previous enabled option
      const prevIdx = this.prevEnabledIndex(currentIdx - 1);

      if (prevIdx >= 0 && prevIdx < this.options.length) {
        const option = this.options[prevIdx];
        if (option && !option.disabled) {
          this.value = option.value;
          this.syncHiddenInput();
          if (props.onChange) {
            props.onChange(option.value);
          }
        }
      }
    },

    selectByIndex(index: number) {
      if (index < 0 || index >= this.options.length) return;
      const option = this.options[index];
      if (!option || option.disabled) return;
      this.value = option.value;
      this.syncHiddenInput();
      if (props.onChange) {
        props.onChange(option.value);
      }
      this.close();
    },

    selectOption(option: StepperOption) {
      if (option.disabled) return;
      this.value = option.value;
      this.syncHiddenInput();
      if (props.onChange) {
        props.onChange(option.value);
      }
      this.close();
    },

    handleKeydown(event: KeyboardEvent) {
      if (this.disabled) return;
      switch (event.key) {
        case 'Enter':
        case ' ':
          event.preventDefault();
          if (!this.isOpen) {
            this.open();
          } else if (this.highlightedIndex >= 0) {
            this.selectByIndex(this.highlightedIndex);
          }
          break;
        case 'Escape':
          this.close();
          break;
        case 'ArrowDown':
          event.preventDefault();
          if (event.ctrlKey || event.metaKey) {
            // Ctrl/Cmd + ArrowDown increments value
            this.increment();
          } else if (!this.isOpen) {
            this.open();
          } else {
            this.moveHighlight(1);
          }
          break;
        case 'ArrowUp':
          event.preventDefault();
          if (event.ctrlKey || event.metaKey) {
            // Ctrl/Cmd + ArrowUp decrements value
            this.decrement();
          } else if (!this.isOpen) {
            this.open();
          } else {
            this.moveHighlight(-1);
          }
          break;
        case 'Home':
          event.preventDefault();
          if (!this.isOpen) this.open();
          this.highlightedIndex = this.nextEnabledIndex(0);
          this.scrollToHighlighted();
          break;
        case 'End':
          event.preventDefault();
          if (!this.isOpen) this.open();
          this.highlightedIndex = this.prevEnabledIndex(this.options.length - 1);
          this.scrollToHighlighted();
          break;
      }
    },

    moveHighlight(direction: 1 | -1) {
      if (this.options.length === 0) return;
      let index = this.highlightedIndex;
      if (index === -1) {
        index = direction === 1 ? -1 : this.options.length;
      }
      do {
        index += direction;
      } while (index >= 0 && index < this.options.length && this.options[index] && this.options[index].disabled);

      if (index >= 0 && index < this.options.length) {
        this.highlightedIndex = index;
        this.scrollToHighlighted();
      }
    },

    scrollToHighlighted() {
      const $el = (this as unknown as { $el: HTMLElement }).$el;
      const $nextTick = (this as unknown as { $nextTick?: (callback: () => void) => void }).$nextTick;

      if ($nextTick) {
        $nextTick(() => {
          const menu = $el.querySelector('.tutor-stepper-dropdown-menu') as HTMLElement;
          const options = menu?.querySelectorAll('.tutor-stepper-dropdown-option');
          if (options && this.highlightedIndex >= 0 && this.highlightedIndex < options.length) {
            const highlightedOption = options[this.highlightedIndex] as HTMLElement;
            if (highlightedOption) {
              highlightedOption.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
          }
        });
      }
    },

    nextEnabledIndex(start: number): number {
      for (let i = start; i < this.options.length; i++) {
        if (!this.options[i]?.disabled) return i;
      }
      return -1;
    },

    prevEnabledIndex(start: number): number {
      for (let i = start; i >= 0; i--) {
        if (!this.options[i]?.disabled) return i;
      }
      return -1;
    },
  };
};

export const stepperDropdownMeta: AlpineComponentMeta<StepperDropdownProps> = {
  name: 'stepperDropdown',
  component: stepperDropdown,
};
