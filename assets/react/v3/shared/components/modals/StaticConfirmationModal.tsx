import Button, { ButtonVariant } from '@Atoms/Button';
import { shadow, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { useTranslation } from '@Hooks/useTranslation';

import { ModalProps } from './Modal';
import ModalWrapper from './ModalWrapper';

interface StaticConfirmationModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

const StaticConfirmationModal = ({ closeModal, title }: StaticConfirmationModalProps) => {
  const t = useTranslation();

  return (
    <ModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title}>
      <div css={styles.contentWrapper}>
        <p css={styles.content}>{t('COM_SPPAGEBUILDER_STORE_DELETE_WARNING')}</p>
        <div css={styles.footerWrapper}>
          <Button variant={ButtonVariant.secondary} onClick={() => closeModal({ action: 'CLOSE' })}>
            {t('COM_SPPAGEBUILDER_STORE_CANCEL')}
          </Button>
          <Button
            variant={ButtonVariant.critical}
            onClick={() => {
              closeModal({ action: 'CONFIRM' });
            }}
          >
            {t('COM_SPPAGEBUILDER_STORE_DELETE')}
          </Button>
        </div>
      </div>
    </ModalWrapper>
  );
};

export default StaticConfirmationModal;

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
