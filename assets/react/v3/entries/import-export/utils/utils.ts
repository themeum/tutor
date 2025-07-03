import { __, _n, sprintf } from '@wordpress/i18n';

import { type ImportExportContentResponseBase } from '@ImportExport/services/import-export';

const generateImportExportMessage = (
  importExportStatus: ImportExportContentResponseBase | undefined,
  type: 'import' | 'export',
): string => {
  // Define operation-specific text based on type
  const operationText = {
    inProgress: type === 'export' ? __('Export in progress...', 'tutor') : __('Import in progress...', 'tutor'),
    verb: type === 'export' ? __('exported', 'tutor') : __('imported', 'tutor'),
    failed: __('failed', 'tutor'),
  };

  // Early return for missing data
  if (!importExportStatus) {
    return operationText.inProgress;
  }

  const completedContents = importExportStatus.completed_contents;

  const successFullyCompletedCourses = completedContents?.courses?.success || [];
  const successFullyCompletedBundles = completedContents?.['course-bundle']?.success || [];
  const successFullyCompletedCollections = completedContents?.['content-bank']?.success || [];
  const successFullyCompletedSettings = completedContents?.settings;

  const completedWithErrorsCourses = completedContents?.courses?.failed || [];
  const completedWithErrorsBundles = completedContents?.['course-bundle']?.failed || [];
  const completedWithErrorsCollections = completedContents?.['content-bank']?.failed || [];

  const noFailures =
    completedWithErrorsCourses.length === 0 &&
    completedWithErrorsBundles.length === 0 &&
    completedWithErrorsCollections.length === 0;

  // Helper function for formatting count with singular/plural text
  const formatCount = (count: number, type: 'course' | 'bundle' | 'content-bank'): string => {
    if (type === 'course') {
      // translators: %d is the number of courses
      return sprintf(_n('%d Course', '%d Courses', count, 'tutor'), count);
    }

    if (type === 'bundle') {
      // translators: %d is the number of bundles
      return sprintf(_n('%d Bundle', '%d Bundles', count, 'tutor'), count);
    }

    // translators: %d is the number of collections
    return sprintf(_n('%d Collection', '%d Collections', count, 'tutor'), count);
  };

  // Handle case with only failures and no completed contents
  if (!completedContents || Object.keys(completedContents).length === 0) {
    if (!noFailures) {
      const items = [];
      if (completedWithErrorsCourses.length) {
        items.push(formatCount(completedWithErrorsCourses.length, 'course'));
      }
      if (completedWithErrorsBundles.length) {
        items.push(formatCount(completedWithErrorsBundles.length, 'bundle'));
      }
      if (completedWithErrorsCollections.length) {
        items.push(formatCount(completedWithErrorsCollections.length, 'content-bank'));
      }

      return `${items.join(', ')} ${operationText.failed}`;
    }
    return operationText.inProgress;
  }

  const successItems = [];

  if (successFullyCompletedCourses.length) {
    successItems.push(formatCount(successFullyCompletedCourses.length, 'course'));
  }

  if (successFullyCompletedBundles.length) {
    successItems.push(formatCount(successFullyCompletedBundles.length, 'bundle'));
  }

  if (successFullyCompletedCollections.length) {
    successItems.push(formatCount(successFullyCompletedCollections.length, 'content-bank'));
  }

  if (successFullyCompletedSettings) {
    successItems.push(__('Settings', 'tutor'));
  }

  // Create failed items list (without the word "failed")
  const failedItems = [];
  if (completedWithErrorsCourses.length) {
    failedItems.push(formatCount(completedWithErrorsCourses.length, 'course'));
  }
  if (completedWithErrorsBundles.length) {
    failedItems.push(formatCount(completedWithErrorsBundles.length, 'bundle'));
  }

  if (completedWithErrorsCollections.length) {
    failedItems.push(formatCount(completedWithErrorsCollections.length, 'content-bank'));
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
