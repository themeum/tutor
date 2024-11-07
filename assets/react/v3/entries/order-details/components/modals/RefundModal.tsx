import Button from '@Atoms/Button';
import FormCheckbox from '@Components/fields/FormCheckbox';
import FormInputWithContent from '@Components/fields/FormInputWithContent';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { tutorConfig } from '@Config/config';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { useRefundOrderMutation } from '@OrderServices/order';
import { formatPrice } from '@Utils/currency';
import { requiredRule } from '@Utils/validation';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { Controller } from 'react-hook-form';

interface RefundModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  available_amount: number;
  order_id: number;
  order_type: string;
}

interface FormField {
  amount: number;
  is_remove_enrolment: boolean;
  reason: string;
}

function RefundModal({ title, closeModal, actions, available_amount, order_id, order_type }: RefundModalProps) {
  const refundOrderMutation = useRefundOrderMutation();
  const form = useFormWithGlobalError<FormField>({
    defaultValues: {
      amount: 0,
      is_remove_enrolment: order_type !== 'single_order',
      reason: '',
    },
  });
  const amount = form.watch('amount', 0);

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title} actions={actions}>
      <form
        css={styles.form}
        onSubmit={form.handleSubmit(async (values) => {
          await refundOrderMutation.mutateAsync({ ...values, order_id });
          closeModal();
        })}
      >
        <div css={styles.formContent}>
          <div>
            <Controller
              control={form.control}
              name="amount"
              rules={{
                ...requiredRule(),
                validate: (value) => {
                  if (value === 0) {
                    return __('Refund amount must be greater than zero.', 'tutor');
                  }
                  if (value > available_amount) {
                    return __('Entered amount exceeds course payment.', 'tutor');
                  }
                  return undefined;
                },
              }}
              render={(props) => (
                <FormInputWithContent
                  {...props}
                  label={__('Refund amount', 'tutor')}
                  content={tutorConfig.tutor_currency.symbol ?? '$'}
                  type="number"
                  selectOnFocus
                />
              )}
            />

            <p css={styles.availableMessage}>
              {__('Available', 'tutor')} <strong>{formatPrice(available_amount)}</strong> {__('for refund', 'tutor')}
            </p>
          </div>

          <Controller
            control={form.control}
            name="reason"
            rules={{ ...requiredRule() }}
            render={(props) => (
              <FormTextareaInput
                {...props}
                label={__('Reason for refund', 'tutor')}
                placeholder={__('Enter the reason of this refund', 'tutor')}
                rows={3}
                enableResize
              />
            )}
          />

          {order_type === 'single_order' && (
            <Controller
              control={form.control}
              name="is_remove_enrolment"
              render={(props) => <FormCheckbox {...props} label={__('Remove the student from enrolment', 'tutor')} />}
            />
          )}
        </div>
        <div css={styles.footer}>
          <Button size="small" variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
            {__('Cancel', 'tutor')}
          </Button>
          <Button type="submit" size="small" variant="primary" loading={refundOrderMutation.isPending}>
            {__('Refund', 'tutor')} {!!amount && formatPrice(amount)}
          </Button>
        </div>
      </form>
    </BasicModalWrapper>
  );
}

export default RefundModal;

const styles = {
  inlineFields: css`
    display: flex;
    gap: ${spacing[16]};
  `,
  availableMessage: css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
    margin-top: ${spacing[12]};

    strong {
      color: ${colorTokens.text.title};
    }
  `,

  form: css`
    width: 480px;
  `,
  formContent: css`
    padding: ${spacing[20]} ${spacing[16]};
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
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
