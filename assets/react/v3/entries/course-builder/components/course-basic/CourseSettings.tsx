import SVGIcon from '@Atoms/SVGIcon';
import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormSwitch from '@Components/fields/FormSwitch';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import type { CourseFormData } from '@CourseBuilderServices/course';
import Tabs from '@Molecules/Tabs';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { useState } from 'react';
import { Controller, useFormContext } from 'react-hook-form';
import ContentDropSettings from './ContentDropSettings';

const CourseSettings = () => {
  const form = useFormContext<CourseFormData>();
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

        {activeTab === 'content_drip' && <ContentDropSettings />}
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
