import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller, useFormContext } from 'react-hook-form';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import FormInput from '@Components/fields/FormInput';
import FormSelectInput from '@Components/fields/FormSelectInput';
import FormSwitch from '@Components/fields/FormSwitch';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { useModal } from '@Components/modals/Modal';
import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@Config/styles';
import For from '@Controls/For';
import { styleUtils } from '@Utils/style-utils';
import { animateLayoutChanges } from '@Utils/dndkit';

import OptionWebhookUrl from '../fields/OptionWebhookUrl';
import Card from '../molecules/Card';
import type { PaymentMethod, PaymentSettings } from '../services/payment';
import StaticConfirmationModal from './modals/StaticConfirmationModal';

interface PaymentItemProps {
  data: PaymentMethod;
  paymentIndex: number;
  isOverlay?: boolean;
}

const PaymentItem = ({ data, paymentIndex, isOverlay = false }: PaymentItemProps) => {
  const { showModal } = useModal();
  const form = useFormContext<PaymentSettings>();

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
        title={data.is_manual ? form.getValues(`payment_methods.${paymentIndex}.fields.0.value`) : data.label}
        titleIcon={data.icon}
        subscription={data.support_recurring}
        actionTray={
          <Controller
            name={`payment_methods.${paymentIndex}.is_active`}
            control={form.control}
            render={(controllerProps) => <FormSwitch {...controllerProps} />}
          />
        }
        style={style}
        hasBorder
        noSeparator
        collapsed
        dataAttribute="data-card"
      >
        <div css={styles.paymentWrapper}>
          <div css={styles.fieldWrapper}>
            <For each={data.fields}>
              {(field, index) => (
                <Controller
                  name={`payment_methods.${paymentIndex}.fields.${index}.value`}
                  control={form.control}
                  render={(controllerProps) => {
                    switch (field.type) {
                      case 'select':
                        return (
                          <FormSelectInput
                            {...controllerProps}
                            label={field.label}
                            options={field.options ?? []}
                            isInlineLabel
                          />
                        );

                      case 'key':
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
                        return <FormTextareaInput {...controllerProps} label={field.label} rows={4} />;

                      case 'webhook_url':
                        return <OptionWebhookUrl {...controllerProps} label={field.label} />;

                      default:
                        return <FormInput {...controllerProps} label={field.label} isInlineLabel />;
                    }
                  }}
                />
              )}
            </For>
          </div>
          <Button
            variant="danger"
            buttonCss={styles.removeButton}
            onClick={() => {
              showModal({
                component: StaticConfirmationModal,
                props: {
                  title: __('Payment gateways', 'tutor'),
                },
                depthIndex: zIndex.highest,
              });
            }}
          >
            {__('Remove', 'tutor')}
          </Button>
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

    ${isOverlay &&
    css`
      [data-card] {
        box-shadow: ${shadow.drag} !important;
      }
    `}
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
};
