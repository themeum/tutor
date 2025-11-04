// Validation Service
// Form validation utilities and rules

interface ValidationRule {
  type: string;
  message: string;
  value?: any;
}

interface ValidationResult {
  isValid: boolean;
  errors: string[];
}

interface FieldValidationResult {
  isValid: boolean;
  error?: string;
}

class ValidationService {
  private rules: Map<string, ValidationRule[]> = new Map();

  // Validate a single field
  validateField(value: any, rules: ValidationRule[]): FieldValidationResult {
    for (const rule of rules) {
      const result = this.applyRule(value, rule);
      if (!result.isValid) {
        return result;
      }
    }
    return { isValid: true };
  }

  // Validate an entire form
  validateForm(form: HTMLFormElement): ValidationResult {
    const errors: string[] = [];
    const formData = new FormData(form);
    
    // Get all form fields
    const fields = form.querySelectorAll('input, select, textarea');
    
    fields.forEach(field => {
      const fieldElement = field as HTMLInputElement;
      const fieldName = fieldElement.name;
      const fieldValue = formData.get(fieldName);
      
      if (!fieldName) return;
      
      // Get validation rules from data attributes or predefined rules
      const rules = this.getFieldRules(fieldElement);
      const result = this.validateField(fieldValue, rules);
      
      if (!result.isValid && result.error) {
        errors.push(`${this.getFieldLabel(fieldElement)}: ${result.error}`);
        this.showFieldError(fieldElement, result.error);
      } else {
        this.clearFieldError(fieldElement);
      }
    });
    
    return {
      isValid: errors.length === 0,
      errors,
    };
  }

  // Apply a single validation rule
  private applyRule(value: any, rule: ValidationRule): FieldValidationResult {
    const stringValue = String(value || '').trim();
    
    switch (rule.type) {
      case 'required':
        return {
          isValid: stringValue.length > 0,
          error: stringValue.length === 0 ? rule.message : undefined,
        };
        
      case 'email':
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return {
          isValid: !stringValue || emailRegex.test(stringValue),
          error: stringValue && !emailRegex.test(stringValue) ? rule.message : undefined,
        };
        
      case 'minLength':
        return {
          isValid: !stringValue || stringValue.length >= rule.value,
          error: stringValue && stringValue.length < rule.value ? rule.message : undefined,
        };
        
      case 'maxLength':
        return {
          isValid: !stringValue || stringValue.length <= rule.value,
          error: stringValue && stringValue.length > rule.value ? rule.message : undefined,
        };
        
      case 'min':
        const numValue = Number(value);
        return {
          isValid: !stringValue || numValue >= rule.value,
          error: stringValue && numValue < rule.value ? rule.message : undefined,
        };
        
      case 'max':
        const maxNumValue = Number(value);
        return {
          isValid: !stringValue || maxNumValue <= rule.value,
          error: stringValue && maxNumValue > rule.value ? rule.message : undefined,
        };
        
      case 'pattern':
        const regex = new RegExp(rule.value);
        return {
          isValid: !stringValue || regex.test(stringValue),
          error: stringValue && !regex.test(stringValue) ? rule.message : undefined,
        };
        
      case 'url':
        try {
          if (!stringValue) return { isValid: true };
          new URL(stringValue);
          return { isValid: true };
        } catch {
          return { isValid: false, error: rule.message };
        }
        
      case 'phone':
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        const cleanPhone = stringValue.replace(/\s/g, '');
        return {
          isValid: !stringValue || phoneRegex.test(cleanPhone),
          error: stringValue && !phoneRegex.test(cleanPhone) ? rule.message : undefined,
        };
        
      case 'match':
        const matchField = document.querySelector(`[name="${rule.value}"]`) as HTMLInputElement;
        const matchValue = matchField?.value || '';
        return {
          isValid: stringValue === matchValue,
          error: stringValue !== matchValue ? rule.message : undefined,
        };
        
      case 'fileSize':
        if (value instanceof File) {
          const maxSize = rule.value; // in bytes
          return {
            isValid: value.size <= maxSize,
            error: value.size > maxSize ? rule.message : undefined,
          };
        }
        return { isValid: true };
        
      case 'fileType':
        if (value instanceof File) {
          const allowedTypes = rule.value as string[];
          return {
            isValid: allowedTypes.includes(value.type),
            error: !allowedTypes.includes(value.type) ? rule.message : undefined,
          };
        }
        return { isValid: true };
        
      default:
        console.warn(`Unknown validation rule: ${rule.type}`);
        return { isValid: true };
    }
  }

