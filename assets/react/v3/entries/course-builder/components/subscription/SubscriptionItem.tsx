import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import FormCheckbox from '@Components/fields/FormCheckbox';
import FormDateInput from '@Components/fields/FormDateInput';
import FormInput from '@Components/fields/FormInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormSwitch from '@Components/fields/FormSwitch';
import FormTimeInput from '@Components/fields/FormTimeInput';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { Subscription } from '@CourseBuilderComponents/course-basic/SubscriptionPreview';
import { AnimatedDiv, AnimationType, useAnimation } from '@Hooks/useAnimation';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, type UseFormReturn } from 'react-hook-form';

function OfferSalePrice({ form }: { form: UseFormReturn<Subscription> }) {
  const hasSale = form.watch('has_sale');
  const { transitions } = useAnimation({
    animationType: AnimationType.slideDown,
    data: hasSale,
  });

  return (
    <div css={saleStyles.wrapper}>
      <div>
        <Controller
          control={form.control}
          name="has_sale"
          render={(props) => <FormSwitch {...props} label="Offer sale price" />}
        />
      </div>
      {transitions((style, openState) => {
        if (openState) {
          return (
            <AnimatedDiv style={style} css={saleStyles.inputWrapper}>
              <Controller
                control={form.control}
                name="sale_price"
                render={(props) => <FormInputWithContent {...props} label="Sale price" content={'$'} />}
              />
              <Controller
                control={form.control}
                name="schedule_sale_price"
                render={(props) => <FormCheckbox {...props} label="Schedule the sale price" />}
              />
              <div css={saleStyles.datetimeWrapper}>
                <label>Schedule start</label>
                <div css={styleUtils.dateAndTimeWrapper}>
                  <Controller
                    name="schedule_start_date"
                    control={form.control}
                    rules={{
                      required: __('Schedule date is required', 'tutor'),
                    }}
                    render={(controllerProps) => (
                      <FormDateInput {...controllerProps} isClearable={false} placeholder="yyyy-mm-dd" />
                    )}
                  />

                  <Controller
                    name="schedule_start_time"
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
                <label>Schedule end</label>
                <div css={styleUtils.dateAndTimeWrapper}>
                  <Controller
                    name="schedule_end_date"
                    control={form.control}
                    rules={{
                      required: __('Schedule date is required', 'tutor'),
                    }}
                    render={(controllerProps) => (
                      <FormDateInput {...controllerProps} isClearable={false} placeholder="yyyy-mm-dd" />
                    )}
                  />

                  <Controller
                    name="schedule_end_time"
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
            </AnimatedDiv>
          );
        }
      })}
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
		gap: ${spacing[8]};
	`,
  datetimeWrapper: css`
		label {
			${typography.caption()};
			color: ${colorTokens.text.title};
		}
	`,
};

export default function SubscriptionItem({
  subscription,
  toggleCollapse,
}: { subscription: Subscription & { isEdit: boolean }; toggleCollapse: (id: number) => void }) {
  const form = useFormWithGlobalError<Subscription>({
    defaultValues: subscription,
  });
  const { transitions } = useAnimation({
    data: subscription.isEdit,
    animationType: AnimationType.slideDown,
  });

  const chargeEnrolmentFee = form.watch('charge_enrolment_fee');
  const enableTrial = form.watch('enable_trial');

  return (
    <div css={styles.subscription}>
      <div css={styles.subscriptionHeader(subscription.isEdit)}>
        <div css={styles.grabber}>
          <SVGIcon name="threeDotsVerticalDouble" />
          <span>{subscription.title}</span>
        </div>
        <div css={styles.actions(subscription.isEdit)}>
          <button type="button">
            <SVGIcon name="copyPaste" width={24} height={24} />
          </button>
          <button type="button">
            <SVGIcon name="delete" width={24} height={24} />
          </button>
          <button type="button" onClick={() => toggleCollapse(subscription.id)}>
            <SVGIcon name="chevronDown" width={24} height={24} />
          </button>
        </div>
      </div>
      {transitions((style, openState) => {
        if (openState) {
          return (
            <AnimatedDiv style={style} css={styles.itemWrapper(subscription.isEdit)}>
              <div css={styles.subscriptionContent}>
                <Controller
                  control={form.control}
                  name="pricing_option"
                  render={(props) => (
                    <FormSelectInput
                      {...props}
                      label={__('Pricing option', 'tutor')}
                      options={[
                        { label: __('Recurring payments', 'tutor'), value: 'recurring' },
                        { label: __('One time payment', 'tutor'), value: 'one-time-purchase' },
                      ]}
                    />
                  )}
                />
                <div
                  css={css`
                    display: grid;
                    grid-template-columns: 2fr 1fr 1fr;
                    align-items: center;
                    gap: ${spacing[8]};
                  `}
                >
                  <Controller
                    control={form.control}
                    name="price"
                    render={(props) => (
                      <FormInputWithContent
                        {...props}
                        label={__('Price', 'tutor')}
                        content={'$'}
                        placeholder={__('Subscription price', 'tutor')}
                      />
                    )}
                  />
                  <Controller
                    control={form.control}
                    name="repeat_every"
                    render={(props) => (
                      <FormInput {...props} label={__('Repeat every', 'tutor')} placeholder="1, 2, etc" />
                    )}
                  />
                  <div
                    css={css`
                      margin-top: auto;
                    `}
                  >
                    <Controller
                      control={form.control}
                      name="repeat_unit"
                      render={(props) => (
                        <FormSelectInput
                          {...props}
                          options={[
                            { label: __('Day(s)', 'tutor'), value: 'day' },
                            { label: __('Week(s)', 'tutor'), value: 'week' },
                            { label: __('Month(s)', 'tutor'), value: 'month' },
                            { label: __('Year(s)', 'tutor'), value: 'year' },
                          ]}
                        />
                      )}
                    />
                  </div>
                </div>

                <Controller
                  control={form.control}
                  name="lifetime"
                  render={(props) => (
                    <FormSelectInput
                      {...props}
                      label={__('Length of the plan', 'tutor')}
                      placeholder={__('Select the length of the plan', 'tutor')}
                      options={[
                        {
                          label: '3',
                          value: 3,
                        },
                        {
                          label: '6',
                          value: 6,
                        },
                        {
                          label: '9',
                          value: 9,
                        },
                        {
                          label: '12',
                          value: 12,
                        },
                        {
                          label: __('Until cancelled', 'tutor'),
                          value: 'until_cancellation',
                        },
                      ]}
                    />
                  )}
                />

                <Controller
                  control={form.control}
                  name="charge_enrolment_fee"
                  render={(props) => <FormCheckbox {...props} label={__('Charge enrolment fee', 'tutor')} />}
                />

                <Show when={chargeEnrolmentFee}>
                  <Controller
                    control={form.control}
                    name="enrolment_fee"
                    render={(props) => (
                      <FormInputWithContent
                        {...props}
                        label={__('Enrolment fee', 'tutor')}
                        content={'$'}
                        placeholder={__('Enter enrolment fee')}
                      />
                    )}
                  />
                </Show>
                <Controller
                  control={form.control}
                  name="enable_trial"
                  render={(props) => <FormCheckbox {...props} label={__('Enable a free trial', 'tutor')} />}
                />

                <Show when={enableTrial}>
                  <div
                    css={css`
                      display: grid;
                      grid-template-columns: 1fr 1fr;
                      align-items: center;
                      gap: ${spacing[8]};
                      
                    `}
                  >
                    <Controller
                      control={form.control}
                      name="trial"
                      render={(props) => (
                        <FormInput
                          {...props}
                          label={__('Length of free trial', 'tutor')}
                          placeholder={__('Enter trial duration', 'tutor')}
                        />
                      )}
                    />
                    <div
                      css={css`
                        margin-top: auto;
                      `}
                    >
                      <Controller
                        control={form.control}
                        name="trial_unit"
                        render={(props) => (
                          <FormSelectInput
                            {...props}
                            placeholder={__('Enter trial duration', 'tutor')}
                            options={[
                              { label: __('Day(s)', 'tutor'), value: 'day' },
                              { label: __('Week(s)', 'tutor'), value: 'week' },
                              { label: __('Month(s)', 'tutor'), value: 'month' },
                              { label: __('Year(s)', 'tutor'), value: 'year' },
                            ]}
                          />
                        )}
                      />
                    </div>
                  </div>
                </Show>

                <OfferSalePrice form={form} />
              </div>
              <div css={styles.subscriptionFooter}>
                <Button variant="text" size="small">
                  {__('Discard', 'tutor')}
                </Button>
                <Button variant="secondary" size="small">
                  {__('Save', 'tutor')}
                </Button>
              </div>
            </AnimatedDiv>
          );
        }
      })}
    </div>
  );
}

const styles = {
  grabber: css`
		display: flex;
		align-items: center;
		gap: ${spacing[4]};
		${typography.body()};
		color: ${colorTokens.text.hints};

		svg {
			color: ${colorTokens.icon.default};
			cursor: grab;
		}
	`,
  subscription: css`
		width: 100%;
		border: 1px solid ${colorTokens.stroke.default};
		border-radius: ${borderRadius.card};
		overflow: hidden;
    
	`,
  itemWrapper: (isActive = false) => css`
    ${
      isActive &&
      css`
        background-color: ${colorTokens.background.hover};
      `
    }
  `,
  subscriptionHeader: (isActive = false) => css`
		padding: ${spacing[12]} ${spacing[16]};
		display: flex;
		align-items: center;
		justify-content: space-between;
    ${
      isActive &&
      css`
        background-color: ${colorTokens.background.hover};
      `
    }
	`,
  subscriptionContent: css`
		padding: ${spacing[16]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};

    
	`,
  subscriptionFooter: css`
		background-color: ${colorTokens.background.white};
		padding: ${spacing[12]} ${spacing[16]};
		display: flex;
		gap: ${spacing[8]};
		justify-content: end;
    box-shadow: ${shadow.footer};
	`,
  actions: (isEdit: boolean) => css`
		display: flex;
		align-items: center;
		gap: ${spacing[4]};

		button {
			width: 24px;
			height: 24px;
			${styleUtils.resetButton};
			color: ${colorTokens.icon.default};
			display: flex;
			align-items: center;
			justify-content: center;
			transition: color 0.3s ease;
			

			&:last-of-type {
				transition: transform 0.3s ease;

				svg {
					width: 20px;
					height: 20px;
				}
	
				&:hover {
					color: ${colorTokens.icon.hover};
				}
	
				${
          isEdit &&
          css`
					transform: rotate(180deg);
				`
        }
			}
		}
	`,
  collapse: (isEdit: boolean) => css`
		svg {
			width: 16px;
			height: 16px;
		}

		transition: transform 0.3s ease;

		${
      isEdit &&
      css`
			transform: rotate(180deg);
		`
    }
	`,
};
