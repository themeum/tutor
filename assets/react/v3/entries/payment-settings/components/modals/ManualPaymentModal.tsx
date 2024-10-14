import Button from '@Atoms/Button';
import FormInput from '@Components/fields/FormInput';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { typography } from '@Config/typography';
import { FormWithGlobalErrorType, useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { requiredRule } from '@Utils/validation';
import { colorTokens, shadow, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller } from 'react-hook-form';
import { PaymentMethod, PaymentSettings } from '../../services/payment';
import { useEffect } from 'react';

interface ManualPaymentModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  paymentForm: FormWithGlobalErrorType<PaymentSettings>;
}

const ManualPaymentModal = ({ closeModal, title, paymentForm }: ManualPaymentModalProps) => {
  const form = useFormWithGlobalError<PaymentMethod>({
    defaultValues: {
      name: '',
      label: '',
      is_active: true,
      icon: '',
      support_recurring: false,
      update_available: false,
      is_manual: true,
      fields: [
        {
          name: 'method_name',
          label: __('Payment Method Name', 'tutor'),
          type: 'text',
          value: '',
        },
        {
          name: 'additional_details',
          label: __('Additional details', 'tutor'),
          type: 'textarea',
          hint: __('Briefly describe this payment option. (e.g., Bank Transfer, Cash on Delivery).', 'tutor'),
          value: '',
        },
        {
          name: 'payment_instructions',
          label: __('Payment instructions', 'tutor'),
          type: 'textarea',
          hint: __('Provide clear, step-by-step instructions on how to complete the payment.', 'tutor'),
          value: '',
        },
      ],
    },
  });

  useEffect(() => {
    form.setFocus('fields.0.value');
  }, []);

  const onSubmit = (data: PaymentMethod) => {
    paymentForm.setValue('payment_methods', [...paymentForm.getValues('payment_methods'), data]);
    closeModal({ action: 'CONFIRM' });
  };

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title}>
      <form onSubmit={form.handleSubmit(onSubmit)} css={styles.contentWrapper}>
        <div css={styles.formBody}>
          {form.getValues().fields.map((field, index) =>
            field.name === 'method_name' ? (
              <Controller
                name={`fields.${index}.value`}
                control={form.control}
                rules={requiredRule()}
                render={(controllerProps) => (
                  <FormInput
                    {...controllerProps}
                    label={__('Custom payment method name', 'tutor')}
                    onChange={(value) => {
                      const name = String(value).toLowerCase().replace(/\s+/g, '-');
                      form.setValue('name', name);
                      form.setValue('label', String(value));
                    }}
                  />
                )}
              />
            ) : (
              <div css={styles.inputWrapper}>
                <Controller
                  name={`fields.${index}.value`}
                  control={form.control}
                  render={(controllerProps) => <FormTextareaInput {...controllerProps} label={field.label} rows={3} />}
                />
                <div css={styles.inputHint}>{field.hint}</div>
              </div>
            )
          )}
        </div>
        <div css={styles.footerWrapper}>
          <Button variant="secondary" onClick={() => closeModal({ action: 'CLOSE' })}>
            {__('Cancel', 'tutor')}
          </Button>
          <Button type="submit" variant="primary">
            {__('Activate', 'tutor')}
          </Button>
        </div>
      </form>
    </BasicModalWrapper>
  );
};

export default ManualPaymentModal;

const styles = {
  contentWrapper: css`
    width: 620px;
  `,
  formBody: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
    max-height: calc(100vh - 122px);
    overflow-y: auto;
    padding: ${spacing[20]};
  `,
  inputWrapper: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[4]};
  `,
  inputHint: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
  `,
  footerWrapper: css`
    display: flex;
    justify-content: space-between;
    gap: ${spacing[8]};
    padding: ${spacing[16]};
    box-shadow: ${shadow.dividerTop};
  `,
};
