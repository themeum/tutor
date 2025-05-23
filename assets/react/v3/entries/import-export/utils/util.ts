import { type ExportContentResponse } from '@ImportExport/services/import-export';
import { __, sprintf } from '@wordpress/i18n';

export const formatCompletedItems = (completedContents?: ExportContentResponse['completed_contents']): string => {
  if (!completedContents) return '';

  const { courses, 'course-bundle': bundles, settings } = completedContents;
  const items = [];

  if (courses?.length) {
    items.push(sprintf(courses.length === 1 ? __('%d Course', 'tutor') : __('%d Courses', 'tutor'), courses.length));
  }

  if (bundles?.length) {
    items.push(sprintf(bundles.length === 1 ? __('%d Bundle', 'tutor') : __('%d Bundles', 'tutor'), bundles.length));
  }

  if (settings) {
    items.push(__('Settings', 'tutor'));
  }

  return items.join(', ');
};
