import { formServiceMeta } from '@Core/ts/services/Form';
import { type AlpineComponentMeta } from '@Core/ts/types';

interface FormControlConfig {
  mode?: 'onChange' | 'onBlur' | 'onSubmit';
  reValidateMode?: 'onChange' | 'onBlur' | 'onSubmit';
  shouldFocusError?: boolean;
  shouldScrollToError?: boolean;
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
  validate?: (value: unknown) => boolean | string | Promise<boolean | string>;
}
interface FieldError {
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

interface FormState {
  values: Record<string, unknown>;
  errors: Record<string, FieldError>;
  touchedFields: Record<string, boolean>;
  dirtyFields: Record<string, boolean>;
  isValid: boolean;
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

const DEFAULT_CONFIG: FormControlConfig = {
  mode: 'onBlur',
  reValidateMode: 'onChange',
  shouldFocusError: true,
  shouldScrollToError: true,
};

export const form = (config: FormControlConfig & { id?: string } = {}) => {
  const { id: formId, ...formConfig } = config;
  const mergedConfig = { ...DEFAULT_CONFIG, ...formConfig };

  const formInstance = {
    config: mergedConfig,
    formId,

    fields: {} as Record<string, FieldConfig>,
    values: {} as Record<string, unknown>,
    errors: {} as Record<string, FieldError>,
    touchedFields: {} as Record<string, boolean>,
    dirtyFields: {} as Record<string, boolean>,
    isValid: true,
    isSubmitting: false,
    isValidating: false,
    cleanup: undefined as (() => void) | undefined,

    init(): void {
      this.isValid = true;
      this.isSubmitting = false;
      this.isValidating = false;

      if (this.formId) {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        (formServiceMeta.instance as any).register(this.formId, this);
      }

      const formElement = (this as unknown as { $el: HTMLElement }).$el;
      if (formElement) {
        const handleFormSubmit = (event: Event) => {
          event.preventDefault();
          // Form submission will be handled by handleSubmit method
        };

        formElement.addEventListener('submit', handleFormSubmit);

        this.cleanup = () => {
          formElement.removeEventListener('submit', handleFormSubmit);
          // Unregister from global registry
          if (this.formId) {
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            (formServiceMeta.instance as any).unregister(this.formId);
          }
        };
      } else {
        this.cleanup = () => {
          if (this.formId) {
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            (formServiceMeta.instance as any).unregister(this.formId);
          }
        };
      }
    },

    destroy(): void {
      this.cleanup?.();

      // Clear all state
      this.fields = {};
      this.values = {};
      this.errors = {};
      this.touchedFields = {};
      this.dirtyFields = {};
    },

    register(name: string, rules?: ValidationRules): Record<string, unknown> {
      this.fields[name] = {
        name,
        rules,
        defaultValue: this.values[name] || '',
        ref: (this as unknown as { $el: HTMLInputElement }).$el,
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
      this.values[name] = value;
      this.dirtyFields[name] = true;

      const element = (this as unknown as { $refs: Record<string, HTMLElement> }).$refs[name] as HTMLInputElement;
      if (element && !this.fields[name].ref) {
        this.fields[name].ref = element;
      }

      if (this.config.mode === 'onChange' || (this.config.reValidateMode === 'onChange' && this.touchedFields[name])) {
        this.validateField(name, value);
      }
    },

    handleFieldBlur(name: string, value: unknown): void {
      this.touchedFields[name] = true;

      const element = (this as unknown as { $refs: Record<string, HTMLElement> }).$refs[name] as HTMLInputElement;
      if (element && !this.fields[name].ref) {
        this.fields[name].ref = element;
      }

      // Validate based on mode
      if (this.config.mode === 'onBlur' || (this.config.reValidateMode === 'onBlur' && this.touchedFields[name])) {
        this.validateField(name, value);
      }
    },

    watch(name?: string): unknown {
      if (name) {
        return this.values[name];
      }
      return { ...this.values };
    },

    setValue(name: string, value: unknown, options: SetValueOptions = {}): void {
      const { shouldValidate = false, shouldTouch = false, shouldDirty = true } = options;

      this.values[name] = value;

      if (shouldTouch) {
        this.touchedFields[name] = true;
      }
      if (shouldDirty) {
        this.dirtyFields[name] = true;
      }

      const formElement = this.fields[name]?.ref as HTMLInputElement | undefined;
      if (formElement) {
        formElement.value = String(value || '');
      }

      if (shouldValidate) {
        this.validateField(name, value);
      }
    },

    getValue(name: string): unknown {
      return this.values[name];
    },

    setFocus(name: string, options: FocusOptions = {}): void {
      const { shouldSelect = false } = options;
      const field = this.fields[name];
      const fieldElement = field?.ref as HTMLInputElement | undefined;

      if (fieldElement && this.isFieldVisible(fieldElement)) {
        fieldElement.focus();
        if (shouldSelect && fieldElement.select) {
          fieldElement.select();
        }

        if (this.config.shouldScrollToError) {
          fieldElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }
    },

    async trigger(name?: string | string[]): Promise<boolean> {
      this.isValidating = true;
      let isValid = true;

      try {
        if (typeof name === 'string') {
          this.touchedFields[name] = true;
          const value = this.values[name];
          isValid = await this.validateField(name, value);
        } else if (Array.isArray(name)) {
          for (const fieldName of name) {
            this.touchedFields[fieldName] = true;
            const value = this.values[fieldName];
            const fieldValid = await this.validateField(fieldName, value);
            if (!fieldValid) {
              isValid = false;
            }
          }
        } else {
          for (const fieldName of Object.keys(this.fields)) {
            this.touchedFields[fieldName] = true;
          }
          isValid = await this.validateAllFields();
        }
      } finally {
        this.isValidating = false;
      }

      return isValid;
    },

    clearErrors(name?: string | string[]): void {
      if (typeof name === 'string') {
        delete this.errors[name];
        this.updateAriaInvalidState(name);
      } else if (Array.isArray(name)) {
        name.forEach((fieldName) => {
          delete this.errors[fieldName];
          this.updateAriaInvalidState(fieldName);
        });
      } else {
        Object.keys(this.fields).forEach((fieldName) => {
          delete this.errors[fieldName];
          this.updateAriaInvalidState(fieldName);
        });
      }

      this.updateFormValidState();
    },

    setError(name: string, error: FieldError): void {
      this.errors[name] = error;
      this.updateFormValidState();
    },

    reset(values?: Record<string, unknown>): void {
      if (values) {
        this.values = { ...values };
      } else {
        this.values = Object.keys(this.fields).reduce(
          (acc, name) => {
            acc[name] = this.fields[name].defaultValue;
            return acc;
          },
          {} as Record<string, unknown>,
        );
      }

      for (const [name, value] of Object.entries(this.values)) {
        const fieldRef = this.fields[name]?.ref as HTMLInputElement | undefined;
        if (!fieldRef) continue;

        fieldRef.value = String(value ?? '');
      }

      this.errors = {};
      this.touchedFields = {};
      this.dirtyFields = {};
      this.isValid = true;
      this.isSubmitting = false;
      this.isValidating = false;
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
            if (onInvalid) {
              onInvalid({ ...this.errors });
            }

            if (this.config.shouldFocusError) {
              this.focusFirstError();
            }
          }
        } finally {
          this.isSubmitting = false;
        }
      };
    },

    getFormState(): FormState {
      this.validateAllFields();

      return {
        values: { ...this.values },
        errors: { ...this.errors },
        touchedFields: { ...this.touchedFields },
        dirtyFields: { ...this.dirtyFields },
        isValid: this.isValid,
        isSubmitting: this.isSubmitting,
        isValidating: this.isValidating,
      };
    },

    isFieldVisible(element: HTMLElement): boolean {
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

    updateAriaInvalidState(name: string): void {
      const fieldRef = this.fields[name]?.ref as HTMLInputElement | undefined;
      if (!fieldRef) return;

      if (this.errors[name]) {
        fieldRef.setAttribute('aria-invalid', 'true');
      } else {
        fieldRef.removeAttribute('aria-invalid');
      }
    },

    async validateField(name: string, value: unknown): Promise<boolean> {
      const fieldConfig = this.fields[name];
      if (!fieldConfig?.rules) {
        delete this.errors[name];
        this.updateAriaInvalidState(name);
        this.updateFormValidState();
        return true;
      }

      delete this.errors[name];

      const rules = fieldConfig.rules;

      if (rules.required) {
        const message = typeof rules.required === 'string' ? rules.required : `${name} is required`;
        if (!value || (typeof value === 'string' && value.trim() === '')) {
          this.errors[name] = { type: 'required', message };
          this.updateAriaInvalidState(name);
          this.updateFormValidState();
          return false;
        }
      }

      const stringValue = String(value || '');
      const numericValue = typeof value === 'number' ? value : parseFloat(stringValue);

      if (rules.minLength && stringValue) {
        const minLength = typeof rules.minLength === 'number' ? rules.minLength : rules.minLength.value;
        const message =
          typeof rules.minLength === 'object' ? rules.minLength.message : `Minimum length is ${minLength}`;

        if (stringValue.length < minLength) {
          this.errors[name] = { type: 'minLength', message };
          this.updateAriaInvalidState(name);
          this.updateFormValidState();
          return false;
        }
      }

      if (rules.maxLength && stringValue) {
        const maxLength = typeof rules.maxLength === 'number' ? rules.maxLength : rules.maxLength.value;
        const message =
          typeof rules.maxLength === 'object' ? rules.maxLength.message : `Maximum length is ${maxLength}`;

        if (stringValue.length > maxLength) {
          this.errors[name] = { type: 'maxLength', message };
          this.updateAriaInvalidState(name);
          this.updateFormValidState();
          return false;
        }
      }

      if (rules.min && !isNaN(numericValue)) {
        const min = typeof rules.min === 'number' ? rules.min : rules.min.value;
        const message = typeof rules.min === 'object' ? rules.min.message : `Minimum value is ${min}`;

        if (numericValue < min) {
          this.errors[name] = { type: 'min', message };
          this.updateAriaInvalidState(name);
          this.updateFormValidState();
          return false;
        }
      }

      if (rules.max && !isNaN(numericValue)) {
        const max = typeof rules.max === 'number' ? rules.max : rules.max.value;
        const message = typeof rules.max === 'object' ? rules.max.message : `Maximum value is ${max}`;

        if (numericValue > max) {
          this.errors[name] = { type: 'max', message };
          this.updateAriaInvalidState(name);
          this.updateFormValidState();
          return false;
        }
      }

      if (rules.pattern && stringValue) {
        const pattern = rules.pattern instanceof RegExp ? rules.pattern : rules.pattern.value;
        const message =
          typeof rules.pattern === 'object' && 'message' in rules.pattern ? rules.pattern.message : 'Invalid format';

        if (!pattern.test(stringValue)) {
          this.errors[name] = { type: 'pattern', message };
          this.updateAriaInvalidState(name);
          this.updateFormValidState();
          return false;
        }
      }

      if (rules.validate) {
        try {
          const result = await rules.validate(value);
          if (result !== true) {
            const message = typeof result === 'string' ? result : 'Validation failed';
            this.errors[name] = { type: 'validate', message };
            this.updateAriaInvalidState(name);
            this.updateFormValidState();
            return false;
          }
        } catch {
          this.errors[name] = { type: 'validate', message: 'Validation error' };
          this.updateAriaInvalidState(name);
          this.updateFormValidState();
          return false;
        }
      }

      this.updateAriaInvalidState(name);
      this.updateFormValidState();

      return true;
    },

    async validateAllFields(): Promise<boolean> {
      let isValid = true;
      const formElement = (this as unknown as { $el: HTMLElement }).$el;

      for (const [name] of Object.entries(this.fields)) {
        const fieldElement = formElement?.querySelector(`[name="${name}"]`) as HTMLElement;
        if (fieldElement && !this.isFieldVisible(fieldElement)) {
          continue;
        }

        const value = this.values[name];
        const fieldValid = await this.validateField(name, value);
        if (!fieldValid) {
          isValid = false;
        }
      }

      return isValid;
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

    getFormBindings() {
      return {
        novalidate: true, // Disable HTML validation to use custom validation
      };
    },

    handleFormErrors(
      response: { status: number; data: Record<string, unknown> },
      values: Record<string, unknown>,
    ): { fieldErrors?: Record<string, string[]>; nonFieldErrors?: string[] } {
      if (response.status === 404 || response.status === 403 || response.status === 500) {
        return {
          nonFieldErrors: ['Unexpected error!'],
        };
      }

      const flatValues = this.flattenObject(values);
      const flatData = this.flattenObject(response.data);

      const { non_field_errors, ...responseWithoutNonFieldErrors } = flatData;
      const nonFieldErrors: string[] = Array.isArray(non_field_errors) ? non_field_errors : [];

      for (const objectKey of Object.keys(responseWithoutNonFieldErrors)) {
        if (!(objectKey in flatValues)) {
          const value = flatData[objectKey];
          if (Array.isArray(value)) {
            nonFieldErrors.push(...value);
          }
        }
      }

      return {
        nonFieldErrors,
        fieldErrors: Object.keys(flatData)
          .filter((objectKey) => objectKey in flatValues)
          .reduce((acc, field) => {
            const errors = flatData[field];
            if (Array.isArray(errors)) {
              return { ...acc, [field]: errors };
            }
            return acc;
          }, {}),
      };
    },

    mapErrorResponseToForm(
      err: { response?: { status: number; data: Record<string, unknown> } },
      values: Record<string, unknown>,
    ): void {
      if (!err.response) {
        return;
      }

      const { fieldErrors, nonFieldErrors } = this.handleFormErrors(err.response, values);

      if (nonFieldErrors?.length) {
        // Set global form error - could be displayed in UI
        // Note: In production, this should be handled by the UI layer
        // For now, we'll store it in a way that can be accessed by the UI
        // You could emit an event or set a global error state here
      }

      if (fieldErrors) {
        for (const fieldName of Object.keys(fieldErrors)) {
          const fieldError = fieldErrors[fieldName];
          if (fieldError.length > 0) {
            this.setError(fieldName, { type: 'server', message: fieldError[0] });
          }
        }
      }
    },

    convertToFormData(values: Record<string, unknown>, method: string = 'POST'): FormData {
      const formData = new FormData();

      for (const key of Object.keys(values)) {
        const value = values[key];

        if (Array.isArray(value)) {
          value.forEach((item, index) => {
            if (item instanceof File || item instanceof Blob || typeof item === 'string') {
              formData.append(`${key}[${index}]`, item);
            } else if (typeof item === 'boolean' || typeof item === 'number') {
              formData.append(`${key}[${index}]`, item.toString());
            } else if (typeof item === 'object' && item !== null) {
              formData.append(`${key}[${index}]`, JSON.stringify(item));
            } else {
              formData.append(`${key}[${index}]`, String(item));
            }
          });
        } else {
          if (value instanceof File || value instanceof Blob || typeof value === 'string') {
            formData.append(key, value);
          } else if (typeof value === 'boolean') {
            formData.append(key, value.toString());
          } else if (typeof value === 'number') {
            formData.append(key, String(value));
          } else if (typeof value === 'object' && value !== null) {
            formData.append(key, JSON.stringify(value));
          } else {
            formData.append(key, String(value));
          }
        }
      }

      formData.append('_method', method.toUpperCase());
      return formData;
    },

    serializeParams(params: Record<string, unknown>): Record<string, unknown> {
      const serialized: Record<string, unknown> = {};

      for (const key in params) {
        const value = params[key];

        if (value === null || value === undefined) {
          serialized[key] = 'null';
        } else if (typeof value === 'boolean') {
          serialized[key] = value === true ? 'true' : 'false';
        } else {
          serialized[key] = value;
        }
      }

      return serialized;
    },

    submitHandler(
      submitFn: (values: Record<string, unknown>) => Promise<unknown>,
    ): (values: Record<string, unknown>) => Promise<void> {
      return async (values: Record<string, unknown>) => {
        try {
          await submitFn(values);
        } catch (err) {
          this.mapErrorResponseToForm(err as { response?: { status: number; data: Record<string, unknown> } }, values);
        }
      };
    },

    flattenObject(obj: Record<string, unknown>, base = ''): Record<string, unknown> {
      return Object.keys(obj).reduce<Record<string, unknown>>((acc, key) => {
        const value = obj[key];

        if (
          typeof value === 'object' &&
          value !== null &&
          !Array.isArray(value) &&
          !(value instanceof File) &&
          !(value instanceof Blob)
        ) {
          return { ...acc, ...this.flattenObject(value as Record<string, unknown>, `${base}${key}.`) };
        }

        return { ...acc, [`${base}${key}`]: value };
      }, {});
    },
  };

  return formInstance;
};

export const formMeta: AlpineComponentMeta<FormControlConfig> = {
  name: 'form',
  component: form,
};
