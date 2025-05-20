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
  type ExportableContent,
  type ExportFormData,
  type ImportExportModalState,
  useExportableContentQuery,
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
import ProBadge from '@TutorShared/atoms/ProBadge';
import { useEffect } from 'react';
import CourseCategorySelectModal from '@TutorShared/components/modals/CourseCategorySelectModal';
import { type Course } from '@TutorShared/services/course';

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
  const exportableContent = getExportableContentQuery.data as ExportableContent;

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
      form.setValue('courses.ids', getExportableContentQuery.data.courses?.ids || []);
      form.setValue('bundles.ids', getExportableContentQuery.data.bundles?.ids || []);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [getExportableContentQuery.isSuccess]);

  const isCoursesChecked = form.watch('courses.isChecked');

  const getLabelByFormDateKey = ({
    key,
    bulkSelection = {
      courses: 0,
      bundles: 0,
    },
    showProBadge = false,
  }: {
    key: string;
    bulkSelection?: {
      courses: number;
      bundles: number;
    };
    showProBadge?: boolean;
  }) => {
    let label = '';
    const hasContent = key.includes('[');

    if (hasContent) {
      const contentKey = hasContent ? key.split('[')[1].split(']')[0] : key;
      const parentKey = key.split('[')[0] as keyof ExportableContent;
      const contents = exportableContent[parentKey].contents as Record<string, string>;
      const contentLabel = contents[contentKey];
      label = bulkSelection?.courses > 0 ? contentLabel?.split('(')[0] : contentLabel;
    } else {
      if (
        (key === 'courses' && bulkSelection?.courses > 0) ||
        (key === 'course-bundle' && bulkSelection?.bundles > 0)
      ) {
        const count = key === 'courses' ? bulkSelection.courses : bulkSelection.bundles;
        label = `${exportableContent?.[key as keyof ExportableContent]?.label.split('(')[0]}(${count})`;
      } else {
        label = exportableContent?.[key as keyof ExportableContent]?.label;
      }
    }

    const [labelText, count = ''] = (label || '').split('(') || [];

    return (
      <div css={styles.checkboxLabel}>
        <span>
          {labelText}
          <Show when={count}>
            <span css={{ color: colorTokens.text.hints }}>{`(${count}`}</span>
          </Show>
        </span>

        <Show when={showProBadge && !isTutorPro}>
          <ProBadge content={__('Pro', 'tutor')} size="small" />
        </Show>
      </div>
    );
  };

  const handleClose = () => {
    form.reset();
    onClose();
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
          <div css={styles.checkboxWrapper}>
            {getExportableContentQuery.isLoading ? (
              <LoadingSection />
            ) : (
              <>
                <div css={styles.checkboxRow}>
                  <div css={styles.checkBoxWithButton}>
                    <div css={styles.checkBoxWithAction}>
                      <Controller
                        control={form.control}
                        name="courses.isChecked"
                        render={(controllerProps) => (
                          <FormCheckbox
                            {...controllerProps}
                            disabled={!isTutorPro}
                            label={getLabelByFormDateKey({
                              key: 'courses',
                              bulkSelection,
                              showProBadge: true,
                            })}
                          />
                        )}
                      />
                      <Show when={bulkSelection.courses > 0}>
                        <Button
                          variant="danger"
                          size="small"
                          onClick={() => resetBulkSelection('courses')}
                          icon={<SVGIcon name="cross" width={16} height={16} />}
                        >
                          {__('Clear Selection', 'tutor')}
                        </Button>
                      </Show>
                    </div>
                    <Button
                      variant="secondary"
                      size="small"
                      onClick={() => {
                        showModal({
                          component: CourseCategorySelectModal,
                          props: {
                            title: __('Select Courses', 'tutor'),
                            type: 'courses',
                            form: bulkSelectionForm,
                            onSelect: (courses) => {
                              if (courses.length) {
                                form.setValue('courses.isChecked', true, {
                                  shouldDirty: true,
                                });
                              }
                            },
                          },
                        });
                      }}
                    >
                      {__('Select Courses', 'tutor')}
                    </Button>
                  </div>
                  <Show when={isCoursesChecked}>
                    <div css={styles.childCheckboxWrapper}>
                      <div css={styles.contentCheckboxWrapper}>
                        <div css={styles.checkboxRow({ isContent: true })}>
                          <Controller
                            control={form.control}
                            name="courses.lessons"
                            render={(controllerProps) => (
                              <FormCheckbox
                                disabled={!isTutorPro}
                                {...controllerProps}
                                label={getLabelByFormDateKey({
                                  key: 'courses[lesson]',
                                  bulkSelection,
                                  showProBadge: true,
                                })}
                              />
                            )}
                          />
                        </div>
                        <div css={styles.checkboxRow({ isContent: true })}>
                          <Controller
                            control={form.control}
                            name="courses.quizzes"
                            render={(controllerProps) => (
                              <FormCheckbox
                                {...controllerProps}
                                disabled={!isTutorPro}
                                label={getLabelByFormDateKey({
                                  key: 'courses[tutor_quiz]',
                                  bulkSelection,
                                  showProBadge: true,
                                })}
                              />
                            )}
                          />
                        </div>
                        <div css={styles.checkboxRow({ isContent: true })}>
                          <Controller
                            control={form.control}
                            name="courses.assignments"
                            render={(controllerProps) => (
                              <FormCheckbox
                                {...controllerProps}
                                disabled={!isTutorPro}
                                label={getLabelByFormDateKey({
                                  key: 'courses[tutor_assignments]',
                                  bulkSelection,
                                  showProBadge: true,
                                })}
                              />
                            )}
                          />
                        </div>
                        <div css={styles.checkboxRow({ isContent: true })}>
                          <Controller
                            control={form.control}
                            name="courses.attachments"
                            render={(controllerProps) => (
                              <FormCheckbox
                                {...controllerProps}
                                disabled={!isTutorPro}
                                label={getLabelByFormDateKey({
                                  key: 'courses[attachments]',
                                  bulkSelection,
                                  showProBadge: true,
                                })}
                              />
                            )}
                          />
                        </div>
                      </div>

                      <div css={styles.contentCheckboxFooter}>
                        <Controller
                          control={form.control}
                          name="courses.keepMediaFiles"
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

                <div css={styles.checkboxRow}>
                  <div css={styles.checkBoxWithButton}>
                    <div css={styles.checkBoxWithAction}>
                      <Controller
                        control={form.control}
                        name="bundles.isChecked"
                        render={(controllerProps) => (
                          <FormCheckbox
                            {...controllerProps}
                            disabled={!isTutorPro}
                            label={getLabelByFormDateKey({
                              key: 'course-bundle',
                              bulkSelection,
                              showProBadge: true,
                            })}
                          />
                        )}
                      />
                      <Show when={bulkSelection.bundles > 0}>
                        <Button
                          variant="danger"
                          size="small"
                          onClick={() => resetBulkSelection('bundles')}
                          icon={<SVGIcon name="cross" width={16} height={16} />}
                        >
                          {__('Clear Selection', 'tutor')}
                        </Button>
                      </Show>
                    </div>

                    <Button
                      variant="secondary"
                      size="small"
                      onClick={() => {
                        showModal({
                          component: CourseCategorySelectModal,
                          props: {
                            title: __('Select Bundles', 'tutor'),
                            type: 'bundles',
                            form: bulkSelectionForm,
                            onSelect: (bundles) => {
                              if (bundles.length) {
                                form.setValue('bundles.isChecked', true, {
                                  shouldDirty: true,
                                });
                              }
                            },
                          },
                        });
                      }}
                    >
                      {__('Select Bundles', 'tutor')}
                    </Button>
                  </div>
                </div>

                <div css={styles.checkboxRow}>
                  <Controller
                    control={form.control}
                    name="settings"
                    render={(controllerProps) => (
                      <FormCheckbox {...controllerProps} label={getLabelByFormDateKey({ key: 'settings' })} />
                    )}
                  />
                </div>
              </>
            )}
          </div>
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
                    courses: {
                      ...data.courses,
                      ids: courses.length ? courses.map((course) => course.id) : data.courses.ids,
                    },
                    bundles: {
                      ...data.bundles,
                      ids: bundles.length ? bundles.map((bundle) => bundle.id) : data.bundles.ids,
                    },
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
    min-height: 760px;
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
};
