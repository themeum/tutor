import { css } from '@emotion/react';
import { useIsFetching, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import { useNavigate } from 'react-router-dom';

import { Box, BoxSubtitle, BoxTitle } from '@TutorShared/atoms/Box';
import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import ProBadge from '@TutorShared/atoms/ProBadge';
import EmptyState from '@TutorShared/molecules/EmptyState';

import Certificate from '@TutorShared/components/certificate/Certificate';
import FormCoursePrerequisites from '@TutorShared/components/fields/FormCoursePrerequisites';
import FormFileUploader from '@TutorShared/components/fields/FormFileUploader';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';
import FormTextareaInput from '@TutorShared/components/fields/FormTextareaInput';

import CoursePrerequisitesEmptyState from '@CourseBuilderComponents/additional/CoursePrerequisitesEmptyState';
import LiveClass from '@CourseBuilderComponents/additional/LiveClass';
import CourseBuilderInjectionSlot from '@CourseBuilderComponents/CourseBuilderSlot';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import { type CourseDetailsResponse, type CourseFormData } from '@CourseBuilderServices/course';

import Navigator from '@CourseBuilderComponents/layouts/Navigator';
import { CourseBuilderRouteConfigs } from '@CourseBuilderConfig/route-configs';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { tutorConfig } from '@TutorShared/config/config';
import { Addons, CURRENT_VIEWPORT, VisibilityControlKeys } from '@TutorShared/config/constants';
import { Breakpoint, colorTokens, footerHeight, headerHeight, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import useVisibilityControl from '@TutorShared/hooks/useVisibilityControl';
import { useCourseListQuery } from '@TutorShared/services/course';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { isAddonEnabled } from '@TutorShared/utils/util';

import attachmentsPro2x from '@SharedImages/pro-placeholders/attachments-2x.webp';
import attachmentsPro from '@SharedImages/pro-placeholders/attachments.webp';

const isTutorPro = !!tutorConfig.tutor_pro_url;
const courseId = getCourseId();
const isPrerequisiteAddonEnabled = isAddonEnabled(Addons.TUTOR_PREREQUISITES);
const isAttachmentsAddonEnabled = isAddonEnabled(Addons.TUTOR_COURSE_ATTACHMENTS);
const isCertificateAddonEnabled = isAddonEnabled(Addons.TUTOR_CERTIFICATE);

const Additional = () => {
  const navigate = useNavigate();

  useEffect(() => {
    if (!courseId) {
      navigate(CourseBuilderRouteConfigs.Home.buildLink(), {
        replace: true,
      });
    }
  }, [navigate]);

  const form = useFormContext<CourseFormData>();
  const queryClient = useQueryClient();
  const isCourseDetailsFetching = useIsFetching({
    queryKey: ['CourseDetails', courseId],
  });
  const isCourseBenefitsVisible = useVisibilityControl(VisibilityControlKeys.COURSE_BUILDER.ADDITIONAL.COURSE_BENEFITS);
  const isCourseTargetAudienceVisible = useVisibilityControl(
    VisibilityControlKeys.COURSE_BUILDER.ADDITIONAL.COURSE_TARGET_AUDIENCE,
  );
  const isTotalCourseDurationVisible = useVisibilityControl(
    VisibilityControlKeys.COURSE_BUILDER.ADDITIONAL.TOTAL_COURSE_DURATION,
  );
  const isCourseMaterialIncluded = useVisibilityControl(
    VisibilityControlKeys.COURSE_BUILDER.ADDITIONAL.COURSE_MATERIALS_INCLUDES,
  );
  const isCourseRequirementsVisible = useVisibilityControl(
    VisibilityControlKeys.COURSE_BUILDER.ADDITIONAL.COURSE_REQUIREMENTS,
  );
  const isCertificateVisible = useVisibilityControl(VisibilityControlKeys.COURSE_BUILDER.ADDITIONAL.CERTIFICATES);
  const isAttachmentsVisible = useVisibilityControl(VisibilityControlKeys.COURSE_BUILDER.ADDITIONAL.ATTACHMENTS);

  const courseDetails = queryClient.getQueryData(['CourseDetails', courseId]) as CourseDetailsResponse;
  const prerequisiteCourseIds =
    courseDetails?.course_prerequisites?.map((prerequisite) => String(prerequisite.id)) || [];

  const prerequisiteCoursesQuery = useCourseListQuery({
    params: {
      exclude: [String(courseId), ...prerequisiteCourseIds],
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

  const currentCertificateKey = form.watch('tutor_course_certificate_template');
  const certificateTemplates = courseDetails?.course_certificates_templates ?? [];

  const isOverViewVisible =
    isCourseBenefitsVisible ||
    isCourseTargetAudienceVisible ||
    isTotalCourseDurationVisible ||
    isCourseMaterialIncluded ||
    isCourseRequirementsVisible;
  const hasLeftSideContent = isOverViewVisible || !isTutorPro || (isCertificateVisible && isCertificateAddonEnabled);
  const hasSidebarContent =
    !isTutorPro ||
    [
      Addons.TUTOR_PREREQUISITES,
      Addons.TUTOR_COURSE_ATTACHMENTS,
      Addons.TUTOR_ZOOM_INTEGRATION,
      Addons.TUTOR_GOOGLE_MEET_INTEGRATION,
    ].some(isAddonEnabled);
  const handleCertificateSelection = (certificateKey: string) => {
    form.setValue('tutor_course_certificate_template', certificateKey, {
      shouldDirty: true,
    });
  };

  const SidebarContent = () => (
    <div css={styles.sidebarContent}>
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
                    prerequisiteCoursesQuery.isLoading || (!!isCourseDetailsFetching && !controllerProps.field.value)
                  }
                />
              )}
            />
          </Show>
        </div>
      </Show>
      <Show when={!isTutorPro || (isAttachmentsVisible && isAttachmentsAddonEnabled)}>
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
                  // prettier-ignore
                  title={__('Provide additional resources like downloadable files and reference materials.', 'tutor')}
                />
              </Show>
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
      </Show>

      <LiveClass visibilityKey={VisibilityControlKeys.COURSE_BUILDER.ADDITIONAL.SCHEDULE_LIVE_CLASS} />

      <CourseBuilderInjectionSlot section="Additional.bottom_of_sidebar" form={form} />
    </div>
  );

  return (
    <div
      css={styles.wrapper({
        showSidebar: hasSidebarContent && hasLeftSideContent,
      })}
    >
      <div css={styles.leftSide}>
        <CanvasHead title={__('Additional', 'tutor')} backUrl="/curriculum" />

        <Show when={hasLeftSideContent}>
          <div css={styles.formWrapper}>
            <Show when={isOverViewVisible}>
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
                        placeholder={__(
                          'Define the key takeaways from this course (list one benefit per line)',
                          'tutor',
                        )}
                        rows={2}
                        enableResize
                        loading={!!isCourseDetailsFetching && !controllerProps.field.value}
                        visibilityKey={VisibilityControlKeys.COURSE_BUILDER.ADDITIONAL.COURSE_BENEFITS}
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
                        visibilityKey={VisibilityControlKeys.COURSE_BUILDER.ADDITIONAL.COURSE_TARGET_AUDIENCE}
                      />
                    )}
                  />

                  <Show when={isTotalCourseDurationVisible}>
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
                            visibilityKey={VisibilityControlKeys.COURSE_BUILDER.ADDITIONAL.TOTAL_COURSE_DURATION}
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
                            visibilityKey={VisibilityControlKeys.COURSE_BUILDER.ADDITIONAL.TOTAL_COURSE_DURATION}
                          />
                        )}
                      />
                    </div>
                  </Show>

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
                        visibilityKey={VisibilityControlKeys.COURSE_BUILDER.ADDITIONAL.COURSE_MATERIALS_INCLUDES}
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
                        visibilityKey={VisibilityControlKeys.COURSE_BUILDER.ADDITIONAL.COURSE_REQUIREMENTS}
                      />
                    )}
                  />
                </div>
              </Box>
            </Show>

            <Show when={!isTutorPro || (isCertificateVisible && isCertificateAddonEnabled)}>
              <Box bordered>
                <div css={styles.titleAndSub}>
                  <BoxTitle css={styles.titleWithBadge}>
                    {__('Certificate', 'tutor')}
                    <Show when={!isTutorPro}>
                      <ProBadge content={__('Pro', 'tutor')} />
                    </Show>
                  </BoxTitle>
                  <Show when={isTutorPro && isCertificateAddonEnabled}>
                    <BoxSubtitle>{__('Select a certificate to award your learners.', 'tutor')}</BoxSubtitle>
                  </Show>
                </div>
                <Show when={!isCourseDetailsFetching} fallback={<LoadingSection />}>
                  <Certificate
                    isSidebarVisible={hasSidebarContent}
                    currentCertificateKey={currentCertificateKey}
                    onSelect={handleCertificateSelection}
                    certificateTemplates={certificateTemplates}
                  />
                </Show>
              </Box>
            </Show>

            <CourseBuilderInjectionSlot section="Additional.after_certificates" form={form} />
          </div>
        </Show>

        <Show when={!hasLeftSideContent && hasSidebarContent}>
          <div css={styles.formWrapper}>
            <SidebarContent />
          </div>
        </Show>

        <Show when={CURRENT_VIEWPORT.isAboveTablet}>
          <Navigator />
        </Show>
      </div>

      <Show when={hasSidebarContent && hasLeftSideContent}>
        <div css={styles.sidebar}>
          <SidebarContent />
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
  sidebarContent: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[16]};
  `,
};
