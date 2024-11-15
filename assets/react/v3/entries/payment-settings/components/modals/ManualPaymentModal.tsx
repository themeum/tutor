import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@Atoms/Button';
import FormImageInput from '@Components/fields/FormImageInput';
import FormInput from '@Components/fields/FormInput';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';

import { colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { type FormWithGlobalErrorType, useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { requiredRule } from '@Utils/validation';

import { type PaymentMethod, type PaymentSettings, manualMethodFields } from '../../services/payment';

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

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
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
                  key={field.name}
                  name={`fields.${index}.value`}
                  control={form.control}
                  rules={requiredRule()}
                  render={(controllerProps) => (
                    <FormInput
                      {...controllerProps}
                      label={__('Title', 'tutor')}
                      placeholder={__('e.g. Bank Transfer', 'tutor')}
                      onChange={(value) => {
                        const name = String(value).toLowerCase().replace(/\s+/g, '-');
                        form.setValue('name', name);
                        form.setValue('label', String(value));
                      }}
                    />
                  )}
                />
              );
            }
            if (field.type === 'image') {
              return (
                <Controller
                  key={field.name}
                  name={`fields.${index}.value`}
                  control={form.control}
                  render={(controllerProps) => (
                    <FormImageInput
                      {...controllerProps}
                      label={field.label}
                      buttonText={__('Upload Image', 'tutor')}
                      infoText={__('Recommended size: 48x48', 'tutor')}
                      previewImageCss={styles.previewImage}
                      onChange={(value) => {
                        form.setValue('icon', value?.url ?? '');
                      }}
                    />
                  )}
                />
              );
            }
            return (
              <div key={field.name} css={styles.inputWrapper}>
                <Controller
                  name={`fields.${index}.value`}
                  control={form.control}
                  rules={{ ...requiredRule() }}
                  render={(controllerProps) => <FormTextareaInput {...controllerProps} label={field.label} rows={5} />}
                />
                <div css={styles.inputHint}>{field.hint}</div>
              </div>
            );
          })}
        </div>
        <div css={styles.footerWrapper}>
          <Button variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
            {__('Cancel', 'tutor')}
          </Button>
          <Button type="submit" variant="primary">
            {__('Save', 'tutor')}
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
    justify-content: end;
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
