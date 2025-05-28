import { css } from '@emotion/react';
import { __, _n, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useState } from 'react';

import {
  type ImportExportContentResponseBase,
  type ImportExportModalState,
} from '@ImportExport/services/import-export';
import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { formatBytes } from '@TutorShared/utils/util';

import exportErrorImage from '@SharedImages/import-export/export-error.webp';
import exportSuccessImage from '@SharedImages/import-export/export-success.webp';
import importErrorImage from '@SharedImages/import-export/import-error.webp';
import importSuccessImage from '@SharedImages/import-export/import-success.webp';

interface ImportExportCompletedStateProps {
  state: ImportExportModalState;
  fileSize?: number;
  message?: string;
  completedContents?: ImportExportContentResponseBase['completed_contents'];
  onDownload?: (fileName: string) => void;
  onClose: () => void;
  importFileName?: string;
  type: 'import' | 'export';
}
const fileName = `tutor-lms-data-${format(new Date(), 'yyyy-MM-dd-HH-mm-ss')}.json`;

const ImportExportCompletedState = ({
  state,
  fileSize,
  message,
  completedContents,
  onDownload,
  onClose,
  type,
}: ImportExportCompletedStateProps) => {
  const [isFailedDataVisible, setIsFailedDataVisible] = useState(false);

  const successFullyCompletedCourses = completedContents?.courses?.success || [];
  const successFullyCompletedBundles = completedContents?.['course-bundle']?.success || [];
  const successFullyCompletedSettings = completedContents?.settings;

  const completedWithErrorsCourses = completedContents?.courses?.failed || [];
  const completedWithErrorsBundles = completedContents?.['course-bundle']?.failed || [];

  const contentMapping = {
    import: {
      image: {
        success: importSuccessImage,
        error: importErrorImage,
      },
      imageAlt: {
        success: __('Import Successful', 'tutor'),
        error: __('Import Failed', 'tutor'),
      },
      header: {
        success: __('Import Complete!', 'tutor'),
        error: __('Import Failed!', 'tutor'),
      },
      subtitle: {
        success:
          completedWithErrorsCourses.length || completedWithErrorsBundles.length
            ? // prettier-ignore
              __( "Your Tutor LMS data was successfully imported. However, some items couldn't be imported. Here's the list:", 'tutor')
            : // prettier-ignore
              __('Your Tutor LMS data has been successfully imported.', 'tutor'),
        error: message || __('Something went wrong during import. Please try again!', 'tutor'),
      },
      reportList: {
        success: __('Successfully Imported', 'tutor'),
        error: __('Failed to Import', 'tutor'),
      },
    },
    export: {
      image: {
        success: exportSuccessImage,
        error: exportErrorImage,
      },
      imageAlt: {
        success: __('Export Successful', 'tutor'),
        error: __('Export Failed!', 'tutor'),
      },
      header: {
        success: __('Your Data is Ready to Download!', 'tutor'),
        error: __('Export Failed', 'tutor'),
      },
      subtitle: {
        success:
          completedWithErrorsCourses.length || completedWithErrorsBundles.length
            ? // prettier-ignore
              __('The export process has finished. However, certain items could not be exported. Check the summary below:', 'tutor')
            : // prettier-ignore
              __('Download the JSON file and use it to import your data into another Tutor LMS website.', 'tutor'),
        error: message,
      },
      reportList: {
        success: __('Successfully Exported', 'tutor'),
        error: __('Failed to Export', 'tutor'),
      },
    },
  };

  const formatItemCount = (count: number, type: 'course' | 'bundle'): string => {
    if (type === 'course') {
      return sprintf(_n('Course (%d)', 'Courses (%d)', count, 'tutor'), count);
    }

    return sprintf(_n('Bundle (%d)', 'Bundles (%d)', count, 'tutor'), count);
  };

  const formatFailedItems = (): string => {
    const failedItems: string[] = [];

    if (completedWithErrorsCourses.length > 0) {
      const coursesText = formatItemCount(completedWithErrorsCourses.length, 'course');
      failedItems.push(coursesText);
    }

    if (completedWithErrorsBundles.length > 0) {
      const bundlesText = formatItemCount(completedWithErrorsBundles.length, 'bundle');
      failedItems.push(bundlesText);
    }

    return failedItems.join(', ');
  };

  const formatCompletedItems = (): string => {
    if (!completedContents) return '';

    const { settings } = completedContents;
    const formattedItems: string[] = [];

    if (successFullyCompletedCourses.length) {
      const courseText = formatItemCount(successFullyCompletedCourses.length, 'course');
      formattedItems.push(courseText);
    }

    if (successFullyCompletedBundles.length) {
      const bundleText = formatItemCount(successFullyCompletedBundles.length, 'bundle');
      formattedItems.push(bundleText);
    }

    if (settings) {
      formattedItems.push(__('Settings', 'tutor'));
    }

    return formattedItems.join(', ');
  };

  return (
    <div css={styles.statusWrapper}>
      <img
        src={
          contentMapping[type as keyof typeof contentMapping].image[
            state as keyof (typeof contentMapping)['import']['image']
          ]
        }
        alt={
          contentMapping[type as keyof typeof contentMapping].imageAlt[
            state as keyof (typeof contentMapping)['import']['imageAlt']
          ]
        }
      />
      <div css={styles.statusHeader}>
        <div css={styles.statusTitle}>
          {
            contentMapping[type as keyof typeof contentMapping].header[
              state as keyof (typeof contentMapping)['import']['header']
            ]
          }
        </div>

        <div css={styles.statusSubtitle}>
          {
            contentMapping[type as keyof typeof contentMapping].subtitle[
              state as keyof (typeof contentMapping)['import']['subtitle']
            ]
          }
        </div>
      </div>

      <Show
        when={state === 'success'}
        fallback={
          <div css>
            <Button variant="primary" size="small" onClick={onClose}>
              {__('Okay', 'tutor')}
            </Button>
          </div>
        }
      >
        <div css={styles.reportList}>
          <Show
            when={
              [...successFullyCompletedCourses, ...successFullyCompletedBundles].length > 0 ||
              successFullyCompletedSettings
            }
          >
            <div css={styles.reportWrapper}>
              <div css={styles.report}>
                <SVGIcon data-check-icon name="checkFilledWhite" width={24} height={24} />

                <div css={styles.reportInfo}>
                  <div css={styles.reportLeft}>
                    <div>{contentMapping[type as keyof typeof contentMapping].reportList.success}</div>
                    <div>{formatCompletedItems()}</div>
                  </div>
                </div>
              </div>
            </div>
          </Show>

          <Show when={[...completedWithErrorsCourses, ...completedWithErrorsBundles].length > 0}>
            <button
              css={[styleUtils.resetButton, styles.reportWrapper]}
              onClick={() => setIsFailedDataVisible(!isFailedDataVisible)}
            >
              <div css={styles.report}>
                <SVGIcon data-cross-icon name="crossCircle" width={28} height={28} />

                <div css={styles.reportInfo}>
                  <div css={styles.reportLeft}>
                    <div>{contentMapping[type as keyof typeof contentMapping].reportList.error}</div>
                    <div>{formatFailedItems()}</div>
                  </div>

                  <SVGIcon data-down-icon name="chevronDown" width={24} height={24} />
                </div>
              </div>

              <Show when={isFailedDataVisible}>
                <Show when={completedWithErrorsCourses.length > 0}>
                  <div css={styles.failedItem}>
                    <label>
                      {completedWithErrorsCourses.length > 1
                        ? sprintf(__('Course IDs (%d)', 'tutor'), completedWithErrorsCourses.length)
                        : sprintf(__('Course ID (%d)', 'tutor'), completedWithErrorsCourses.length)}
                    </label>
                    <div css={styles.failedList}>
                      <For each={completedWithErrorsCourses}>
                        {(courseId) => (
                          <div key={courseId} css={styles.failedId}>
                            {courseId}
                          </div>
                        )}
                      </For>
                    </div>
                  </div>
                </Show>
                <Show when={completedWithErrorsBundles.length > 0}>
                  <div css={styles.failedItem}>
                    <label>
                      {completedWithErrorsBundles.length > 1
                        ? sprintf(__('Bundle IDs (%d)', 'tutor'), completedWithErrorsBundles.length)
                        : sprintf(__('Bundle ID (%d)', 'tutor'), completedWithErrorsBundles.length)}
                    </label>
                    <div css={styles.failedList}>
                      <For each={completedWithErrorsBundles}>
                        {(bundleId) => (
                          <div key={bundleId} css={styles.failedId}>
                            {bundleId}
                          </div>
                        )}
                      </For>
                    </div>
                  </div>
                </Show>
              </Show>
            </button>
          </Show>
        </div>
        <Show when={type === 'export'}>
          <div css={styles.file}>
            <div css={styles.fileIcon}>
              <SVGIcon name="attachmentLine" width={24} height={24} />
            </div>
            <div css={styles.fileRight}>
              <div css={styles.fileDetails}>
                <div css={styles.fileName} title={fileName}>
                  {fileName}
                </div>
                <div css={styles.fileSize}>{formatBytes(fileSize || 0)}</div>
              </div>

              <div>
                <Button
                  variant="primary"
                  size="small"
                  icon={<SVGIcon name="download" width={24} height={24} />}
                  onClick={() => onDownload?.(fileName)}
                >
                  {__('Download', 'tutor')}
                </Button>
              </div>
            </div>
          </div>
        </Show>
        <Show when={type === 'import'}>
          <div>
            <Button variant="primary" size="small" onClick={onClose}>
              {__('Okay', 'tutor')}
            </Button>
          </div>
        </Show>
      </Show>
    </div>
  );
};

