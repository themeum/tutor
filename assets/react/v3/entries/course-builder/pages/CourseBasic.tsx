import { css } from '@emotion/react';
import { useIsFetching, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { Controller, useFormContext } from 'react-hook-form';

import FormEditableAlias from '@Components/fields/FormEditableAlias';
import FormInput from '@Components/fields/FormInput';
import FormWPEditor from '@Components/fields/FormWPEditor';
import CourseBasicSidebar from '@CourseBuilderComponents/course-basic/CourseBasicSidebar';
import CourseSettings from '@CourseBuilderComponents/course-basic/CourseSettings';
import Navigator from '@CourseBuilderComponents/layouts/Navigator';

import { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, headerHeight, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import {
  type CourseDetailsResponse,
  type CourseFormData,
  convertCourseDataToPayload,
  useUpdateCourseMutation,
} from '@CourseBuilderServices/course';
import { convertToSlug, determinePostStatus, getCourseId } from '@CourseBuilderUtils/utils';
import { styleUtils } from '@Utils/style-utils';
import { maxLimitRule, requiredRule } from '@Utils/validation';

const courseId = getCourseId();

const CourseBasic = () => {
  const form = useFormContext<CourseFormData>();
  const queryClient = useQueryClient();
  const isCourseDetailsFetching = useIsFetching({
    queryKey: ['CourseDetails', courseId],
  });
  const updateCourseMutation = useUpdateCourseMutation();

  const [isWpEditorFullScreen, setIsWpEditorFullScreen] = useState(false);

  const courseDetails = queryClient.getQueryData(['CourseDetails', courseId]) as CourseDetailsResponse;
  const isTutorPro = !!tutorConfig.tutor_pro_url;
  const isOpenAiEnabled = tutorConfig.settings?.chatgpt_enable === 'on';

  const postStatus = form.watch('post_status');
  const isPostNameDirty = form.formState.dirtyFields.post_name;

  return (
    <div css={styles.wrapper}>
      <div css={styles.mainForm({ isWpEditorFullScreen })}>
        <div css={styles.fieldsWrapper}>
          <div css={styles.titleAndSlug}>
            <Controller
              name="post_title"
              control={form.control}
              rules={{ ...requiredRule(), ...maxLimitRule(255) }}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  label={__('Title', 'tutor')}
                  placeholder={__('ex. Learn Photoshop CS6 from scratch', 'tutor')}
                  isClearable
                  selectOnFocus
                  generateWithAi={!isTutorPro || isOpenAiEnabled}
                  loading={!!isCourseDetailsFetching && !controllerProps.field.value}
                  onChange={(value) => {
                    if (postStatus === 'draft' && !isPostNameDirty) {
                      form.setValue('post_name', convertToSlug(String(value)), {
                        shouldValidate: true,
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
                  baseURL={`${tutorConfig.home_url}/${tutorConfig.settings?.course_permalink_base}`}
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
                generateWithAi={!isTutorPro || isOpenAiEnabled}
                hasCustomEditorSupport
                editorUsed={courseDetails?.editor_used}
                editors={courseDetails?.editors}
                onCustomEditorButtonClick={async () => {
                  form.handleSubmit(async (data) => {
                    const payload = convertCourseDataToPayload(data);

                    await updateCourseMutation.mutateAsync({
                      course_id: courseId,
                      ...payload,
                      post_status: determinePostStatus(
                        form.getValues('post_status') as 'trash' | 'future' | 'draft',
                        form.getValues('visibility') as 'private' | 'password_protected',
                      ),
                    });
                  })();
                }}
                onFullScreenChange={(isFullScreen) => {
                  setIsWpEditorFullScreen(isFullScreen);
                }}
              />
            )}
          />

          <CourseSettings />
        </div>
        <Navigator styleModifier={styles.navigator} />
      </div>

      <CourseBasicSidebar />
    </div>
  );
};

export default CourseBasic;

const styles = {
  wrapper: css`
    display: grid;
    grid-template-columns: 1fr 338px;
    gap: ${spacing[32]};
  `,
  mainForm: ({
    isWpEditorFullScreen,
  }: {
    isWpEditorFullScreen: boolean;
  }) => css`
    padding-block: ${spacing[32]} ${spacing[24]};
    align-self: start;
    top: ${headerHeight}px;
    position: sticky;

    ${
      isWpEditorFullScreen &&
      css`
        z-index: ${zIndex.header + 1};
      `
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
  navigator: css`
    margin-top: ${spacing[40]};
  `,
  editorsButtonWrapper: css`
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    padding-bottom: ${spacing[10]};
    gap: ${spacing[8]};

    * {
      flex-shrink: 0;
      margin-right: ${spacing[8]};
    }
  `,
  descriptionWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[6]};
  `,
  descriptionLabel: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.title};
  `,
  editorWrapper: css`
    position: relative;
  `,
  editorOverlay: css`
    height: 360px;
    ${styleUtils.flexCenter()};
    background-color: ${colorTokens.bg.gray20};
    border-radius: ${borderRadius.card};
  `,
  statusAndDate: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
  `,
  updatedOn: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
  `,
};
