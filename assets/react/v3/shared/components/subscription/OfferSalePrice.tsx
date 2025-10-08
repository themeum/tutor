import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import FormDateInput from '@TutorShared/components/fields/FormDateInput';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';
import FormSwitch from '@TutorShared/components/fields/FormSwitch';
import FormTimeInput from '@TutorShared/components/fields/FormTimeInput';

import type { SubscriptionFormDataWithSaved } from '@TutorShared/components/modals/SubscriptionModal';
import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { requiredRule } from '@TutorShared/utils/validation';

const { tutor_currency } = tutorConfig;

export function OfferSalePrice() {
  const form = useFormContext<SubscriptionFormDataWithSaved>();
  const hasSale = form.watch('offer_sale_price');
  const regularPrice = form.watch('regular_price');
  const hasScheduledSale = !!form.watch('schedule_sale_price');

  return (
    <div css={saleStyles.wrapper}>
      <div>
        <Controller
          control={form.control}
          name={'offer_sale_price'}
          render={(props) => <FormSwitch {...props} label={__('Offer sale price', __TUTOR_TEXT_DOMAIN__)} />}
        />
      </div>
      <Show when={hasSale}>
        <div css={saleStyles.inputWrapper}>
          <Controller
            control={form.control}
            name={'sale_price'}
            rules={{
              ...requiredRule(),
              validate: (value) => {
                if (value && regularPrice && Number(value) >= Number(regularPrice)) {
                  return __('Sale price should be less than regular price', __TUTOR_TEXT_DOMAIN__);
                }

                if (value && regularPrice && Number(value) <= 0) {
                  return __('Sale price should be greater than 0', __TUTOR_TEXT_DOMAIN__);
                }

                return undefined;
              },
            }}
            render={(props) => (
              <FormInputWithContent
                {...props}
                type="number"
                label={__('Sale Price', __TUTOR_TEXT_DOMAIN__)}
                content={tutor_currency?.symbol || '$'}
                selectOnFocus
                contentCss={styleUtils.inputCurrencyStyle}
              />
            )}
          />
          <Controller
            control={form.control}
            name={'schedule_sale_price'}
            render={(props) => <FormCheckbox {...props} label={__('Schedule the sale price', __TUTOR_TEXT_DOMAIN__)} />}
          />
          <Show when={hasScheduledSale}>
            <div css={saleStyles.datetimeWrapper}>
              <label>{__('Sale starts from', __TUTOR_TEXT_DOMAIN__)}</label>
              <div css={styleUtils.dateAndTimeWrapper}>
                <Controller
                  name={'sale_price_from_date'}
                  control={form.control}
                  rules={{
                    required: __('Schedule date is required', __TUTOR_TEXT_DOMAIN__),
                  }}
                  render={(controllerProps) => (
                    <FormDateInput
                      {...controllerProps}
                      isClearable={false}
                      placeholder="yyyy-mm-dd"
                      disabledBefore={new Date().toISOString()}
                    />
                  )}
                />

                <Controller
                  name={'sale_price_from_time'}
                  control={form.control}
                  rules={{
                    required: __('Schedule time is required', __TUTOR_TEXT_DOMAIN__),
                  }}
                  render={(controllerProps) => (
                    <FormTimeInput {...controllerProps} interval={60} isClearable={false} placeholder="hh:mm A" />
                  )}
                />
              </div>
            </div>
            <div css={saleStyles.datetimeWrapper}>
              <label>{__('Sale ends to', __TUTOR_TEXT_DOMAIN__)}</label>
              <div css={styleUtils.dateAndTimeWrapper}>
                <Controller
                  name={'sale_price_to_date'}
                  control={form.control}
                  rules={{
                    required: __('Schedule date is required', __TUTOR_TEXT_DOMAIN__),
                    validate: {
                      checkEndDate: (value) => {
                        const startDate = form.watch('sale_price_from_date');
                        const endDate = value;
                        if (startDate && endDate) {
                          return new Date(startDate) > new Date(endDate)
                            ? __('Sales End date should be greater than start date', __TUTOR_TEXT_DOMAIN__)
                            : undefined;
                        }
                        return undefined;
                      },
                    },
                    deps: ['sale_price_from_date'],
                  }}
                  render={(controllerProps) => (
                    <FormDateInput
                      {...controllerProps}
                      isClearable={false}
                      placeholder="yyyy-mm-dd"
                      disabledBefore={form.watch('sale_price_from_date') || undefined}
                    />
                  )}
                />

                <Controller
                  name={'sale_price_to_time'}
                  control={form.control}
                  rules={{
                    required: __('Schedule time is required', __TUTOR_TEXT_DOMAIN__),
                    validate: {
                      checkEndTime: (value) => {
                        const startDate = form.watch('sale_price_from_date');
                        const startTime = form.watch('sale_price_from_time');
                        const endDate = form.watch('sale_price_to_date');
                        const endTime = value;
                        if (startDate && endDate && startTime && endTime) {
                          return new Date(`${startDate} ${startTime}`) > new Date(`${endDate} ${endTime}`)
                            ? __('Sales End time should be greater than start time', __TUTOR_TEXT_DOMAIN__)
                            : undefined;
                        }
                        return undefined;
                      },
                    },
                    deps: ['sale_price_from_date', 'sale_price_from_time', 'sale_price_to_date'],
                  }}
                  render={(controllerProps) => (
                    <FormTimeInput {...controllerProps} interval={60} isClearable={false} placeholder="hh:mm A" />
                  )}
                />
              </div>
            </div>
          </Show>
        </div>
      </Show>
    </div>
  );
}

const saleStyles = {
  wrapper: css`
    background-color: ${colorTokens.background.white};
    padding: ${spacing[12]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[20]};
  `,
  inputWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
    padding: ${spacing[4]};
    margin: -${spacing[4]};
  `,
  datetimeWrapper: css`
    label {
      ${typography.caption()};
      color: ${colorTokens.text.title};
    }
  `,
};
