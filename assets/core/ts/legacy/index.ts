/**
 * Legacy compatibility layer for old Tutor LMS window functions
 *
 * This module provides backward compatibility wrappers that translate
 * old API calls to the new core system, ensuring zero breaking changes.
 *
 * @since 4.0.0
 */

import { tutor_get_nonce_data } from './nonce';
import { tutor_esc_attr, tutor_esc_html } from './security';
import { tutor_toast } from './toast';

/**
 * Register legacy functions to the window object for backward compatibility
 *
 * This function should be called after the core system is initialized
 * to ensure all services are available.
 *
 * @since 4.0.0
 */
export function registerLegacyFunctions(): void {
  // Nonce handling
  window.tutor_get_nonce_data = tutor_get_nonce_data;

  // Toast notifications
  window.tutor_toast = tutor_toast;

  // Security utilities
  window.tutor_esc_html = tutor_esc_html;
  window.tutor_esc_attr = tutor_esc_attr;

  // Set default error message from WordPress i18n
  if (typeof wp !== 'undefined' && wp.i18n) {
    window.defaultErrorMessage = wp.i18n.__('Something went wrong', 'tutor');
  }
}

// Export all legacy functions for direct import if needed
export { tutor_esc_attr, tutor_esc_html, tutor_get_nonce_data, tutor_toast };
