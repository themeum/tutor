import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { Controller, useFormContext } from 'react-hook-form';

import SVGIcon from '@Atoms/SVGIcon';
import FormInput from '@Components/fields/FormInput';
import FormMultiSelectInput from '@Components/fields/FormMultiSelectInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormSwitch from '@Components/fields/FormSwitch';
import Tabs from '@Molecules/Tabs';

import FormCheckbox from '@Components/fields/FormCheckbox';
import { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import ContentDripSettings from '@CourseBuilderComponents/course-basic/ContentDripSettings';
import type { CourseFormData } from '@CourseBuilderServices/course';
import { isAddonEnabled } from '@CourseBuilderUtils/utils';

const CourseSettings = () => {
  const form = useFormContext<CourseFormData>();
  const [activeTab, setActiveTab] = useState('general');

  const isContentDripActive = form.watch('contentDripType');
  const isBuddyPressEnabled = form.watch('enable_tutor_bp');

  const tabList = [
    {
      label: __('General', 'tutor'),
      value: 'general',
      icon: <SVGIcon name="settings" width={24} height={24} />,
    },
    {
      label: __('Content Drip', 'tutor'),
      value: 'content_drip',
      icon: <SVGIcon name="contentDrip" width={24} height={24} />,
      activeBadge: isContentDripActive ? true : false,
    },
    ...(isAddonEnabled('BuddyPress')
      ? [
          {
            label: __('BuddyPress', 'tutor'),
            value: 'buddyPress',
            icon: <SVGIcon name="buddyPress" width={24} height={24} />,
            activeBadge: isBuddyPressEnabled ? true : false,
          },
        ]
      : []),
  ];

  const difficultyLevelOptions = [
    {
      label: 'All Levels',
      value: 'all_levels',
    },
    {
      label: 'Beginner',
      value: 'beginner',
    },
    {
      label: 'Intermediate',
      value: 'intermediate',
    },
    {
      label: 'Expert',
      value: 'expert',
    },
  ];

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
                />
              )}
            />

            <Controller
              name="course_level"
              control={form.control}
              defaultValue="all_levels"
              render={(controllerProps) => (
                <FormSelectInput
                  {...controllerProps}
                  label={__('Difficulty Level', 'tutor')}
                  helpText={__('Course difficulty level', 'tutor')}
                  options={difficultyLevelOptions}
                  isClearable={false}
                />
              )}
            />

            {/* @TODO: Add condition based on tutor pro and tutor settings */}
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
                  placeholder="0"
                  type="number"
                  isClearable
                />
              )}
            />

            <div css={styles.courseAndQna}>
              <Controller
                name="is_public_course"
                control={form.control}
                render={(controllerProps) => (
                  <FormSwitch
                    {...controllerProps}
                    label={__('Public Course', 'tutor')}
                    helpText={__('Make This Course Public. No Enrollment Required.', 'tutor')}
                  />
                )}
              />

              <Controller
                name="enable_qna"
                control={form.control}
                render={(controllerProps) => (
                  <FormSwitch
                    {...controllerProps}
                    label={__('Q&A', 'tutor')}
                    helpText={__('Enable Q&A section for your course', 'tutor')}
                  />
                )}
              />
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
                  options={tutorConfig.bp_groups.map((group) => ({
                    label: group.name,
                    value: String(group.id),
                  }))}
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
    padding: ${spacing[16]} ${spacing[32]} ${spacing[32]} ${spacing[32]};
    background-color: ${colorTokens.background.white};
  `,
  courseAndQna: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[32]};
    margin-top: ${spacing[12]};
  `,
};
