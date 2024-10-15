import { LoadingSection } from '@Atoms/LoadingSpinner';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import For from '@Controls/For';
import { typography } from '@Config/typography';
import { FormWithGlobalErrorType } from '@Hooks/useFormWithGlobalError';
import { colorTokens, shadow, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { PaymentSettings, usePaymentGatewaysQuery } from '../../services/payment';
import PaymentGatewayItem from '../PaymentGatewayItem';
import Show from '@/v3/shared/controls/Show';

interface PaymentGatewaysModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  form: FormWithGlobalErrorType<PaymentSettings>;
}

const PaymentGatewaysModal = ({ closeModal, title, form }: PaymentGatewaysModalProps) => {
  const paymentGatewaysQuery = usePaymentGatewaysQuery();

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title}>
      <div css={styles.contentWrapper}>
        <div css={styles.modalBody}>
          <Show when={!paymentGatewaysQuery.isLoading} fallback={<LoadingSection />}>
            <Show
              when={paymentGatewaysQuery.data?.length}
              fallback={<div css={styles.noData}>{__('No data found!', 'tutor')}</div>}
            >
              <For each={paymentGatewaysQuery.data ?? []}>
                {(gateway) => (
                  <PaymentGatewayItem data={gateway} onInstallSuccess={() => closeModal({ action: 'CONFIRM' })} form={form} />
                )}
              </For>
            </Show>
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
