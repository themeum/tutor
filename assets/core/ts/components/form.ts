import { __, sprintf } from '@wordpress/i18n';

import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { type AlpineComponentMeta } from '@Core/ts/types';
import { parseNumberOnly } from '@TutorShared/utils/util';

interface FormControlConfig {
  mode?: 'onChange' | 'onBlur' | 'onSubmit';
  shouldFocusError?: boolean;
  shouldScrollToError?: boolean;
  defaultValues?: Record<string, unknown>;
}

interface FieldConfig {
  name: string;
  rules?: ValidationRules;
  defaultValue?: unknown;
  ref?: HTMLInputElement;
}

interface ValidationRules {
  required?: boolean | string;
  minLength?: number | { value: number; message: string };
  maxLength?: number | { value: number; message: string };
  min?: number | { value: number; message: string };
  max?: number | { value: number; message: string };
  pattern?: RegExp | { value: RegExp; message: string };
  numberOnly: boolean | { allowNegative?: boolean; whole?: boolean };
  validate?: (value: unknown) => boolean | string | Promise<boolean | string>;
}

export interface FieldError {
  type: string;
  message: string;
}

interface SetValueOptions {
  shouldValidate?: boolean;
  shouldTouch?: boolean;
  shouldDirty?: boolean;
}

interface FocusOptions {
  shouldSelect?: boolean;
}

export interface FormState {
  values: Record<string, unknown>;
  errors: Record<string, FieldError>;
  touchedFields: Record<string, boolean>;
  dirtyFields: Record<string, boolean>;
  isValid: boolean;
  isDirty: boolean;
  isSubmitting: boolean;
  isValidating: boolean;
}

export interface FormControlMethods {
  register(name: string, rules?: ValidationRules): Record<string, unknown>;
  watch(name?: string): unknown;
  setValue(name: string, value: unknown, options?: SetValueOptions): void;
  getValue(name: string): unknown;
  setFocus(name: string, options?: FocusOptions): void;
  trigger(name?: string | string[]): Promise<boolean>;
  clearErrors(name?: string | string[]): void;
  setError(name: string, error: FieldError): void;
  reset(values?: Record<string, unknown>): void;
  handleSubmit(
    onValid: (data: Record<string, unknown>) => void,
    onInvalid?: (errors: Record<string, FieldError>) => void,
  ): (event: Event) => void;
  getFormState(): FormState;
  isFieldVisible(element: HTMLElement): boolean;
}

const ValidationHelpers = {
  validateRequired(name: string, value: unknown, rule?: boolean | string): FieldError | null {
    if (!rule) return null;

    const message = typeof rule === 'string' ? rule : __('This field is required', 'tutor');
    const isEmpty = !value || (typeof value === 'string' && value.trim() === '');

    return isEmpty ? { type: 'required', message } : null;
  },

  validateMinLength(value: string, rule: number | { value: number; message: string }): FieldError | null {
    if (!value) return null;

    const minLength = typeof rule === 'number' ? rule : rule.value;
    const message = typeof rule === 'object' ? rule.message : sprintf(__('Minimum length is %s', 'tutor'), minLength);

    return value.length < minLength ? { type: 'minLength', message } : null;
  },

  validateMaxLength(value: string, rule: number | { value: number; message: string }): FieldError | null {
    if (!value) return null;

    const maxLength = typeof rule === 'number' ? rule : rule.value;
    const message = typeof rule === 'object' ? rule.message : sprintf(__('Maximum length is %s', 'tutor'), maxLength);

    return value.length > maxLength ? { type: 'maxLength', message } : null;
  },

  validateMin(value: number, rule: number | { value: number; message: string }): FieldError | null {
    const min = typeof rule === 'number' ? rule : rule.value;
    const message = typeof rule === 'object' ? rule.message : sprintf(__('Minimum value is %s', 'tutor'), min);

    return value < min ? { type: 'min', message } : null;
  },

  validateMax(value: number, rule: number | { value: number; message: string }): FieldError | null {
    const max = typeof rule === 'number' ? rule : rule.value;
    const message = typeof rule === 'object' ? rule.message : sprintf(__('Maximum value is %s', 'tutor'), max);

    return value > max ? { type: 'max', message } : null;
  },

  validatePattern(value: string, rule: RegExp | { value: RegExp; message: string }): FieldError | null {
    const pattern = rule instanceof RegExp ? rule : rule.value;
    const message = typeof rule === 'object' && 'message' in rule ? rule.message : __('Invalid format', 'tutor');

    return !pattern.test(value) ? { type: 'pattern', message } : null;
  },

  async validateCustom(
    value: unknown,
    validate: (value: unknown) => boolean | string | Promise<boolean | string>,
  ): Promise<FieldError | null> {
    try {
      const result = await validate(value);
      if (result === true) return null;

      const message = typeof result === 'string' ? result : __('Validation failed', 'tutor');
      return { type: 'validate', message };
    } catch {
      return { type: 'validate', message: __('Validation error', 'tutor') };
    }
  },
};

