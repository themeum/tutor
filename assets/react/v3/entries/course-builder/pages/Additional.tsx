import { css } from '@emotion/react';
import { useIsFetching, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { useNavigate } from 'react-router-dom';

import Button from '@Atoms/Button';
import ProBadge from '@Atoms/ProBadge';
import SVGIcon from '@Atoms/SVGIcon';
import { Box, BoxSubtitle, BoxTitle } from '@Atoms/Box';
import EmptyState from '@Molecules/EmptyState';

import FormCoursePrerequisites from '@Components/fields/FormCoursePrerequisites';
import FormFileUploader from '@Components/fields/FormFileUploader';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormTextareaInput from '@Components/fields/FormTextareaInput';

import CoursePrerequisitesEmptyState from '@CourseBuilderComponents/additional/CoursePrerequisitesEmptyState';
import LiveClass from '@CourseBuilderComponents/additional/LiveClass';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import {
  type CourseDetailsResponse,
  type CourseFormData,
  usePrerequisiteCoursesQuery,
} from '@CourseBuilderServices/course';

import config, { tutorConfig } from '@Config/config';
import { Addons } from '@Config/constants';
import { colorTokens, footerHeight, headerHeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import Navigator from '@CourseBuilderComponents/layouts/Navigator';
import { getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';
import { styleUtils } from '@Utils/style-utils';

import addonDisabled2x from '@Images/addon-disabled-2x.webp';
import addonDisabled from '@Images/addon-disabled.webp';
import attachmentsPro2x from '@Images/pro-placeholders/attachments-2x.webp';
import attachmentsPro from '@Images/pro-placeholders/attachments.webp';

import Certificate from '../components/additional/Certificate';

const isTutorPro = !!tutorConfig.tutor_pro_url;
const courseId = getCourseId();
const isPrerequisiteAddonEnabled = isAddonEnabled(Addons.TUTOR_PREREQUISITES);

const Additional = () => {
  const navigate = useNavigate();

  useEffect(() => {
    if (!courseId) {
      navigate('/', {
        replace: true,
      });
    }
  }, [navigate]);

  if (!courseId) {
    return null;
  }

  const form = useFormContext<CourseFormData>();
  const queryClient = useQueryClient();
  const isCourseDetailsFetching = useIsFetching({
    queryKey: ['CourseDetails', courseId],
  });

  const courseDetails = queryClient.getQueryData(['CourseDetails', courseId]) as CourseDetailsResponse;
  const prerequisiteCourseIds = (courseDetails?.course_prerequisites || []).map((prerequisite) =>
    String(prerequisite.id),
  );

  const prerequisiteCoursesQuery = usePrerequisiteCoursesQuery({
    excludedIds: [String(courseId), ...prerequisiteCourseIds],
    isEnabled: !!isPrerequisiteAddonEnabled && !isCourseDetailsFetching,
  });

  return (
    <div css={styles.wrapper}>
      <div css={styles.leftSide}>
        <CanvasHead title={__('Additional', 'tutor')} backUrl="/curriculum" />
        <div css={styles.formWrapper}>
          <Box bordered>
            <div css={styles.titleAndSub}>
              <BoxTitle>{__('Overview', 'tutor')}</BoxTitle>
              <BoxSubtitle>
                {__('Provide essential course information to attract and inform potential students', 'tutor')}
              </BoxSubtitle>
            </div>
            <div css={styles.fieldsWrapper}>
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
                    loading={!!isCourseDetailsFetching && !controllerProps.field.value}
                  />
                )}
              />

              <Controller
                name="course_target_audience"
                control={form.control}
                render={(controllerProps) => (
                  <FormTextareaInput
                    {...controllerProps}
                    label={__('Target Audience', 'tutor')}
                    placeholder={__(
                      'Specify the target audience that will benefit the most from the course.(One Line Per target audience)',
                      'tutor',
                    )}
                    rows={2}
                    enableResize
                    loading={!!isCourseDetailsFetching && !controllerProps.field.value}
                  />
                )}
              />

              <div css={styles.totalCourseDuration}>
                <Controller
                  name="course_duration_hours"
                  control={form.control}
                  render={(controllerProps) => (
                    <FormInputWithContent
                      {...controllerProps}
                      type="number"
                      label={__('Total Course Duration', 'tutor')}
                      placeholder="0"
                      contentPosition="right"
                      content={__('hour(s)', 'tutor')}
                      loading={!!isCourseDetailsFetching && !controllerProps.field.value}
                    />
                  )}
                />
                <Controller
                  name="course_duration_minutes"
                  control={form.control}
                  render={(controllerProps) => (
                    <FormInputWithContent
                      {...controllerProps}
                      type="number"
                      placeholder="0"
                      contentPosition="right"
                      content={__('min(s)', 'tutor')}
                      loading={!!isCourseDetailsFetching && !controllerProps.field.value}
                    />
                  )}
                />
              </div>

              <Controller
                name="course_material_includes"
                control={form.control}
                render={(controllerProps) => (
                  <FormTextareaInput
                    {...controllerProps}
                    label={__('Materials Included', 'tutor')}
                    placeholder={__(
                      'A list of assets you will be providing for the students in this course (One Per Line)',
                      'tutor',
                    )}
                    rows={4}
                    enableResize
                    loading={!!isCourseDetailsFetching && !controllerProps.field.value}
                  />
                )}
              />

              <Controller
                name="course_requirements"
                control={form.control}
                render={(controllerProps) => (
                  <FormTextareaInput
                    {...controllerProps}
                    label={__('Requirements/Instructions', 'tutor')}
                    placeholder={__(
                      'Additional requirements or special instructions for the students (One Per Line)',
                      'tutor',
                    )}
                    rows={2}
                    enableResize
                    loading={!!isCourseDetailsFetching && !controllerProps.field.value}
                  />
                )}
              />
            </div>
          </Box>

          <Box bordered>
            <div css={styles.titleAndSub}>
              <BoxTitle>
                {__('Certificate', 'tutor')}
                <Show when={!isTutorPro}>
                  <ProBadge content={__('Pro', 'tutor')} />
                </Show>
              </BoxTitle>
              <Show when={isTutorPro && isAddonEnabled(Addons.TUTOR_CERTIFICATE)}>
                <BoxSubtitle>{__('Select a certificate to award your learners.', 'tutor')}</BoxSubtitle>
              </Show>
            </div>
            <Certificate />
          </Box>
        </div>

        <Navigator />
      </div>

      <div css={styles.sidebar}>
        <div>
          <div css={styles.label}>
            {__('Course Prerequisites', 'tutor')}
            {!isTutorPro && <ProBadge content={__('Pro', 'tutor')} />}
          </div>
          <Show when={isTutorPro && isPrerequisiteAddonEnabled} fallback={<CoursePrerequisitesEmptyState />}>
            <Controller
              name="course_prerequisites"
              control={form.control}
              render={(controllerProps) => (
                <FormCoursePrerequisites
                  {...controllerProps}
                  placeholder={__('Search courses for prerequisites', 'tutor')}
                  options={prerequisiteCoursesQuery.data || []}
                  isSearchable
                  loading={
                    prerequisiteCoursesQuery.isLoading || (!!isCourseDetailsFetching && !controllerProps.field.value)
                  }
                />
              )}
            />
          </Show>
        </div>
        <div>
          <div css={styles.label}>
            {__('Attachments', 'tutor')}
            {!isTutorPro && <ProBadge content={__('Pro', 'tutor')} />}
          </div>
          <Show
            when={isTutorPro && isAddonEnabled(Addons.TUTOR_COURSE_ATTACHMENTS)}
            fallback={
              <EmptyState
                size="small"
                removeBorder={false}
                emptyStateImage={!isTutorPro ? attachmentsPro : addonDisabled}
                emptyStateImage2x={!isTutorPro ? attachmentsPro2x : addonDisabled2x}
                title={
                  !isTutorPro
                    ? __('Provide additional resources like downloadable files and reference materials.', 'tutor')
                    : __('Activate the “Course Attachments” addon to use this feature.', 'tutor')
                }
                description={
                  isTutorPro
                    ? __(
                        `Provide additional resources to support your student's learning. Attachments can include documents, audio or video files.`,
                        'tutor',
                      )
                    : ''
                }
                actions={
                  isTutorPro && (
                    <Button
                      size="small"
                      variant="secondary"
                      icon={<SVGIcon name="linkExternal" width={24} height={24} />}
                      onClick={() => {
                        window.open(config.TUTOR_ADDONS_PAGE, '_blank', 'noopener');
                      }}
                    >
                      {__('Enable Course Attachments Addon', 'tutor')}
                    </Button>
                  )
                }
              />
            }
          >
            <Controller
              name="course_attachments"
              control={form.control}
              render={(controllerProps) => (
                <FormFileUploader {...controllerProps} buttonText={__('Upload Attachment', 'tutor')} selectMultiple />
              )}
            />
          </Show>
        </div>
        <LiveClass />
      </div>
    </div>
  );
};

export default Additional;

const styles = {
  wrapper: css`
    display: grid;
    grid-template-columns: 1fr 338px;
  `,
  leftSide: css`
    padding: ${spacing[32]} ${spacing[32]} ${spacing[32]} 0;
    ${styleUtils.display.flex('column')}
    gap: ${spacing[32]};
  `,
  formWrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[24]};
  `,
  titleAndSub: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[4]};
    margin-bottom: ${spacing[20]};
  `,
  fieldsWrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[24]};
  `,
  totalCourseDuration: css`
    ${styleUtils.display.flex()}
    align-items: end;
    gap: ${spacing[8]};

    & > div {
      flex: 1;
    }
  `,
  sidebar: css`
    ${styleUtils.display.flex('column')}
    padding: ${spacing[32]} 0 ${spacing[32]} ${spacing[32]};
    border-left: 1px solid ${colorTokens.stroke.divider};
    min-height: calc(100vh - (${headerHeight}px + ${footerHeight}px));
    gap: ${spacing[16]};
  `,
  label: css`
    ${styleUtils.display.inlineFlex()}
    align-items: center;
    gap: ${spacing[4]};
    ${typography.body('medium')}
    color: ${colorTokens.text.title};
    margin-bottom: ${spacing[8]};
  `,
};
