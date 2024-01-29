import Button, { ButtonVariant } from '@Atoms/Button';
import { shadow, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { useGenericMutation } from '@Hooks/useGenericMutation';
import { useTranslation } from '@Hooks/useTranslation';
import { MutationFunction, QueryKey } from '@tanstack/react-query';

import { ModalProps, useModal } from './Modal';
import ModalWrapper from './ModalWrapper';

interface Response {
  data: {
    status: boolean;
  };
}

interface ConfirmationModalProps<TData, TVariables> extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  confirmationMessage?: string;
  confirmButtonText?: string;
  confirmButtonVariant?: ButtonVariant;
  payload: TVariables;
  mutationFn: MutationFunction<TData, TVariables>;
  invalidateKeys?: (QueryKey | undefined)[];
}

const ConfirmationModal = <TData, TVariables>({
  title,
  confirmationMessage,
  confirmButtonText,
  confirmButtonVariant = ButtonVariant.critical,
  payload,
  mutationFn,
  closeModal,
  invalidateKeys,
}: ConfirmationModalProps<TData, TVariables>) => {
  const t = useTranslation();

  const genericMutation = useGenericMutation({
    mutationFn,
    invalidateKeys,
  });

  return (
    <ModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title}>
      <div css={styles.contentWrapper}>
        <p css={styles.content}>{confirmationMessage || t('COM_SPPAGEBUILDER_STORE_DELETE_WARNING')}</p>
        <div css={styles.footerWrapper}>
          <Button variant={ButtonVariant.secondary} onClick={() => closeModal({ action: 'CLOSE' })}>
            {t('COM_SPPAGEBUILDER_STORE_CANCEL')}
          </Button>
          <Button
            variant={confirmButtonVariant}
            onClick={async () => {
              const { data } = (await genericMutation.mutateAsync(payload)) as unknown as Response;

              if (data.status) {
                closeModal({ action: 'CONFIRM' });
              }
            }}
            loading={genericMutation.isLoading}
          >
            {confirmButtonText || t('COM_SPPAGEBUILDER_STORE_DELETE')}
          </Button>
        </div>
      </div>
    </ModalWrapper>
  );
};

export const useConfirmationModal = () => {
  const { showModal } = useModal();

  const handleShowModal = <TData, TVariables>(props: Omit<ConfirmationModalProps<TData, TVariables>, 'closeModal'>) => {
    return showModal({ component: ConfirmationModal, props, closeOnOutsideClick: true });
  };

  return handleShowModal;
};

export default ConfirmationModal;

const styles = {
  contentWrapper: css`
    width: 620px;
  `,
  content: css`
    padding: ${spacing[20]};
  `,
  footerWrapper: css`
    display: flex;
    justify-content: end;
    gap: ${spacing[8]};
    padding: ${spacing[16]};
    box-shadow: ${shadow.dividerTop};
  `,
};
