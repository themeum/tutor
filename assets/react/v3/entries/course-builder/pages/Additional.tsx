import { css } from '@emotion/react';
import { useIsFetching, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { useNavigate } from 'react-router-dom';

import { Box, BoxSubtitle, BoxTitle } from '@TutorShared/atoms/Box';
import ProBadge from '@TutorShared/atoms/ProBadge';
import EmptyState from '@TutorShared/molecules/EmptyState';

import FormCoursePrerequisites from '@TutorShared/components/fields/FormCoursePrerequisites';
import FormFileUploader from '@TutorShared/components/fields/FormFileUploader';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';
import FormTextareaInput from '@TutorShared/components/fields/FormTextareaInput';

import Certificate from '@CourseBuilderComponents/additional/Certificate';
import CoursePrerequisitesEmptyState from '@CourseBuilderComponents/additional/CoursePrerequisitesEmptyState';
import LiveClass from '@CourseBuilderComponents/additional/LiveClass';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import { type CourseDetailsResponse, type CourseFormData } from '@CourseBuilderServices/course';

import Navigator from '@CourseBuilderComponents/layouts/Navigator';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { tutorConfig } from '@TutorShared/config/config';
import { Addons, CURRENT_VIEWPORT } from '@TutorShared/config/constants';
import { Breakpoint, colorTokens, footerHeight, headerHeight, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { isAddonEnabled } from '@TutorShared/utils/util';

import { default as CourseBuilderInjectionSlot, default as CourseBuilderSlot } from '@CourseBuilderComponents/CourseBuilderSlot';
import attachmentsPro2x from '@SharedImages/pro-placeholders/attachments-2x.webp';
import attachmentsPro from '@SharedImages/pro-placeholders/attachments.webp';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import { useCourseListQuery } from '@TutorShared/services/course';

const isTutorPro = !!tutorConfig.tutor_pro_url;
const courseId = getCourseId();
const isPrerequisiteAddonEnabled = isAddonEnabled(Addons.TUTOR_PREREQUISITES);
const isAttachmentsAddonEnabled = isAddonEnabled(Addons.TUTOR_COURSE_ATTACHMENTS);
const isCertificateAddonEnabled = isAddonEnabled(Addons.TUTOR_CERTIFICATE);

const Additional = () => {
  const navigate = useNavigate();

  useEffect(() => {
    if (!courseId) {
      navigate('/', {
        replace: true,
      });
    }
  }, [navigate]);

  const form = useFormContext<CourseFormData>();
  const queryClient = useQueryClient();
  const isCourseDetailsFetching = useIsFetching({
    queryKey: ['CourseDetails', courseId],
  });

  useEffect(() => {
    if (!courseId) {
      navigate('/', { replace: true });
    }
  }, [navigate]);

  const courseDetails = queryClient.getQueryData(['CourseDetails', courseId]) as CourseDetailsResponse;
  const prerequisiteCourseIds =
    courseDetails?.course_prerequisites?.map((prerequisite) => String(prerequisite.id)) || [];

  const prerequisiteCoursesQuery = useCourseListQuery({
    params: {
      excludedIds: [String(courseId), ...prerequisiteCourseIds],
      limit: -1,
    },
    isEnabled: !!isPrerequisiteAddonEnabled && !isCourseDetailsFetching,
  });

  if (!courseId) {
    return null;
  }

  if (!courseId) {
    return null;
  }

  const isSidebarVisible =
    !isTutorPro ||
    [
      Addons.TUTOR_PREREQUISITES,
      Addons.TUTOR_COURSE_ATTACHMENTS,
      Addons.TUTOR_ZOOM_INTEGRATION,
      Addons.TUTOR_GOOGLE_MEET_INTEGRATION,
    ].some(isAddonEnabled);

  return (
    <div
      css={styles.wrapper({
        showSidebar: isSidebarVisible,
      })}
    >
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
                    // prettier-ignore
                    placeholder={__('Specify the target audience that will benefit the most from the course. (One Line Per target audience)', 'tutor')}
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
                    // prettier-ignore
                    placeholder={__('A list of assets you will be providing for the students in this course (One Per Line)', 'tutor' )}
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
                    // prettier-ignore
                    placeholder={__('Additional requirements or special instructions for the students (One Per Line)', 'tutor')}
                    rows={2}
                    enableResize
                    loading={!!isCourseDetailsFetching && !controllerProps.field.value}
                  />
                )}
              />
            </div>
          </Box>

          <Show when={!isTutorPro || isCertificateAddonEnabled}>
            <Box bordered>
              <div css={styles.titleAndSub}>
                <BoxTitle css={styles.titleWithBadge}>
                  {__('Certificate', 'tutor')}
                  <Show when={!isTutorPro}>
                    <ProBadge content={__('Pro', 'tutor')} />
                  </Show>
                </BoxTitle>
                <Show when={isTutorPro && isAddonEnabled(Addons.TUTOR_CERTIFICATE)}>
                  <BoxSubtitle>{__('Select a certificate to award your learners.', 'tutor')}</BoxSubtitle>
                </Show>
              </div>
              <Show when={!isCourseDetailsFetching} fallback={<LoadingSection />}>
                <Certificate isSidebarVisible={isSidebarVisible} />
              </Show>
            </Box>
          </Show>

          <CourseBuilderInjectionSlot section="Additional.after_certificates" form={form} />
        </div>

        <Show when={CURRENT_VIEWPORT.isAboveTablet}>
          <Navigator />
        </Show>
      </div>

      <Show when={isSidebarVisible}>
        <div css={styles.sidebar}>
          <Show when={!isTutorPro || isPrerequisiteAddonEnabled}>
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
                      options={prerequisiteCoursesQuery.data?.results || []}
                      isSearchable
                      loading={
                        prerequisiteCoursesQuery.isLoading ||
                        (!!isCourseDetailsFetching && !controllerProps.field.value)
                      }
                    />
                  )}
                />
              </Show>
            </div>
          </Show>
          <Show when={!isTutorPro || isAttachmentsAddonEnabled}>
            <div>
              <div css={styles.label}>
                {__('Attachments', 'tutor')}
                {!isTutorPro && <ProBadge content={__('Pro', 'tutor')} />}
              </div>
              <Show
                when={isTutorPro && isAttachmentsAddonEnabled}
                fallback={
                  <Show when={!isTutorPro}>
                    <EmptyState
                      size="small"
                      removeBorder={false}
                      emptyStateImage={attachmentsPro}
                      emptyStateImage2x={attachmentsPro2x}
                      title={__(
                        // prettier-ignore
                        __( 'Provide additional resources like downloadable files and reference materials.', 'tutor'),
                      )}
                    />
                  </Show>
                }
              >
                <Controller
                  name="course_attachments"
                  control={form.control}
                  render={(controllerProps) => (
                    <FormFileUploader
                      {...controllerProps}
                      buttonText={__('Upload Attachment', 'tutor')}
                      selectMultiple
                    />
                  )}
                />
              </Show>
            </div>
          </Show>
          <LiveClass />

          <CourseBuilderSlot section="Additional.bottom_of_sidebar" form={form} />
        </div>
      </Show>
      <Show when={!CURRENT_VIEWPORT.isAboveTablet}>
        <Navigator />
      </Show>
    </div>
  );
};

export default Additional;
const styles = {
  wrapper: ({ showSidebar }: { showSidebar: boolean }) => css`
    display: grid;
    grid-template-columns: ${showSidebar ? '1fr 338px' : '1fr'};
    width: 100%;

    ${Breakpoint.smallTablet} {
      grid-template-columns: 1fr;
      gap: ${spacing[24]};
    }
  `,
  leftSide: css`
    padding: ${spacing[32]} ${spacing[32]} ${spacing[32]} 0;
    ${styleUtils.display.flex('column')}
    gap: ${spacing[32]};

    ${Breakpoint.smallTablet} {
      padding: 0;
      padding-top: ${spacing[16]};
      gap: ${spacing[16]};
    }
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
  titleWithBadge: css`
    span {
      ${styleUtils.display.flex()};
      align-items: center;
      gap: ${spacing[4]};
    }
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

    ${Breakpoint.smallTablet} {
      padding: 0;
      padding-top: ${spacing[24]};
      border-left: none;
      border-top: 1px solid ${colorTokens.stroke.divider};
    }
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
