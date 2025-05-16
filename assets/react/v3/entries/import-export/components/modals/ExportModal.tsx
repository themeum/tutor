import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import { type ModalProps } from '@TutorShared/components/modals/Modal';

import {
  type ExportableContent,
  type ExportFormData,
  type ImportExportModalState,
  useExportableContentQuery,
} from '@ImportExport/services/import-export';
import Logo from '@TutorShared/components/Logo';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { styleUtils } from '@TutorShared/utils/style-utils';

import exportInProgressImage from '@SharedImages/import-export/export-inprogress.webp';
import exportSuccessImage from '@SharedImages/import-export/export-success.webp';
import { formatBytes } from '@TutorShared/utils/util';

interface ExportModalProps extends ModalProps {
  onClose: () => void;
  onExport: (data: ExportFormData) => void;
  currentStep: ImportExportModalState;
  onDownload?: () => void;
}

const ExportModal = ({ onClose, onExport, currentStep, onDownload }: ExportModalProps) => {
  const form = useFormWithGlobalError<ExportFormData>({
    defaultValues: {
      courses: false,
      'courses[lesson]': false,
      'courses[tutor_quiz]': false,
      'courses[tutor_assignments]': false,
      'courses[attachments]': false,
      'courses[keepMediaFiles]': false,
      courseBundle: false,
      settings: false,
    },
  });

  const fileName = `tutor_data_${Date.now()}.json`;

  const getExportableContentQuery = useExportableContentQuery();
  const exportableContent = getExportableContentQuery.data as ExportableContent;

  const getLabelByFormDateKey = (key: string) => {
    let label = '';
    const hasContent = key.includes('[');

    if (hasContent) {
      const contentKey = hasContent ? key.split('[')[1].split(']')[0] : key;
      const parentKey = key.split('[')[0] as keyof ExportableContent;
      const contents = exportableContent[parentKey].contents as Record<string, string>;
      const contentLabel = contents[contentKey];
      label = contentLabel;
    } else {
      label = exportableContent?.[key as keyof ExportableContent]?.label;
    }

    const [labelText, count = ''] = (label || '').split('(') || [];

    return (
      <span>
        {labelText}
        <Show when={count}>
          <span css={{ color: colorTokens.text.hints }}>{`(${count}`}</span>
        </Show>
      </span>
    );
  };

  const isCoursesChecked = form.watch('courses');

  const renderInitialState = () => {
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
                  <Controller
                    control={form.control}
                    name="courses"
                    render={(controllerProps) => (
                      <FormCheckbox {...controllerProps} label={getLabelByFormDateKey('courses')} />
                    )}
                  />
                  <Show when={isCoursesChecked}>
                    <div css={styles.childCheckboxWrapper}>
                      <div css={styles.contentCheckboxWrapper}>
                        <div css={styles.checkboxRow({ isContent: true })}>
                          <Controller
                            control={form.control}
                            name="courses[lesson]"
                            render={(controllerProps) => (
                              <FormCheckbox {...controllerProps} label={getLabelByFormDateKey('courses[lesson]')} />
                            )}
                          />
                        </div>
                        <div css={styles.checkboxRow({ isContent: true })}>
                          <Controller
                            control={form.control}
                            name="courses[tutor_quiz]"
                            render={(controllerProps) => (
                              <FormCheckbox {...controllerProps} label={getLabelByFormDateKey('courses[tutor_quiz]')} />
                            )}
                          />
                        </div>
                        <div css={styles.checkboxRow({ isContent: true })}>
                          <Controller
                            control={form.control}
                            name="courses[tutor_assignments]"
                            render={(controllerProps) => (
                              <FormCheckbox
                                {...controllerProps}
                                label={getLabelByFormDateKey('courses[tutor_assignments]')}
                              />
                            )}
                          />
                        </div>
                        <div css={styles.checkboxRow({ isContent: true })}>
                          <Controller
                            control={form.control}
                            name="courses[attachments]"
                            render={(controllerProps) => (
                              <FormCheckbox
                                {...controllerProps}
                                label={getLabelByFormDateKey('courses[attachments]')}
                              />
                            )}
                          />
                        </div>
                      </div>

                      <div css={styles.contentCheckboxFooter}>
                        <Controller
                          control={form.control}
                          name="courses[keepMediaFiles]"
                          render={(controllerProps) => (
                            <FormCheckbox {...controllerProps} label={__('Keep media files', 'tutor')} />
                          )}
                        />
                      </div>
                    </div>
                  </Show>
                </div>

                <div css={styles.checkboxRow}>
                  <Controller
                    control={form.control}
                    name="courseBundle"
                    render={(controllerProps) => (
                      <FormCheckbox {...controllerProps} label={getLabelByFormDateKey('course-bundle')} />
                    )}
                  />
                </div>

                <div css={styles.checkboxRow}>
                  <Controller
                    control={form.control}
                    name="settings"
                    render={(controllerProps) => (
                      <FormCheckbox {...controllerProps} label={getLabelByFormDateKey('settings')} />
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
          <div css={styles.progressCount}>{__('In Progress', 'tutor')}</div>
        </div>
        <div css={styles.progressBar} />
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
              <div css={styles.fileSize}>{formatBytes(1204)}</div>
            </div>

            <div>
              <Button
                variant="primary"
                size="small"
                icon={<SVGIcon name="download" width={24} height={24} />}
                onClick={onDownload}
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
    initial: renderInitialState(),
    progress: renderProgressState(),
    success: renderSuccessState(),
    error: <div>{__('Export failed', 'tutor')}</div>,
  };

  return (
    <BasicModalWrapper
      onClose={onClose}
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
                onClick={form.handleSubmit((data) => {
                  onExport?.(data);
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
    height: 760px;
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
  progressBar: css`
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
      background-image: linear-gradient(
        45deg,
        rgba(255, 255, 255, 0.15) 25%,
        transparent 25%,
        transparent 50%,
        rgba(255, 255, 255, 0.15) 50%,
        rgba(255, 255, 255, 0.15) 75%,
        transparent 75%,
        transparent
      );
      background-size: 1rem 1rem;
      animation: progress-stripes 1s linear infinite;
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
