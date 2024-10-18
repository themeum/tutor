import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import For from '@Controls/For';
import { typography } from '@Config/typography';
import { FormWithGlobalErrorType } from '@Hooks/useFormWithGlobalError';
import { colorTokens, shadow, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { PaymentSettings } from '../../services/payment';
import PaymentGatewayItem from '../PaymentGatewayItem';
import { usePaymentContext } from '../../contexts/payment-context';
import Show from '@Controls/Show';
import Alert from '@Atoms/Alert';

interface PaymentGatewaysModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  form: FormWithGlobalErrorType<PaymentSettings>;
}

const PaymentGatewaysModal = ({ closeModal, title, form }: PaymentGatewaysModalProps) => {
  const { payment_gateways, errorMessage } = usePaymentContext();

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title}>
      <div css={styles.contentWrapper}>
        <div css={styles.modalBody}>
          <Show when={!errorMessage} fallback={<Alert>{errorMessage}</Alert>}>
            <For each={payment_gateways}>
              {(gateway) => (
                <PaymentGatewayItem
                  data={gateway}
                  onInstallSuccess={() => closeModal({ action: 'CONFIRM' })}
                  form={form}
                />
              )}
            </For>
          </Show>
        </div>
      </div>
    </BasicModalWrapper>
  );
};

export default PaymentGatewaysModal;

const styles = {
  contentWrapper: css`
    width: 620px;
  `,
  modalBody: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
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
  noData: css`
    ${typography.caption()};
    text-align: center;
    color: ${colorTokens.text.hints};
  `,
};
