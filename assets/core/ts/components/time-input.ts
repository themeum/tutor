import { __ } from '@wordpress/i18n';
import dayjs from 'dayjs';

import { type FormControlMethods, type ValidationRules } from '@Core/ts/components/form';
import { popover, type PopoverProps } from '@Core/ts/components/popover';
import { DateFormats } from '@Core/ts/date-formats';
import { type AlpineComponentMeta } from '@Core/ts/types';

export interface TimeInputProps extends PopoverProps {
  value?: string;
  defaultValue?: string;
  interval?: number;
  placeholder?: string;
  disabled?: boolean;
  clearable?: boolean;
  name?: string;
  required?: boolean | string;
  onChange?: (value: string) => void;
}

interface TimeInputState {
  highlightedIndex: number;
  value: string;
  options: string[];
}

const defaultProps: Required<
  Pick<TimeInputProps, 'interval' | 'placeholder' | 'disabled' | 'clearable' | 'name' | 'required'>
> = {
  interval: 30,
  placeholder: __('Select time', 'tutor'),
  disabled: false,
  clearable: true,
  name: '',
  required: false,
};

const buildTimeOptions = (interval: number): string[] => {
  const safeInterval = Number.isFinite(interval) && interval > 0 ? interval : defaultProps.interval;
  const start = dayjs().hour(0).minute(0);
  const end = dayjs().hour(23).minute(59);

  const options: string[] = [];
  let current = start;
  while (current.isBefore(end) || current.isSame(end, 'minute')) {
    options.push(current.format(DateFormats.hoursMinutes));
    current = current.add(safeInterval, 'minute');
  }
  return options;
};

const normalizeTimeValue = (value: string): string => {
  const raw = String(value || '')
    .replace(/\u00a0/g, ' ')
    .trim();

  const match = raw.match(/^(\d{1,2}):(\d{2})\s*([AaPp][Mm])$/);
  if (match) {
    const hour = String(Math.min(Math.max(parseInt(match[1], 10), 1), 12)).padStart(2, '0');
    const minute = match[2];
    const meridiem = match[3].toUpperCase();
    return `${hour}:${minute} ${meridiem}`;
  }

  return raw.toUpperCase().replace(/\s+/g, ' ');
};