export default ImportExportCompletedState;

const styles = {
  statusWrapper: css`
    ${styleUtils.display.flex('column')}
    align-items: center;
    gap: ${spacing[16]};
    padding: ${spacing[32]} ${spacing[24]};

    img {
      align-self: center;
      width: 109px;
      height: auto;
      object-fit: contain;
      object-position: center;
    }
  `,
  statusHeader: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[8]};
    align-items: center;
    text-align: center;
    padding-top: ${spacing[16]};
  `,
  statusTitle: css`
    ${typography.heading6('medium')};
  `,
  statusSubtitle: css`
    ${typography.caption('regular')};
    color: ${colorTokens.text.subdued};
  `,
  reportList: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
    width: 100%;
    padding-top: ${spacing[16]};
  `,
  reportWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
    background-color: ${colorTokens.primary[30]};
    border-radius: ${borderRadius[6]};
    padding: ${spacing[8]} ${spacing[12]};
  `,
  report: css`
    width: 100%;
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[12]};

    [data-check-icon] {
      color: ${colorTokens.icon.success};
      flex-shrink: 0;
    }

    [data-cross-icon] {
      color: ${colorTokens.icon.error};
      flex-shrink: 0;
    }

    [data-down-icon] {
      color: ${colorTokens.icon.default};
      flex-shrink: 0;
    }
  `,
  reportInfo: css`
    width: 100%;
    ${styleUtils.display.flex()};
    justify-content: space-between;
    align-items: center;
  `,
  reportLeft: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};

    div:first-of-type {
      ${typography.small()};
      color: ${colorTokens.text.title};
    }

    div:last-of-type {
      ${typography.small('medium')};
      color: ${colorTokens.text.primary};
    }
  `,
  failedItem: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
    padding: ${spacing[8]} ${spacing[12]};
    background-color: ${colorTokens.primary[30]};
    border-radius: ${borderRadius[6]};

    label {
      ${typography.small('medium')};
    }
  `,
  failedList: css`
    display: flex;
    flex-wrap: wrap;
    border-radius: ${borderRadius[6]};
    gap: 4px;
  `,
  failedId: css`
    ${typography.tiny()};
    background-color: ${colorTokens.background.white};
    color: ${colorTokens.text.subdued};
    padding: ${spacing[2]} ${spacing[8]};
    border-radius: ${borderRadius[4]};
  `,
  file: css`
    ${styleUtils.display.flex()};
    height: 64px;
    border: 1px solid ${colorTokens.stroke.divider};
    overflow: hidden;
    border-radius: ${borderRadius[6]};
  `,
  fileIcon: css`
    ${styleUtils.flexCenter()};
    width: 64px;
    height: 100%;
    border-right: 1px solid ${colorTokens.stroke.divider};
    flex-shrink: 0;
    background-color: #f7f7f7;

    svg {
      color: ${colorTokens.icon.hover};
    }
  `,
  fileRight: css`
    flex-grow: 1;
    ${styleUtils.display.flex()};
    gap: ${spacing[8]};
    justify-content: space-between;
    align-items: center;
    padding: ${spacing[10]} ${spacing[16]} ${spacing[10]} ${spacing[20]};
  `,
  fileDetails: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
  `,
  fileName: css`
    ${typography.small('medium')};
    color: ${colorTokens.text.subdued};
    ${styleUtils.text.ellipsis(1)};
    width: 100%;
  `,
  fileSize: css`
    ${typography.tiny()};
    color: ${colorTokens.text.hints};
  `,
};
