import { css } from '@emotion/react';
import { useIsFetching } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormRadioGroup from '@Components/fields/FormRadioGroup';
import SubscriptionPreview from '@Components/subscription/SubscriptionPreview';

import { type BundleFormData } from '@BundleBuilderServices/bundle';
import { tutorConfig } from '@Config/config';
import { Addons } from '@Config/constants';
import { spacing } from '@Config/styles';
import Show from '@Controls/Show';
import { isAddonEnabled } from '@CourseBuilderUtils/utils';
import { styleUtils } from '@Utils/style-utils';
import { getBundleId } from '../../utils/utils';

const bundleId = getBundleId();

const BundlePricing = () => {
  const form = useFormContext<BundleFormData>();
  const isCourseDetailsFetching = useIsFetching({
    queryKey: ['CourseBundle', bundleId],
  });

  const { tutor_currency } = tutorConfig;

  return (
    <>
      <div css={styles.coursePriceWrapper}>
        <Controller
          name="regular_price"
          control={form.control}
          render={(controllerProps) => (
            <FormInputWithContent
              {...controllerProps}
              disabled
              label={__('Regular Price', 'tutor')}
              content={tutor_currency?.symbol || '$'}
              placeholder={__('0', 'tutor')}
              type="number"
              loading={!!isCourseDetailsFetching && !controllerProps.field.value}
              selectOnFocus
              contentCss={styleUtils.inputCurrencyStyle}
            />
          )}
        />
        <Controller
          name="sale_price"
          control={form.control}
          rules={{
            validate: (value) => {
              if (!value) {
                return true;
              }

              const regularPrice = form.getValues('regular_price');
              if (Number(value) >= Number(regularPrice)) {
                return __('Sale price must be less than regular price', 'tutor');
              }

              return true;
            },
          }}
          render={(controllerProps) => (
            <FormInputWithContent
              {...controllerProps}
              label={__('Sale Price', 'tutor')}
              content={tutor_currency?.symbol || '$'}
              placeholder={__('0', 'tutor')}
              type="number"
              loading={!!isCourseDetailsFetching && !controllerProps.field.value}
              selectOnFocus
              contentCss={styleUtils.inputCurrencyStyle}
            />
          )}
        />
      </div>

      <Show when={isAddonEnabled(Addons.SUBSCRIPTION) && tutorConfig.settings?.monetize_by === 'tutor'}>
        <SubscriptionPreview courseId={bundleId} />

        <Controller
          name="course_selling_option"
          control={form.control}
          render={(controllerProps) => (
            <FormRadioGroup
              {...controllerProps}
              wrapperCss={css`
                > div:not(:last-child) {
                  margin-bottom: ${spacing[10]};
                }
              `}
              label={__('Purchase Options', 'tutor')}
              options={[
                {
                  label: __('Subscription only', 'tutor'),
                  value: 'subscription',
                },
                {
                  label: __('One-time purchase only', 'tutor'),
                  value: 'one_time',
                },
                {
                  label: __('Subscription & one-time purchase', 'tutor'),
                  value: 'both',
                },
              ]}
            />
          )}
        />
      </Show>
    </>
  );
};

export default BundlePricing;

const styles = {
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
