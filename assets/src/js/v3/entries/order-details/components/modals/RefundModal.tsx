import { useRefundOrderMutation } from '@OrderDetails/services/order';
import Alert from '@TutorShared/atoms/Alert';
import Button from '@TutorShared/atoms/Button';
import FormCheckbox from '@TutorShared/components/fields/FormCheckbox';
import FormInputWithContent from '@TutorShared/components/fields/FormInputWithContent';
import FormTextareaInput from '@TutorShared/components/fields/FormTextareaInput';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import type { ModalProps } from '@TutorShared/components/modals/Modal';
import { tutorConfig } from '@TutorShared/config/config';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { formatPrice } from '@TutorShared/utils/currency';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { requiredRule } from '@TutorShared/utils/validation';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { Controller } from 'react-hook-form';

interface RefundModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  available_amount: number;
  order_id: number;
  order_type: string;
  payment_method: string;
}

interface FormField {
  amount: number;
  is_remove_enrolment: boolean;
  is_cancel_subscription: boolean;
  reason: string;
}

function RefundModal({
  title,
  closeModal,
  actions,
  available_amount,
  order_id,
  order_type,
  payment_method,
}: RefundModalProps) {
  const refundOrderMutation = useRefundOrderMutation();
  const form = useFormWithGlobalError<FormField>({
    defaultValues: {
      amount: 0,
      is_remove_enrolment: false,
      is_cancel_subscription: false,
      reason: '',
    },
  });
  const amount = form.watch('amount', 0);

  useEffect(() => {
    form.setFocus('amount');
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title} actions={actions} maxWidth={480}>
      <form
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
                  if (Number(value) === 0) {
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
                  label={__('Amount', 'tutor')}
                  content={tutorConfig.tutor_currency.symbol ?? '$'}
                  type="number"
                  selectOnFocus
                  contentCss={styleUtils.inputCurrencyStyle}
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
                label={__('Reason', 'tutor')}
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
              render={(props) => <FormCheckbox {...props} label={__('Remove the student from enrollment', 'tutor')} />}
            />
          )}

          {(order_type === 'subscription' || order_type === 'renewal') && (
            <Controller
              control={form.control}
              name="is_cancel_subscription"
              render={(props) => <FormCheckbox {...props} label={__('Cancel the subscription', 'tutor')} />}
            />
          )}

          <Show when={payment_method !== 'stripe' && payment_method !== 'paypal'}>
            <Alert type="warning" icon="bulb">
              {
                // prettier-ignore
                __( "Note: Refund won't be processed automatically. You are required to process the refund manually via the payment gateway.", 'tutor')
              }
            </Alert>
          </Show>
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
