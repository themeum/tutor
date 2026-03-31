import { useMarkAsPaidMutation } from '@OrderDetails/services/order';
import Button from '@TutorShared/atoms/Button';
import FormTextareaInput from '@TutorShared/components/fields/FormTextareaInput';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import type { ModalProps } from '@TutorShared/components/modals/Modal';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { formatPrice } from '@TutorShared/utils/currency';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';

interface MarkAsPaidModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  total: number;
  order_id: number;
}

interface FormField {
  note: string;
}

function MarkAsPaidModal({ title, closeModal, actions, total, order_id }: MarkAsPaidModalProps) {
  const markAsPaidMutation = useMarkAsPaidMutation();
  const form = useFormWithGlobalError<FormField>({
    defaultValues: {
      note: '',
    },
  });

  useEffect(() => {
    form.setFocus('note');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title} actions={actions} maxWidth={480}>
      <form
        onSubmit={form.handleSubmit(async (values) => {
          await markAsPaidMutation.mutateAsync({ note: values.note, order_id });
          closeModal();
        })}
      >
        <div css={styles.formContent}>
          <p css={styles.availableMessage}>
            {__('This will create an order. Mark this as paid if you have manually received ', 'tutor')}
            <span>{formatPrice(total)}</span> {__(' manually.', 'tutor')}
          </p>
          <Controller
            control={form.control}
            name="note"
            render={(props) => (
              <FormTextareaInput
                {...props}
                label={__('Note', 'tutor')}
                rows={3}
                placeholder={__('Write some note against this action.', 'tutor')}
              />
            )}
          />
        </div>
        <div css={styles.footer}>
          <Button size="small" variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
            {__('Cancel', 'tutor')}
          </Button>
          <Button type="submit" size="small" variant="primary" loading={markAsPaidMutation.isPending}>
            {__('Mark as Paid', 'tutor')}
          </Button>
        </div>
      </form>
    </BasicModalWrapper>
  );
}

export default MarkAsPaidModal;

const styles = {
  inlineFields: css`
    display: flex;
    gap: ${spacing[16]};
  `,
  availableMessage: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};

    span {
      color: ${colorTokens.brand.blue};
    }
  `,
  formContent: css`
    padding: ${spacing[20]} ${spacing[16]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[10]};
  `,
  footer: css`
    box-shadow: 0px 1px 0px 0px #e4e5e7 inset;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: end;
    gap: ${spacing[16]};
    padding-inline: ${spacing[16]};
  `,
};
