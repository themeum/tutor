import For from '@/v3/shared/controls/For';
import Card from '../molecules/Card';
import { PaymentMethod, PaymentSettings } from '../services/payment';
import FormSelectInput from '@/v3/shared/components/fields/FormSelectInput';
import { Controller, useFormContext } from 'react-hook-form';
import { __ } from '@wordpress/i18n';
import { css } from '@emotion/react';
import { borderRadius, colorTokens, spacing } from '@/v3/shared/config/styles';
import FormInput from '@/v3/shared/components/fields/FormInput';
import Button from '@/v3/shared/atoms/Button';
import FormTextareaInput from '@/v3/shared/components/fields/FormTextareaInput';
import OptionWebhookUrl from '../fields/OptionWebhookUrl';
import FormSwitch from '@/v3/shared/components/fields/FormSwitch';

const PaymentItem = ({ data, paymentIndex }: { data: PaymentMethod; paymentIndex: number }) => {
  const form = useFormContext<PaymentSettings>();

  return (
    <Card
      title={data.label}
      titleIcon={data.icon}
      subscription={data.support_recurring}
      actionTray={
        <Controller
          name={`payment_methods.${paymentIndex}.isActive`}
          control={form.control}
          render={(controllerProps) => <FormSwitch {...controllerProps} />}
        />
      }
      hasBorder
      noSeparator
      collapsed
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
                        <FormInput {...controllerProps} type="password" isPassword label={field.label} isInlineLabel />
                      );

                    case 'textarea':
                      return <FormTextareaInput {...controllerProps} label={field.label} />;

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
        <Button variant="danger" buttonCss={styles.removeButton}>
          {__('Remove', 'tutor')}
        </Button>
      </div>
    </Card>
  );
};

export default PaymentItem;

const styles = {
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
};