export const timeInput = (props: TimeInputProps = {}) => {
  const popoverInstance = popover({
    placement: props.placement || 'bottom-start',
    offset: props.offset ?? 4,
    onShow: props.onShow,
    onHide: props.onHide,
  });

  const state: TimeInputState = {
    highlightedIndex: -1,
    value: String(props.value ?? props.defaultValue ?? ''),
    options: buildTimeOptions(props.interval ?? defaultProps.interval),
  };
  const popoverUpdatePosition = popoverInstance.updatePosition;
  const popoverInit = popoverInstance.init;
  const popoverDestroy = popoverInstance.destroy;

  return {
    ...popoverInstance,
    ...state,
    interval: props.interval ?? defaultProps.interval,
    placeholder: props.placeholder ?? defaultProps.placeholder,
    disabled: props.disabled ?? defaultProps.disabled,
    clearable: props.clearable ?? defaultProps.clearable,
    name: props.name ?? defaultProps.name,
    required: props.required ?? defaultProps.required,
    onChange: props.onChange,

    init() {
      popoverInit.call(this);
      const matchingOption = this.getMatchingOption(this.value);
      if (matchingOption) {
        this.value = matchingOption;
      }
      this.setupFormIntegration();
      this.syncHiddenInput();
    },

    destroy() {
      popoverDestroy.call(this);
    },

    show() {
      this.open = true;

      const afterShow = () => {
        this.updatePosition();
        if (props.onShow) {
          props.onShow();
        }
      };

      const component = this as unknown as { $nextTick?: (callback: () => void) => void };
      if (component.$nextTick) {
        component.$nextTick(afterShow);
      } else {
        requestAnimationFrame(afterShow);
      }
    },

    hide() {
      this.open = false;
      if (props.onHide) {
        props.onHide();
      }
    },

    updatePosition() {
      this.syncPopoverWidth();
      popoverUpdatePosition.call(this);
    },

    get hasValue(): boolean {
      return this.value !== '';
    },

    get displayValue(): string {
      return this.value || this.placeholder;
    },

    get canClear(): boolean {
      return this.clearable && this.hasValue && !this.disabled;
    },

    openDropdown() {
      if (this.disabled) return;
      this.show();
      this.syncHighlightedIndex();
      this.scrollToHighlighted();
    },

    closeDropdown() {
      this.hide();
      this.highlightedIndex = -1;
    },

    toggleDropdown() {
      if (this.disabled) return;
      if (this.open) {
        this.closeDropdown();
      } else {
        this.openDropdown();
      }
    },

    syncHighlightedIndex() {
      const selectedIndex = this.getSelectedIndex();
      this.highlightedIndex = selectedIndex >= 0 ? selectedIndex : 0;
    },

    getSelectedIndex(): number {
      const matchingOption = this.getMatchingOption(this.value);
      if (!matchingOption) return -1;
      return this.options.findIndex((option) => option === matchingOption);
    },

    getMatchingOption(value: string): string | null {
      const normalizedValue = normalizeTimeValue(value);
      if (!normalizedValue) return null;

      const option = this.options.find((item) => normalizeTimeValue(item) === normalizedValue);
      return option || null;
    },

    selectOption(option: string) {
      this.value = option;
      this.syncHiddenInput();
      this.syncFormValue();
      if (this.onChange) {
        this.onChange(option);
      }
      this.closeDropdown();
    },

    clearValue() {
      if (!this.canClear) return;
      this.value = '';
      this.syncHiddenInput();
      this.syncFormValue();
      if (this.onChange) {
        this.onChange('');
      }
    },

    onInputChange(event: Event) {
      if (this.disabled) return;
      const target = event.target as HTMLInputElement;
      this.value = target.value;

      const matchingOption = this.getMatchingOption(this.value);
      if (matchingOption) {
        this.value = matchingOption;
        this.highlightedIndex = this.options.findIndex((option) => option === matchingOption);
      } else {
        this.highlightedIndex = -1;
      }

      this.syncHiddenInput();
      this.syncFormValue();

      if (this.open && this.highlightedIndex >= 0) {
        this.scrollToHighlighted();
      }

      if (this.onChange) {
        this.onChange(this.value);
      }
    },

    onInputKeydown(event: KeyboardEvent) {
      if (this.disabled) return;

      if (event.key === 'Enter') {
        event.preventDefault();
        if (this.open) {
          const highlightedOption = this.options[this.highlightedIndex];
          if (highlightedOption) {
            this.selectOption(highlightedOption);
          } else {
            this.closeDropdown();
          }
        } else {
          this.openDropdown();
        }
        return;
      }

      if (event.key === 'Escape' && this.open) {
        event.preventDefault();
        this.closeDropdown();
        return;
      }

      if (event.key === 'Tab') {
        this.closeDropdown();
        return;
      }

      if (event.key === 'ArrowDown') {
        event.preventDefault();
        if (!this.open) {
          this.openDropdown();
        } else {
          this.moveHighlight(1);
        }
        return;
      }

      if (event.key === 'ArrowUp') {
        event.preventDefault();
        if (!this.open) {
          this.openDropdown();
        } else {
          this.moveHighlight(-1);
        }
      }
    },

    onListKeydown(event: KeyboardEvent) {
      if (this.disabled) return;

      if (event.key === 'ArrowDown') {
        event.preventDefault();
        this.moveHighlight(1);
        return;
      }

      if (event.key === 'ArrowUp') {
        event.preventDefault();
        this.moveHighlight(-1);
        return;
      }

      if (event.key === 'Enter') {
        event.preventDefault();
        const highlightedOption = this.options[this.highlightedIndex];
        if (highlightedOption) {
          this.selectOption(highlightedOption);
        }
      }
    },

    moveHighlight(direction: 1 | -1) {
      if (this.options.length === 0) return;

      let nextIndex = this.highlightedIndex;
      if (nextIndex < 0) {
        nextIndex = direction === 1 ? 0 : this.options.length - 1;
      } else {
        nextIndex += direction;
      }

      if (nextIndex < 0) {
        nextIndex = 0;
      } else if (nextIndex >= this.options.length) {
        nextIndex = this.options.length - 1;
      }

      this.highlightedIndex = nextIndex;
      this.scrollToHighlighted();
    },

    scrollToHighlighted() {
      const component = this as unknown as {
        $nextTick?: (callback: () => void) => void;
        $refs: Record<string, HTMLElement>;
      };
      const scroll = () => {
        const content = component.$refs.content;
        const option = content?.querySelector(`[data-option-index="${this.highlightedIndex}"]`) as HTMLElement | null;
        option?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
      };

      if (component.$nextTick) {
        component.$nextTick(scroll);
      } else {
        setTimeout(scroll, 0);
      }
    },

    syncPopoverWidth() {
      const component = this as unknown as { $refs: { trigger?: HTMLElement; content?: HTMLElement } };
      const trigger = component.$refs.trigger;
      const content = component.$refs.content;
      if (!trigger || !content) return;

      const triggerRect = trigger.getBoundingClientRect();
      content.style.width = `${triggerRect.width}px`;
      content.style.minWidth = `${triggerRect.width}px`;
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

    syncFormValue() {
      if (!this.name) return;
      const $el = (this as unknown as { $el: HTMLElement }).$el;
      const formElement = $el.closest('form[x-data*="tutorForm"], form[x-data*="form("]') as HTMLElement | null;
      if (!formElement) return;

      const alpineData = window.Alpine?.$data(formElement) as FormControlMethods | undefined;
      if (alpineData && typeof alpineData.setValue === 'function') {
        alpineData.setValue(this.name, this.value, { shouldValidate: true });
      }
    },

    setupFormIntegration() {
      if (!this.name) return;
      const $el = (this as unknown as { $el: HTMLElement }).$el;
      const formElement = $el.closest('form[x-data*="tutorForm"], form[x-data*="form("]') as HTMLElement | null;

      if (!formElement) return;

      try {
        const alpineData = window.Alpine?.$data(formElement) as
          | (FormControlMethods & { values: Record<string, string> })
          | undefined;
        if (!alpineData || typeof alpineData.register !== 'function') {
          return;
        }

        const rules: ValidationRules = {
          numberOnly: false,
          validTime: true,
        };

        if (this.required) {
          rules.required = this.required;
        }

        alpineData.register(this.name, rules);

        const externalValue = alpineData.values?.[this.name];
        if (externalValue !== undefined && externalValue !== null && externalValue !== '') {
          const matchingOption = this.getMatchingOption(String(externalValue));
          this.value = matchingOption || String(externalValue);
          this.syncHiddenInput();
        } else {
          alpineData.setValue(this.name, this.value, { shouldValidate: false });
        }

        const component = this as unknown as { $watch?: (path: string, cb: (value: unknown) => void) => void };
        component.$watch?.(`values['${this.name}']`, (newValue: unknown) => {
          const normalized = newValue === null || newValue === undefined ? '' : String(newValue);
          const matchingOption = this.getMatchingOption(normalized);
          const nextValue = matchingOption || normalized;

          if (nextValue !== this.value) {
            this.value = nextValue;
            this.syncHiddenInput();
            this.highlightedIndex = this.getSelectedIndex();
            if (this.open && this.highlightedIndex >= 0) {
              this.scrollToHighlighted();
            }
          }
        });
      } catch (error) {
        // eslint-disable-next-line no-console
        console.warn('timeInput form integration failed', error);
      }
    },
  };
};

export const timeInputMeta: AlpineComponentMeta<TimeInputProps> = {
  name: 'timeInput',
  component: timeInput,
};
