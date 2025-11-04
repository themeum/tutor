// Profile Forms Service
// Handles form validation and submission for profile-related forms

import { showNotification } from '@FrontendServices/notifications';
import { DashboardAPI } from './dashboard-api';

export class ProfileFormsManager {
  private forms: Map<string, HTMLFormElement> = new Map();

  constructor() {
    this.initializeForms();
  }

  private initializeForms(): void {
    // Profile form
    const profileForm = document.querySelector('#profile-form') as HTMLFormElement;
    if (profileForm) {
      this.forms.set('profile', profileForm);
      this.setupFormValidation(profileForm);
      profileForm.addEventListener('submit', this.handleProfileSubmit.bind(this));
    }

    // Password form
    const passwordForm = document.querySelector('#password-form') as HTMLFormElement;
    if (passwordForm) {
      this.forms.set('password', passwordForm);
      this.setupFormValidation(passwordForm);
      passwordForm.addEventListener('submit', this.handlePasswordSubmit.bind(this));
    }

    // Preferences form
    const preferencesForm = document.querySelector('#preferences-form') as HTMLFormElement;
    if (preferencesForm) {
      this.forms.set('preferences', preferencesForm);
      this.setupFormValidation(preferencesForm);
      preferencesForm.addEventListener('submit', this.handlePreferencesSubmit.bind(this));
    }
  }

