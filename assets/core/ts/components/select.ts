/**
 * Tutor Select Component
 *
 * @package Tutor\Core
 * @since 4.0.0
 */

import { type AlpineComponentMeta } from '@Core/ts/types';
import { type FormControlMethods } from './form';

export interface SelectOption {
  label: string;
  value: string | number;
  disabled?: boolean;
  icon?: string;
  description?: string;
  group?: string;
}

export interface SelectGroup {
  label: string;
  options: SelectOption[];
}

export interface SelectProps {
  // Data
  options?: SelectOption[];
  groups?: SelectGroup[];
  value?: string | number | (string | number)[];
  defaultValue?: string | number | (string | number)[];

  // Multi-select
  multiple?: boolean;
  maxSelections?: number;

  // Behavior
  searchable?: boolean;
  clearable?: boolean;
  disabled?: boolean;
  loading?: boolean;
  closeOnSelect?: boolean;

  // Display
  placeholder?: string;
  searchPlaceholder?: string;
  emptyMessage?: string;
  loadingMessage?: string;
  maxHeight?: number;

  // Form integration
  name?: string;
  required?: boolean | string;

  // Callbacks
  onChange?: (value: string | number | (string | number)[]) => void;
  onSearch?: (query: string) => void | Promise<SelectOption[]>;
  onOpen?: () => void;
  onClose?: () => void;
}

interface SelectState {
  isOpen: boolean;
  searchQuery: string;
  highlightedIndex: number;
  selectedValues: Set<string | number>;
  isLoading: boolean;
  asyncOptions: SelectOption[];
  dropdownPosition: 'top' | 'bottom';
  isFocused: boolean;
}

