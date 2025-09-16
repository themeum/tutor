import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import FormImageInput from '@TutorShared/components/fields/FormImageInput';
import FormInput from '@TutorShared/components/fields/FormInput';
import FormWPEditor from '@TutorShared/components/fields/FormWPEditor';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import type { ModalProps } from '@TutorShared/components/modals/Modal';

import { colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { type FormWithGlobalErrorType, useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { requiredRule } from '@TutorShared/utils/validation';

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
          name: 'payment_instructions',
          value: '',
        },
      ],
    },
  });

  useEffect(() => {
    form.setFocus('fields.0.value');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const onSubmit = (data: PaymentMethod) => {
    paymentForm.setValue('payment_methods', [...(paymentForm.getValues('payment_methods') ?? []), data]);
    closeModal({ action: 'CONFIRM' });
  };

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title} maxWidth={620}>
      <form onSubmit={form.handleSubmit(onSubmit)}>
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
                  render={(controllerProps) => (
                    <FormWPEditor
                      {...controllerProps}
                      label={field.label}
                      toolbar1="formatselect bold italic underline | bullist numlist | blockquote | alignleft aligncenter alignright | link unlink"
                      toolbar2=""
                    />
                  )}
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