async function validateFieldValue(name: string, value: unknown, rules?: ValidationRules): Promise<FieldError | null> {
  if (!rules) return null;

  // Required validation
  const requiredError = ValidationHelpers.validateRequired(name, value, rules.required);
  if (requiredError) return requiredError;

  const stringValue = String(value || '');
  const numericValue = typeof value === 'number' ? value : parseFloat(stringValue);

  // String length validations
  if (rules.minLength) {
    const error = ValidationHelpers.validateMinLength(stringValue, rules.minLength);
    if (error) return error;
  }

  if (rules.maxLength) {
    const error = ValidationHelpers.validateMaxLength(stringValue, rules.maxLength);
    if (error) return error;
  }

  // Numeric validations
  if (rules.min && !isNaN(numericValue)) {
    const error = ValidationHelpers.validateMin(numericValue, rules.min);
    if (error) return error;
  }

  if (rules.max && !isNaN(numericValue)) {
    const error = ValidationHelpers.validateMax(numericValue, rules.max);
    if (error) return error;
  }

  // Pattern validation
  if (rules.pattern && stringValue) {
    const error = ValidationHelpers.validatePattern(stringValue, rules.pattern);
    if (error) return error;
  }

  // Custom validation
  if (rules.validate) {
    const error = await ValidationHelpers.validateCustom(value, rules.validate);
    if (error) return error;
  }

  return null;
}

