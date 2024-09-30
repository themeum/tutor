import { css } from '@emotion/react';
import { useIsFetching } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';
import { Controller, useFormContext } from 'react-hook-form';

import SVGIcon from '@Atoms/SVGIcon';
import Tabs, { type TabItem } from '@Molecules/Tabs';

import FormInput from '@Components/fields/FormInput';
import FormSwitch from '@Components/fields/FormSwitch';

import FormCheckbox from '@Components/fields/FormCheckbox';
import FormMultiSelectInput from '@Components/fields/FormMultiSelectInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import { tutorConfig } from '@Config/config';
import { Addons } from '@Config/constants';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import ContentDripSettings from '@CourseBuilderComponents/course-basic/ContentDripSettings';
import type { CourseFormData } from '@CourseBuilderServices/course';
import { getCourseId, isAddonEnabled } from '@CourseBuilderUtils/utils';
import type { Option } from '@Utils/types';

const courseId = getCourseId();

const CourseSettings = () => {
  const form = useFormContext<CourseFormData>();
  const [activeTab, setActiveTab] = useState('general');
  const isCourseDetailsLoading = useIsFetching({
    queryKey: ['CourseDetails', courseId],
  });

  const isContentDripActive = form.watch('contentDripType');
  const isBuddyPressEnabled = form.watch('enable_tutor_bp');
  const priceCategory = form.watch('course_pricing_category');

  const tabList: TabItem<string>[] = [
    {
      label: __('General', 'tutor'),
      value: 'general',
      icon: <SVGIcon name="settings" width={24} height={24} />,
    },
    {
      label: __('Content Drip', 'tutor'),
      value: 'content_drip',
      icon: <SVGIcon name="contentDrip" width={24} height={24} />,
      activeBadge: !!isContentDripActive,
    },
  ];

  isAddonEnabled(Addons.BUDDYPRESS) &&
    tabList.push({
      label: __('BuddyPress', 'tutor'),
      value: 'buddyPress',
      icon: <SVGIcon name="buddyPress" width={24} height={24} />,
      activeBadge: isBuddyPressEnabled,
    });

  const difficultyLevelOptions: Option<string>[] = (tutorConfig.difficulty_levels || []).map((level) => ({
    label: level.label,
    value: level.value,
  }));

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (priceCategory === 'subscription') {
      form.setValue('enrollment_expiry', 0);
    }
  }, [priceCategory]);

  return (
    <div>
      <label css={typography.caption()}>{__('Course Settings', 'tutor')}</label>

      <div css={styles.courseSettings}>
        <Tabs tabList={tabList} activeTab={activeTab} onChange={setActiveTab} orientation="vertical" />

        {activeTab === 'general' && (
          <div css={styles.settingsOptions}>
            <Controller
              name="maximum_students"
              control={form.control}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  label={__('Maximum Student', 'tutor')}
                  helpText={__('Number of students that can enrol in this course. Set 0 for no limits.', 'tutor')}
                  placeholder="0"
                  type="number"
                  isClearable
                  selectOnFocus
                  loading={!!isCourseDetailsLoading && !controllerProps.field.value}
                />
              )}
            />

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

            <Show when={tutorConfig.settings?.enrollment_expiry_enabled === 'on'}>
              <Controller
                name="enrollment_expiry"
                control={form.control}
                render={(controllerProps) => (
                  <FormInput
                    {...controllerProps}
                    label={__('Enrollment Expiration', 'tutor')}
                    helpText={__(
                      "Student's enrollment will be removed after this number of days. Set 0 for lifetime enrollment.",
                      'tutor',
                    )}
                    disabled={priceCategory === 'subscription'}
                    placeholder="0"
                    type="number"
                    isClearable
                    selectOnFocus
                    loading={!!isCourseDetailsLoading && !controllerProps.field.value}
                  />
                )}
              />
            </Show>

            <div css={styles.courseAndQna}>
              <Controller
                name="is_public_course"
                control={form.control}
                render={(controllerProps) => (
                  <FormSwitch
                    {...controllerProps}
                    label={__('Public Course', 'tutor')}
                    helpText={__('Make This Course Public. No Enrollment Required.', 'tutor')}
                    loading={!!isCourseDetailsLoading && !controllerProps.field.value}
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
  `,
  settingsOptions: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
    padding: ${spacing[16]} ${spacing[32]} ${spacing[48]} ${spacing[32]};
    background-color: ${colorTokens.background.white};
  `,
  courseAndQna: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[32]};
    margin-top: ${spacing[12]};
  `,
};
