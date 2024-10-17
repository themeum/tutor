import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { animated, useSpring } from '@react-spring/web';
import { __, sprintf } from '@wordpress/i18n';
import { useCallback, useEffect, useRef, useState } from 'react';
import { Controller, useFormContext } from 'react-hook-form';

import SVGIcon from '@Atoms/SVGIcon';
import Tooltip from '@Atoms/Tooltip';
import ConfirmationPopover from '@Molecules/ConfirmationPopover';

import FormCheckbox from '@Components/fields/FormCheckbox';
import FormInput from '@Components/fields/FormInput';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormInputWithPresets from '@Components/fields/FormInputWithPresets';
import FormSelectInput from '@Components/fields/FormSelectInput';

import { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import {
  useDeleteCourseSubscriptionMutation,
  useDuplicateCourseSubscriptionMutation,
} from '@CourseBuilderServices/subscription';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { AnimationType } from '@Hooks/useAnimation';

import { animateLayoutChanges } from '@Utils/dndkit';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';
import { noop } from '@Utils/util';
import { requiredRule } from '@Utils/validation';

import LoadingSpinner from '@Atoms/LoadingSpinner';
import type { SubscriptionFormDataWithSaved } from '@CourseBuilderComponents/modals/SubscriptionModal';
import type { ID } from '@CourseBuilderServices/curriculum';
import { OfferSalePrice } from './OfferSalePrice';

interface SubscriptionItemProps {
  id: ID;
  toggleCollapse: (id: string) => void;
  bgLight?: boolean;
  onDiscard: () => void;
  isExpanded: boolean;
  isOverlay?: boolean;
}

const SET_FOCUS_AFTER = 100; // this is hack to fix layout shifting while animating.

const courseId = getCourseId();
const { tutor_currency } = tutorConfig;

export default function SubscriptionItem({
  id,
  toggleCollapse,
  bgLight = false,
  onDiscard,
  isExpanded,
  isOverlay = false,
}: SubscriptionItemProps) {
  const wrapperRef = useRef<HTMLFormElement>(null);
  const subscriptionRef = useRef<HTMLDivElement>(null);
  const deleteButtonRef = useRef<HTMLButtonElement>(null);
  const form = useFormContext<{
    subscriptions: SubscriptionFormDataWithSaved[];
  }>();
  const subscriptionItems = form.watch('subscriptions');
  const index = subscriptionItems.findIndex((item) => item.id === id);
  const subscription = form.watch(`subscriptions.${index}`);
  const isFormDirty = form.formState.isDirty;
  const errorFieldsLength = form.formState.errors.subscriptions
    ? Object.keys(form.formState.errors.subscriptions[index] || {}).length
    : 0;

  const [isDeletePopoverOpen, setIsDeletePopoverOpen] = useState(false);
  const [isActive, setIsActive] = useState(false);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (isExpanded) {
      const timeoutId = setTimeout(() => {
        form.setFocus(`subscriptions.${index}.plan_name` as `subscriptions.0.plan_name`);
      }, SET_FOCUS_AFTER);

      if (index > 0) {
        wrapperRef.current?.scrollIntoView({
          behavior: 'smooth',
          block: 'start',
        });
      }
      return () => {
        clearTimeout(timeoutId);
      };
    }
  }, [isExpanded]);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    const handleOutsideClick = (event: MouseEvent) => {
      if (isDefined(wrapperRef.current) && !wrapperRef.current.contains(event.target as HTMLFormElement)) {
        setIsActive(false);
      }
    };

    document.addEventListener('click', handleOutsideClick);

    return () => document.removeEventListener('click', handleOutsideClick);
  }, [isActive]);

  const deleteSubscriptionMutation = useDeleteCourseSubscriptionMutation(courseId);
  const duplicateSubscriptionMutation = useDuplicateCourseSubscriptionMutation(courseId);

  const handleDeleteSubscription = async () => {
    try {
      const response = await deleteSubscriptionMutation.mutateAsync(Number(subscription.id));

      if (response.data) {
        setIsDeletePopoverOpen(false);

        isExpanded && toggleCollapse(subscription.id);
      }
    } catch (error) {
      // handle error
    }
  };

  const handleDuplicateSubscription = async () => {
    const response = await duplicateSubscriptionMutation.mutateAsync(Number(subscription.id));

    if (response.data) {
      toggleCollapse(String(response.data));
    }
  };

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: subscription.id || '',
    animateLayoutChanges,
  });

  const combinedRef = useCallback(
    (node: HTMLFormElement) => {
      if (node) {
        setNodeRef(node);
        // biome-ignore lint/suspicious/noExplicitAny: <explanation>
        (wrapperRef as any).current = node;
      }
    },
    [setNodeRef],
  );

  const subscriptionName = form.watch(`subscriptions.${index}.plan_name` as `subscriptions.0.plan_name`);
  const chargeEnrolmentFee = form.watch(
    `subscriptions.${index}.charge_enrollment_fee` as `subscriptions.0.charge_enrollment_fee`,
  );
  // @TODO: Will be added after confirmation
  // const enableTrial = form.watch(`subscriptions.${index}.enable_free_trial` as `subscriptions.0.enable_free_trial`);
  const isFeatured = form.watch(`subscriptions.${index}.is_featured` as `subscriptions.0.is_featured`);
  const hasSale = form.watch(`subscriptions.${index}.offer_sale_price` as `subscriptions.0.offer_sale_price`);
  const hasScheduledSale = !!form.watch(
    `subscriptions.${index}.schedule_sale_price` as `subscriptions.0.schedule_sale_price`,
  );

  const [collapseAnimation, collapseAnimate] = useSpring(
    {
      height: isExpanded ? subscriptionRef.current?.scrollHeight : 0,
      opacity: isExpanded ? 1 : 0,
      overflow: 'hidden',
      config: {
        duration: 300,
        easing: (t) => t * (2 - t),
      },
    },
    [
      chargeEnrolmentFee,
      // enableTrial,
      isFeatured,
      hasSale,
      hasScheduledSale,
      isFormDirty,
      isExpanded,
      errorFieldsLength,
    ],
  );

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (isDefined(subscriptionRef.current)) {
      collapseAnimate.start({
        height: isExpanded ? subscriptionRef.current?.scrollHeight : 0,
        opacity: isExpanded ? 1 : 0,
      });
    }
  }, [
    chargeEnrolmentFee,
    // enableTrial,
    isFeatured,
    hasSale,
    hasScheduledSale,
    isFormDirty,
    isExpanded,
    errorFieldsLength,
  ]);

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

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.3 : undefined,
    background: isDragging ? colorTokens.stroke.hover : undefined,
  };

  return (
    <form
      {...attributes}
      css={styles.subscription({
        bgLight,
        isActive: isActive,
        isDragging: isOverlay,
        isDeletePopoverOpen,
      })}
      onClick={() => setIsActive(true)}
      style={style}
      ref={combinedRef}
    >
      <div css={styles.subscriptionHeader(isExpanded)}>
        <div css={styles.grabber({ isFormDirty })} {...(isFormDirty ? {} : listeners)}>
          <SVGIcon data-grabber name="threeDotsVerticalDouble" width={24} height={24} />
          <button
            type="button"
            css={styles.title}
            disabled={isFormDirty}
            title={subscriptionName}
            onClick={() => !isFormDirty && toggleCollapse(subscription.id)}
          >
            {subscriptionName}

            <Show when={subscription.is_featured}>
              <Tooltip content={__('Featured', 'tutor')} delay={200}>
                <SVGIcon name="star" width={24} height={24} />
              </Tooltip>
            </Show>
          </button>
        </div>

        <div css={styles.actions(isExpanded)} data-visually-hidden>
          <Show when={!isExpanded}>
            <Tooltip content={__('Edit', 'tutor')} delay={200}>
              <button
                type="button"
                disabled={isFormDirty}
                onClick={() => !isFormDirty && toggleCollapse(subscription.id)}
              >
                <SVGIcon name="edit" width={24} height={24} />
              </button>
            </Tooltip>
          </Show>
          <Show when={subscription.isSaved}>
            <Tooltip content={__('Duplicate', 'tutor')} delay={200}>
              <button type="button" disabled={isFormDirty} onClick={handleDuplicateSubscription}>
                <Show when={!duplicateSubscriptionMutation.isPending} fallback={<LoadingSpinner size={24} />}>
                  <SVGIcon name="copyPaste" width={24} height={24} />
                </Show>
              </button>
            </Tooltip>
            <Tooltip content={__('Delete', 'tutor')} delay={200}>
              <button
                ref={deleteButtonRef}
                type="button"
                disabled={isFormDirty}
                onClick={() => setIsDeletePopoverOpen(true)}
              >
                <SVGIcon name="delete" width={24} height={24} />
              </button>
            </Tooltip>
            <button
              type="button"
              disabled={isFormDirty}
              onClick={() => !isFormDirty && toggleCollapse(subscription.id)}
              data-collapse-button
              title={__('Collapse/expand plan', 'tutor')}
            >
              <SVGIcon name="chevronDown" width={24} height={24} />
            </button>
          </Show>
        </div>
      </div>
      <animated.div
        style={{
          ...collapseAnimation,
        }}
        css={styles.itemWrapper(isExpanded)}
      >
        <div ref={subscriptionRef} css={styleUtils.display.flex('column')}>
          <div css={styles.subscriptionContent}>
            <Controller
              control={form.control}
              name={`subscriptions.${index}.plan_name`}
              rules={requiredRule()}
              render={(controllerProps) => (
                <FormInput
                  {...controllerProps}
                  placeholder={__('Enter plan name', 'tutor')}
                  label={__('Plan name', 'tutor')}
                />
              )}
            />

            {/* <Controller
              control={form.control}
              name={`subscriptions.${index}.payment_type`}
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
            /> */}

            <div css={styles.inputGroup}>
              <Controller
                control={form.control}
                name={`subscriptions.${index}.regular_price`}
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
                name={`subscriptions.${index}.recurring_value`}
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
                    label={__('Repeat every', 'tutor')}
                    placeholder={__('Repeat every', 'tutor')}
                    selectOnFocus
                    type="number"
                  />
                )}
              />

              <Controller
                control={form.control}
                name={`subscriptions.${index}.recurring_interval`}
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
                name={`subscriptions.${index}.recurring_limit`}
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
                    label={__('Renew Plan', 'tutor')}
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
              name={`subscriptions.${index}.charge_enrollment_fee`}
              render={(controllerProps) => (
                <FormCheckbox {...controllerProps} label={__('Charge enrolment fee', 'tutor')} />
              )}
            />

            <Show when={chargeEnrolmentFee}>
              <Controller
                control={form.control}
                name={`subscriptions.${index}.enrollment_fee`}
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
                    placeholder={__('Enter enrolment fee', 'tutor')}
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
              name={`subscriptions.${index}.do_not_provide_certificate`}
              render={(controllerProps) => (
                <FormCheckbox {...controllerProps} label={__('Do not provide certificate', 'tutor')} />
              )}
            />

            <Controller
              control={form.control}
              name={`subscriptions.${index}.is_featured`}
              render={(controllerProps) => (
                <FormCheckbox {...controllerProps} label={__('Mark as featured', 'tutor')} />
              )}
            />

            <Show when={isFeatured}>
              <Controller
                control={form.control}
                rules={requiredRule()}
                name={`subscriptions.${index}.featured_text`}
                render={(controllerProps) => (
                  <FormInput
                    {...controllerProps}
                    label={__('Feature text', 'tutor')}
                    placeholder={__('Enter feature text', 'tutor')}
                  />
                )}
              />
            </Show>

            <OfferSalePrice index={index} />
          </div>
        </div>
      </animated.div>
      <ConfirmationPopover
        isOpen={isDeletePopoverOpen}
        triggerRef={deleteButtonRef}
        closePopover={noop}
        maxWidth="258px"
        title={sprintf(__('Delete "%s"', 'tutor'), subscription.plan_name)}
        message={__('Are you sure you want to delete this plan? This cannot be undone.', 'tutor')}
        animationType={AnimationType.slideUp}
        arrow="auto"
        hideArrow
        isLoading={deleteSubscriptionMutation.isPending}
        confirmButton={{
          text: __('Delete', 'tutor'),
          variant: 'text',
          isDelete: true,
        }}
        cancelButton={{
          text: __('Cancel', 'tutor'),
          variant: 'text',
        }}
        onConfirmation={handleDeleteSubscription}
        onCancel={() => setIsDeletePopoverOpen(false)}
      />
    </form>
  );
}

