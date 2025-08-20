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

  const lifetimePresets = [3, 6, 9, 12];
  const lifetimeOptions = [
    ...lifetimePresets.map((preset) => ({
      /* translators: %s is the number of times */
      label: sprintf(__('%s times', 'tutor'), preset.toString()),
      value: String(preset),
    })),
    {
      label: __('Until cancelled', 'tutor'),
      value: __('Until cancelled', 'tutor'),
    },
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
                placeholder={__('Enter plan name', 'tutor')}
                label={__('Plan Name', 'tutor')}
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
              name={'recurring_value'}
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
              name={'recurring_interval'}
              render={(controllerProps) => (
                <FormSelectInput
                  {...controllerProps}
                  label={<div>&nbsp;</div>}
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
              name={'recurring_limit'}
              rules={{
                ...requiredRule(),
                validate: (value) => {
                  if (value === __('Until cancelled', 'tutor')) {
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
                  content={controllerProps.field.value !== __('Until cancelled', 'tutor') && __('Times', 'tutor')}
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
            name={'charge_enrollment_fee'}
            render={(controllerProps) => (
              <FormCheckbox {...controllerProps} label={__('Charge enrollment fee', 'tutor')} />
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
          {/* @TODO: Will be added after confirmation */}
          {/* <Controller
              control={form.control}
              name={`subscriptions.${index}.enable_free_trial`}
              render={(controllerProps) => (
                <FormCheckbox {...controllerProps} label={__('Enable a free trial', 'tutor')} />
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
                        return __('Trial duration must be greater than 0', 'tutor');
                      }
                      return true;
                    },
                  }}
                  render={(controllerProps) => (
                    <FormInput
                      {...controllerProps}
                      label={__('Length of free trial', 'tutor')}
                      placeholder={__('Enter trial duration', 'tutor')}
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
                      placeholder={__('Enter trial duration unit', 'tutor')}
                      options={[
                        { label: __('Hour(s)', 'tutor'), value: 'hour' },
                        { label: __('Day(s)', 'tutor'), value: 'day' },
                        { label: __('Week(s)', 'tutor'), value: 'week' },
                        { label: __('Month(s)', 'tutor'), value: 'month' },
                        { label: __('Year(s)', 'tutor'), value: 'year' },
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
              <FormCheckbox {...controllerProps} label={__('Do not provide certificate', 'tutor')} />
            )}
          />

          <Controller
            control={form.control}
            name={'is_featured'}
            render={(controllerProps) => <FormCheckbox {...controllerProps} label={__('Mark as featured', 'tutor')} />}
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
