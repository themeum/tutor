import { type AlpineComponentMeta } from '@Core/types';

export interface SelectOption {
  label: string;
  value: string;
  disabled?: boolean;
  icon?: string;
}

export interface SelectDropdownProps {
  options: SelectOption[];
  value?: string;
  placeholder?: string;
  disabled?: boolean;
  onChange?: (value: string) => void;
}

export const selectDropdown = (props: SelectDropdownProps) => {
  return {
    options: props.options || [],
    isOpen: false,
    highlightedIndex: -1,
    value: props.value ?? '',
    placeholder: props.placeholder || '',
    disabled: props.disabled || false,
    dropdownPosition: 'bottom' as 'top' | 'bottom',

    init() {
      const $el = (this as unknown as { $el: HTMLElement }).$el;
      $el.classList.add('tutor-select-dropdown');
    },

    calculateDropdownPosition() {
      const $el = (this as unknown as { $el: HTMLElement }).$el;
      let controlElement: HTMLElement;

      if ($el.classList.contains('tutor-select-dropdown-control')) {
        controlElement = $el;
      } else {
        controlElement = $el.querySelector('.tutor-select-dropdown-control') as HTMLElement;
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

      if ($el.classList.contains('tutor-select-dropdown-control')) {
        const container = $el.closest('.tutor-select-dropdown');
        if (container) {
          menuElement = container.querySelector('.tutor-select-dropdown-menu') as HTMLElement;
        } else {
          menuElement = $el.nextElementSibling?.classList.contains('tutor-select-dropdown-menu')
            ? ($el.nextElementSibling as HTMLElement)
            : null;
        }
      } else {
        menuElement = $el.querySelector('.tutor-select-dropdown-menu') as HTMLElement;
      }

      if (!menuElement) {
        return;
      }

      menuElement.classList.remove('tutor-select-dropdown-menu--top', 'tutor-select-dropdown-menu--bottom');

      const newClass =
        this.dropdownPosition === 'top' ? 'tutor-select-dropdown-menu--top' : 'tutor-select-dropdown-menu--bottom';
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
      setTimeout(() => {
        this.dropdownPosition = this.calculateDropdownPosition();
        this.updateDropdownClasses();
      }, 50);

      const selectedIndex = this.options.findIndex((o) => o.value === this.value && !o.disabled);
      this.highlightedIndex = selectedIndex >= 0 ? selectedIndex : this.nextEnabledIndex(0);
    },

    close() {
      this.isOpen = false;
    },

    isSelected(option: SelectOption): boolean {
      return this.value === option.value;
    },

    get selectedLabel(): string {
      const match = this.options.find((o) => o.value === this.value);
      return match ? match.label : this.placeholder;
    },

    get selectedOption(): SelectOption | null {
      const match = this.options.find((o) => o.value === this.value);
      return match || null;
    },

    selectByIndex(index: number) {
      if (index < 0 || index >= this.options.length) return;
      const option = this.options[index];
      if (!option || option.disabled) return;
      this.value = option.value;
      if (props.onChange) {
        props.onChange(option.value);
      }
      this.close();
    },

    selectOption(option: SelectOption) {
      if (option.disabled) return;
      this.value = option.value;
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
          if (!this.isOpen) this.open();
          this.moveHighlight(1);
          break;
        case 'ArrowUp':
          event.preventDefault();
          if (!this.isOpen) this.open();
          this.moveHighlight(-1);
          break;
        case 'Home':
          event.preventDefault();
          this.highlightedIndex = this.nextEnabledIndex(0);
          break;
        case 'End':
          event.preventDefault();
          this.highlightedIndex = this.prevEnabledIndex(this.options.length - 1);
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

export const selectDropdownMeta: AlpineComponentMeta<SelectDropdownProps> = {
  name: 'selectDropdown',
  component: selectDropdown,
  global: true,
};
