import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller, useFormContext } from 'react-hook-form';

import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import FormInput from '@TutorShared/components/fields/FormInput';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';
import FormInputWithPresets from '@TutorShared/components/fields/FormInputWithPresets';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
import type { SubscriptionFormDataWithSaved } from '@TutorShared/components/modals/SubscriptionModal';
import { OfferSalePrice } from '@TutorShared/components/subscription/OfferSalePrice';

import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import Show from '@TutorShared/controls/Show';
import { BILLING_CYCLE_CUSTOM_PRESETS, BILLING_CYCLE_PRESETS } from '@TutorShared/services/subscription';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { requiredRule } from '@TutorShared/utils/validation';

const SET_FOCUS_AFTER = 250; // this is hack to fix layout shifting while animating.

const { tutor_currency } = tutorConfig;

export default function SubscriptionItem() {
  const form = useFormContext<SubscriptionFormDataWithSaved>();

  useEffect(() => {
    const timeoutId = setTimeout(() => {
      form.setFocus('plan_name');
    }, SET_FOCUS_AFTER);
    return () => {
      clearTimeout(timeoutId);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const chargeEnrolmentFee = form.watch('charge_enrollment_fee');
  // @TODO: Will be added after confirmation
  // const enableTrial = form.watch(`subscriptions.${index}.enable_free_trial` as `subscriptions.0.enable_free_trial`);

  const billingCyclesCustomPresets = Object.values(BILLING_CYCLE_CUSTOM_PRESETS);

  const billingCycles = [
    ...BILLING_CYCLE_PRESETS.map((preset) => ({
      /* translators: %s: number of times. */
      label: sprintf(__('%s times', __TUTOR_TEXT_DOMAIN__), preset.toString()),
      value: String(preset),
    })),
    ...billingCyclesCustomPresets.map((value) => ({
      label: value,
      value: value,
    })),
  ];

  return (
    <form css={styles.subscription}>
      <div css={styleUtils.display.flex('column')}>
        <div css={styles.subscriptionContent}>
          <Controller
            control={form.control}
            name={'plan_name'}
            rules={requiredRule()}
            render={(controllerProps) => (
              <FormInput
                {...controllerProps}
                placeholder={__('Enter plan name', __TUTOR_TEXT_DOMAIN__)}
                label={__('Plan Name', __TUTOR_TEXT_DOMAIN__)}
              />
            )}
          />

          <div css={styles.inputGroup}>
            <Controller
              control={form.control}
              name={'regular_price'}
              rules={{
                ...requiredRule(),
                validate: (value) => {
                  if (Number(value) <= 0) {
                    return __('Price must be greater than 0', __TUTOR_TEXT_DOMAIN__);
                  }
                },
              }}
              render={(controllerProps) => (
                <FormInputWithContent
                  {...controllerProps}
                  label={__('Price', __TUTOR_TEXT_DOMAIN__)}
                  content={tutor_currency?.symbol || '$'}
                  placeholder={__('Plan price', __TUTOR_TEXT_DOMAIN__)}
                  selectOnFocus
                  contentCss={styleUtils.inputCurrencyStyle}
                  type="number"
                />
              )}
            />
            <Controller
              control={form.control}
              name={'recurring_value'}
              rules={{
                ...requiredRule(),
                validate: (value) => {
                  if (Number(value) < 1) {
                    return __('This value must be equal to or greater than 1', __TUTOR_TEXT_DOMAIN__);
                  }
                },
              }}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  label={__('Billing Interval', __TUTOR_TEXT_DOMAIN__)}
                  placeholder={__('12', __TUTOR_TEXT_DOMAIN__)}
                  selectOnFocus
                  type="number"
                />
              )}
            />

            <Controller
              control={form.control}
              name={'recurring_interval'}
              render={(controllerProps) => (
                <FormSelectInput
                  {...controllerProps}
                  label={<div>&nbsp;</div>}
                  options={[
                    { label: __('Day(s)', __TUTOR_TEXT_DOMAIN__), value: 'day' },
                    { label: __('Week(s)', __TUTOR_TEXT_DOMAIN__), value: 'week' },
                    { label: __('Month(s)', __TUTOR_TEXT_DOMAIN__), value: 'month' },
                    { label: __('Year(s)', __TUTOR_TEXT_DOMAIN__), value: 'year' },
                  ]}
                  removeOptionsMinWidth
                />
              )}
            />

            <Controller
              control={form.control}
              name={'recurring_limit'}
              rules={{
                ...requiredRule(),
                validate: (value) => {
                  if (billingCyclesCustomPresets.includes(value)) {
                    return true;
                  }

                  if (Number(value) <= 0) {
                    return __('Renew plan must be greater than 0', __TUTOR_TEXT_DOMAIN__);
                  }
                  return true;
                },
              }}
              render={(controllerProps) => (
                <FormInputWithPresets
                  {...controllerProps}
                  label={__('Billing Cycles', __TUTOR_TEXT_DOMAIN__)}
                  placeholder={__('Select or type times to renewing the plan', __TUTOR_TEXT_DOMAIN__)}
                  content={
                    !billingCyclesCustomPresets.includes(controllerProps.field.value) &&
                    __('Times', __TUTOR_TEXT_DOMAIN__)
                  }
                  contentPosition="right"
                  type="number"
                  presetOptions={billingCycles}
                  selectOnFocus
                />
              )}
            />
          </div>

          <Controller
            control={form.control}
            name={'charge_enrollment_fee'}
            render={(controllerProps) => (
              <FormCheckbox {...controllerProps} label={__('Charge enrollment fee', __TUTOR_TEXT_DOMAIN__)} />
            )}
          />

          <Show when={chargeEnrolmentFee}>
            <Controller
              control={form.control}
              name={'enrollment_fee'}
              rules={{
                ...requiredRule(),
                validate: (value) => {
                  if (Number(value) <= 0) {
                    return __('Enrollment fee must be greater than 0', __TUTOR_TEXT_DOMAIN__);
                  }
                  return true;
                },
              }}
              render={(controllerProps) => (
                <FormInputWithContent
                  {...controllerProps}
                  label={__('Enrollment fee', __TUTOR_TEXT_DOMAIN__)}
                  content={tutor_currency?.symbol || '$'}
                  placeholder={__('Enter enrollment fee', __TUTOR_TEXT_DOMAIN__)}
                  selectOnFocus
                  contentCss={styleUtils.inputCurrencyStyle}
                  type="number"
                />
              )}
            />
          </Show>
          {/* @TODO: Will be added after confirmation */}
          {/* <Controller
              control={form.control}
              name={`subscriptions.${index}.enable_free_trial`}
              render={(controllerProps) => (
                <FormCheckbox {...controllerProps} label={__('Enable a free trial', __TUTOR_TEXT_DOMAIN__)} />
              )}
            />

            <Show when={enableTrial}>
              <div css={styles.trialWrapper}>
                <Controller
                  control={form.control}
                  name={`subscriptions.${index}.trial_value`}
                  rules={{
                    ...requiredRule(),
                    validate: (value) => {
                      if (Number(value) <= 0) {
                        return __('Trial duration must be greater than 0', __TUTOR_TEXT_DOMAIN__);
                      }
                      return true;
                    },
                  }}
                  render={(controllerProps) => (
                    <FormInput
                      {...controllerProps}
                      label={__('Length of free trial', __TUTOR_TEXT_DOMAIN__)}
                      placeholder={__('Enter trial duration', __TUTOR_TEXT_DOMAIN__)}
                      selectOnFocus
                    />
                  )}
                />

                <Controller
                  control={form.control}
                  name={`subscriptions.${index}.trial_interval`}
                  render={(controllerProps) => (
                    <FormSelectInput
                      {...controllerProps}
                      label={<div>&nbsp;</div>}
                      placeholder={__('Enter trial duration unit', __TUTOR_TEXT_DOMAIN__)}
                      options={[
                        { label: __('Hour(s)', __TUTOR_TEXT_DOMAIN__), value: 'hour' },
                        { label: __('Day(s)', __TUTOR_TEXT_DOMAIN__), value: 'day' },
                        { label: __('Week(s)', __TUTOR_TEXT_DOMAIN__), value: 'week' },
                        { label: __('Month(s)', __TUTOR_TEXT_DOMAIN__), value: 'month' },
                        { label: __('Year(s)', __TUTOR_TEXT_DOMAIN__), value: 'year' },
                      ]}
                    />
                  )}
                />
              </div>
            </Show> */}

          <Controller
            control={form.control}
            name={'do_not_provide_certificate'}
            render={(controllerProps) => (
              <FormCheckbox {...controllerProps} label={__('Do not provide certificate', __TUTOR_TEXT_DOMAIN__)} />
            )}
          />

          <Controller
            control={form.control}
            name={'is_featured'}
            render={(controllerProps) => (
              <FormCheckbox {...controllerProps} label={__('Mark as featured', __TUTOR_TEXT_DOMAIN__)} />
            )}
          />

          <OfferSalePrice />
        </div>
      </div>
    </form>
  );
}

const styles = {
  trialWrapper: css`
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: start;
    gap: ${spacing[8]};
  `,
  subscription: css`
    width: 100%;
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius.card};
    overflow: hidden;
    transition: border-color 0.3s ease;
  `,
  subscriptionContent: css`
    padding: ${spacing[16]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
  `,
  inputGroup: css`
    display: grid;
    grid-template-columns: 1fr 0.7fr 1fr 1fr;
    align-items: start;
    gap: ${spacing[8]};

    ${Breakpoint.smallMobile} {
      grid-template-columns: 1fr;
    }
  `,
};
