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
  searchable?: boolean;
  name?: string; // Form field name for the hidden input
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
    searchable: props.searchable || false,
    searchText: '',
    dropdownPosition: 'bottom' as 'top' | 'bottom',
    name: props.name || '',

    init() {
      const $el = (this as unknown as { $el: HTMLElement }).$el;
      $el.classList.add('tutor-select-dropdown');
      this.syncHiddenInput();
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

      const optionCount = this.filteredOptions.length;
      const estimatedDropdownHeight = Math.min(280, optionCount * 44) + (this.searchable ? 56 : 20); // include search box height buffer

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

      menuElement.classList.remove('tutor-select-dropdown-menu-top', 'tutor-select-dropdown-menu-bottom');

      const newClass =
        this.dropdownPosition === 'top' ? 'tutor-select-dropdown-menu-top' : 'tutor-select-dropdown-menu-bottom';
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

      const selectedIndex = this.filteredOptions.findIndex((o) => o.value === this.value && !o.disabled);
      this.highlightedIndex = selectedIndex >= 0 ? selectedIndex : this.nextEnabledIndex(0);

      // Use $nextTick to ensure DOM is ready
      const $nextTick = (this as unknown as { $nextTick?: (callback: () => void) => void }).$nextTick;
      if ($nextTick) {
        $nextTick(() => {
          if (this.searchable) {
            const $el = (this as unknown as { $el: HTMLElement }).$el;
            const input = $el.querySelector('.tutor-select-dropdown-search-input') as HTMLInputElement | null;
            if (input) {
              input.focus();
              input.select();
            }
          }
          this.scrollToHighlighted();
        });
      }
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

    get filteredOptions(): SelectOption[] {
      if (!this.searchable || !this.searchText.trim()) {
        return this.options;
      }
      const query = this.searchText.toLowerCase();
      return this.options.filter((opt) => opt.label.toLowerCase().includes(query));
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

    selectByIndex(index: number) {
      if (index < 0 || index >= this.filteredOptions.length) return;
      const option = this.filteredOptions[index];
      if (!option || option.disabled) return;
      this.value = option.value;
      this.syncHiddenInput();
      if (props.onChange) {
        props.onChange(option.value);
      }
      this.close();
    },

    selectOption(option: SelectOption) {
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
          if (!this.isOpen) this.open();
          this.highlightedIndex = this.nextEnabledIndex(0);
          this.scrollToHighlighted();
          break;
        case 'End':
          event.preventDefault();
          if (!this.isOpen) this.open();
          this.highlightedIndex = this.prevEnabledIndex(this.filteredOptions.length - 1);
          this.scrollToHighlighted();
          break;
      }
    },

    moveHighlight(direction: 1 | -1) {
      if (this.filteredOptions.length === 0) return;
      let index = this.highlightedIndex;
      if (index === -1) {
        index = direction === 1 ? -1 : this.filteredOptions.length;
      }
      do {
        index += direction;
      } while (
        index >= 0 &&
        index < this.filteredOptions.length &&
        this.filteredOptions[index] &&
        this.filteredOptions[index].disabled
      );

      if (index >= 0 && index < this.filteredOptions.length) {
        this.highlightedIndex = index;
        this.scrollToHighlighted();
      }
    },

    scrollToHighlighted() {
      const $el = (this as unknown as { $el: HTMLElement }).$el;
      const $nextTick = (this as unknown as { $nextTick?: (callback: () => void) => void }).$nextTick;

      if ($nextTick) {
        $nextTick(() => {
          const menu = $el.querySelector('.tutor-select-dropdown-menu') as HTMLElement;
          const options = menu?.querySelectorAll('.tutor-select-dropdown-option');
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
      for (let i = start; i < this.filteredOptions.length; i++) {
        if (!this.filteredOptions[i]?.disabled) return i;
      }
      return -1;
    },

    prevEnabledIndex(start: number): number {
      for (let i = start; i >= 0; i--) {
        if (!this.filteredOptions[i]?.disabled) return i;
      }
      return -1;
    },
  };
};

export const selectDropdownMeta: AlpineComponentMeta<SelectDropdownProps> = {
  name: 'selectDropdown',
  component: selectDropdown,
};
