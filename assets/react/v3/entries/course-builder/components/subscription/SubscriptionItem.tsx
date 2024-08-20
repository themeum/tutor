import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import FormCheckbox from '@Components/fields/FormCheckbox';
import FormInput from '@Components/fields/FormInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormSelectInput from '@Components/fields/FormSelectInput';

import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import {
  type SubscriptionFormData,
  convertFormDataToSubscription,
  useDeleteCourseSubscriptionMutation,
  useDuplicateCourseSubscriptionMutation,
  useSaveCourseSubscriptionMutation,
} from '@CourseBuilderServices/subscription';
import { getCourseId } from '@CourseBuilderUtils/utils';

import { AnimatedDiv, AnimationType, useAnimation } from '@Hooks/useAnimation';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';

import { animateLayoutChanges } from '@Utils/dndkit';
import { styleUtils } from '@Utils/style-utils';

import FormInputWithPresets from '@Components/fields/FormInputWithPresets';
import { tutorConfig } from '@Config/config';
import { requiredRule } from '@Utils/validation';
import { OfferSalePrice } from './OfferSalePrice';

const courseId = getCourseId();
const { tutor_currency } = tutorConfig;

export default function SubscriptionItem({
  subscription,
  toggleCollapse,
  bgLight = false,
  onDiscard,
}: {
  subscription: SubscriptionFormData & { isExpanded: boolean };
  toggleCollapse: (id: string) => void;
  bgLight?: boolean;
  onDiscard: () => void;
}) {
  const form = useFormWithGlobalError<SubscriptionFormData>({
    defaultValues: subscription,
    shouldFocusError: true,
  });

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (subscription.isExpanded) {
      form.setFocus('plan_name');
    }
  }, [subscription.isExpanded]);

  const saveSubscriptionMutation = useSaveCourseSubscriptionMutation(courseId);
  const deleteSubscriptionMutation = useDeleteCourseSubscriptionMutation(courseId);
  const duplicateSubscriptionMutation = useDuplicateCourseSubscriptionMutation(courseId);

  const handleSaveSubscription = async (values: SubscriptionFormData) => {
    try {
      const payload = convertFormDataToSubscription({
        ...values,
        assign_id: String(courseId),
      });
      const response = await saveSubscriptionMutation.mutateAsync(payload);

      if (response.status_code === 200 || response.status_code === 201) {
        toggleCollapse(subscription.id);
      }
    } catch (error) {
      // handle error
    }
  };

  const handleDeleteSubscription = async () => {
    try {
      const response = await deleteSubscriptionMutation.mutateAsync(Number(subscription.id));

      if (response.data && subscription.isExpanded) {
        toggleCollapse(subscription.id);
      }
    } catch (error) {
      // handle error
    }
  };

  const handleDuplicateSubscription = () => {
    duplicateSubscriptionMutation.mutate(Number(subscription.id));
  };

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: subscription.id,
    animateLayoutChanges,
  });

  const { transitions } = useAnimation({
    data: subscription.isExpanded,
    animationType: AnimationType.slideDown,
  });

  const subscriptionName = form.watch('plan_name');
  const paymentType = form.watch('payment_type');
  const recurringInterval = form.watch('recurring_interval', 'month');
  const chargeEnrolmentFee = form.watch('charge_enrollment_fee');
  const enableTrial = form.watch('enable_free_trial');
  const isFeatured = form.watch('is_featured');

  const lifetimePresets = [3, 6, 9, 12];
  const lifetimeOptions = [
    ...lifetimePresets.map((preset) => ({
      label: `${preset.toString()} ${__('Times', 'tutor')}`,
      value: String(preset),
    })),
    {
      label: __('Until cancelled', 'tutor'),
      value: 'Until cancelled',
    },
  ];

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.3 : undefined,
  };

  return (
    <form
      {...attributes}
      css={styles.subscription(bgLight)}
      onSubmit={form.handleSubmit((values) => {
        handleSaveSubscription(values);
      })}
      style={style}
      ref={setNodeRef}
    >
      <div css={styles.subscriptionHeader(subscription.isExpanded)}>
        <div css={styles.grabber} {...listeners}>
          <SVGIcon name="threeDotsVerticalDouble" />
          <span css={styles.title} title={subscriptionName}>
            {subscriptionName}

            <Show when={subscription.is_featured}>
              <SVGIcon name="star" width={24} height={24} />
            </Show>
          </span>
        </div>

        <div css={styles.actions(subscription.isExpanded)}>
          <Show when={!subscription.isExpanded}>
            <button
              type="button"
              onClick={() => toggleCollapse(subscription.id)}
              title={__('Edit subscription title', 'tutor')}
            >
              <SVGIcon name="edit" width={24} height={24} />
            </button>
          </Show>
          <Show when={subscription.id}>
            <button type="button" title={__('Duplicate subscription', 'tutor')} onClick={handleDuplicateSubscription}>
              <SVGIcon name="copyPaste" width={24} height={24} />
            </button>
            <button type="button" title={__('Delete subscription', 'tutor')} onClick={handleDeleteSubscription}>
              <SVGIcon name="delete" width={24} height={24} />
            </button>
            <button
              type="button"
              onClick={() => toggleCollapse(subscription.id)}
              title={__('Collapse/expand subscription', 'tutor')}
            >
              <SVGIcon name="chevronDown" width={24} height={24} />
            </button>
          </Show>
        </div>
      </div>
      {transitions((style, openState) => {
        if (openState) {
          return (
            <AnimatedDiv style={style} css={styles.itemWrapper(subscription.isExpanded)}>
              <div css={styles.subscriptionContent}>
                <Controller
                  control={form.control}
                  name="plan_name"
                  rules={requiredRule()}
                  render={(controllerProps) => (
                    <FormInput {...controllerProps} placeholder="Enter subscription name" label="Subscription name" />
                  )}
                />

                <Controller
                  control={form.control}
                  name="payment_type"
                  render={(controllerProps) => (
                    <FormSelectInput
                      {...controllerProps}
                      label={__('Pricing option', 'tutor')}
                      options={[
                        { label: __('Recurring payments', 'tutor'), value: 'recurring' },
                        { label: __('One time payment', 'tutor'), value: 'onetime' },
                      ]}
                    />
                  )}
                />
                <Show
                  when={paymentType === 'recurring'}
                  fallback={
                    <Controller
                      control={form.control}
                      name="regular_price"
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
                          placeholder={__('Subscription price', 'tutor')}
                          selectOnFocus
                          contentCss={styleUtils.inputCurrencyStyle}
                        />
                      )}
                    />
                  }
                >
                  <div css={styles.inputGroup}>
                    <Controller
                      control={form.control}
                      name="regular_price"
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
                          placeholder={__('Subscription price', 'tutor')}
                          selectOnFocus
                          contentCss={styleUtils.inputCurrencyStyle}
                        />
                      )}
                    />
                    <Controller
                      control={form.control}
                      name="recurring_value"
                      rules={{
                        ...requiredRule(),
                        validate: (value) => {
                          if (Number(value) < 1) {
                            return __('This value must be equal to or greater than 1');
                          }
                        },
                      }}
                      render={(controllerProps) => (
                        <FormInput
                          {...controllerProps}
                          label={__('Repeat every', 'tutor')}
                          placeholder={__('Repeat every', 'tutor')}
                          selectOnFocus
                        />
                      )}
                    />

                    <Controller
                      control={form.control}
                      name="recurring_interval"
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
                        />
                      )}
                    />

                    <Controller
                      control={form.control}
                      name="recurring_limit"
                      rules={{
                        ...requiredRule(),
                        validate: (value) => {
                          if (value === 'Until cancelled') {
                            return true;
                          }

                          if (Number(value) <= 0) {
                            return __('Plan duration must be greater than 0', 'tutor');
                          }
                          return true;
                        },
                      }}
                      render={(controllerProps) => (
                        <FormInputWithPresets
                          {...controllerProps}
                          label={__('Length of the plan', 'tutor')}
                          placeholder={__('Select the length of the plan', 'tutor')}
                          content={controllerProps.field.value !== 'Until cancelled' && `${__('Times', 'tutor')}`}
                          contentPosition="right"
                          type="number"
                          presetOptions={lifetimeOptions}
                          selectOnFocus
                        />
                      )}
                    />
                  </div>
                </Show>

                <Controller
                  control={form.control}
                  name="charge_enrollment_fee"
                  render={(controllerProps) => (
                    <FormCheckbox {...controllerProps} label={__('Charge enrolment fee', 'tutor')} />
                  )}
                />

                <Show when={chargeEnrolmentFee}>
                  <Controller
                    control={form.control}
                    name="enrollment_fee"
                    rules={{
                      ...requiredRule(),
                      validate: (value) => {
                        if (Number(value) <= 0) {
                          return __('Enrolment fee must be greater than 0', 'tutor');
                        }
                        return true;
                      },
                    }}
                    render={(controllerProps) => (
                      <FormInputWithContent
                        {...controllerProps}
                        label={__('Enrolment fee', 'tutor')}
                        content={tutor_currency?.symbol || '$'}
                        placeholder={__('Enter enrolment fee')}
                        selectOnFocus
                        contentCss={styleUtils.inputCurrencyStyle}
                      />
                    )}
                  />
                </Show>
                <Controller
                  control={form.control}
                  name="enable_free_trial"
                  render={(controllerProps) => (
                    <FormCheckbox {...controllerProps} label={__('Enable a free trial', 'tutor')} />
                  )}
                />

                <Show when={enableTrial}>
                  <div css={styles.trialWrapper}>
                    <Controller
                      control={form.control}
                      name="trial_value"
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
                      name="trial_interval"
                      render={(controllerProps) => (
                        <FormSelectInput
                          {...controllerProps}
                          label={<div>&nbsp;</div>}
                          placeholder={__('Enter trial duration', 'tutor')}
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
                </Show>

                <Controller
                  control={form.control}
                  name="do_not_provide_certificate"
                  render={(controllerProps) => (
                    <FormCheckbox {...controllerProps} label={__('Do not provide certificate', 'tutor')} />
                  )}
                />

                <Controller
                  control={form.control}
                  name="is_featured"
                  render={(controllerProps) => (
                    <FormCheckbox {...controllerProps} label={__('Feature this subscription', 'tutor')} />
                  )}
                />

                <Show when={isFeatured}>
                  <Controller
                    control={form.control}
                    rules={requiredRule()}
                    name="featured_text"
                    render={(controllerProps) => (
                      <FormInput
                        {...controllerProps}
                        label={__('Feature text', 'tutor')}
                        placeholder={__('Enter feature text', 'tutor')}
                      />
                    )}
                  />
                </Show>

                <OfferSalePrice form={form} />
              </div>
              <div css={styles.subscriptionFooter}>
                <Button
                  variant="text"
                  size="small"
                  onClick={() => {
                    toggleCollapse(subscription.id);
                    onDiscard();
                  }}
                >
                  {subscription.id ? __('Cancel', 'tutor') : __('Discard', 'tutor')}
                </Button>
                <Button variant="secondary" size="small" type="submit" loading={saveSubscriptionMutation.isPending}>
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
		align-items: start;
		gap: ${spacing[8]};
	`,
  title: css`
    display: flex;
    align-items: center;

    svg {
      margin-left: ${spacing[8]};
      color: ${colorTokens.icon.brand};
    }
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
  subscription: (bgLight = false) => css`
		width: 100%;
		border: 1px solid ${colorTokens.stroke.default};
		border-radius: ${borderRadius.card};
		overflow: hidden;

		${
      bgLight &&
      css`
			background-color: ${colorTokens.background.white};
		`
    }
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
		grid-template-columns: 1fr 0.56fr 1fr 1fr;
		align-items: start;
		gap: ${spacing[8]};
	`,
};
