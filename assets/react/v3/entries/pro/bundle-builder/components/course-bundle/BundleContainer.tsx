import { css } from '@emotion/react';
import { useIsFetching } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { Controller, useFormContext } from 'react-hook-form';

import { convertToSlug } from '@/v3/entries/course-builder/utils/utils';
import FormEditableAlias from '@/v3/shared/components/fields/FormEditableAlias';
import FormTextareaInput from '@/v3/shared/components/fields/FormTextareaInput';
import FormWPEditor from '@/v3/shared/components/fields/FormWPEditor';
import { tutorConfig } from '@/v3/shared/config/config';
import BundleSidebar from '@BundleBuilderComponents/course-bundle/BundleSidebar';
import CourseSelection from '@BundleBuilderComponents/course-bundle/CourseSelection';
import { type BundleFormData } from '@BundleBuilderServices/bundle';
import { getBundleId } from '@BundleBuilderUtils/utils';
import FormInput from '@Components/fields/FormInput';
import { borderRadius, Breakpoint, colorTokens, headerHeight, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';

const bundleId = getBundleId();
let hasAliasChanged = false;

const BundleContainer = () => {
  const form = useFormContext<BundleFormData>();
  const [isWpEditorFullScreen, setIsWpEditorFullScreen] = useState(false);
  const isBundleDetailsQueryFetching = useIsFetching({
    queryKey: ['CourseBundle', bundleId],
  });

  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const isOpenAiEnabled = tutorConfig.settings?.chatgpt_enable === 'on';

  const postStatus = form.watch('post_status');

  return (
    <div css={styles.wrapper}>
      <div css={styles.mainForm({ isWpEditorFullScreen })}>
        <div css={styles.fieldsWrapper}>
          <div css={styles.titleAndSlug}>
            <Controller
              name="post_title"
              control={form.control}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  label={__('Title', 'tutor')}
                  placeholder={__('ex. Learn Photoshop CS6 from scratch', 'tutor')}
                  isClearable
                  selectOnFocus
                  generateWithAi={!isTutorPro || isOpenAiEnabled}
                  loading={!!isBundleDetailsQueryFetching && !controllerProps.field.value}
                  onChange={(value) => {
                    if (postStatus === 'draft' && !hasAliasChanged) {
                      form.setValue('post_name', convertToSlug(String(value)), {
                        shouldValidate: true,
                        shouldDirty: true,
                      });
                    }
                  }}
                />
              )}
            />

            <Controller
              name="post_name"
              control={form.control}
              render={(controllerProps) => (
                <FormEditableAlias
                  {...controllerProps}
                  label={__('Course URL', 'tutor')}
                  baseURL={tutorConfig.site_url}
                  onChange={() => (hasAliasChanged = true)}
                />
              )}
            />
          </div>

          <Controller
            name="post_content"
            control={form.control}
            render={(controllerProps) => (
              <FormWPEditor
                {...controllerProps}
                label={__('Description', 'tutor')}
                loading={!!isBundleDetailsQueryFetching && !controllerProps.field.value}
                max_height={280}
                generateWithAi={!isTutorPro || isOpenAiEnabled}
                onFullScreenChange={(isFullScreen) => {
                  setIsWpEditorFullScreen(isFullScreen);
                }}
              />
            )}
          />

          <CourseSelection />

          <div css={styles.additionalFields}>
            <Controller
              name="course_benefits"
              control={form.control}
              render={(controllerProps) => (
                <FormTextareaInput
                  {...controllerProps}
                  label={__('What Will I Learn?', 'tutor')}
                  placeholder={__('Define the key takeaways from this course (list one benefit per line)', 'tutor')}
                  rows={2}
                  enableResize
                />
              )}
            />
          </div>
        </div>
      </div>
      <BundleSidebar />
    </div>
  );
};

export default BundleContainer;

const styles = {
  wrapper: css`
    display: grid;
    grid-template-columns: 1fr 338px;
    gap: ${spacing[32]};
    width: 100%;

    ${Breakpoint.smallTablet} {
      grid-template-columns: 1fr;
      gap: 0;
    }
  `,
  mainForm: ({ isWpEditorFullScreen }: { isWpEditorFullScreen: boolean }) => css`
    padding-block: ${spacing[32]} ${spacing[24]};
    align-self: start;
    top: ${headerHeight}px;
    position: sticky;

    ${isWpEditorFullScreen &&
    css`
      z-index: ${zIndex.header + 1};
    `}

    ${Breakpoint.smallTablet} {
      padding-top: ${spacing[16]};
      position: unset;
    }
  `,

  fieldsWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[24]};
  `,
  titleAndSlug: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
  sidebar: css`
    border-left: 1px solid ${colorTokens.stroke.divider};
    min-height: calc(100vh - ${headerHeight}px);
    padding-left: ${spacing[32]};
    padding-block: ${spacing[24]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
  `,
  priceRadioGroup: css`
    display: flex;
    align-items: center;
    gap: ${spacing[36]};
  `,
  coursePriceWrapper: css`
    display: flex;
    align-items: flex-start;
    gap: ${spacing[16]};
  `,
  statusAndDate: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
  `,
  updatedOn: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
  `,
  additionalFields: css`
    padding: ${spacing[12]} ${spacing[20]};
    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius.card};
  `,
};
