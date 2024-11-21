import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect, useState } from 'react';
import { Controller, useFormContext } from 'react-hook-form';

import Alert from '@Atoms/Alert';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import FormImageInput from '@Components/fields/FormImageInput';
import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormSwitch from '@Components/fields/FormSwitch';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { useModal } from '@Components/modals/Modal';
import StaticConfirmationModal from '@Components/modals/StaticConfirmationModal';

import { borderRadius, colorTokens, fontWeight, lineHeight, shadow, spacing, zIndex } from '@Config/styles';
import For from '@Controls/For';
import Show from '@Controls/Show';
import { animateLayoutChanges } from '@Utils/dndkit';
import { styleUtils } from '@Utils/style-utils';
import { isObject } from '@Utils/types';
import { requiredRule } from '@Utils/validation';

import Badge from '../atoms/Badge';
import { usePaymentContext } from '../contexts/payment-context';
import OptionWebhookUrl from '../fields/OptionWebhookUrl';
import Card from '../molecules/Card';
import {
  type PaymentMethod,
  type PaymentSettings,
  getWebhookUrl,
  manualMethodFields,
  useInstallPaymentMutation,
  useRemovePaymentMutation,
} from '../services/payment';

interface PaymentItemProps {
  data: PaymentMethod;
  paymentIndex: number;
  isOverlay?: boolean;
}

const PaymentItem = ({ data, paymentIndex, isOverlay = false }: PaymentItemProps) => {
  const { payment_gateways } = usePaymentContext();
  const { showModal } = useModal();
  const form = useFormContext<PaymentSettings>();
  const [isCollapsed, setIsCollapsed] = useState<boolean>(true);

  const paymentFormFields = data.is_manual
    ? manualMethodFields
    : payment_gateways.find((item) => item.name === data.name)?.fields ?? [];

  const installPaymentMutation = useInstallPaymentMutation();
  const removePaymentMutation = useRemovePaymentMutation();

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: data.name,
    animateLayoutChanges,
  });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.3 : undefined,
    background: isDragging ? colorTokens.stroke.hover : undefined,
  };

  const convertToOptions = (options: Record<string, string>) => {
    return Object.keys(options).map((item) => {
      return {
        label: options[item],
        value: item,
      };
    });
  };

  const hasEmptyFields = form
    .getValues(`payment_methods.${paymentIndex}.fields`)
    .some((field) => !['icon', 'webhook_url'].includes(field.name) && !field.value);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (hasEmptyFields) {
      form.setValue(`payment_methods.${paymentIndex}.is_active`, false, { shouldDirty: true });
    }
  }, [hasEmptyFields]);

  return (
    <div
      {...attributes}
      css={styles.wrapper({
        isOverlay,
      })}
      ref={setNodeRef}
    >
      <button
        {...listeners}
        type="button"
        css={styles.dragButton({
          isOverlay,
        })}
        data-drag-button
      >
        <SVGIcon width={24} height={24} name="dragVertical" />
      </button>

      <Card
        title={data.label}
        titleIcon={data.icon}
        toggleCollapse={() => {
          setIsCollapsed(!isCollapsed);
        }}
        style={style}
        hasBorder
        noSeparator
        collapsed={isDragging || isCollapsed}
        dataAttribute="data-card"
        subscription={data.support_subscription}
        actionTray={
          <div css={styles.cardActions}>
            <Show when={data.update_available}>
              <Badge variant="warning" icon={<SVGIcon name="warning" width={24} height={24} />}>
                {__('Update available', 'tutor')}
              </Badge>
              <Button
                variant="text"
                size="small"
                icon={<SVGIcon name="update" width={24} height={24} />}
                onClick={async () => {
                  const response = await installPaymentMutation.mutateAsync({
                    slug: data.name,
                    action_type: 'upgrade',
                  });

                  if (response.status_code === 200) {
                    form.setValue(`payment_methods.${paymentIndex}.update_available`, false, { shouldDirty: true });
                  }
                }}
                loading={installPaymentMutation.isPending}
              >
                {__('Update now', 'tutor')}
              </Button>
            </Show>
            <Controller
              name={`payment_methods.${paymentIndex}.is_active`}
              control={form.control}
              render={(controllerProps) => (
                <FormSwitch
                  {...controllerProps}
                  onChange={async (value) => {
                    const isValid = await form.trigger(`payment_methods.${paymentIndex}.fields`);

                    if (value && !isValid) {
                      form.setValue(`payment_methods.${paymentIndex}.is_active`, false, { shouldDirty: true });
                      setIsCollapsed(false);
                      return;
                    }
                  }}
                />
              )}
            />
          </div>
        }
      >
        <div css={styles.paymentWrapper}>
          <div css={styles.fieldWrapper}>
            <Show
              when={paymentFormFields.length}
              fallback={<Alert>{__('Necessary plugin is not installed to display options!', 'tutor')}</Alert>}
            >
              <For each={paymentFormFields}>
                {(field, index) => (
                  <Controller
                    key={field.name}
                    name={`payment_methods.${paymentIndex}.fields.${index}.value`}
                    control={form.control}
                    rules={
                      ['icon', 'webhook_url'].includes(field.name || '')
                        ? {
                            required: false,
                          }
                        : { ...requiredRule() }
                    }
                    render={(controllerProps) => {
                      switch (field.type) {
                        case 'select':
                          return (
                            <FormSelectInput
                              {...controllerProps}
                              label={field.label}
                              options={
                                isObject(field.options)
                                  ? convertToOptions(field.options as Record<string, string>)
                                  : field.options ?? []
                              }
                              isInlineLabel
                            />
                          );

                        case 'secret_key':
                          return (
                            <FormInput
                              {...controllerProps}
                              type="password"
                              isPassword
                              label={field.label}
                              isInlineLabel
                            />
                          );

                        case 'textarea':
                          return (
                            <FormTextareaInput
                              {...controllerProps}
                              label={field.label}
                              rows={6}
                              helpText={field.hint}
                            />
                          );

                        case 'webhook_url':
                          return (
                            <OptionWebhookUrl
                              {...controllerProps}
                              field={{ ...controllerProps.field, value: getWebhookUrl(data.name) }}
                              label={field.label}
                            />
                          );

                        case 'image':
                          return (
                            <FormImageInput
                              {...controllerProps}
                              label={field.label}
                              buttonText={__('Upload Image', 'tutor')}
                              infoText={__('Recommended size: 48x48', 'tutor')}
                              previewImageCss={styles.previewImage}
                              onChange={(value) => {
                                form.setValue(`payment_methods.${paymentIndex}.icon`, value?.url ?? '');
                              }}
                            />
                          );

                        default:
                          return (
                            <FormInput
                              {...controllerProps}
                              label={field.label}
                              isInlineLabel
                              onChange={(value) => {
                                if (data.is_manual) {
                                  form.setValue(`payment_methods.${paymentIndex}.label`, String(value));
                                }
                              }}
                            />
                          );
                      }
                    }}
                  />
                )}
              </For>
            </Show>
          </div>
          <Show when={data.name !== 'paypal'}>
            <Button
              variant="danger"
              buttonCss={styles.removeButton}
              loading={removePaymentMutation.isPending}
              onClick={async () => {
                const { action } = await showModal({
                  component: StaticConfirmationModal,
                  props: {
                    title: sprintf(__('Remove %s', 'tutor'), data.label),
                    description: __('Are you sure you want to remove this payment method?', 'tutor'),
                  },
                  depthIndex: zIndex.highest,
                });

                if (action === 'CONFIRM') {
                  if (data.is_manual) {
                    form.setValue(
                      'payment_methods',
                      form.getValues('payment_methods').filter((_, index) => index !== paymentIndex),
                      {
                        shouldDirty: true,
                      },
                    );
                  } else {
                    const response = await removePaymentMutation.mutateAsync({
                      slug: data.name,
                    });

                    if (response.status_code === 200) {
                      form.setValue(
                        'payment_methods',
                        form.getValues('payment_methods').filter((_, index) => index !== paymentIndex),
                      );

                      // Save settings
                      setTimeout(() => {
                        document.getElementById('save_tutor_option')?.removeAttribute('disabled');
                        document.getElementById('save_tutor_option')?.click();
                      }, 100);
                    }
                  }
                }
              }}
            >
              {__('Remove', 'tutor')}
            </Button>
          </Show>
        </div>
      </Card>
    </div>
  );
};

