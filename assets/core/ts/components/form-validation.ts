// Form Validation Component
// Alpine.js form validation with real-time validation and custom rules

import { type AlpineFormValidationData, type FormValidationConfig, type ValidationRule } from '../types/components';

export function createFormValidation(config: FormValidationConfig = {}): AlpineFormValidationData {
  return {
    errors: {} as Record<string, string>,
    touched: {} as Record<string, boolean>,
    $el: undefined as HTMLElement | undefined,

    init() {
      this.setupValidation();
    },

    validate(field: string, value: unknown): boolean {
      const rules = config.rules?.[field] || [];
      let isValid = true;

      // Clear previous error
      delete this.errors[field];

      for (const rule of rules) {
        if (!this.validateRule(field, value, rule)) {
          isValid = false;
          break; // Stop at first validation error
        }
      }

      return isValid;
    },

    validateRule(field: string, value: unknown, rule: ValidationRule): boolean {
      const stringValue = String(value || '');
      const numericValue = typeof value === 'number' ? value : parseFloat(stringValue);

      switch (rule.type) {
        case 'required':
          if (!value || (typeof value === 'string' && value.trim() === '')) {
            this.errors[field] = rule.message || `${field} is required`;
            return false;
          }
          break;

        case 'email': {
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (stringValue && !emailRegex.test(stringValue)) {
            this.errors[field] = rule.message || 'Please enter a valid email address';
            return false;
          }
          break;
        }

        case 'minLength':
          if (stringValue && rule.value && typeof rule.value === 'number' && stringValue.length < rule.value) {
            this.errors[field] = rule.message || `Minimum length is ${rule.value} characters`;
            return false;
          }
          break;

        case 'maxLength':
          if (stringValue && rule.value && typeof rule.value === 'number' && stringValue.length > rule.value) {
            this.errors[field] = rule.message || `Maximum length is ${rule.value} characters`;
            return false;
          }
          break;

        case 'min':
          if (!isNaN(numericValue) && rule.value && typeof rule.value === 'number' && numericValue < rule.value) {
            this.errors[field] = rule.message || `Minimum value is ${rule.value}`;
            return false;
          }
          break;

        case 'max':
          if (!isNaN(numericValue) && rule.value && typeof rule.value === 'number' && numericValue > rule.value) {
            this.errors[field] = rule.message || `Maximum value is ${rule.value}`;
            return false;
          }
          break;

        case 'number':
          if (stringValue && isNaN(numericValue)) {
            this.errors[field] = rule.message || 'Please enter a valid number';
            return false;
          }
          break;

        case 'url': {
          const urlRegex = /^https?:\/\/.+\..+/;
          if (stringValue && !urlRegex.test(stringValue)) {
            this.errors[field] = rule.message || 'Please enter a valid URL';
            return false;
          }
          break;
        }

        case 'pattern':
          if (stringValue && rule.value && !new RegExp(String(rule.value)).test(stringValue)) {
            this.errors[field] = rule.message || 'Invalid format';
            return false;
          }
          break;
      }

      return true;
    },

    validateAll(): boolean {
      if (!config.rules) return true;

      let allValid = true;
      const form = this.$el as HTMLFormElement;

      Object.keys(config.rules).forEach((field) => {
        const input = form?.querySelector(`[name="${field}"]`) as HTMLInputElement;
        if (input) {
          this.touch(field);
          const isValid = this.validate(field, input.value);
          if (!isValid) allValid = false;
        }
      });

      return allValid;
    },

    hasError(field: string): boolean {
      return !!this.errors[field];
    },

    getError(field: string): string {
      return this.errors[field] || '';
    },

    clearError(field: string): void {
      delete this.errors[field];
    },

    clearAllErrors(): void {
      this.errors = {};
    },

    touch(field: string): void {
      this.touched[field] = true;
    },

    isTouched(field: string): boolean {
      return !!this.touched[field];
    },

    shouldShowError(field: string): boolean {
      return this.hasError(field) && (this.isTouched(field) || config.showErrors !== false);
    },

    handleInput(field: string, value: unknown): void {
      if (config.validateOnInput && this.isTouched(field)) {
        this.validate(field, value);
      }
    },

    handleBlur(field: string, value: unknown): void {
      this.touch(field);
      if (config.validateOnBlur !== false) {
        this.validate(field, value);
      }
    },

    handleSubmit(event: Event): boolean {
      event.preventDefault();

      const isValid = this.validateAll();

      if (isValid) {
        // Form is valid, allow submission
        return true;
      } else {
        // Focus first field with error
        this.focusFirstError();
        return false;
      }
    },

    focusFirstError(): void {
      const form = this.$el as HTMLFormElement;
      const firstErrorField = Object.keys(this.errors)[0];

      if (firstErrorField) {
        const input = form?.querySelector(`[name="${firstErrorField}"]`) as HTMLElement;
        input?.focus();
      }
    },

    setupValidation(): void {
      const form = this.$el as HTMLFormElement;
      if (!form || !config.rules) return;

      // Set up input event listeners
      Object.keys(config.rules).forEach((field) => {
        const input = form.querySelector(`[name="${field}"]`) as HTMLInputElement;
        if (input) {
          input.addEventListener('input', (e) => {
            const target = e.target as HTMLInputElement;
            this.handleInput(field, target.value);
          });

          input.addEventListener('blur', (e) => {
            const target = e.target as HTMLInputElement;
            this.handleBlur(field, target.value);
          });
        }
      });

      // Set up form submit handler
      form.addEventListener('submit', (e) => this.handleSubmit(e));
    },

    // Helper method to get field validation status
    getFieldStatus(field: string): 'valid' | 'invalid' | 'untouched' {
      if (!this.isTouched(field)) return 'untouched';
      return this.hasError(field) ? 'invalid' : 'valid';
    },
  };
}
