import { __, sprintf } from '@wordpress/i18n';

import { type ImportExportContentResponseBase } from '@ImportExport/services/import-export';

const generateImportExportMessage = (
  importExportStatus: ImportExportContentResponseBase | undefined,
  type: 'import' | 'export',
): string => {
  // Define operation-specific text based on type
  const operationText = {
    inProgress: type === 'export' ? __('Export in progress...', 'tutor') : __('Import in progress...', 'tutor'),
    verb: type === 'export' ? __('Exported', 'tutor') : __('Imported', 'tutor'),
    failed: __('failed', 'tutor'),
  };

  // Early return for missing data
  if (!importExportStatus) {
    return operationText.inProgress;
  }

  const { completed_contents: completedContents, failed_course_ids = [], failed_bundle_ids = [] } = importExportStatus;
  const noFailures = failed_course_ids.length === 0 && failed_bundle_ids.length === 0;

  // Helper function for formatting count with singular/plural text
  const formatCount = (count: number, singular: string, plural: string): string =>
    sprintf(__(count === 1 ? '%d ' + singular : '%d ' + plural, 'tutor'), count);

  // Handle case with only failures and no completed contents
  if (!completedContents || Object.keys(completedContents).length === 0) {
    if (!noFailures) {
      const items = [];
      if (failed_course_ids.length) {
        items.push(formatCount(failed_course_ids.length, 'Course', 'Courses'));
      }
      if (failed_bundle_ids.length) {
        items.push(formatCount(failed_bundle_ids.length, 'Bundle', 'Bundles'));
      }
      return `${items.join(', ')} ${operationText.failed}`;
    }
    return operationText.inProgress;
  }

  // Process successful and failed items
  const { courses, 'course-bundle': bundles, settings } = completedContents;
  const successItems = [];

  if (courses?.length) {
    successItems.push(formatCount(courses.length, 'Course', 'Courses'));
  }

  if (bundles?.length) {
    successItems.push(formatCount(bundles.length, 'Bundle', 'Bundles'));
  }

  if (settings) {
    successItems.push(__('Settings', 'tutor'));
  }

  // Create failed items list (without the word "failed")
  const failedItems = [];
  if (failed_course_ids.length) {
    failedItems.push(formatCount(failed_course_ids.length, 'Course', 'Courses'));
  }
  if (failed_bundle_ids.length) {
    failedItems.push(formatCount(failed_bundle_ids.length, 'Bundle', 'Bundles'));
  }

  // Early return if nothing to report
  if (successItems.length === 0 && failedItems.length === 0) {
    return operationText.inProgress;
  }

  // Build final message
  let message = '';

  if (successItems.length > 0) {
    message = `${successItems.join(', ')} ${operationText.verb}`;
  }

  if (failedItems.length > 0) {
    const failureMessage = `${failedItems.join(', ')} ${operationText.failed}`;
    message = message ? `${message}. ${failureMessage}` : failureMessage;
  }

  return message;
};

export default generateImportExportMessage;
