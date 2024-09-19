import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

import FormCheckbox from '@Components/fields/FormCheckbox';
import FormDateInput from '@Components/fields/FormDateInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormSwitch from '@Components/fields/FormSwitch';
import FormTimeInput from '@Components/fields/FormTimeInput';

import { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { SubscriptionFormDataWithSaved } from '@CourseBuilderComponents/modals/SubscriptionModal';
import { styleUtils } from '@Utils/style-utils';
import { requiredRule } from '@Utils/validation';

const { tutor_currency } = tutorConfig;

export function OfferSalePrice({ index }: { index: number }) {
  const form = useFormContext<{
    subscriptions: SubscriptionFormDataWithSaved[];
  }>();
  const hasSale = form.watch(`subscriptions.${index}.offer_sale_price`);
  const regularPrice = form.watch(`subscriptions.${index}.regular_price`);
  const hasScheduledSale = !!form.watch(`subscriptions.${index}.schedule_sale_price`);

  return (
    <div css={saleStyles.wrapper}>
      <div>
        <Controller
          control={form.control}
          name={`subscriptions.${index}.offer_sale_price`}
          render={(props) => <FormSwitch {...props} label={__('Offer sale price', 'tutor')} />}
        />
      </div>
      <Show when={hasSale}>
        <div css={saleStyles.inputWrapper}>
          <Controller
            control={form.control}
            name={`subscriptions.${index}.sale_price`}
            rules={{
              ...requiredRule(),
              validate: (value) => {
                if (value && regularPrice && Number(value) >= Number(regularPrice)) {
                  return __('Sale price should be less than regular price', 'tutor');
                }

                if (value && regularPrice && Number(value) <= 0) {
                  return __('Sale price should be greater than 0', 'tutor');
                }

                return undefined;
              },
            }}
            render={(props) => (
              <FormInputWithContent
                {...props}
                type="number"
                label="Sale price"
                content={tutor_currency?.symbol || '$'}
                selectOnFocus
                contentCss={styleUtils.inputCurrencyStyle}
              />
            )}
          />
          <Controller
            control={form.control}
            name={`subscriptions.${index}.schedule_sale_price`}
            render={(props) => <FormCheckbox {...props} label={__('Schedule the sale price', 'tutor')} />}
          />
          <Show when={hasScheduledSale}>
            <div css={saleStyles.datetimeWrapper}>
              <label>{__('Sale starts from', 'tutor')}</label>
              <div css={styleUtils.dateAndTimeWrapper}>
                <Controller
                  name={`subscriptions.${index}.sale_price_from_date`}
                  control={form.control}
                  rules={{
                    required: __('Schedule date is required', 'tutor'),
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
                  name={`subscriptions.${index}.sale_price_from_time`}
                  control={form.control}
                  rules={{
                    required: __('Schedule time is required', 'tutor'),
                  }}
                  render={(controllerProps) => (
                    <FormTimeInput {...controllerProps} interval={60} isClearable={false} placeholder="hh:mm A" />
                  )}
                />
              </div>
            </div>
            <div css={saleStyles.datetimeWrapper}>
              <label>{__('Sale ends to', 'tutor')}</label>
              <div css={styleUtils.dateAndTimeWrapper}>
                <Controller
                  name={`subscriptions.${index}.sale_price_to_date`}
                  control={form.control}
                  rules={{
                    required: __('Schedule date is required', 'tutor'),
                    validate: {
                      checkEndDate: (value) => {
                        const startDate = form.watch(`subscriptions.${index}.sale_price_from_date`);
                        const endDate = value;
                        if (startDate && endDate) {
                          return new Date(startDate) > new Date(endDate)
                            ? __('Sales End date should be greater than start date', 'tutor')
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
                      disabledBefore={form.watch(`subscriptions.${index}.sale_price_from_date`) || undefined}
                    />
                  )}
                />

                <Controller
                  name={`subscriptions.${index}.sale_price_to_time`}
                  control={form.control}
                  rules={{
                    required: __('Schedule time is required', 'tutor'),
                    validate: {
                      checkEndTime: (value) => {
                        const startDate = form.watch(`subscriptions.${index}.sale_price_from_date`);
                        const startTime = form.watch(`subscriptions.${index}.sale_price_from_time`);
                        const endDate = form.watch(`subscriptions.${index}.sale_price_to_date`);
                        const endTime = value;
                        if (startDate && endDate && startTime && endTime) {
                          return new Date(`${startDate} ${startTime}`) > new Date(`${endDate} ${endTime}`)
                            ? __('Sales End time should be greater than start time', 'tutor')
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
