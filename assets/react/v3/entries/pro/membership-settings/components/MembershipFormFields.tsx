import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

import { borderRadius, Breakpoint, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import FormInput from '@/v3/shared/components/fields/FormInput';
import FormInputWithContent from '@/v3/shared/components/fields/FormInputWithContent';
import { styleUtils } from '@/v3/shared/utils/style-utils';
import FormSelectInput from '@/v3/shared/components/fields/FormSelectInput';
import { requiredRule } from '@Utils/validation';
import { tutorConfig } from '@/v3/shared/config/config';
import FormInputWithPresets from '@/v3/shared/components/fields/FormInputWithPresets';
import FormCheckbox from '@/v3/shared/components/fields/FormCheckbox';
import Show from '@/v3/shared/controls/Show';
import FormTimeInput from '@/v3/shared/components/fields/FormTimeInput';
import FormDateInput from '@/v3/shared/components/fields/FormDateInput';
import FormSwitch from '@/v3/shared/components/fields/FormSwitch';
import { CURRENT_VIEWPORT } from '@/v3/shared/config/constants';

import FormRadioGroup from '@/v3/shared/components/fields/FormRadioGroup';
import IconsAndFeatures from './IconsAndFeatures';
import Categories from './Categories';
import { type MembershipFormData } from '../services/memberships';
const { tutor_currency } = tutorConfig;

export default function MembershipFormFields() {
  const form = useFormContext<MembershipFormData>();

  const chargeEnrolmentFee = form.watch('charge_enrollment_fee');
  const hasSale = form.watch('offer_sale_price');
  const regularPrice = form.watch('regular_price');
  const hasScheduledSale = !!form.watch('schedule_sale_price');
  const isFeatured = !!form.watch('is_featured');

  const lifetimePresets = [3, 6, 9, 12];
  const lifetimeOptions = [
    ...lifetimePresets.map((preset) => ({
      label: sprintf(__('%s times', 'tutor'), preset.toString()),
      value: String(preset),
    })),
    {
      label: __('Until cancelled', 'tutor'),
      value: 'Until cancelled',
    },
  ];

  const planType = form.watch('plan_type');
  const planTypeOptions = [
    { label: __('Full Site', 'tutor'), value: 'full_site' },
    { label: __('Specific Categories', 'tutor'), value: 'category' },
  ];

  return (
    <div css={styles.container}>
      <Controller
        control={form.control}
        name={`plan_name`}
        rules={requiredRule()}
        render={(controllerProps) => (
          <FormInput
            {...controllerProps}
            label={__('Title', 'tutor')}
            placeholder={__('e.g., Silver Membership', 'tutor')}
          />
        )}
      />

      <Controller
        control={form.control}
        name={`short_description`}
        rules={requiredRule()}
        render={(controllerProps) => (
          <FormInput
            {...controllerProps}
            label={__('Short Description', 'tutor')}
            placeholder={__('e.g., Perfect for beginners looking for weekly classes', 'tutor')}
          />
        )}
      />

      <div css={styles.inputGroup}>
        <Controller
          control={form.control}
          name={`regular_price`}
          rules={{
            ...requiredRule(),
            validate: (value) => {
              if (Number(value) <= 0) {
                return __('Price must be greater than 0', 'tutor');
              }
            },
          }}
          render={(controllerProps) => (
            <FormInputWithContent
              {...controllerProps}
              label={__('Price', 'tutor')}
              content={tutor_currency?.symbol || '$'}
              placeholder={__('Plan price', 'tutor')}
              selectOnFocus
              contentCss={styleUtils.inputCurrencyStyle}
              type="number"
            />
          )}
        />

        <Controller
          control={form.control}
          name={`recurring_value`}
          rules={{
            ...requiredRule(),
            validate: (value) => {
              if (Number(value) < 1) {
                return __('This value must be equal to or greater than 1', 'tutor');
              }
            },
          }}
          render={(controllerProps) => (
            <FormInput
              {...controllerProps}
              label={__('Billing Interval', 'tutor')}
              placeholder={__('12', 'tutor')}
              selectOnFocus
              type="number"
            />
          )}
        />

        <Controller
          control={form.control}
          name={`recurring_interval`}
          render={(controllerProps) => (
            <FormSelectInput
              {...controllerProps}
              label={CURRENT_VIEWPORT.isAboveMobile ? <div>&nbsp;</div> : __('Recurring Options', 'tutor')}
              options={[
                { label: __('Day(s)', 'tutor'), value: 'day' },
                { label: __('Week(s)', 'tutor'), value: 'week' },
                { label: __('Month(s)', 'tutor'), value: 'month' },
                { label: __('Year(s)', 'tutor'), value: 'year' },
              ]}
              removeOptionsMinWidth
            />
          )}
        />

        <Controller
          control={form.control}
          name={`recurring_limit`}
          rules={{
            ...requiredRule(),
            validate: (value) => {
              if (value === 'Until cancelled') {
                return true;
              }

              if (Number(value) <= 0) {
                return __('Renew plan must be greater than 0', 'tutor');
              }
              return true;
            },
          }}
          render={(controllerProps) => (
            <FormInputWithPresets
              {...controllerProps}
              label={__('Billing Cycles', 'tutor')}
              placeholder={__('Select or type times to renewing the plan', 'tutor')}
              content={controllerProps.field.value !== 'Until cancelled' && __('Times', 'tutor')}
              contentPosition="right"
              type="number"
              presetOptions={lifetimeOptions}
              selectOnFocus
            />
          )}
        />
      </div>

      <Controller
        control={form.control}
        name="plan_type"
        render={(controllerProps) => (
          <FormRadioGroup
            {...controllerProps}
            label={__('Membership Type', 'tutor')}
            options={planTypeOptions}
            wrapperCss={styles.planTypeWrapper}
          />
        )}
      />

      <Show when={planType === 'category'}>
        <Categories form={form} />
      </Show>

      <IconsAndFeatures />

      <Controller
        control={form.control}
        name={`charge_enrollment_fee`}
        render={(controllerProps) => <FormCheckbox {...controllerProps} label={__('Charge enrollment fee', 'tutor')} />}
      />

      <Show when={chargeEnrolmentFee}>
        <Controller
          control={form.control}
          name={`enrollment_fee`}
          rules={{
            ...requiredRule(),
            validate: (value) => {
              if (Number(value) <= 0) {
                return __('Enrollment fee must be greater than 0', 'tutor');
              }
              return true;
            },
          }}
          render={(controllerProps) => (
            <FormInputWithContent
              {...controllerProps}
              label={__('Enrollment fee', 'tutor')}
              content={tutor_currency?.symbol || '$'}
              placeholder={__('Enter enrollment fee', 'tutor')}
              selectOnFocus
              contentCss={styleUtils.inputCurrencyStyle}
              type="number"
            />
          )}
        />
      </Show>

      <Controller
        control={form.control}
        name={`do_not_provide_certificate`}
        render={(controllerProps) => (
          <FormCheckbox {...controllerProps} label={__('Do not provide certificate', 'tutor')} />
        )}
      />

      <Controller
        control={form.control}
        name={`is_featured`}
        render={(controllerProps) => <FormCheckbox {...controllerProps} label={__('Mark as featured', 'tutor')} />}
      />

      <Show when={isFeatured}>
        <Controller
          control={form.control}
          name="featured_text"
          render={(controllerProps) => <FormInput {...controllerProps} label={__('Feature Text', 'tutor')} />}
        />
      </Show>

      <div css={styles.salePriceWrapper}>
        <div>
          <Controller
            control={form.control}
            name={`offer_sale_price`}
            render={(props) => <FormSwitch {...props} label={__('Offer sale price', 'tutor')} />}
          />
        </div>
        <Show when={hasSale}>
          <div css={styles.salePriceInputs}>
            <Controller
              control={form.control}
              name={`sale_price`}
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
                  label="Sale Price"
                  content={tutor_currency?.symbol || '$'}
                  selectOnFocus
                  contentCss={styleUtils.inputCurrencyStyle}
                />
              )}
            />

            <Controller
              control={form.control}
              name={`schedule_sale_price`}
              render={(props) => <FormCheckbox {...props} label={__('Schedule the sale price', 'tutor')} />}
            />

            <Show when={hasScheduledSale}>
              <div css={styles.datetimeWrapper}>
                <label>{__('Sale starts from', 'tutor')}</label>
                <div css={styleUtils.dateAndTimeWrapper}>
                  <Controller
                    name={`sale_price_from_date`}
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
                    name={`sale_price_from_time`}
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
              <div css={styles.datetimeWrapper}>
                <label>{__('Sale ends to', 'tutor')}</label>
                <div css={styleUtils.dateAndTimeWrapper}>
                  <Controller
                    name={`sale_price_to_date`}
                    control={form.control}
                    rules={{
                      required: __('Schedule date is required', 'tutor'),
                      validate: {
                        checkEndDate: (value) => {
                          const startDate = form.watch(`sale_price_from_date`);
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
                        disabledBefore={form.watch(`sale_price_from_date`) || undefined}
                      />
                    )}
                  />

                  <Controller
                    name={`sale_price_to_time`}
                    control={form.control}
                    rules={{
                      required: __('Schedule time is required', 'tutor'),
                      validate: {
                        checkEndTime: (value) => {
                          const startDate = form.watch(`sale_price_from_date`);
                          const startTime = form.watch(`sale_price_from_time`);
                          const endDate = form.watch(`sale_price_to_date`);
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
    </div>
  );
}

const styles = {
  container: css`
    width: 100%;
    max-width: 640px;
    margin: 0 auto;
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius.card};
    padding: ${spacing[16]};

    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
  `,
  salePriceWrapper: css`
    background-color: ${colorTokens.background.white};
    display: flex;
    flex-direction: column;
    gap: ${spacing[20]};

    padding: ${spacing[12]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius.card};
  `,
  salePriceInputs: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
  `,
  inputGroup: css`
    display: grid;
    grid-template-columns: 1fr 0.7fr 1fr 1fr;
    align-items: start;
    gap: ${spacing[8]};

    ${Breakpoint.mobile} {
      grid-template-columns: 1fr;
    }
  `,
  datetimeWrapper: css`
    label {
      ${typography.caption()};
      color: ${colorTokens.text.title};
    }
  `,
  planTypeWrapper: css`
    display: flex;
    gap: ${spacing[8]};
  `,
};
