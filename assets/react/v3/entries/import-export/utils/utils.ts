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

  const completedContents = importExportStatus.completed_contents;

  const successFullyCompletedCourses = completedContents?.courses?.success || [];
  const successFullyCompletedBundles = completedContents?.['course-bundle']?.success || [];
  const successFullyCompletedSettings = completedContents?.settings;

  const completedWithErrorsCourses = completedContents?.courses?.failed || [];
  const completedWithErrorsBundles = completedContents?.['course-bundle']?.failed || [];

  const noFailures = completedWithErrorsCourses.length === 0 && completedWithErrorsBundles.length === 0;

  // Helper function for formatting count with singular/plural text
  const formatCount = (count: number, singular: string, plural: string): string =>
    sprintf(__(count === 1 ? '%d ' + singular : '%d ' + plural, 'tutor'), count);

  // Handle case with only failures and no completed contents
  if (!completedContents || Object.keys(completedContents).length === 0) {
    if (!noFailures) {
      const items = [];
      if (completedWithErrorsCourses.length) {
        items.push(formatCount(completedWithErrorsCourses.length, 'Course', 'Courses'));
      }
      if (completedWithErrorsBundles.length) {
        items.push(formatCount(completedWithErrorsBundles.length, 'Bundle', 'Bundles'));
      }
      return `${items.join(', ')} ${operationText.failed}`;
    }
    return operationText.inProgress;
  }

  const successItems = [];

  if (successFullyCompletedCourses.length) {
    successItems.push(formatCount(successFullyCompletedCourses.length, 'Course', 'Courses'));
  }

  if (successFullyCompletedBundles.length) {
    successItems.push(formatCount(successFullyCompletedBundles.length, 'Bundle', 'Bundles'));
  }

  if (successFullyCompletedSettings) {
    successItems.push(__('Settings', 'tutor'));
  }

  // Create failed items list (without the word "failed")
  const failedItems = [];
  if (completedWithErrorsCourses.length) {
    failedItems.push(formatCount(completedWithErrorsCourses.length, 'Course', 'Courses'));
  }
  if (completedWithErrorsBundles.length) {
    failedItems.push(formatCount(completedWithErrorsBundles.length, 'Bundle', 'Bundles'));
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
