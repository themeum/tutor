import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { type FunctionComponent } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import ProBadge from '@TutorShared/atoms/ProBadge';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import { useModal } from '@TutorShared/components/modals/Modal';

import { type BulkSelectionFormData, type ExportFormData } from '@ImportExport/services/import-export';
import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, Breakpoint, colorTokens, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { type useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { type ExportableContent, type ExportableCourseContentType } from '@TutorShared/services/import-export';
import { styleUtils } from '@TutorShared/utils/style-utils';

interface ExportInitialStateProps {
  form: ReturnType<typeof useFormWithGlobalError<ExportFormData>>;
  bulkSelectionForm: ReturnType<typeof useFormWithGlobalError<BulkSelectionFormData>>;
  exportableContent: ExportableContent[];
  isLoading: boolean;
  componentMapping: {
    [key: string]: {
      modal: {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        component: FunctionComponent<any>;
        props: object;
      };
      bulkSelectionButtonLabel: string;
    };
  };
  resetBulkSelection: (type: 'courses' | 'course-bundle' | 'content_bank') => void;
}

const isTutorPro = !!tutorConfig.tutor_pro_url;

const ExportInitialState = ({
  form,
  bulkSelectionForm,
  exportableContent,
  isLoading,
  componentMapping,
  resetBulkSelection,
}: ExportInitialStateProps) => {
  const { showModal } = useModal();
  /**
   * Returns properly formatted label for form data keys with appropriate count information
   *
   * @param {string} key - The form data key to get the label for
   * @returns {string | JSX.Element} - The formatted label with count information
   */
  const getLabelByFormDataKey = (key: string): string | JSX.Element => {
    if (!exportableContent || !Array.isArray(exportableContent)) {
      return key;
    }

    const createLabelWithCount = (label: string, count: number | undefined, key: string) => {
      if (count === undefined) {
        return (
          <div css={styles.checkboxLabel}>
            {label}

            <Show when={!isTutorPro && key !== 'settings'}>
              <ProBadge size="small" content={__('Pro', 'tutor')} />
            </Show>
          </div>
        );
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

    if (key.includes('__')) {
      const [mainType, subType] = key.split('__');

      const mainContent = exportableContent.find((item) => item.key === mainType);
      if (!mainContent) {
        return key;
      }

      if (!mainContent.contents) {
        return key;
      }

      const subContent = mainContent.contents.find(
        (content) => content.key === (subType as ExportableCourseContentType),
      );
      if (!subContent) {
        return key;
      }

      return createLabelWithCount(subContent.label, subContent.count, key);
    }

    const content = exportableContent.find((item) => item.key === key);
    if (!content) {
      return key;
    }

    const getSelectionCount = () => {
      const countMap: Record<string, number> = {
        courses: bulkSelectionForm.getValues('courses').length,
        'course-bundle': bulkSelectionForm.getValues('course-bundle').length,
        content_bank: bulkSelectionForm.getValues('content_bank').length,
      };

      return countMap[key] || 0;
    };

    const selectedCount = getSelectionCount();

    return selectedCount > 0
      ? createLabelWithCount(content.label, selectedCount, key)
      : createLabelWithCount(content.label, content.count, key);
  };

  const renderExportableContentOptions = () => {
    if (isLoading) {
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

          const bulkSelectionCount =
            bulkSelectionForm.getValues(contentKey as keyof BulkSelectionFormData)?.length || 0;

          if (['keep_media_files', 'keep_user_data'].includes(contentKey)) {
            return null;
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
                <Show when={isChecked && ['courses', 'course-bundle', 'content_bank'].includes(contentKey)}>
                  <Button
                    variant="secondary"
                    buttonCss={styles.selectButton}
                    size="small"
                    onClick={() => {
                      const modalConfig = componentMapping[contentKey];
                      showModal({
                        component: modalConfig.modal.component,
                        props: modalConfig.modal.props,
                        depthIndex: zIndex.highest,
                      });
                    }}
                  >
                    {componentMapping[contentKey]?.bulkSelectionButtonLabel}
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

  return (
    <div css={styles.wrapper}>
      <div css={styles.formWrapper}>
        <div css={styles.formTitle}>{__('What do you want to export?', 'tutor')}</div>
        <div css={styles.checkboxWrapper}>{renderExportableContentOptions()}</div>

        <Show
          when={
            (exportableContent || []).some((item) => item.key === 'keep_media_files') &&
            (form.getValues('courses') || form.getValues('course-bundle') || form.getValues('content_bank'))
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
                    __('If checked, course media files will also be exported with the course data.', 'tutor')
                  }
                />
              )}
            />
          </div>
        </Show>

        <Show
          when={
            (exportableContent || []).some((item) => item.key === 'keep_user_data') &&
            (form.getValues('courses') || form.getValues('course-bundle'))
          }
        >
          <div css={styles.contentCheckboxFooter}>
            <Controller
              control={form.control}
              name="keep_user_data"
              render={(controllerProps) => (
                <FormCheckbox
                  {...controllerProps}
                  label={__('Keep User Data', 'tutor')}
                  disabled={!isTutorPro}
                  description={
                    // prettier-ignore
                    __('If checked, user data will also be exported with the course data.', 'tutor')
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

export default ExportInitialState;

const styles = {
  wrapper: css`
    height: calc(100vh - 140px);
    max-height: 680px;
    padding: ${spacing[32]} 107px ${spacing[32]} 107px;
    background-color: ${colorTokens.surface.courseBuilder};
    border-top: 1px solid ${colorTokens.stroke.divider};

    ${Breakpoint.tablet} {
      padding: ${spacing[24]} ${spacing[16]};
      height: calc(100vh - 160px);
    }
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
    min-height: 32px;

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

    ${Breakpoint.smallMobile} {
      grid-template-columns: 1fr;
    }
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
  selectButton: css`
    background-color: ${colorTokens.background.white};
  `,
};