export default PaymentItem;

const styles = {
  wrapper: ({ isOverlay }: { isOverlay: boolean }) => css`
    position: relative;

    &:hover {
      [data-drag-button] {
        opacity: 1;
      }
    }

    ${
      isOverlay &&
      css`
      [data-card] {
        box-shadow: ${shadow.drag} !important;
      }
    `
    }
  `,
  cardActions: css`
    display: flex;
    align-items: center;

    & > div {
      width: auto;
    }

    button {
      margin-right: ${spacing[24]};
      line-height: ${lineHeight[16]};
      color: ${colorTokens.brand.blue};
      font-weight: ${fontWeight.medium};

      svg {
        color: ${colorTokens.icon.brand};
      }
    }

    &:hover button {
      color: ${colorTokens.brand.blue};
    }
  `,
  paymentWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
    padding: 0 ${spacing[24]} ${spacing[16]};
  `,
  removeButton: css`
    width: fit-content;
  `,
  fieldWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
    padding: ${spacing[16]};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[6]};

    input[type='text'],
    input[type='password'] {
      min-width: 350px;
    }
  `,
  dragButton: ({ isOverlay }: { isOverlay: boolean }) => css`
    ${styleUtils.resetButton};
    position: absolute;
    top: ${spacing[24]};
    left: -${spacing[28]};
    cursor: ${isOverlay ? 'grabbing' : 'grab'};
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
    color: ${colorTokens.icon.default};
  `,
  previewImage: css`
    img {
      object-fit: contain;
    }
  `,
};
