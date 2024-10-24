import { useEffect } from 'react';
import Button from '@Atoms/Button';
import FormInput from '@Components/fields/FormInput';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import FormImageInput from '@Components/fields/FormImageInput';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { typography } from '@Config/typography';
import { FormWithGlobalErrorType, useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { requiredRule } from '@Utils/validation';
import { colorTokens, shadow, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller } from 'react-hook-form';
import { manualMethodFields, PaymentMethod, PaymentSettings } from '../../services/payment';

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
      support_subscription: false,
      update_available: false,
      is_manual: true,
      fields: [
        {
          name: 'method_name',
          value: '',
        },
        {
          name: 'icon',
          value: '',
        },
        {
          name: 'additional_details',
          value: '',
        },
        {
          name: 'payment_instructions',
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
          {manualMethodFields.map((field, index) => {
            if (field.name === 'method_name') {
              return (
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
              );
            } else if (field.type === 'image') {
              return (
                <Controller
                  name={`fields.${index}.value`}
                  control={form.control}
                  render={(controllerProps) => (
                    <FormImageInput
                      {...controllerProps}
                      label={field.label}
                      size="small"
                      previewImageCss={styles.previewImage}
                      onChange={(value) => {
                        form.setValue('icon', value?.url ?? '');
                      }}
                    />
                  )}
                />
              );
            } else {
              return (
                <div css={styles.inputWrapper}>
                  <Controller
                    name={`fields.${index}.value`}
                    control={form.control}
                    render={(controllerProps) => (
                      <FormTextareaInput {...controllerProps} label={field.label} rows={5} />
                    )}
                  />
                  <div css={styles.inputHint}>{field.hint}</div>
                </div>
              );
            }
          })}
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
    max-height: calc(100vh - 160px);
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
  previewImage: css`
    img {
      object-fit: contain;
    }
  `,
};