export const select = (props: SelectProps = {}) => {
  const state: SelectState = {
    isOpen: false,
    searchQuery: '',
    highlightedIndex: -1,
    selectedValues: new Set(),
    isLoading: false,
    asyncOptions: [],
    dropdownPosition: 'bottom',
    isFocused: false,
  };

  return {
    // Props with defaults
    options: props.options || [],
    groups: props.groups || [],
    multiple: props.multiple || false,
    searchable: props.searchable || false,
    clearable: props.clearable || false,
    disabled: props.disabled || false,
    loading: props.loading || false,
    closeOnSelect: props.closeOnSelect ?? !props.multiple,
    placeholder: props.placeholder || 'Select...',
    searchPlaceholder: props.searchPlaceholder || 'Search...',
    emptyMessage: props.emptyMessage || 'No options found',
    loadingMessage: props.loadingMessage || 'Loading...',
    maxHeight: props.maxHeight || 280,
    name: props.name || '',
    required: props.required || false,
    maxSelections: props.maxSelections,

    // State
    ...state,
    _boundReposition: null as (() => void) | null,

    // Computed
    get allOptions(): SelectOption[] {
      if (this.groups.length > 0) {
        return this.groups.flatMap((g) => g.options);
      }
      return [...this.options, ...this.asyncOptions];
    },

    get filteredOptions(): SelectOption[] {
      const query = this.searchQuery.toLowerCase().trim();
      if (!query) return this.allOptions;

      return this.allOptions.filter(
        (opt) => opt.label.toLowerCase().includes(query) || opt.description?.toLowerCase().includes(query),
      );
    },

    get filteredGroups(): SelectGroup[] {
      if (this.groups.length === 0) return [];

      const query = this.searchQuery.toLowerCase().trim();
      if (!query) return this.groups;

      return this.groups
        .map((group) => ({
          ...group,
          options: group.options.filter(
            (opt) => opt.label.toLowerCase().includes(query) || opt.description?.toLowerCase().includes(query),
          ),
        }))
        .filter((group) => group.options.length > 0);
    },

    get hasGroups(): boolean {
      return this.groups.length > 0;
    },

    get selectedOptions(): SelectOption[] {
      return this.allOptions.filter((opt) => this.selectedValues.has(opt.value));
    },

    get displayValue(): string {
      if (this.selectedValues.size === 0) return this.placeholder;

      if (this.multiple) {
        const count = this.selectedValues.size;
        if (count === 1) {
          const opt = this.selectedOptions[0];
          return opt ? opt.label : this.placeholder;
        }
        return `${count} selected`;
      }

      const opt = this.selectedOptions[0];
      return opt ? opt.label : this.placeholder;
    },

    get canClear(): boolean {
      return this.clearable && this.selectedValues.size > 0 && !this.disabled;
    },

    get isMaxSelectionsReached(): boolean {
      return this.multiple && this.maxSelections !== undefined && this.selectedValues.size >= this.maxSelections;
    },

    // Lifecycle
    init() {
      this.initializeValue();
      this.setupFormIntegration();
      this.setupKeyboardNavigation();
      this.syncHiddenInput();
      this._boundReposition = this.calculateDropdownPosition.bind(this);
    },

    destroy() {
      // Cleanup
      if (this._boundReposition) {
        window.removeEventListener('resize', this._boundReposition);
        window.removeEventListener('scroll', this._boundReposition);
      }
    },

    // Initialization
    initializeValue() {
      const initialValue = props.value ?? props.defaultValue;
      if (initialValue === undefined || initialValue === null) return;

      if (Array.isArray(initialValue)) {
        this.selectedValues = new Set(initialValue);
      } else {
        this.selectedValues = new Set([initialValue]);
      }
    },

    setupFormIntegration() {
      if (!this.name) return;

      const $el = (this as unknown as { $el: HTMLElement }).$el;
      const formElement = $el.closest('form[x-data*="tutorForm"], form[x-data*="form("]') as HTMLElement;

      if (formElement) {
        try {
          const alpine = window.Alpine;
          // eslint-disable-next-line @typescript-eslint/no-explicit-any
          const alpineData = alpine?.$data(formElement) as FormControlMethods & { values: Record<string, any> };

          if (alpineData && typeof alpineData.register === 'function') {
            // Build validation rules
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            const rules: Record<string, any> = {};
            if (this.required) {
              rules.required = this.required;
            }

            // Register with form
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            alpineData.register(this.name, rules as any);

            // If form already has a value for this field (e.g. from reset() called earlier), sync it TO this component
            const formValue = alpineData.values?.[this.name];
            if (formValue !== undefined && formValue !== null && formValue !== '') {
              if (Array.isArray(formValue)) {
                this.selectedValues = new Set(formValue);
              } else {
                this.selectedValues = new Set([formValue]);
              }
              this.syncHiddenInput();
            } else {
              // Otherwise, set form value from component's initial state
              const currentValue = this.getCurrentValue();
              alpineData.setValue(this.name, currentValue ?? '', { shouldValidate: false });
            }

            // Watch for external form changes
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            const component = this as unknown as { $watch: (path: string, cb: (val: any) => void) => void };
            if (component.$watch) {
              // eslint-disable-next-line @typescript-eslint/no-explicit-any
              component.$watch(`values.${this.name}`, (newVal: any) => {
                const current = this.getCurrentValue();
                const isSame = Array.isArray(newVal)
                  ? JSON.stringify([...newVal].sort()) ===
                    JSON.stringify(Array.isArray(current) ? [...current].sort() : [current])
                  : String(newVal) === String(current);
                if (!isSame) {
                  if (Array.isArray(newVal)) {
                    this.selectedValues = new Set(newVal);
                  } else if (newVal === null || newVal === undefined || newVal === '') {
                    this.selectedValues.clear();
                  } else {
                    this.selectedValues = new Set([newVal]);
                  }
                  this.syncHiddenInput();
                }
              });
            }
          }
        } catch (error) {
          // eslint-disable-next-line no-console
          console.warn('Failed to integrate with form:', error);
        }
      }
    },

    setupKeyboardNavigation() {
      const $el = (this as unknown as { $el: HTMLElement }).$el;

      $el.addEventListener('keydown', (e: KeyboardEvent) => {
        if (this.disabled) return;
        this.handleKeyDown(e);
      });
    },

    // Actions
    async open() {
      if (this.disabled || this.isOpen) return;

      this.isOpen = true;
      this.isFocused = true;

      // Call onOpen callback
      props.onOpen?.();

      // Wait for dropdown to render, then calculate position
      await this.nextTick();
      this.calculateDropdownPosition();

      // Listen for resize/scroll to update position
      if (this._boundReposition) {
        window.addEventListener('resize', this._boundReposition);
        window.addEventListener('scroll', this._boundReposition, { passive: true });
      }

      // Focus search input if searchable
      if (this.searchable) {
        this.focusSearchInput();
      }

      // Scroll to selected option
      this.scrollToSelected();
    },

    close() {
      if (!this.isOpen) return;

      this.isOpen = false;
      this.searchQuery = '';
      this.highlightedIndex = -1;

      props.onClose?.();

      if (this._boundReposition) {
        window.removeEventListener('resize', this._boundReposition);
        window.removeEventListener('scroll', this._boundReposition);
      }
    },

    toggle() {
      if (this.isOpen) {
        this.close();
      } else {
        this.open();
      }
    },

    async selectOption(option: SelectOption) {
      if (option.disabled) return;

      if (this.multiple) {
        this.toggleMultipleSelection(option);
      } else {
        this.setSingleSelection(option);
      }

      this.notifyChange();
      this.syncHiddenInput();
      this.updateFormValue();

      if (this.closeOnSelect) {
        this.close();
      }
    },

    toggleMultipleSelection(option: SelectOption) {
      if (this.selectedValues.has(option.value)) {
        this.selectedValues.delete(option.value);
      } else {
        if (this.isMaxSelectionsReached) return;
        this.selectedValues.add(option.value);
      }
    },

    setSingleSelection(option: SelectOption) {
      this.selectedValues.clear();
      this.selectedValues.add(option.value);
    },

    deselectOption(option: SelectOption, event?: Event) {
      event?.stopPropagation();

      this.selectedValues.delete(option.value);
      this.notifyChange();
      this.syncHiddenInput();
      this.updateFormValue();
    },

    clear(event?: Event) {
      event?.stopPropagation();

      this.selectedValues.clear();
      this.notifyChange();
      this.syncHiddenInput();
      this.updateFormValue();
    },

    async handleSearch(query: string) {
      this.searchQuery = query;
      this.highlightedIndex = 0;

      if (props.onSearch) {
        this.isLoading = true;
        try {
          const result = props.onSearch(query);
          if (result instanceof Promise) {
            const asyncOptions = await result;
            this.asyncOptions = asyncOptions || [];
          }
        } finally {
          this.isLoading = false;
        }
      }
    },

    // Keyboard Navigation
    handleKeyDown(event: KeyboardEvent) {
      const handlers: Record<string, () => void> = {
        Enter: () => this.handleEnterKey(event),
        ' ': () => this.handleSpaceKey(event),
        Escape: () => this.close(),
        ArrowDown: () => this.handleArrowDown(event),
        ArrowUp: () => this.handleArrowUp(event),
        Home: () => this.handleHomeKey(event),
        End: () => this.handleEndKey(event),
        Tab: () => this.handleTabKey(event),
        Backspace: () => this.handleBackspaceKey(event),
      };

      const handler = handlers[event.key];
      if (handler) {
        handler();
      }
    },

    handleEnterKey(event: Event) {
      event.preventDefault();

      if (!this.isOpen) {
        this.open();
        return;
      }

      if (this.highlightedIndex >= 0) {
        const option = this.filteredOptions[this.highlightedIndex];
        if (option) {
          this.selectOption(option);
        }
      }
    },

    handleSpaceKey(event: Event) {
      // Only toggle if not in search input
      const target = event.target as HTMLElement;
      if (target.tagName === 'INPUT') return;

      event.preventDefault();
      if (!this.isOpen) {
        this.open();
      }
    },

    handleArrowDown(event: Event) {
      event.preventDefault();

      if (!this.isOpen) {
        this.open();
        return;
      }

      this.moveHighlight(1);
    },

    handleArrowUp(event: Event) {
      event.preventDefault();

      if (!this.isOpen) {
        this.open();
        return;
      }

      this.moveHighlight(-1);
    },

    handleHomeKey(event: Event) {
      event.preventDefault();
      this.highlightedIndex = this.findNextEnabledIndex(0, 1);
      this.scrollToHighlighted();
    },

    handleEndKey(event: Event) {
      event.preventDefault();
      this.highlightedIndex = this.findNextEnabledIndex(this.filteredOptions.length - 1, -1);
      this.scrollToHighlighted();
    },

    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    handleTabKey(event: Event) {
      if (this.isOpen) {
        this.close();
      }
    },

    handleBackspaceKey(event: Event) {
      // Remove last selected item if search is empty
      const target = event.target as HTMLInputElement;
      if (target.tagName === 'INPUT' && target.value === '' && this.multiple) {
        const lastOption = this.selectedOptions[this.selectedOptions.length - 1];
        if (lastOption) {
          this.deselectOption(lastOption);
        }
      }
    },

    moveHighlight(direction: 1 | -1) {
      const options = this.filteredOptions;
      if (options.length === 0) return;

      let newIndex = this.highlightedIndex + direction;
      newIndex = this.findNextEnabledIndex(newIndex, direction);

      if (newIndex >= 0 && newIndex < options.length) {
        this.highlightedIndex = newIndex;
        this.scrollToHighlighted();
      }
    },

    findNextEnabledIndex(startIndex: number, direction: 1 | -1): number {
      const options = this.filteredOptions;
      let index = startIndex;

      while (index >= 0 && index < options.length) {
        if (!options[index]?.disabled) {
          return index;
        }
        index += direction;
      }

      return -1;
    },

    // Helpers
    isSelected(option: SelectOption): boolean {
      return this.selectedValues.has(option.value);
    },

    isHighlighted(index: number): boolean {
      return this.highlightedIndex === index;
    },

    getCurrentValue(): string | number | (string | number)[] | null {
      if (this.selectedValues.size === 0) return null;

      if (this.multiple) {
        return Array.from(this.selectedValues);
      }

      return Array.from(this.selectedValues)[0];
    },

    notifyChange() {
      const value = this.getCurrentValue();
      if (value !== null) {
        props.onChange?.(value);
      }
    },

    updateFormValue() {
      if (!this.name) return;

      const $el = (this as unknown as { $el: HTMLElement }).$el;
      const formElement = $el.closest('form[x-data*="tutorForm"], form[x-data*="form("]') as HTMLElement;

      if (formElement) {
        try {
          // eslint-disable-next-line @typescript-eslint/no-explicit-any
          const alpineData = window.Alpine?.$data(formElement) as any;

          if (alpineData && typeof alpineData.setValue === 'function') {
            const value = this.getCurrentValue();
            // Always update, even if null (for required validation)
            alpineData.setValue(this.name, value ?? '', {
              shouldValidate: true,
              shouldTouch: true,
              shouldDirty: true,
            });
          }
        } catch (error) {
          // eslint-disable-next-line no-console
          console.warn('Failed to update form value:', error);
        }
      }
    },

    syncHiddenInput() {
      if (!this.name) return;

      // Always get the root select container (not the clicked element)
      let $el = (this as unknown as { $el: HTMLElement }).$el;

      // If $el is not the root select container, find it
      if (!$el.classList.contains('tutor-select')) {
        const rootSelect = $el.closest('.tutor-select') as HTMLElement;
        if (rootSelect) {
          $el = rootSelect;
        }
      }

      const value = this.getCurrentValue();

      if (this.multiple && Array.isArray(value)) {
        // Remove ALL existing hidden inputs for this field
        $el.querySelectorAll(`input[type="hidden"][name^="${this.name}"]`).forEach((input) => input.remove());

        // Create hidden input for each value
        value.forEach((val, index) => {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = `${this.name}[${index}]`;
          input.value = String(val);
          $el.appendChild(input);
        });
      } else {
        // Single value - remove all existing first, then create one
        $el.querySelectorAll(`input[type="hidden"][name="${this.name}"]`).forEach((input) => input.remove());

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = this.name;
        input.value = value !== null ? String(value) : '';
        $el.appendChild(input);
      }
    },

    calculateDropdownPosition() {
      const $el = (this as unknown as { $el: HTMLElement }).$el;

      // Find the root select container
      let rootEl = $el;
      if (!$el.classList.contains('tutor-select')) {
        const root = $el.closest('.tutor-select') as HTMLElement;
        if (root) rootEl = root;
      }

      const trigger = rootEl.querySelector('[data-select-trigger]') as HTMLElement;
      if (!trigger) {
        return;
      }

      const rect = trigger.getBoundingClientRect();
      const viewportHeight = window.innerHeight;
      const spaceBelow = viewportHeight - rect.bottom;
      const spaceAbove = rect.top;

      // Calculate estimated dropdown height
      // Account for: options + search box (if searchable) + padding
      const optionHeight = 44; // Height per option
      const searchBoxHeight = this.searchable ? 60 : 0;
      const padding = 16;
      const optionsCount = this.filteredOptions.length || this.allOptions.length;
      const estimatedHeight = Math.min(this.maxHeight, optionsCount * optionHeight + searchBoxHeight + padding);

      // Add some buffer space (8px)
      const buffer = 8;

      // Prefer bottom, but flip to top if not enough space below AND more space above
      if (spaceBelow < estimatedHeight + buffer && spaceAbove > spaceBelow) {
        this.dropdownPosition = 'top';
      } else {
        this.dropdownPosition = 'bottom';
      }
    },

    async nextTick() {
      return new Promise((resolve) => {
        const component = this as unknown as { $nextTick?: (cb: () => void) => void };
        if (component.$nextTick) {
          component.$nextTick(() => resolve(undefined));
        } else {
          setTimeout(() => resolve(undefined), 0);
        }
      });
    },

    focusSearchInput() {
      const $el = (this as unknown as { $el: HTMLElement }).$el;
      const input = $el.querySelector('[data-select-search]') as HTMLInputElement;
      if (input) {
        input.focus();
      }
    },

    scrollToSelected() {
      const selectedIndex = this.filteredOptions.findIndex((opt) => this.isSelected(opt));
      if (selectedIndex >= 0) {
        this.highlightedIndex = selectedIndex;
        this.scrollToHighlighted();
      }
    },

    scrollToHighlighted() {
      this.nextTick().then(() => {
        const $el = (this as unknown as { $el: HTMLElement }).$el;
        const menu = $el.querySelector('[data-select-menu]');
        const options = menu?.querySelectorAll('[data-select-option]');

        if (options && this.highlightedIndex >= 0 && this.highlightedIndex < options.length) {
          const option = options[this.highlightedIndex] as HTMLElement;
          option?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
      });
    },
  };
};

export const selectMeta: AlpineComponentMeta<SelectProps> = {
  name: 'select',
  component: select,
};