  // Get validation rules for a field
  private getFieldRules(field: HTMLInputElement): ValidationRule[] {
    const rules: ValidationRule[] = [];
    
    // Required field
    if (field.hasAttribute('required')) {
      rules.push({
        type: 'required',
        message: 'This field is required',
      });
    }
    
    // Email field
    if (field.type === 'email') {
      rules.push({
        type: 'email',
        message: 'Please enter a valid email address',
      });
    }
    
    // URL field
    if (field.type === 'url') {
      rules.push({
        type: 'url',
        message: 'Please enter a valid URL',
      });
    }
    
    // Min/Max length
    if (field.hasAttribute('minlength')) {
      rules.push({
        type: 'minLength',
        value: parseInt(field.getAttribute('minlength')!),
        message: `Minimum length is ${field.getAttribute('minlength')} characters`,
      });
    }
    
    if (field.hasAttribute('maxlength')) {
      rules.push({
        type: 'maxLength',
        value: parseInt(field.getAttribute('maxlength')!),
        message: `Maximum length is ${field.getAttribute('maxlength')} characters`,
      });
    }
    
    // Min/Max value
    if (field.hasAttribute('min')) {
      rules.push({
        type: 'min',
        value: Number(field.getAttribute('min')),
        message: `Minimum value is ${field.getAttribute('min')}`,
      });
    }
    
    if (field.hasAttribute('max')) {
      rules.push({
        type: 'max',
        value: Number(field.getAttribute('max')),
        message: `Maximum value is ${field.getAttribute('max')}`,
      });
    }
    
    // Pattern
    if (field.hasAttribute('pattern')) {
      rules.push({
        type: 'pattern',
        value: field.getAttribute('pattern'),
        message: field.getAttribute('title') || 'Invalid format',
      });
    }
    
    // Custom validation rules from data attributes
    if (field.dataset.validate) {
      const customRules = JSON.parse(field.dataset.validate);
      rules.push(...customRules);
    }
    
    return rules;
  }

  // Get field label for error messages
  private getFieldLabel(field: HTMLInputElement): string {
    // Try to find label
    const label = document.querySelector(`label[for="${field.id}"]`);
    if (label) {
      return label.textContent?.trim() || field.name;
    }
    
    // Try placeholder
    if (field.placeholder) {
      return field.placeholder;
    }
    
    // Use field name
    return field.name.replace(/[_-]/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
  }

  // Show field error
  private showFieldError(field: HTMLInputElement, message: string): void {
    field.classList.add('error');
    
    // Remove existing error
    const existingError = field.parentElement?.querySelector('.field-error');
    if (existingError) {
      existingError.remove();
    }
    
    // Add new error
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    field.parentElement?.appendChild(errorElement);
  }

  // Clear field error
  private clearFieldError(field: HTMLInputElement): void {
    field.classList.remove('error');
    const errorElement = field.parentElement?.querySelector('.field-error');
    if (errorElement) {
      errorElement.remove();
    }
  }

  // Predefined validation rule sets
  static readonly RULES = {
    PASSWORD: [
      { type: 'required', message: 'Password is required' },
      { type: 'minLength', value: 8, message: 'Password must be at least 8 characters' },
      { type: 'pattern', value: '(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)', message: 'Password must contain uppercase, lowercase, and number' },
    ],
    
    EMAIL: [
      { type: 'required', message: 'Email is required' },
      { type: 'email', message: 'Please enter a valid email address' },
    ],
    
    PHONE: [
      { type: 'phone', message: 'Please enter a valid phone number' },
    ],
    
    URL: [
      { type: 'url', message: 'Please enter a valid URL' },
    ],
    
    IMAGE_FILE: [
      { type: 'fileType', value: ['image/jpeg', 'image/png', 'image/gif'], message: 'Only JPEG, PNG, and GIF images are allowed' },
      { type: 'fileSize', value: 2 * 1024 * 1024, message: 'Image size must be less than 2MB' },
    ],
    
    DOCUMENT_FILE: [
      { type: 'fileType', value: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'], message: 'Only PDF and Word documents are allowed' },
      { type: 'fileSize', value: 10 * 1024 * 1024, message: 'File size must be less than 10MB' },
    ],
  };
}

// Create and export singleton instance
export const validationService = new ValidationService();

// Convenience function for form validation
export const validateForm = (form: HTMLFormElement): ValidationResult => {
  return validationService.validateForm(form);
};

// Convenience function for field validation
export const validateField = (value: any, rules: ValidationRule[]): FieldValidationResult => {
  return validationService.validateField(value, rules);
};

// Export types
export type { FieldValidationResult, ValidationResult, ValidationRule };
