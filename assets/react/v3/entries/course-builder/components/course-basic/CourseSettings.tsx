import React from 'react';
import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormSwitch from '@Components/fields/FormSwitch';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { Controller, useFormContext } from 'react-hook-form';
import Tabs from '@Molecules/Tabs';
import { useState } from 'react';
import SVGIcon from '@Atoms/SVGIcon';
import { __ } from '@wordpress/i18n';
import FormRadioGroup from '@Components/fields/FormRadioGroup';
import { typography } from '@Config/typography';

const CourseSettings = () => {
  const form = useFormContext();
  const [activeTab, setActiveTab] = useState('general');

  // @TODO: Need to add buddyboss options based on plugin installation
  const tabList = [
    {
      label: 'General',
      value: 'general',
      icon: <SVGIcon name="settings" width={24} height={24} />,
    },
    {
      label: 'Content Drip',
      value: 'content_drip',
      icon: <SVGIcon name="contentDrip" width={24} height={24} />,
      activeBadge: true,
    },
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

  const contentDropOptions = [
    {
      label: __('None', 'tutor'),
      value: 0,
    },
    {
      label: __('Schedule course contents by date', 'tutor'),
      value: 1,
    },
    {
      label: __('Content available after X days from enrollment', 'tutor'),
      value: 2,
    },
    {
      label: __('Course content available sequentially', 'tutor'),
      value: 3,
    },
    {
      label: __('Course content unlocked after finishing prerequisites', 'tutor'),
      value: 4,
    },
  ];

  return (
    <div>
      <label css={typography.caption()}>Course Settings</label>

      <div css={styles.courseSettings}>
        <Tabs tabList={tabList} activeTab={activeTab} onChange={setActiveTab} orientation="vertical" />

        {activeTab === 'general' && (
          <div css={styles.settingsOptions}>
            <Controller
              name="maximum_student"
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
              name="enrollment_expiration"
              control={form.control}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  label={__('Enrollment Expiration', 'tutor')}
                  helpText={__(
                    "Student's enrollment will be removed after this number of days. Set 0 for lifetime enrollment.",
                    'tutor'
                  )}
                  placeholder="0"
                  type="number"
                  isClearable
                />
              )}
            />

            <div css={styles.courseAndQna}>
              <Controller
                name="public_course"
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

        {activeTab === 'content_drip' && (
          <div css={styles.dripWrapper}>
            <h6 css={styles.dripTitle}>{__('Content Drip Type', 'tutor')}</h6>
            <p css={styles.dripSubTitle}>
              {__('You can schedule your course content using the above content drip options', 'tutor')}
            </p>

            <Controller
              name="content_drop"
              control={form.control}
              render={(controllerProps) => (
                <FormRadioGroup {...controllerProps} options={contentDropOptions} wrapperCss={styles.radioWrapper} />
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
  dripWrapper: css`
    background-color: ${colorTokens.background.white};
    padding: ${spacing[16]} ${spacing[24]} ${spacing[32]} ${spacing[32]};
  `,
  dripTitle: css`
    ${typography.body('medium')};
    margin-bottom: ${spacing[4]};
  `,
  dripSubTitle: css`
    ${typography.small()};
    color: ${colorTokens.text.hints};
    max-width: 280px;
    margin-bottom: ${spacing[16]};
  `,
  radioWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
};
