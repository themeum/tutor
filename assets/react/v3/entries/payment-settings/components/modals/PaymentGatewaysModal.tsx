import { LoadingSection } from '@Atoms/LoadingSpinner';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { typography } from '@Config/typography';
import { FormWithGlobalErrorType } from '@Hooks/useFormWithGlobalError';
import { colorTokens, shadow, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { PaymentSettings, usePaymentGatewaysQuery } from '../../services/payment';
import PaymentGatewayItem from '../PaymentGatewayItem';

interface PaymentGatewaysModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  form: FormWithGlobalErrorType<PaymentSettings>;
}

const PaymentGatewaysModal = ({ closeModal, title, form }: PaymentGatewaysModalProps) => {
  const paymentGatewaysQuery = usePaymentGatewaysQuery();

  if (paymentGatewaysQuery.isLoading) {
    return <LoadingSection />;
  }

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title}>
      <div css={styles.contentWrapper}>
        <div css={styles.modalBody}>
          {paymentGatewaysQuery.data?.map((gateway) => (
            <PaymentGatewayItem data={gateway} />
          ))}
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
};
