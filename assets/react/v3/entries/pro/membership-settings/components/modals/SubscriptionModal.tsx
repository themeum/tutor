import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { Controller, FormProvider } from 'react-hook-form';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import type { ModalProps } from '@Components/modals/Modal';
import ModalWrapper from '@Components/modals/ModalWrapper';

import { borderRadius, Breakpoint, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
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
import IconsAndFeatures from '../IconsAndFeatures';
const { tutor_currency } = tutorConfig;

interface SubscriptionModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

export default function SubscriptionModal({ title, subtitle, icon, closeModal }: SubscriptionModalProps) {
  const form = useFormWithGlobalError({
    defaultValues: {
      plan_name: '',
      short_description: '',
      features: [],
      categories: [],
      payment_type: '',
      plan_type: '',
      recurring_value: '',
      recurring_interval: '',
      recurring_limit: '',
      regular_price: '',
      offer_sale_price: false,
      sale_price: '',
      schedule_sale_price: false,
      sale_price_from: '',
      sale_price_from_date: '',
      sale_price_from_time: '',
      sale_price_to: '',
      sale_price_to_date: '',
      sale_price_to_time: '',
      charge_enrollment_fee: false,
      enrollment_fee: '',
      provide_certificate: false,
      is_featured: false,
    },
  });

  const isFormDirty = form.formState.isDirty;

  const chargeEnrolmentFee = form.watch('charge_enrollment_fee');
  const hasSale = form.watch('offer_sale_price');
  const regularPrice = form.watch(`regular_price`);
  const hasScheduledSale = !!form.watch(`schedule_sale_price`);

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

  return (
    <FormProvider {...form}>
      <ModalWrapper
        maxWidth={1060}
        onClose={() => closeModal({ action: 'CLOSE' })}
        icon={isFormDirty ? <SVGIcon name="warning" width={24} height={24} /> : icon}
        title={isFormDirty ? __('Unsaved Changes', 'tutor') : title}
        subtitle={isFormDirty ? title?.toString() : subtitle}
        actions={
          <>
            <Button
              variant="text"
              size="small"
              onClick={() => {
                closeModal({ action: 'CLOSE' });
              }}
            >
              {__('Cancel', 'tutor')}
            </Button>
            <Button
              variant="primary"
              size="small"
              onClick={() => {
                form.handleSubmit((data) => {
                  console.log(data);
                })();
              }}
            >
              {__('Save', 'tutor')}
            </Button>
          </>
        }
      >
        <div css={styles.wrapper}>
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
              name={`categories`}
              render={(controllerProps) => (
                <FormSelectInput {...controllerProps} label={__('Select Categories', 'tutor')} options={[]} />
              )}
            />

            <IconsAndFeatures />

            <Controller
              control={form.control}
              name={`charge_enrollment_fee`}
              render={(controllerProps) => (
                <FormCheckbox {...controllerProps} label={__('Charge enrollment fee', 'tutor')} />
              )}
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
              name={`provide_certificate`}
              render={(controllerProps) => (
                <FormCheckbox {...controllerProps} label={__('Do not provide certificate', 'tutor')} />
              )}
            />

            <Controller
              control={form.control}
              name={`is_featured`}
              render={(controllerProps) => (
                <FormCheckbox {...controllerProps} label={__('Mark as featured', 'tutor')} />
              )}
            />

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
                            <FormTimeInput
                              {...controllerProps}
                              interval={60}
                              isClearable={false}
                              placeholder="hh:mm A"
                            />
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
                            <FormTimeInput
                              {...controllerProps}
                              interval={60}
                              isClearable={false}
                              placeholder="hh:mm A"
                            />
                          )}
                        />
                      </div>
                    </div>
                  </Show>
                </div>
              </Show>
            </div>
          </div>
        </div>
      </ModalWrapper>
    </FormProvider>
  );
}

const styles = {
  wrapper: css`
    padding: ${spacing[40]} ${spacing[16]};

    ${Breakpoint.mobile} {
      padding: ${spacing[24]} ${spacing[16]};
    }
  `,
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
  `,
  datetimeWrapper: css`
    label {
      ${typography.caption()};
      color: ${colorTokens.text.title};
    }
  `,
};