  private setupFormValidation(form: HTMLFormElement): void {
    // Real-time validation on input change
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
      input.addEventListener('blur', () => this.validateField(input as HTMLElement));
      input.addEventListener('input', () => this.clearFieldError(input as HTMLElement));
    });
  }

  private validateField(field: HTMLElement): boolean {
    const fieldName = (field as HTMLInputElement).name;
    const value = (field as HTMLInputElement).value;
    let isValid = true;
    let errorMessage = '';

    // Clear previous errors
    this.clearFieldError(field);

    // Required field validation
    if (field.hasAttribute('required') && !value.trim()) {
      isValid = false;
      errorMessage = 'This field is required';
    }

    // Email validation
    if (field.getAttribute('type') === 'email' && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value)) {
        isValid = false;
        errorMessage = 'Please enter a valid email address';
      }
    }

    // Password validation
    if (fieldName === 'new_password' && value) {
      const passwordValidation = this.validatePassword(value);
      if (!passwordValidation.isValid) {
        isValid = false;
        errorMessage = passwordValidation.message;
      }
    }

    // Confirm password validation
    if (fieldName === 'confirm_password' && value) {
      const newPasswordField = document.querySelector('[name="new_password"]') as HTMLInputElement;
      if (newPasswordField && value !== newPasswordField.value) {
        isValid = false;
        errorMessage = 'Passwords do not match';
      }
    }

    // Phone number validation
    if (fieldName === 'phone' && value) {
      const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
      if (!phoneRegex.test(value.replace(/\s/g, ''))) {
        isValid = false;
        errorMessage = 'Please enter a valid phone number';
      }
    }

    // Display error if validation failed
    if (!isValid) {
      this.showFieldError(field, errorMessage);
    }

    return isValid;
  }

  private validatePassword(password: string): { isValid: boolean; message: string } {
    if (password.length < 8) {
      return { isValid: false, message: 'Password must be at least 8 characters long' };
    }

    if (!/[a-z]/.test(password)) {
      return { isValid: false, message: 'Password must contain at least one lowercase letter' };
    }

    if (!/[A-Z]/.test(password)) {
      return { isValid: false, message: 'Password must contain at least one uppercase letter' };
    }

    if (!/[0-9]/.test(password)) {
      return { isValid: false, message: 'Password must contain at least one number' };
    }

    return { isValid: true, message: '' };
  }

  private showFieldError(field: HTMLElement, message: string): void {
    field.classList.add('error');
    
    // Remove existing error message
    const existingError = field.parentElement?.querySelector('.field-error');
    if (existingError) {
      existingError.remove();
    }

    // Add new error message
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    field.parentElement?.appendChild(errorElement);
  }

  private clearFieldError(field: HTMLElement): void {
    field.classList.remove('error');
    const errorElement = field.parentElement?.querySelector('.field-error');
    if (errorElement) {
      errorElement.remove();
    }
  }

  private async handleProfileSubmit(event: Event): Promise<void> {
    event.preventDefault();
    
    const form = event.target as HTMLFormElement;
    const isValid = this.validateForm(form);
    
    if (!isValid) {
      showNotification('Please fix the form errors', 'error');
      return;
    }

    const submitButton = form.querySelector('[type="submit"]') as HTMLButtonElement;
    const originalText = submitButton.textContent;
    
    try {
      // Show loading state
      submitButton.disabled = true;
      submitButton.textContent = 'Updating...';

      const formData = new FormData(form);
      await DashboardAPI.updateProfile(formData);
      
      showNotification('Profile updated successfully', 'success');
      
      // Update UI elements that display user info
      this.updateUserInfoInUI(formData);
      
    } catch (error) {
      console.error('Failed to update profile:', error);
      showNotification('Failed to update profile', 'error');
    } finally {
      // Reset button state
      submitButton.disabled = false;
      submitButton.textContent = originalText;
    }
  }

  private async handlePasswordSubmit(event: Event): Promise<void> {
    event.preventDefault();
    
    const form = event.target as HTMLFormElement;
    const isValid = this.validateForm(form);
    
    if (!isValid) {
      showNotification('Please fix the form errors', 'error');
      return;
    }

    const formData = new FormData(form);
    const currentPassword = formData.get('current_password') as string;
    const newPassword = formData.get('new_password') as string;
    const confirmPassword = formData.get('confirm_password') as string;

    // Additional validation
    if (newPassword !== confirmPassword) {
      showNotification('New passwords do not match', 'error');
      return;
    }

    const submitButton = form.querySelector('[type="submit"]') as HTMLButtonElement;
    const originalText = submitButton.textContent;
    
    try {
      // Show loading state
      submitButton.disabled = true;
      submitButton.textContent = 'Changing...';

      await DashboardAPI.changePassword({
        current_password: currentPassword,
        new_password: newPassword
      });
      
      showNotification('Password changed successfully', 'success');
      form.reset();
      
    } catch (error) {
      console.error('Failed to change password:', error);
      showNotification('Failed to change password', 'error');
    } finally {
      // Reset button state
      submitButton.disabled = false;
      submitButton.textContent = originalText;
    }
  }

  private async handlePreferencesSubmit(event: Event): Promise<void> {
    event.preventDefault();
    
    const form = event.target as HTMLFormElement;
    const submitButton = form.querySelector('[type="submit"]') as HTMLButtonElement;
    const originalText = submitButton.textContent;
    
    try {
      // Show loading state
      submitButton.disabled = true;
      submitButton.textContent = 'Saving...';

      const formData = new FormData(form);
      await DashboardAPI.updatePreferences(formData);
      
      showNotification('Preferences updated successfully', 'success');
      
    } catch (error) {
      console.error('Failed to update preferences:', error);
      showNotification('Failed to update preferences', 'error');
    } finally {
      // Reset button state
      submitButton.disabled = false;
      submitButton.textContent = originalText;
    }
  }

  private validateForm(form: HTMLFormElement): boolean {
    const fields = form.querySelectorAll('input, select, textarea');
    let isValid = true;

    fields.forEach(field => {
      if (!this.validateField(field as HTMLElement)) {
        isValid = false;
      }
    });

    return isValid;
  }

  private updateUserInfoInUI(formData: FormData): void {
    const firstName = formData.get('first_name') as string;
    const lastName = formData.get('last_name') as string;
    const email = formData.get('email') as string;

    // Update user name in header/sidebar
    document.querySelectorAll('.user-name').forEach(element => {
      element.textContent = `${firstName} ${lastName}`.trim();
    });

    // Update email displays
    document.querySelectorAll('.user-email').forEach(element => {
      element.textContent = email;
    });
  }

  // Public methods
  public resetForm(formName: string): void {
    const form = this.forms.get(formName);
    if (form) {
      form.reset();
      
      // Clear all field errors
      form.querySelectorAll('.error').forEach(field => {
        this.clearFieldError(field as HTMLElement);
      });
    }
  }

  public getFormData(formName: string): FormData | null {
    const form = this.forms.get(formName);
    return form ? new FormData(form) : null;
  }

  public setFieldValue(formName: string, fieldName: string, value: string): void {
    const form = this.forms.get(formName);
    if (form) {
      const field = form.querySelector(`[name="${fieldName}"]`) as HTMLInputElement;
      if (field) {
        field.value = value;
      }
    }
  }
}