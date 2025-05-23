import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useState } from 'react';

import { type ExportContentResponse, type ImportExportModalState } from '@ImportExport/services/import-export';
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

interface ExportCompletedStateProps {
  state: ImportExportModalState;
  fileName: string;
  fileSize?: number;
  message?: string;
  completedContents?: ExportContentResponse['completed_contents'];
  failedCourseIds?: number[];
  failedBundleIds?: number[];
  onDownload?: (fileName: string) => void;
  onClose: () => void;
}

const ExportCompletedState = ({
  state,
  fileName,
  fileSize,
  message,
  completedContents,
  failedCourseIds = [],
  failedBundleIds = [],
  onDownload,
  onClose,
}: ExportCompletedStateProps) => {
  const [isFailedDataVisible, setIsFailedDataVisible] = useState(false);

  const imageSrc = {
    success: exportSuccessImage,
    error: exportErrorImage,
  };

  const titles = {
    success: __('Your File is Ready to Download!', 'tutor'),
    error: __('Export Failed', 'tutor'),
  };

  const subtitles = {
    success: __('Click the button below to download your file.', 'tutor'),
    error: message,
  };

  const formatCompletedItems = (completedContents?: ExportContentResponse['completed_contents']): string => {
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

  return (
    <div css={styles.success}>
      <img src={imageSrc[state as keyof typeof imageSrc]} alt={titles[state as keyof typeof titles]} />
      <div css={styles.successHeader}>
        <div css={styles.successTitle}>{titles[state as keyof typeof titles]}</div>
        <div css={styles.successSubtitle}>{subtitles[state as keyof typeof subtitles]}</div>
      </div>

      <Show
        when={state === 'success'}
        fallback={
          <div>
            <Button variant="primary" size="small" onClick={onClose}>
              {__('Okay', 'tutor')}
            </Button>
          </div>
        }
      >
        <div css={styles.reportList}>
          <div css={styles.reportWrapper}>
            <div css={styles.report}>
              <SVGIcon data-check-icon name="checkFilledWhite" width={24} height={24} />

              <div css={styles.reportInfo}>
                <div css={styles.reportLeft}>
                  <div>{__('Successfully Exported', 'tutor')}</div>
                  <div>{formatCompletedItems(completedContents)}</div>
                </div>
              </div>
            </div>
          </div>

          <Show when={[...failedBundleIds, ...failedCourseIds].length > 0}>
            <button
              css={[styleUtils.resetButton, styles.reportWrapper]}
              onClick={() => setIsFailedDataVisible(!isFailedDataVisible)}
            >
              <div css={styles.report}>
                <SVGIcon data-cross-icon name="crossCircle" width={28} height={28} />

                <div css={styles.reportInfo}>
                  <div css={styles.reportLeft}>
                    <div>{__('Failed to Export', 'tutor')}</div>
                    <div>
                      {failedCourseIds.length > 0 ? `${failedCourseIds.length} ${__('Courses', 'tutor')}` : ''}
                      {failedBundleIds.length > 0 && failedCourseIds.length > 0 ? ', ' : ''}
                      {failedBundleIds.length > 0 ? `${failedBundleIds.length} ${__('Bundles', 'tutor')}` : ''}
                    </div>
                  </div>

                  <SVGIcon data-down-icon name="chevronDown" width={24} height={24} />
                </div>
              </div>

              <Show when={isFailedDataVisible}>
                <Show when={failedCourseIds.length > 0}>
                  <div css={styles.failedItem}>
                    <label>{sprintf(__('Course IDs (%d)', 'tutor'), failedCourseIds.length)}</label>
                    <div css={styles.failedList}>
                      <For each={failedCourseIds}>
                        {(courseId) => (
                          <div key={courseId} css={styles.failedId}>
                            {courseId}
                          </div>
                        )}
                      </For>
                    </div>
                  </div>
                </Show>
                <Show when={failedBundleIds.length > 0}>
                  <div css={styles.failedItem}>
                    <label>{sprintf(__('Bundle IDs (%d)', 'tutor'), failedBundleIds.length)}</label>
                    <div css={styles.failedList}>
                      <For each={failedBundleIds}>
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
    </div>
  );
};

export default ExportCompletedState;

const styles = {
  success: css`
    ${styleUtils.display.flex('column')}
    align-items: center;
    gap: ${spacing[32]};
    padding: ${spacing[32]} ${spacing[24]};

    img {
      align-self: center;
      width: 109px;
      height: auto;
      object-fit: contain;
      object-position: center;
    }
  `,
  successHeader: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[8]};
    align-items: center;
    text-align: center;
  `,
  successTitle: css`
    ${typography.heading6('medium')};
  `,
  successSubtitle: css`
    ${typography.caption('regular')};
    color: ${colorTokens.text.subdued};
  `,
  reportList: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
    width: 100%;
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
    display: grid;
    grid-template-columns: repeat(4, 1fr);
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
    width: 100%;
  `,
  fileIcon: css`
    ${styleUtils.flexCenter()};
    width: 64px;
    height: 100%;
    border-right: 1px solid ${colorTokens.stroke.divider};
    flex-shrink: 0;

    svg {
      color: ${colorTokens.icon.hover};
    }
  `,
  fileRight: css`
    flex-grow: 1;
    ${styleUtils.display.flex()};
    justify-content: space-between;
    align-items: center;
    padding: ${spacing[10]} ${spacing[16]} ${spacing[10]} ${spacing[20]};
  `,
  fileDetails: css`
    flex-grow: 1;
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
  `,
  fileName: css`
    ${typography.small('medium')};
    color: ${colorTokens.text.subdued};
    ${styleUtils.text.ellipsis(1)};
  `,
  fileSize: css`
    ${typography.tiny()};
    color: ${colorTokens.text.hints};
  `,
};