const DOMUtils = {
  isElementVisible(element: HTMLElement): boolean {
    const style = getComputedStyle(element);
    const rect = element.getBoundingClientRect();

    return (
      style.display !== 'none' &&
      style.visibility !== 'hidden' &&
      parseFloat(style.opacity) > 0 &&
      rect.width > 0 &&
      rect.height > 0
    );
  },

  focusElement(element: HTMLElement, options: { shouldSelect?: boolean; shouldScroll?: boolean }): void {
    const { shouldSelect = false, shouldScroll = true } = options;

    if (!this.isElementVisible(element)) return;

    element.focus();

    if (shouldSelect && element instanceof HTMLInputElement && element.select) {
      element.select();
    }

    if (shouldScroll) {
      element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  },

  updateElementValue(element: HTMLElement, value: unknown): void {
    if (element instanceof HTMLInputElement || element instanceof HTMLTextAreaElement) {
      element.value = String(value ?? '');
    }
  },

  setAriaInvalid(element: HTMLElement, isInvalid: boolean): void {
    if (isInvalid) {
      element.setAttribute('aria-invalid', 'true');
    } else {
      element.removeAttribute('aria-invalid');
    }
  },

  getFieldElement(form: HTMLElement, fieldName: string): HTMLElement | null {
    return form.querySelector(`[name="${fieldName}"]`);
  },
};

const FormDataUtils = {
  convertToFormData(values: Record<string, unknown>, method: string = 'POST'): FormData {
    const formData = new FormData();

    Object.entries(values).forEach(([key, value]) => {
      if (Array.isArray(value)) {
        this.appendArrayToFormData(formData, key, value);
      } else {
        this.appendValueToFormData(formData, key, value);
      }
    });

    formData.append('_method', method.toUpperCase());
    return formData;
  },

  serializeParams(params: Record<string, unknown>): Record<string, unknown> {
    return Object.entries(params).reduce(
      (acc, [key, value]) => ({
        ...acc,
        [key]: this.serializeValue(value),
      }),
      {},
    );
  },

  appendArrayToFormData(formData: FormData, key: string, array: unknown[]): void {
    array.forEach((item, index) => {
      const value = this.convertToFormDataValue(item);
      formData.append(`${key}[${index}]`, value);
    });
  },

  appendValueToFormData(formData: FormData, key: string, value: unknown): void {
    const formDataValue = this.convertToFormDataValue(value);
    formData.append(key, formDataValue);
  },

  convertToFormDataValue(value: unknown): string | Blob {
    if (value instanceof File || value instanceof Blob) return value;
    if (typeof value === 'string') return value;
    if (typeof value === 'boolean' || typeof value === 'number') return String(value);
    if (typeof value === 'object' && value !== null) return JSON.stringify(value);
    return String(value);
  },

  serializeValue(value: unknown): string | unknown {
    if (value === null || value === undefined) return 'null';
    if (typeof value === 'boolean') return value ? 'true' : 'false';
    return value;
  },
};

const DEFAULT_CONFIG: FormControlConfig = {
  mode: 'onBlur',
  shouldFocusError: true,
  shouldScrollToError: true,
};

interface AlpineComponent {
  $el: HTMLElement;
  $refs: Record<string, HTMLElement & { _x_model?: { set: (val: unknown) => void } }>;
  $data: (el: HTMLElement) => Record<string, unknown>;
}

export const form = (config: FormControlConfig & { id?: string } = {}) => {
  const { id: formId, defaultValues = {}, ...formConfig } = config;
  const mergedConfig = { ...DEFAULT_CONFIG, ...formConfig };

  const formInstance = {
    config: mergedConfig,
    formId,

    fields: {} as Record<string, FieldConfig>,
    values: { ...defaultValues },
    errors: {} as Record<string, FieldError>,
    touchedFields: {} as Record<string, boolean>,
    dirtyFields: {} as Record<string, boolean>,
    isValid: true,
    isSubmitting: false,
    isValidating: false,
    cleanup: undefined as (() => void) | undefined,
    lastIsDirty: false,

    init(): void {
      this.isValid = true;
      this.isSubmitting = false;
      this.isValidating = false;
      this.lastIsDirty = false;

      if (this.formId) {
        document.dispatchEvent(
          new CustomEvent(TUTOR_CUSTOM_EVENTS.FORM_REGISTER, {
            detail: { id: this.formId, instance: this as unknown as FormControlMethods },
          }),
        );
      }

      this.setupFormListeners();
      this.dispatchStateChange(); // Initial dispatch
    },

    destroy(): void {
      this.cleanup?.();
      this.clearAllState();
    },

    dispatchStateChange(): void {
      if (!this.formId) {
        return;
      }

      const isDirty = Object.values(this.dirtyFields).some(Boolean);

      if (isDirty === this.lastIsDirty) {
        return;
      }

      this.lastIsDirty = isDirty;

      document.dispatchEvent(
        new CustomEvent(TUTOR_CUSTOM_EVENTS.FORM_STATE_CHANGE, {
          detail: { id: this.formId, isDirty },
        }),
      );
    },

    register(name: string, rules?: ValidationRules): Record<string, unknown> {
      const component = this as unknown as AlpineComponent;
      this.fields[name] = {
        name,
        rules,
        defaultValue: this.values[name] || '',
        ref: component.$el as HTMLInputElement,
      };

      if (!(name in this.values)) {
        this.values[name] = this.fields[name].defaultValue;
      }

      return {
        name,
        'x-model': `values.${name}`,
        '@input': `handleFieldInput('${name}', $event.target.value)`,
        '@blur': `handleFieldBlur('${name}', $event.target.value)`,
        'x-ref': name,
        ':aria-invalid': `errors.${name} ? 'true' : 'false'`,
        ':class': `{
          'tutor-input-error': errors.${name},
          'tutor-input-touched': touchedFields.${name},
          'tutor-input-dirty': dirtyFields.${name}
        }`,
      };
    },

    handleFieldInput(name: string, value: unknown): void {
      const field = this.fields[name];
      const isNumber = field?.rules?.numberOnly;
      const allowNegative = typeof isNumber === 'object' && isNumber.allowNegative;
      const whole = typeof isNumber === 'object' && isNumber.whole;
      const parsedValue = isNumber ? parseNumberOnly(value as string, allowNegative, whole) : value;

      // Only mark as dirty if the value is different from the baseline
      const isActuallyChanged = String(parsedValue) !== String(field?.defaultValue ?? '');

      this.values[name] = parsedValue;
      this.dirtyFields[name] = isActuallyChanged;
      this.updateFieldRef(name);

      if (isNumber) {
        const component = this as unknown as AlpineComponent;
        const refs = component.$refs;
        if (refs[name]?._x_model) {
          refs[name]._x_model?.set(parsedValue);
        }
      }

      const shouldValidate = this.config.mode === 'onChange' || this.touchedFields[name];

      if (shouldValidate) {
        this.validateField(name, isNumber ? parsedValue : value);
      } else {
        this.dispatchStateChange();
      }
    },

    handleFieldBlur(name: string, value: unknown): void {
      this.touchedFields[name] = true;
      this.updateFieldRef(name);

      const shouldValidate = this.config.mode === 'onBlur' || this.touchedFields[name];

      if (shouldValidate) {
        this.validateField(name, value);
      } else {
        this.dispatchStateChange();
      }
    },

    watch(name?: string): unknown {
      return name ? this.values[name] : { ...this.values };
    },

    setValue(name: string, value: unknown, options: SetValueOptions = {}): void {
      const { shouldValidate = false, shouldTouch = false, shouldDirty = true } = options;

      this.values[name] = value;

      if (shouldTouch) this.touchedFields[name] = true;
      if (shouldDirty) {
        const field = this.fields[name];
        this.dirtyFields[name] = String(value) !== String(field?.defaultValue ?? '');
      }

      const fieldElement = this.fields[name]?.ref;
      if (fieldElement) {
        DOMUtils.updateElementValue(fieldElement, value);
      }

      if (shouldValidate) {
        this.validateField(name, value);
      } else {
        this.dispatchStateChange();
      }
    },

    getValue(name: string): unknown {
      return this.values[name];
    },

    setFocus(name: string, options: FocusOptions = {}): void {
      const field = this.fields[name];
      const fieldElement = field?.ref;

      if (fieldElement && DOMUtils.isElementVisible(fieldElement)) {
        DOMUtils.focusElement(fieldElement, {
          shouldSelect: options.shouldSelect,
          shouldScroll: this.config.shouldScrollToError,
        });
      }
    },

    async trigger(name?: string | string[]): Promise<boolean> {
      this.isValidating = true;

      try {
        if (typeof name === 'string') {
          return await this.validateSingleField(name);
        }

        if (Array.isArray(name)) {
          return await this.validateMultipleFields(name);
        }

        return await this.validateAllFields();
      } finally {
        this.isValidating = false;
        this.dispatchStateChange();
      }
    },

    async validateField(name: string, value: unknown): Promise<boolean> {
      const fieldConfig = this.fields[name];
      const error = await validateFieldValue(name, value, fieldConfig?.rules);

      if (error) {
        this.errors[name] = error;
      } else {
        delete this.errors[name];
      }

      this.updateAriaInvalidState(name);
      this.updateFormValidState();
      this.dispatchStateChange();

      return !error;
    },

    async validateSingleField(name: string): Promise<boolean> {
      this.touchedFields[name] = true;
      const value = this.values[name];
      return await this.validateField(name, value);
    },

    async validateMultipleFields(names: string[]): Promise<boolean> {
      let isValid = true;

      for (const fieldName of names) {
        this.touchedFields[fieldName] = true;
        const value = this.values[fieldName];
        const fieldValid = await this.validateField(fieldName, value);
        if (!fieldValid) isValid = false;
      }

      return isValid;
    },

    async validateAllFields(): Promise<boolean> {
      let isValid = true;

      for (const name of Object.keys(this.fields)) {
        const value = this.values[name];
        const fieldValid = await this.validateField(name, value);
        if (!fieldValid) isValid = false;
      }

      return isValid;
    },

    clearErrors(name?: string | string[]): void {
      if (typeof name === 'string') {
        this.clearSingleError(name);
      } else if (Array.isArray(name)) {
        name.forEach((fieldName) => this.clearSingleError(fieldName));
      } else {
        Object.keys(this.fields).forEach((fieldName) => this.clearSingleError(fieldName));
      }

      this.updateFormValidState();
      this.dispatchStateChange();
    },

    setError(name: string, error: FieldError): void {
      this.errors[name] = error;
      this.updateAriaInvalidState(name);
      this.updateFormValidState();
      this.dispatchStateChange();
    },

    reset(values?: Record<string, unknown>): void {
      if (values) {
        // Update reactive object props instead of replacing it to maintain bindings
        Object.keys(this.values).forEach((key) => delete this.values[key]);
        Object.assign(this.values, values);
      } else {
        const defaultValues = Object.keys(this.fields).reduce(
          (acc, name) => {
            acc[name] = this.fields[name].defaultValue;
            return acc;
          },
          {} as Record<string, unknown>,
        );
        Object.keys(this.values).forEach((key) => delete this.values[key]);
        Object.assign(this.values, defaultValues);
      }

      this.syncDOMWithState();
      this.errors = {};
      this.touchedFields = {};
      this.dirtyFields = {};
      this.isValid = true;
      this.isSubmitting = false;
      this.isValidating = false;
      this.dispatchStateChange();
    },

    handleSubmit(
      onValid: (data: Record<string, unknown>) => void,
      onInvalid?: (errors: Record<string, FieldError>) => void,
    ): (event: Event) => void {
      return async (event: Event) => {
        event.preventDefault();
        this.isSubmitting = true;

        try {
          const isValid = await this.validateAllFields();

          if (isValid) {
            onValid({ ...this.values });
          } else {
            onInvalid?.({ ...this.errors });

            if (this.config.shouldFocusError) {
              this.focusFirstError();
            }
          }
        } finally {
          this.isSubmitting = false;
          this.dispatchStateChange();
        }
      };
    },

    getFormState(): FormState {
      return {
        values: { ...this.values },
        errors: { ...this.errors },
        touchedFields: { ...this.touchedFields },
        dirtyFields: { ...this.dirtyFields },
        isValid: this.isValid,
        isDirty: Object.values(this.dirtyFields).some(Boolean),
        isSubmitting: this.isSubmitting,
        isValidating: this.isValidating,
      };
    },

    isFieldVisible(element: HTMLElement): boolean {
      return DOMUtils.isElementVisible(element);
    },

    getFormBindings() {
      return {
        novalidate: true,
      };
    },

    convertToFormData: FormDataUtils.convertToFormData.bind(FormDataUtils),
    serializeParams: FormDataUtils.serializeParams.bind(FormDataUtils),

    setupFormListeners(): void {
      const component = this as unknown as AlpineComponent;
      const formElement = component.$el;

      if (!formElement) {
        this.cleanup = () => {
          if (this.formId) {
            document.dispatchEvent(
              new CustomEvent(TUTOR_CUSTOM_EVENTS.FORM_UNREGISTER, {
                detail: { id: this.formId },
              }),
            );
          }
        };
        return;
      }

      const handleFormSubmit = (event: Event) => {
        event.preventDefault();
      };

      formElement.addEventListener('submit', handleFormSubmit);

      this.cleanup = () => {
        formElement.removeEventListener('submit', handleFormSubmit);
        if (this.formId) {
          document.dispatchEvent(
            new CustomEvent(TUTOR_CUSTOM_EVENTS.FORM_UNREGISTER, {
              detail: { id: this.formId },
            }),
          );
        }
      };
    },

    updateFieldRef(name: string): void {
      const component = this as unknown as AlpineComponent;
      const formElement = component.$el;
      if (!formElement) return;

      const element = DOMUtils.getFieldElement(formElement, name) as HTMLInputElement;
      const field = this.fields[name];

      if (element && field) {
        field.ref = element;
      }
    },

    clearSingleError(name: string): void {
      delete this.errors[name];
      this.updateAriaInvalidState(name);
    },

    updateAriaInvalidState(name: string): void {
      const fieldRef = this.fields[name]?.ref;
      if (!fieldRef) return;

      DOMUtils.setAriaInvalid(fieldRef, !!this.errors[name]);
    },

    updateFormValidState(): void {
      this.isValid = Object.keys(this.errors).length === 0;
    },

    focusFirstError(): void {
      const firstErrorField = Object.keys(this.errors)[0];
      if (firstErrorField) {
        this.setFocus(firstErrorField);
      }
    },

    syncDOMWithState(): void {
      for (const [name, value] of Object.entries(this.values)) {
        const fieldRef = this.fields[name]?.ref;
        if (fieldRef) {
          DOMUtils.updateElementValue(fieldRef, value);
        }
      }
    },

    clearAllState(): void {
      this.fields = {};
      this.values = {};
      this.errors = {};
      this.touchedFields = {};
      this.dirtyFields = {};
    },
  };

  return formInstance;
};

export const formMeta: AlpineComponentMeta<FormControlConfig> = {
  name: 'form',
  component: form,
};
