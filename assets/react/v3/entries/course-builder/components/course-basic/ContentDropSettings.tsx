import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import FormRadioGroup from '@Components/fields/FormRadioGroup';
import { tutorConfig } from '@Config/config';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { Controller, useFormContext } from 'react-hook-form';

const ContentDropSettings = () => {
  const form = useFormContext();

  const contentDropOptions = [
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
    {
      label: __('None', 'tutor'),
      value: 0,
    },
  ];

  if (!tutorConfig.tutor_pro_url) {
    return (
      <div css={styles.dripNoProWrapper}>
        <SVGIcon name="crown" width={72} height={72} />
        <h6 css={typography.body('medium')}>{__('Content Drip is a pro feature', 'tutor')}</h6>
        <p css={styles.dripNoProDescription}>
          {__('You can schedule your course content using  content drip options', 'tutor')}
        </p>
        {/* @TODO: Redirect to tutor pro url */}
        <Button icon={<SVGIcon name="crown" width={24} height={24} />}>{__('Get Tutor LMS Pro', 'tutor')}</Button>
      </div>
    );
  }

  if (!tutorConfig.addons_data.find((addon) => addon.name === 'Content Drip')?.is_enabled) {
    return (
      <div css={styles.dripNoProWrapper}>
        <SVGIcon name="contentDrip" width={72} height={72} style={styles.dripIcon} />
        <h6 css={typography.body('medium')}>{__('Content Drip Addon is not enabled!', 'tutor')}</h6>
        <p css={styles.dripNoProDescription}>{__('Please enable content drip addon to see options', 'tutor')}</p>
      </div>
    );
  }
  return (
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
  );
};

export default ContentDropSettings;

const styles = {
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
  dripNoProWrapper: css`
    min-height: 400px;
    background: ${colorTokens.background.white};
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: ${spacing[4]};
    padding: ${spacing[24]};
    text-align: center;
  `,
  dripNoProDescription: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    max-width: 280px;
    margin: 0 auto ${spacing[12]};
  `,
  dripIcon: css`
    color: ${colorTokens.icon.brand};
  `,
};
