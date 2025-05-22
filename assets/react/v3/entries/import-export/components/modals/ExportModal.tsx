import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect, useState } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import ProBadge from '@TutorShared/atoms/ProBadge';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import CourseListModal from '@ImportExport/components/modals/CourseListModal';
import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import Logo from '@TutorShared/components/Logo';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import CourseCategorySelectModal from '@TutorShared/components/modals/CourseCategorySelectModal';
import { useModal, type ModalProps } from '@TutorShared/components/modals/Modal';

import {
  defaultExportFormData,
  useExportableContentQuery,
  type ExportableCourseContentType,
  type ExportContentResponse,
  type ExportFormData,
  type ImportExportModalState,
} from '@ImportExport/services/import-export';
import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { type Course } from '@TutorShared/services/course';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { formatBytes } from '@TutorShared/utils/util';

import exportErrorImage from '@SharedImages/import-export/export-error.webp';
import exportInProgressImage from '@SharedImages/import-export/export-inprogress.webp';
import exportSuccessImage from '@SharedImages/import-export/export-success.webp';

interface ExportModalProps extends ModalProps {
  onClose: () => void;
  onExport: (data: ExportFormData) => void;
  currentStep: ImportExportModalState;
  onDownload?: (fileName: string) => void;
  progress: number;
  fileSize?: number;
  message?: string;
  completedContents?: ExportContentResponse['completed_contents'];
  failedCourseIds?: ExportContentResponse['failed_course_ids'];
  failedBundleIds?: ExportContentResponse['failed_bundle_ids'];
}

interface BulkSelectionFormData {
  courses: Course[];
  'course-bundle': Course[];
}

const isTutorPro = !!tutorConfig.tutor_pro_url;

const fileName = `tutor_data_${Date.now()}.json`;

