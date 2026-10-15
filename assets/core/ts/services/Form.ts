import { type FormControlMethods, type FormState } from '@Core/ts/components/form';
import { TUTOR_CUSTOM_EVENTS } from '@Core/ts/constant';
import { type ServiceMeta } from '@Core/ts/types';

/**
 * FormService: programmatic API for interacting with form instances.
 * Provides methods to access form state and control form behavior from outside Alpine components.
 */
export class FormService {
  private forms: Map<string, FormControlMethods> = new Map();

  constructor() {
    this.setupEventListeners();
  }

  /** Setup event listeners for form events */
  private setupEventListeners(): void {
    document.addEventListener(TUTOR_CUSTOM_EVENTS.FORM_REGISTER, ((event: CustomEvent) => {
      const { id, instance } = event.detail;
      this.register(id, instance);
    }) as EventListener);

    document.addEventListener(TUTOR_CUSTOM_EVENTS.FORM_UNREGISTER, ((event: CustomEvent) => {
      const { id } = event.detail;
      this.unregister(id);
    }) as EventListener);
  }

  /**
   * Register a form instance with the service
   * @internal Called by form component during initialization
   */
  register(id: string, formInstance: FormControlMethods): void {
    this.forms.set(id, formInstance);
  }

  /**
   * Unregister a form instance from the service
   * @internal Called by form component during cleanup
   */
  unregister(id: string): void {
    this.forms.delete(id);
  }

  /**
   * Get a form instance by ID
   * @throws Error if form not found
   */
  private getForm(id: string): FormControlMethods {
    const form = this.forms.get(id);
    if (!form) {
      throw new Error(`Form with id "${id}" not found. Make sure the form is initialized with the correct id.`);
    }
    return form;
  }

  /**
   * Get all values from a form
   * @param id - The form ID
   * @returns Object containing all form values
   */
  getValues(id: string): Record<string, unknown> {
    return this.getForm(id).watch() as Record<string, unknown>;
  }

  /**
   * Get a specific field value from a form
   * @param id - The form ID
   * @param name - The field name
   * @returns The field value
   */
  getValue(id: string, name: string): unknown {
    return this.getForm(id).getValue(name);
  }

  /**
   * Set a field value in a form
   * @param id - The form ID
   * @param name - The field name
   * @param value - The value to set
   * @param options - Optional settings for validation, touch, and dirty state
   */
  setValue(
    id: string,
    name: string,
    value: unknown,
    options?: { shouldValidate?: boolean; shouldTouch?: boolean; shouldDirty?: boolean },
  ): void {
    this.getForm(id).setValue(name, value, options);
  }

  /**
   * Set multiple field values in a form
   * @param id - The form ID
   * @param values - Object containing field names and values
   * @param options - Optional settings for validation, touch, and dirty state
   */
  setValues(
    id: string,
    values: Record<string, unknown>,
    options?: { shouldValidate?: boolean; shouldTouch?: boolean; shouldDirty?: boolean },
  ): void {
    const form = this.getForm(id);
    for (const [name, value] of Object.entries(values)) {
      form.setValue(name, value, options);
    }
  }

  /**
   * Reset a form to its default values or provided values
   * @param id - The form ID
   * @param values - Optional values to reset to (defaults to initial values)
   */
  reset(id: string, values?: Record<string, unknown>): void {
    this.getForm(id).reset(values);
  }

  /**
   * Trigger validation for specific field(s) or all fields
   * @param id - The form ID
   * @param name - Optional field name or array of field names. Omit to validate all fields.
   * @returns Promise resolving to true if valid, false otherwise
   */
  async trigger(id: string, name?: string | string[]): Promise<boolean> {
    return this.getForm(id).trigger(name);
  }

  /**
   * Clear errors for specific field(s) or all fields
   * @param id - The form ID
   * @param name - Optional field name or array of field names. Omit to clear all errors.
   */
  clearErrors(id: string, name?: string | string[]): void {
    this.getForm(id).clearErrors(name);
  }

  /**
   * Set an error for a specific field
   * @param id - The form ID
   * @param name - The field name
   * @param error: { type: string; message: string }
   */
  setError(id: string, name: string, error: { type: string; message: string }): void {
    this.getForm(id).setError(name, error);
  }

  /**
   * Set focus on a specific field
   * @param id - The form ID
   * @param name - The field name
   * @param options - Optional settings for selection behavior
   */
  setFocus(id: string, name: string, options?: { shouldSelect?: boolean }): void {
    this.getForm(id).setFocus(name, options);
  }

  /**
   * Get the complete form state snapshot
   * @param id - The form ID
   * @returns Object containing all form state
   */
  getFormState(id: string): FormState {
    return this.getForm(id).getFormState();
  }

  /**
   * Watch a specific field value
   * @param id - The form ID
   * @param name - The field name
   * @returns The current field value
   */
  watch(id: string, name: string): unknown {
    return this.getForm(id).watch(name);
  }

  /**
   * Check if a form exists
   * @param id - The form ID
   * @returns True if form exists, false otherwise
   */
  hasForm(id: string): boolean {
    return this.forms.has(id);
  }
}

export const formServiceMeta: ServiceMeta = {
  name: 'form',
  instance: new FormService(),
};
