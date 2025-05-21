import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import Logo from '@TutorShared/components/Logo';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import { useModal, type ModalProps } from '@TutorShared/components/modals/Modal';

import {
  defaultExportFormData,
  useExportableContentQuery,
  type ExportableCourseContentType,
  type ExportFormData,
  type ImportExportModalState,
} from '@ImportExport/services/import-export';
import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { formatBytes } from '@TutorShared/utils/util';

import exportInProgressImage from '@SharedImages/import-export/export-inprogress.webp';
import exportSuccessImage from '@SharedImages/import-export/export-success.webp';
import CourseCategorySelectModal from '@TutorShared/components/modals/CourseCategorySelectModal';
import { type Course } from '@TutorShared/services/course';
import { useEffect } from 'react';
import CourseListModal from './CourseListModal';

interface ExportModalProps extends ModalProps {
  onClose: () => void;
  onExport: (data: ExportFormData) => void;
  currentStep: ImportExportModalState;
  onDownload?: (fileName: string) => void;
  progress: number;
  fileSize?: number;
}

interface BulkSelectionFormData {
  courses: Course[];
  bundles: Course[];
}

const isTutorPro = !!tutorConfig.tutor_pro_url;

const fileName = `tutor_data_${Date.now()}.json`;

const ExportModal = ({ onClose, onExport, currentStep, onDownload, progress, fileSize }: ExportModalProps) => {
  const form = useFormWithGlobalError<ExportFormData>({
    defaultValues: defaultExportFormData,
  });

  const bulkSelectionForm = useFormWithGlobalError<BulkSelectionFormData>({
    defaultValues: {
      courses: [],
      bundles: [],
    },
  });

  const { showModal } = useModal();
  const getExportableContentQuery = useExportableContentQuery();
  const exportableContent = getExportableContentQuery.data;

  const resetBulkSelection = (type: 'courses' | 'bundles') => {
    if (type === 'courses') {
      bulkSelectionForm.reset({
        courses: [],
        bundles: bulkSelectionForm.getValues('bundles'),
      });
    }

    if (type === 'bundles') {
      bulkSelectionForm.reset({
        courses: bulkSelectionForm.getValues('courses'),
        bundles: [],
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

  const getLabelByFormDateKey = (key: string) => {
    // Early return if no exportable content data
    if (!exportableContent || !Array.isArray(exportableContent)) {
      return key;
    }

    // Check if the key contains delimiter '__'
    if (key.includes('__')) {
      // This is a sub-content item (like 'courses__lessons')
      const [mainType, subType] = key.split('__');

      // Find the parent content item
      const mainContent = exportableContent.find((item) => item.key === mainType);
      if (!mainContent) {
        return key;
      }

      // Check if there are selected items in the parent content
      const hasSelectedItems =
        (mainType === 'courses' && bulkSelectionForm.getValues('courses').length > 0) ||
        (mainType === 'course-bundle' && bulkSelectionForm.getValues('bundles').length > 0);

      // Find the sub-content item
      const subContentType = subType as ExportableCourseContentType;
      if (!subContentType || !mainContent.contents) {
        return key;
      }

      const subContent = mainContent.contents.find((content) => content.key === subContentType);
      if (!subContent) {
        return key;
      }

      // If parent has selected items, don't show count for sub-content
      if (hasSelectedItems) {
        return subContent.label;
      }

      // Return formatted label with count for non-selected state
      return `${subContent.label} (${subContent.count})`;
    } else {
      // This is a main content item (like 'courses', 'course-bundle', 'settings')
      const content = exportableContent.find((item) => item.key === key);
      if (!content) {
        return key;
      }

      // Get selection count for courses and bundles
      let selectedCount = 0;
      if (key === 'courses') {
        selectedCount = bulkSelectionForm.getValues('courses').length;
      } else if (key === 'course-bundle') {
        selectedCount = bulkSelectionForm.getValues('bundles').length;
      }

      // Use selected count if available (without "selected" text)
      if (selectedCount > 0) {
        return `${content.label} (${selectedCount})`;
      }

      // Default to total count when no selections
      return content.count !== undefined ? `${content.label} (${content.count})` : content.label;
    }
  };

  const handleClose = () => {
    form.reset();
    onClose();
  };

  const renderExportableContentOptions = () => {
    if (getExportableContentQuery.isLoading) {
      return <LoadingSection />;
    }

    if (!exportableContent || !Array.isArray(exportableContent)) {
      return null;
    }

    return (
      <>
        {exportableContent.map((contentType) => {
          const contentKey = contentType.key;
          const isChecked = form.watch(contentKey);

          // Calculate bulk selection count
          const bulkSelectionCount =
            contentKey === 'courses'
              ? bulkSelectionForm.getValues('courses').length
              : contentKey === 'course-bundle'
                ? bulkSelectionForm.getValues('bundles').length
                : 0;

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
                        label={getLabelByFormDateKey(contentKey)}
                      />
                    )}
                  />
                  <Show when={bulkSelectionCount > 0}>
                    <Button
                      variant="danger"
                      size="small"
                      onClick={() => resetBulkSelection(contentKey === 'courses' ? 'courses' : 'bundles')}
                      icon={<SVGIcon name="cross" width={16} height={16} />}
                    >
                      {__('Clear', 'tutor')}
                    </Button>
                  </Show>
                </div>

                {/* Show select button for courses and bundles */}
                <Show when={isChecked && (contentKey === 'courses' || contentKey === 'course-bundle')}>
                  <Button
                    variant="secondary"
                    buttonCss={styles.selectButton}
                    size="small"
                    onClick={() => {
                      const modalComponent = contentKey === 'courses' ? CourseListModal : CourseCategorySelectModal;
                      const modalTitle =
                        contentKey === 'courses' ? __('Select Courses', 'tutor') : __('Select Bundles', 'tutor');

                      showModal({
                        component: modalComponent,
                        props: {
                          title: modalTitle,
                          ...(contentKey === 'courses'
                            ? {
                                addedCourses: bulkSelectionForm.getValues('courses'),
                                form: bulkSelectionForm,
                              }
                            : {
                                type: 'bundles',
                                form: bulkSelectionForm,
                              }),
                        },
                      });
                    }}
                  >
                    {contentKey === 'courses' && bulkSelectionCount > 0
                      ? __('Edit Selected Courses', 'tutor')
                      : contentKey === 'courses'
                        ? __('Select Specific Courses', 'tutor')
                        : __('Select Specific Bundles', 'tutor')}
                  </Button>
                </Show>
              </div>

              {/* Render sub-content checkboxes for courses and bundles */}
              <Show when={isChecked && (contentKey === 'courses' || contentKey === 'course-bundle')}>
                <div css={styles.childCheckboxWrapper}>
                  {contentKey === 'courses' && contentType.contents && (
                    <div css={styles.contentCheckboxWrapper}>
                      {contentType.contents.map((subContent) => {
                        const formKey = `${contentKey}__${subContent.key}`;
                        return (
                          <div key={formKey} css={styles.checkboxRow({ isContent: true })}>
                            <Controller
                              control={form.control}
                              name={formKey as any}
                              render={(controllerProps) => (
                                <FormCheckbox
                                  {...controllerProps}
                                  disabled={!isTutorPro}
                                  label={getLabelByFormDateKey(formKey)}
                                />
                              )}
                            />
                          </div>
                        );
                      })}
                    </div>
                  )}

                  <div css={styles.contentCheckboxFooter}>
                    <Controller
                      control={form.control}
                      name={`${contentKey}__keep_media_files` as any}
                      render={(controllerProps) => (
                        <FormCheckbox
                          {...controllerProps}
                          disabled={!isTutorPro}
                          label={__('Keep media files', 'tutor')}
                        />
                      )}
                    />
                  </div>
                </div>
              </Show>
            </div>
          );
        })}
      </>
    );
  };

  const renderInitialState = ({
    bulkSelection,
  }: {
    bulkSelection: {
      courses: number;
      bundles: number;
    };
  }) => {
    return (
      <div css={styles.wrapper}>
        <div css={styles.formWrapper}>
          <div css={styles.formTitle}>{__('What do you want to export', 'tutor')}</div>
          <div css={styles.checkboxWrapper}>{renderExportableContentOptions()}</div>
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
        <div css={styles.progressInfo}>{fileName}</div>
      </div>
    );
  };

  const renderSuccessState = () => {
    return (
      <div css={styles.success}>
        <img src={exportSuccessImage} alt={__('Export completed successfully', 'tutor')} />
        <div css={styles.successHeader}>
          <div css={styles.successTitle}>{__('Your File is Ready to Download!', 'tutor')}</div>
          <div css={styles.successSubtitle}>{__('Click the button below to download your file.', 'tutor')}</div>
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
      </div>
    );
  };

  const renderModalContent = {
    initial: renderInitialState({
      bulkSelection: {
        courses: bulkSelectionForm.getValues('courses').length,
        bundles: bulkSelectionForm.getValues('bundles').length,
      },
    }),
    progress: renderProgressState(),
    success: renderSuccessState(),
    error: <div>{__('Export failed', 'tutor')}</div>,
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
                disabled={!form.formState.isDirty}
                onClick={form.handleSubmit((data) => {
                  const { courses, bundles } = bulkSelectionForm.getValues();
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
    border-top: 1px solid ${colorTokens.stroke.divider};
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
};