const styles = {
  grabber: ({
    isFormDirty,
  }: {
    isFormDirty: boolean;
  }) => css`
		display: flex;
		align-items: center;
		gap: ${spacing[4]};
		${typography.body()};
		color: ${colorTokens.text.hints};
		width: 100%;
		min-height: 40px;

		[data-grabber] {
			color: ${colorTokens.icon.default};
			cursor: ${isFormDirty ? 'not-allowed' : 'grab'};
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
    ${styleUtils.resetButton};
    display: flex;
    align-items: center;
    color: ${colorTokens.text.hints};
    flex-grow: 1;
    gap: ${spacing[8]};

    :disabled {
      cursor: default;
    }

    svg {
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
  subscription: ({
    bgLight,
    isActive,
    isDragging,
    isDeletePopoverOpen,
  }: {
    bgLight?: boolean;
    isActive: boolean;
    isDragging: boolean;
    isDeletePopoverOpen?: boolean;
  }) => css`
		width: 100%;
		border: 1px solid ${colorTokens.stroke.default};
		border-radius: ${borderRadius.card};
		overflow: hidden;
    transition: border-color 0.3s ease;

    [data-visually-hidden] {
      opacity: ${isDeletePopoverOpen ? 1 : 0};
      transition: opacity 0.3s ease;
    }

		${
      bgLight &&
      css`
        background-color: ${colorTokens.background.white};
      `
    }

    ${
      isActive &&
      css`
        border-color: ${colorTokens.stroke.brand};
      `
    }

    ${
      isDragging &&
      css`
        box-shadow: ${shadow.drag};

        [data-grabber] {
          cursor: grabbing;
        }
      `
    }

    &:hover:not(:disabled) {
      [data-visually-hidden] {
        opacity: 1;
      }
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
        border-bottom: 1px solid ${colorTokens.stroke.border};
      `
    }
	`,
  subscriptionContent: css`
		padding: ${spacing[16]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
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

      :disabled {
        cursor: not-allowed;
        color: ${colorTokens.icon.disable.background};
      }

			&[data-collapse-button] {
				transition: transform 0.3s ease;

				svg {
					width: 20px;
					height: 20px;
				}
	
				&:hover:not(:disabled) {
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
