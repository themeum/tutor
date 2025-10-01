import { css } from '@emotion/react';
import { useIsFetching } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { Controller, useFormContext } from 'react-hook-form';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Tabs, { type TabItem } from '@TutorShared/molecules/Tabs';

import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import FormMultiSelectInput from '@TutorShared/components/fields/FormMultiSelectInput';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import FormSwitch from '@TutorShared/components/fields/FormSwitch';

import ContentDripSettings from '@CourseBuilderComponents/course-basic/ContentDripSettings';
import EnrollmentSettings from '@CourseBuilderComponents/course-basic/EnrollmentSettings';
import type { CourseFormData } from '@CourseBuilderServices/course';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { tutorConfig } from '@TutorShared/config/config';
import { Addons, CURRENT_VIEWPORT, VisibilityControlKeys } from '@TutorShared/config/constants';
import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import useVisibilityControl from '@TutorShared/hooks/useVisibilityControl';
import type { Option } from '@TutorShared/utils/types';
import { isAddonEnabled } from '@TutorShared/utils/util';

const courseId = getCourseId();

const CourseSettings = () => {
  const form = useFormContext<CourseFormData>();
  const isCourseDetailsLoading = useIsFetching({
    queryKey: ['CourseDetails', courseId],
  });

  const isGeneralSettingsVisible = useVisibilityControl(VisibilityControlKeys.COURSE_BUILDER.BASICS.OPTIONS.GENERAL);
  const isContentDripSettingsVisible = useVisibilityControl(
    VisibilityControlKeys.COURSE_BUILDER.BASICS.OPTIONS.CONTENT_DRIP,
  );
  const isEnrollmentSettingsVisible = useVisibilityControl(
    VisibilityControlKeys.COURSE_BUILDER.BASICS.OPTIONS.ENROLLMENT,
  );

  const isContentDripActive = form.watch('contentDripType');
  const isBuddyPressEnabled = form.watch('enable_tutor_bp');
  const isPaidCourse = form.watch('course_price_type') === 'paid';

  const availableTabs = [
    isGeneralSettingsVisible && {
      label: __('General', 'tutor'),
      value: 'general',
      icon: <SVGIcon name="settings" width={24} height={24} />,
    },
    isContentDripSettingsVisible && {
      label: __('Content Drip', 'tutor'),
      value: 'content_drip',
      icon: <SVGIcon name="contentDrip" width={24} height={24} />,
      activeBadge: !!isContentDripActive,
    },
    isEnrollmentSettingsVisible && {
      label: __('Enrollment', 'tutor'),
      value: 'enrollment',
      icon: <SVGIcon name="update" width={24} height={24} />,
    },
    isAddonEnabled(Addons.BUDDYPRESS) && {
      label: __('BuddyPress', 'tutor'),
      value: 'buddyPress',
      icon: <SVGIcon name="buddyPress" width={24} height={24} />,
      activeBadge: isBuddyPressEnabled,
    },
  ].filter(Boolean) as TabItem<string>[];

  const [activeTab, setActiveTab] = useState(availableTabs[0]?.value || 'general');

  if (!availableTabs.length) {
    return null;
  }

  const tabList = CURRENT_VIEWPORT.isAboveSmallMobile
    ? availableTabs
    : availableTabs.map((tab) => ({
        ...tab,
        label: activeTab === tab.value ? tab.label : '',
      }));

  const difficultyLevelOptions: Option<string>[] = (tutorConfig.difficulty_levels || []).map((level) => ({
    label: level.label,
    value: level.value,
  }));

  return (
    <div>
      <label css={typography.caption()}>{__('Options', 'tutor')}</label>

      <div data-cy="course-settings" css={styles.courseSettings}>
        <Tabs
          tabList={tabList}
          activeTab={activeTab}
          onChange={setActiveTab}
          orientation={!CURRENT_VIEWPORT.isAboveSmallMobile ? 'horizontal' : 'vertical'}
          wrapperCss={css`
            button {
              min-width: auto;
            }
          `}
        />

        <div
          css={{
            borderLeft: `1px solid ${colorTokens.stroke.divider}`,
          }}
        >
          {activeTab === 'general' && (
            <div css={styles.settingsOptions}>
              <Controller
                name="course_level"
                control={form.control}
                render={(controllerProps) => (
                  <FormSelectInput
                    {...controllerProps}
                    label={__('Difficulty Level', 'tutor')}
                    placeholder={__('Select Difficulty Level', 'tutor')}
                    helpText={__('Course difficulty level', 'tutor')}
                    options={difficultyLevelOptions}
                    isClearable={false}
                    loading={!!isCourseDetailsLoading && !controllerProps.field.value}
                  />
                )}
              />

              <div css={styles.courseAndQna}>
                <Controller
                  name="is_public_course"
                  control={form.control}
                  rules={{
                    validate: (value) => {
                      if (value && isPaidCourse) {
                        return __('Paid courses cannot be public.', 'tutor');
                      }
                      return true;
                    },
                    deps: ['course_price_type'],
                  }}
                  render={(controllerProps) => (
                    <FormSwitch
                      {...controllerProps}
                      label={__('Public Course', 'tutor')}
                      helpText={__('Make This Course Public. No Enrollment Required.', 'tutor')}
                      loading={!!isCourseDetailsLoading && !controllerProps.field.value}
                      onChange={(value) => {
                        if (isPaidCourse && value) {
                          form.setValue('is_public_course', false);
                          form.setError('is_public_course', {
                            type: 'validate',
                            message: __('Paid courses cannot be public.', 'tutor'),
                          });
                        }
                      }}
                    />
                  )}
                />

                <Show when={tutorConfig.settings?.enable_q_and_a_on_course === 'on'}>
                  <Controller
                    name="enable_qna"
                    control={form.control}
                    render={(controllerProps) => (
                      <FormSwitch
                        {...controllerProps}
                        label={__('Q&A', 'tutor')}
                        helpText={__('Enable Q&A section for your course', 'tutor')}
                        loading={!!isCourseDetailsLoading && !controllerProps.field.value}
                      />
                    )}
                  />
                </Show>
              </div>
            </div>
          )}

          {activeTab === 'content_drip' && <ContentDripSettings />}

          {activeTab === 'enrollment' && <EnrollmentSettings />}

          {activeTab === 'buddyPress' && (
            <div css={styles.settingsOptions}>
              <Controller
                name="enable_tutor_bp"
                control={form.control}
                render={(controllerProps) => (
                  <FormCheckbox {...controllerProps} label={__('Enable BuddyPress group activity feeds', 'tutor')} />
                )}
              />

              <Controller
                name="bp_attached_group_ids"
                control={form.control}
                render={(controllerProps) => (
                  <FormMultiSelectInput
                    {...controllerProps}
                    label={__('BuddyPress Groups', 'tutor')}
                    helpText={__('Assign this course to BuddyPress Groups', 'tutor')}
                    placeholder={__('Search BuddyPress Groups', 'tutor')}
                    options={(tutorConfig.bp_groups || []).map((group) => ({
                      label: group.name,
                      value: String(group.id),
                    }))}
                    loading={!!isCourseDetailsLoading && !controllerProps.field.value}
                  />
                )}
              />
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default CourseSettings;

const styles = {
  courseSettings: css`
    display: grid;
    grid-template-columns: 200px 1fr;
    margin-top: ${spacing[12]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[6]};
    background-color: ${colorTokens.background.default};
    overflow: hidden;

    ${Breakpoint.smallMobile} {
      grid-template-columns: 1fr;
    }
  `,
  settingsOptions: css`
    min-height: 400px;
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
    padding: ${spacing[16]} ${spacing[32]} ${spacing[48]} ${spacing[32]};
    background-color: ${colorTokens.background.white};

    ${Breakpoint.smallMobile} {
      padding: ${spacing[16]};
    }
  `,
  courseAndQna: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[32]};
    margin-top: ${spacing[12]};
  `,
};
