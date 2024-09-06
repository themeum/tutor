import Button from '@Atoms/Button';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { useMarkAsPaidMutation } from '@OrderServices/order';
import { formatPrice } from '@Utils/currency';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
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

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title} actions={actions}>
      <form
        css={styles.form}
        onSubmit={form.handleSubmit((values) => {
          markAsPaidMutation.mutate({ note: values.note, order_id });
          closeModal();
        })}
      >
        <div css={styles.formContent}>
          <p css={styles.availableMessage}>
            {__('This will create an order. Mark this order as paid if you received ', 'tutor')}
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
          <Button type="submit" size="small" variant="WP" loading={markAsPaidMutation.isPending}>
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

  form: css`
		width: 480px;
	`,
  formContent: css`
		padding: ${spacing[20]} ${spacing[16]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[10]};
	`,
  footer: css`
		box-shadow: 0px 1px 0px 0px #E4E5E7 inset;
		height: 56px;
		display: flex;
		align-items: center;
		justify-content: end;
		gap: ${spacing[16]};
		padding-inline: ${spacing[16]};
	`,
};