const ExportModal = ({
  onClose,
  onExport,
  currentStep,
  onDownload,
  progress,
  fileSize,
  message,
  completedContents,
  failedCourseIds = [],
  failedBundleIds = [],
}: ExportModalProps) => {
  const form = useFormWithGlobalError<ExportFormData>({
    defaultValues: defaultExportFormData,
  });

  const bulkSelectionForm = useFormWithGlobalError<BulkSelectionFormData>({
    defaultValues: {
      courses: [],
      'course-bundle': [],
    },
  });

  const [isFailedDataVisible, setIsFailedDataVisible] = useState(false);
  const { showModal } = useModal();
  const getExportableContentQuery = useExportableContentQuery();
  const exportableContent = getExportableContentQuery.data;

  const resetBulkSelection = (type: 'courses' | 'course-bundle') => {
    if (type === 'courses') {
      bulkSelectionForm.reset({
        courses: [],
        'course-bundle': bulkSelectionForm.getValues('course-bundle'),
      });
    }

    if (type === 'course-bundle') {
      bulkSelectionForm.reset({
        courses: bulkSelectionForm.getValues('courses'),
        'course-bundle': [],
      });
    }
  };

  useEffect(() => {
    if (getExportableContentQuery.isSuccess && getExportableContentQuery.data) {
      const courseIds = getExportableContentQuery.data.filter((item) => item.key === 'courses')[0].ids || [];
      const bundleIds = getExportableContentQuery.data.filter((item) => item.key === 'course-bundle')[0].ids || [];

      form.setValue('courses__ids', courseIds);
      form.setValue('course-bundle__ids', bundleIds);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [getExportableContentQuery.isSuccess]);

  /**
   * Returns properly formatted label for form data keys with appropriate count information
   *
   * @param {string} key - The form data key to get the label for
   * @returns {string | JSX.Element} - The formatted label with count information
   */
  const getLabelByFormDataKey = (key: string): string | JSX.Element => {
    // Early return if no exportable content data
    if (!exportableContent || !Array.isArray(exportableContent)) {
      return key;
    }

    // Create a formatted label with count
    const createLabelWithCount = (label: string, count: number | undefined) => {
      if (count === undefined) {
        return label;
      }

      return (
        <div css={styles.checkboxLabel}>
          {label}
          <span>{` (${count})`}</span>

          <Show when={!isTutorPro}>
            <ProBadge size="small" content={__('Pro', 'tutor')} />
          </Show>
        </div>
      );
    };

    // Handle sub-content items (keys with delimiter)
    if (key.includes('__')) {
      const [mainType, subType] = key.split('__');

      // Find parent content item
      const mainContent = exportableContent.find((item) => item.key === mainType);
      if (!mainContent) {
        return key;
      }

      // Check for bulk selections
      const hasSelectedItems =
        (mainType === 'courses' && bulkSelectionForm.getValues('courses').length > 0) ||
        (mainType === 'course-bundle' && bulkSelectionForm.getValues('course-bundle').length > 0);

      // Find sub-content item
      if (!mainContent.contents) {
        return key;
      }

      const subContent = mainContent.contents.find(
        (content) => content.key === (subType as ExportableCourseContentType),
      );
      if (!subContent) {
        return key;
      }

      // Don't show count when parent has selected items
      return hasSelectedItems ? subContent.label : createLabelWithCount(subContent.label, subContent.count);
    }

    // Handle main content items
    const content = exportableContent.find((item) => item.key === key);
    if (!content) {
      return key;
    }

    // Get selection count for dynamic content types
    const getSelectionCount = () => {
      const countMap: Record<string, number> = {
        courses: bulkSelectionForm.getValues('courses').length,
        'course-bundle': bulkSelectionForm.getValues('course-bundle').length,
      };

      return countMap[key] || 0;
    };

    const selectedCount = getSelectionCount();

    // Use selected count if available, otherwise use total count
    return selectedCount > 0
      ? createLabelWithCount(content.label, selectedCount)
      : createLabelWithCount(content.label, content.count);
  };

  const handleClose = () => {
    form.reset();
    onClose();
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

  const renderExportableContentOptions = () => {
    if (getExportableContentQuery.isLoading) {
      return <LoadingSection />;
    }

    if (!exportableContent || !Array.isArray(exportableContent)) {
      return null;
    }

    const componentMapping = {
      courses: {
        modal: {
          component: CourseListModal,
          props: {
            title: __('Select Courses', 'tutor'),
            form: bulkSelectionForm,
          },
        },
        bulkSelectionButtonLabel:
          bulkSelectionForm.getValues('courses').length > 0
            ? __('Edit Selected Courses', 'tutor')
            : __('Select Specific Courses', 'tutor'),
      },
      'course-bundle': {
        modal: {
          component: CourseCategorySelectModal,
          props: {
            title: __('Select Bundles', 'tutor'),
            type: 'bundles',
            form: bulkSelectionForm,
          },
        },
        bulkSelectionButtonLabel:
          bulkSelectionForm.getValues('course-bundle').length > 0
            ? __('Edit Selected Bundles', 'tutor')
            : __('Select Specific Bundles', 'tutor'),
      },
    };

    return (
      <>
        {exportableContent.map((contentType) => {
          const contentKey = contentType.key;
          const isChecked = form.watch(contentKey);

          const bulkSelectionCount =
            bulkSelectionForm.getValues(contentKey as keyof BulkSelectionFormData)?.length || 0;

          if (contentKey === 'keep_media_files') {
            return;
          }

          return (
            <div key={contentKey} css={styles.checkboxRow}>
              <div css={styles.checkBoxWithButton}>
                <div css={styles.checkBoxWithAction}>
                  <Controller
                    control={form.control}
                    name={contentKey}
                    render={(controllerProps) => (
                      <FormCheckbox
                        {...controllerProps}
                        disabled={contentKey !== 'settings' && !isTutorPro}
                        label={getLabelByFormDataKey(contentKey)}
                      />
                    )}
                  />
                  <Show when={bulkSelectionCount > 0}>
                    <Button
                      variant="danger"
                      size="small"
                      onClick={() => resetBulkSelection(contentKey as keyof BulkSelectionFormData)}
                      icon={<SVGIcon name="cross" width={16} height={16} />}
                    >
                      {__('Clear', 'tutor')}
                    </Button>
                  </Show>
                </div>

                {/* Show select button for courses and bundles */}
                <Show when={isChecked && ['courses', 'course-bundle'].includes(contentKey)}>
                  <Button
                    variant="secondary"
                    buttonCss={styles.selectButton}
                    size="small"
                    onClick={() => {
                      const modalConfig = componentMapping[contentKey as keyof typeof componentMapping];
                      showModal({
                        component: modalConfig.modal.component,
                        // eslint-disable-next-line @typescript-eslint/no-explicit-any
                        props: modalConfig.modal.props as any,
                      });
                    }}
                  >
                    {componentMapping[contentKey as keyof typeof componentMapping]?.bulkSelectionButtonLabel}
                  </Button>
                </Show>
              </div>

              {/* Render sub-content checkboxes for courses and bundles */}
              <Show when={isChecked && (contentType?.contents || []).length > 0}>
                <div css={styles.childCheckboxWrapper}>
                  <div css={styles.contentCheckboxWrapper}>
                    <For each={contentType?.contents || []}>
                      {(subContent) => {
                        const formKey = `${contentKey}__${subContent.key}`;
                        return (
                          <div key={formKey} css={styles.checkboxRow({ isContent: true })}>
                            <Controller
                              control={form.control}
                              name={formKey as 'courses'}
                              render={(controllerProps) => (
                                <FormCheckbox
                                  {...controllerProps}
                                  disabled={!isTutorPro && !isChecked}
                                  label={getLabelByFormDataKey(formKey)}
                                />
                              )}
                            />
                          </div>
                        );
                      }}
                    </For>
                  </div>
                </div>
              </Show>
            </div>
          );
        })}
      </>
    );
  };

  const renderInitialState = () => {
    return (
      <div css={styles.wrapper}>
        <div css={styles.formWrapper}>
          <div css={styles.formTitle}>{__('What do you want to export', 'tutor')}</div>
          <div css={styles.checkboxWrapper}>{renderExportableContentOptions()}</div>

          <Show
            when={
              (getExportableContentQuery?.data || []).some((item) => item.key === 'keep_media_files') &&
              (form.getValues('courses') || form.getValues('course-bundle'))
            }
          >
            <div css={styles.contentCheckboxFooter}>
              <Controller
                control={form.control}
                name="keep_media_files"
                render={(controllerProps) => (
                  <FormCheckbox
                    {...controllerProps}
                    label={__('Keep Media Files', 'tutor')}
                    disabled={!isTutorPro}
                    description={
                      // prettier-ignore
                      __('If you check this media files will be exported along with the selected data', 'tutor')
                    }
                  />
                )}
              />
            </div>
          </Show>
        </div>
      </div>
    );
  };

  const renderProgressState = () => {
    return (
      <div css={styles.progress}>
        <img src={exportInProgressImage} alt={__('Exporting...', 'tutor')} />
        <div css={styles.progressHeader}>
          <div css={typography.caption()}>{__('Getting your files ready!', 'tutor')}</div>
          <div css={styles.progressCount}>{progress}%</div>
        </div>
        <div css={styles.progressBar({ progress })} />
        <div css={styles.progressInfo} key={message}>
          {message || fileName}
        </div>
      </div>
    );
  };

  const renderCompletedState = (state: ImportExportModalState) => {
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
              <Button variant="primary" size="small" onClick={handleClose}>
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
                <div css={styles.fileName}>{fileName}</div>
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

  const renderModalContent = {
    initial: renderInitialState(),
    progress: renderProgressState(),
    success: renderCompletedState('success'),
    error: renderCompletedState('error'),
  };

  return (
    <BasicModalWrapper
      onClose={handleClose}
      maxWidth={currentStep === 'initial' ? 823 : 500}
      isCloseAble={currentStep !== 'progress'}
      entireHeader={
        <Show when={currentStep === 'initial'} fallback={<>&nbsp;</>}>
          <div css={styles.header}>
            <div css={styles.headerTitle}>
              <Logo
                wrapperCss={css`
                  padding-left: 0;
                `}
              />
              <span>{__('Exporter', 'tutor')}</span>
            </div>
            <div>
              <Button
                variant="primary"
                size="small"
                icon={<SVGIcon name="export" width={24} height={24} />}
                disabled={
                  !Object.entries(form.getValues()).some(([key, value]) => {
                    if (!key.includes('__')) {
                      return value === true;
                    }
                    return false;
                  })
                }
                onClick={form.handleSubmit((data) => {
                  const { courses, 'course-bundle': bundles } = bulkSelectionForm.getValues();
                  onExport?.({
                    ...data,
                    courses__ids:
                      courses.length > 0 ? courses.map((course) => course.id) : form.getValues('courses__ids'),
                    'course-bundle__ids':
                      bundles.length > 0 ? bundles.map((bundle) => bundle.id) : form.getValues('course-bundle__ids'),
                  });
                })}
              >
                {__('Export', 'tutor')}
              </Button>
            </div>
          </div>
        </Show>
      }
    >
      {renderModalContent[currentStep]}
    </BasicModalWrapper>
  );
};

export default ExportModal;

const styles = {
  header: css`
    height: 64px;
    width: 100%;
    ${styleUtils.display.flex()}
    justify-content: space-between;
    align-items: center;
    padding-inline: 88px;
  `,
  headerTitle: css`
    ${styleUtils.display.flex()}
    align-items: center;
    gap: ${spacing[4]};
    ${typography.heading6('medium')}
    color: ${colorTokens.text.brand};
  `,
  wrapper: css`
    height: calc(100vh - 140px);
    max-height: 680px;
    padding: ${spacing[32]} 107px ${spacing[32]} 107px;
    background-color: ${colorTokens.surface.courseBuilder};
    border-top: 1px solid ${colorTokens.stroke.divider};
  `,
  formWrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[12]};
    padding: ${spacing[16]} ${spacing[20]};
    border-radius: ${borderRadius.card};
    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.divider};
  `,
  formTitle: css`
    ${typography.caption()};
    color: ${colorTokens.text.title};
  `,
  checkboxWrapper: css`
    ${styleUtils.display.flex('column')}
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius.card};
    overflow: hidden;
  `,
  checkboxRow: ({ isContent = false }) => css`
    padding: ${spacing[8]} ${spacing[16]};

    ${!isContent &&
    css`
      &:not(:only-of-type):not(:last-of-type) {
        border-bottom: 1px solid ${colorTokens.stroke.divider};
      }
    `}
  `,
  checkboxLabel: css`
    ${styleUtils.display.flex()}
    align-items: center;
    gap: ${spacing[4]};
    padding-block: ${spacing[2]};

    span {
      color: ${colorTokens.text.hints};
    }
  `,
  checkBoxWithButton: css`
    ${styleUtils.display.flex()}
    justify-content: space-between;
    align-items: center;

    button {
      flex-shrink: 0;
    }
  `,
  checkBoxWithAction: css`
    ${styleUtils.display.flex()}
    align-items: center;
    gap: ${spacing[8]};

    button {
      flex-shrink: 0;
    }
  `,
  childCheckboxWrapper: css`
    margin-top: ${spacing[8]};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius.card};
    overflow: hidden;
  `,
  contentCheckboxWrapper: css`
    display: grid;
    grid-template-columns: repeat(2, 1fr);
  `,
  contentCheckboxFooter: css`
    padding: ${spacing[8]} ${spacing[16]} ${spacing[8]} ${spacing[16]};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius.card};
    background-color: ${colorTokens.primary[30]};

    &:only-of-type {
      border-top: none;
    }
  `,
  progress: css`
    width: 100%;
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
    padding: ${spacing[32]} 91px ${spacing[48]} 91px;

    img {
      align-self: center;
      width: 120px;
      height: 'auto';
      object-fit: contain;
      object-position: center;
      margin-bottom: ${spacing[36]};
    }
  `,
  progressHeader: css`
    ${styleUtils.display.flex()};
    justify-content: space-between;
  `,
  progressCount: css`
    ${styleUtils.flexCenter()};
    ${typography.tiny('bold')};
    padding: ${spacing[2]} ${spacing[4]};
    background-color: ${colorTokens.background.status.success};
    color: ${colorTokens.text.success};
    border-radius: ${borderRadius[12]};
  `,
  progressBar: ({ progress = 0 }) => css`
    position: relative;
    width: 100%;
    height: 6px;
    background-color: ${colorTokens.color.black[10]};
    border-radius: ${borderRadius[50]};
    overflow: hidden;

    &::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: ${colorTokens.bg.success};
      border-radius: ${borderRadius[50]};
      transition: width 0.3s ease-in;
      width: ${progress}%;
    }

    @keyframes progress-stripes {
      from {
        background-position: 1rem 0;
      }
      to {
        background-position: 0 0;
      }
    }
  `,
  progressInfo: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
  `,
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
  selectButton: css`
    background-color: ${colorTokens.background.white};
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
  `,
  failedId: css`
    ${typography.tiny()};
    background-color: ${colorTokens.background.white};
    color: ${colorTokens.text.subdued};
    padding: ${spacing[2]} ${spacing[8]};
    border-radius: ${borderRadius[4]};
  `,
};
