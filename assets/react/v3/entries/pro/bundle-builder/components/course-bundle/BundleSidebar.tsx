import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { Controller, useFormContext } from 'react-hook-form';

import SVGIcon from '@Atoms/SVGIcon';
import FormImageInput from '@Components/fields/FormImageInput';
import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';

import BundlePricing from '@BundleBuilderComponents/course-bundle/BundlePricing';
import ScheduleOptions from '@BundleBuilderComponents/course-bundle/ScheduleOptions';
import { type BundleFormData } from '@BundleBuilderServices/bundle';
import { tutorConfig } from '@Config/config';
import { DateFormats, visibilityStatusOptions } from '@Config/constants';
import { Breakpoint, colorTokens, headerHeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { styleUtils } from '@Utils/style-utils';
import { useIsFetching } from '@tanstack/react-query';
import { getBundleId } from '../../utils/utils';

const bundleId = getBundleId();
const isTutorPro = !!tutorConfig.tutor_pro_url;
const isOpenAiEnabled = tutorConfig.settings?.chatgpt_enable === 'on';

const ribbonOptions = [
  {
    label: __('Show Discount % off', 'tutor'),
    value: 'in_percentage',
  },
  {
    label: sprintf(__('Show discount amount (%s)', 'tutor'), tutorConfig.tutor_currency.symbol),
    value: 'in_amount',
  },
  {
    label: __('Show None', 'tutor'),
    value: 'none',
  },
];

const BundleSidebar = () => {
  const form = useFormContext<BundleFormData>();
  const isBundleDetailsQueryFetching = useIsFetching({
    queryKey: ['CourseBundle', bundleId],
  });

  const postModified = form.watch('post_modified');
  const visibilityStatus = form.watch('visibility');

  return (
    <div css={styles.sidebar}>
      <div css={styles.statusAndDate}>
        <Controller
          name="visibility"
          control={form.control}
          render={(controllerProps) => (
            <FormSelectInput
              {...controllerProps}
              label={__('Visibility', 'tutor')}
              placeholder={__('Select visibility status', 'tutor')}
              options={visibilityStatusOptions}
              leftIcon={<SVGIcon name="eye" width={32} height={32} />}
              loading={!!isBundleDetailsQueryFetching && !controllerProps.field.value}
              onChange={() => {
                form.setValue('post_password', '');
              }}
            />
          )}
        />

        <Show when={postModified}>
          {(date) => (
            <div css={styles.updatedOn}>
              {sprintf(__('Last updated on %s', 'tutor'), format(new Date(date), DateFormats.dayMonthYear) || '')}
            </div>
          )}
        </Show>
      </div>

      <Show when={visibilityStatus === 'password_protected'}>
        <Controller
          name="post_password"
          control={form.control}
          rules={{
            required: __('Password is required', 'tutor'),
          }}
          render={(controllerProps) => (
            <FormInput
              {...controllerProps}
              label={__('Password', 'tutor')}
              placeholder={__('Enter password', 'tutor')}
              type="password"
              isPassword
              selectOnFocus
              // loading={!!isCourseDetailsFetching && !controllerProps.field.value}
            />
          )}
        />
      </Show>

      <ScheduleOptions />

      <Controller
        name="thumbnail"
        control={form.control}
        render={(controllerProps) => (
          <FormImageInput
            {...controllerProps}
            label={__('Featured Image', 'tutor')}
            buttonText={__('Upload Thumbnail', 'tutor')}
            infoText={sprintf(__('JPEG, PNG, GIF, and WebP formats, up to %s', 'tutor'), tutorConfig.max_upload_size)}
            generateWithAi={!isTutorPro || isOpenAiEnabled}
            // loading={!!isCourseDetailsFetching && !controllerProps.field.value}
          />
        )}
      />

      <BundlePricing />

      <Controller
        name="ribbon"
        control={form.control}
        render={(controllerProps) => (
          <FormSelectInput
            {...controllerProps}
            label={__('Select ribbon to display', 'tutor')}
            placeholder={__('Select ribbon', 'tutor')}
            options={ribbonOptions}
            // loading={!!isCourseDetailsFetching && !controllerProps.field.value}
          />
        )}
      />
    </div>
  );
};

export default BundleSidebar;

const styles = {
  sidebar: css`
    border-left: 1px solid ${colorTokens.stroke.divider};
    min-height: calc(100vh - ${headerHeight}px);
    padding-left: ${spacing[32]};
    padding-block: ${spacing[24]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};

    ${Breakpoint.smallTablet} {
      border-left: none;
      border-top: 1px solid ${colorTokens.stroke.divider};
      padding-block: ${spacing[16]};
      padding-left: 0;
    }
  `,
  statusAndDate: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
  `,
  updatedOn: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
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
};
