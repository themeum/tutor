import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import FormCheckbox from '@Components/fields/FormCheckbox';
import FormInput from '@Components/fields/FormInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormSelectInput from '@Components/fields/FormSelectInput';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { Subscription } from '@CourseBuilderServices/subscription';
import { AnimatedDiv, AnimationType, useAnimation } from '@Hooks/useAnimation';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller } from 'react-hook-form';
import { OfferSalePrice } from './OfferSalePrice';
import { formatRepeatUnit } from './PreviewItem';

export default function SubscriptionItem({
  subscription,
  toggleCollapse,
}: { subscription: Subscription & { isExpanded: boolean }; toggleCollapse: (id: number) => void }) {
  const form = useFormWithGlobalError<Subscription>({
    defaultValues: subscription,
  });

  const { transitions } = useAnimation({
    data: subscription.isExpanded,
    animationType: AnimationType.slideDown,
  });

  const subscriptionName = form.watch('title');
  const pricingType = form.watch('pricing_option');
  const repeatUnit = form.watch('repeat_unit', 'month');
  const chargeEnrolmentFee = form.watch('charge_enrolment_fee');
  const enableTrial = form.watch('enable_trial');
  const { isDirty } = form.formState;

  const lifetimePresets = [3, 6, 9, 12];
  const lifetimeOptions = [
    ...lifetimePresets.map((preset) => ({
      label: `${preset.toString()} ${formatRepeatUnit(repeatUnit, preset)}`,
      value: preset,
    })),
    {
      label: __('Until cancelled', 'tutor'),
      value: 'until_cancellation',
    },
  ];

  return (
    <form
      css={styles.subscription}
      onSubmit={form.handleSubmit((values) => {
        alert('@TODO: will be implemented later.');
      })}
    >
      <div css={styles.subscriptionHeader(subscription.isExpanded)}>
        <div css={styles.grabber}>
          <SVGIcon name="threeDotsVerticalDouble" />
          <span title={subscriptionName}>{subscriptionName}</span>
        </div>
        <div css={styles.actions(subscription.isExpanded)}>
          <button type="button" title={__('Delete subscription', 'tutor')}>
            <SVGIcon name="delete" width={24} height={24} />
          </button>
          <button type="button" title={__('Duplicate subscription', 'tutor')}>
            <SVGIcon name="copyPaste" width={24} height={24} />
          </button>
          <Show when={!subscription.isExpanded}>
            <button
              type="button"
              onClick={() => toggleCollapse(subscription.id)}
              title={__('Edit subscription title', 'tutor')}
            >
              <SVGIcon name="edit" width={24} height={24} />
            </button>
          </Show>
          <button
            type="button"
            onClick={() => toggleCollapse(subscription.id)}
            title={__('Collapse/expand subscription', 'tutor')}
          >
            <SVGIcon name="chevronDown" width={24} height={24} />
          </button>
        </div>
      </div>
      {transitions((style, openState) => {
        if (openState) {
          return (
            <AnimatedDiv style={style} css={styles.itemWrapper(subscription.isExpanded)}>
              <div css={styles.subscriptionContent}>
                <Controller
                  control={form.control}
                  name="title"
                  render={(props) => (
                    <FormInput {...props} placeholder="Enter subscription name" label="Subscription name" />
                  )}
                />
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
                <Show
                  when={pricingType === 'recurring'}
                  fallback={
                    <Controller
                      control={form.control}
                      name="price"
                      render={(props) => (
                        <FormInputWithContent
                          {...props}
                          label={__('Price', 'tutor')}
                          content={'$'}
                          placeholder={__('Subscription price', 'tutor')}
                          selectOnFocus
                        />
                      )}
                    />
                  }
                >
                  <div css={styles.inputGroup}>
                    <Controller
                      control={form.control}
                      name="price"
                      render={(props) => (
                        <FormInputWithContent
                          {...props}
                          label={__('Price', 'tutor')}
                          content={'$'}
                          placeholder={__('Subscription price', 'tutor')}
                          selectOnFocus
                        />
                      )}
                    />
                    <Controller
                      control={form.control}
                      name="repeat_every"
                      render={(props) => (
                        <FormInput
                          {...props}
                          label={__('Repeat every', 'tutor')}
                          placeholder={__('Repeat every', 'tutor')}
                          selectOnFocus
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
                </Show>

                <Controller
                  control={form.control}
                  name="lifetime"
                  render={(props) => (
                    <FormSelectInput
                      {...props}
                      label={__('Length of the plan', 'tutor')}
                      placeholder={__('Select the length of the plan', 'tutor')}
                      options={lifetimeOptions}
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
                        selectOnFocus
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
                  <div css={styles.trialWrapper}>
                    <Controller
                      control={form.control}
                      name="trial"
                      render={(props) => (
                        <FormInput
                          {...props}
                          label={__('Length of free trial', 'tutor')}
                          placeholder={__('Enter trial duration', 'tutor')}
                          selectOnFocus
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

                <Controller
                  control={form.control}
                  name="do_not_provide_certificate"
                  render={(props) => <FormCheckbox {...props} label={__('Do not provide certificate', 'tutor')} />}
                />

                <OfferSalePrice form={form} />
              </div>
              <div css={styles.subscriptionFooter}>
                <Button
                  variant="text"
                  size="small"
                  onClick={() => {
                    form.reset();
                  }}
                  disabled={!isDirty}
                >
                  {__('Discard', 'tutor')}
                </Button>
                <Button variant="secondary" size="small" type="submit">
                  {__('Save', 'tutor')}
                </Button>
              </div>
            </AnimatedDiv>
          );
        }
      })}
    </form>
  );
}

const styles = {
  grabber: css`
		display: flex;
		align-items: center;
		gap: ${spacing[4]};
		${typography.body()};
		color: ${colorTokens.text.hints};
		width: 100%;
		min-height: 40px;

		svg {
			color: ${colorTokens.icon.default};
			cursor: grab;
			flex-shrink: 0;
		}

		span {
			max-width: 496px;
			width: 100%;
			${styleUtils.textEllipsis};
		}
	`,
  trialWrapper: css`
		display: grid;
		grid-template-columns: 1fr 1fr;
		align-items: center;
		gap: ${spacing[8]};
		
	`,
  titleField: css`
		width: 100%;
		position: relative;

		input {
			padding-right: ${spacing[128]} !important;
		}
	`,
  titleActions: css`
		position: absolute;
		right: ${spacing[4]};
		top: 50%;
		transform: translateY(-50%);
		display: flex;
		align-items: center;
		gap: ${spacing[8]};
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
		transition: transform 0.3s ease;
		svg {
			width: 16px;
			height: 16px;
		}
		${
      isEdit &&
      css`
			transform: rotate(180deg);
		`
    }
	`,
  inputGroup: css`
		display: grid;
		grid-template-columns: 2fr 1fr 1fr;
		align-items: center;
		gap: ${spacing[8]};
	`,
};
