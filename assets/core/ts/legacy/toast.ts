/**
 * Legacy tutor_toast wrapper for backward compatibility
 *
 * @since 4.0.0
 */

import type { ToastType } from '@Core/ts/types/toast';

/**
 * Legacy tutor_toast function that wraps the new core toast service
 *
 * This function maintains backward compatibility with the old API signature:
 * tutor_toast(title, description, type, autoClose)
 *
 * @deprecated Use window.TutorCore.toast methods instead
 *
 * @param title - Toast title (used as message if description is empty)
 * @param description - Toast description (becomes the main message)
 * @param type - Toast type: 'success' | 'error' | 'warning' | 'info'
 * @param autoClose - Whether to auto-dismiss (default: true)
 *
 * @example
 * // Legacy usage (still works)
 * tutor_toast('Success', 'Item saved successfully', 'success');
 *
 * // Translates to new API
 * TutorCore.toast.show('Item saved successfully', { type: 'success', title: 'Success' });
 *
 * @since 4.0.0
 */
export function tutor_toast(title: string, description?: string, type: ToastType = 'info', autoClose = true): void {
  // Determine message and title for new API
  // If description exists, use it as message and title as custom title
  // If no description, use title as message with default title
  const message = description || title;
  const toastTitle = description ? title : undefined;
  const duration = autoClose ? 5000 : 0;

  // Use the new toast service
  if (window.TutorCore?.toast) {
    window.TutorCore.toast.show(message, {
      type,
      ...(toastTitle && { title: toastTitle }),
      duration,
    });
  } else {
    // Fallback to console if core is not loaded
    // eslint-disable-next-line no-console
    console.warn('[Tutor Toast] Core toast service not available:', message);
  }
}
