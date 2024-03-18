import Button from '@Atoms/Button';
import { colorTokens, spacing } from '@Config/styles';
import { css } from '@emotion/react';

import { __ } from '@wordpress/i18n';
import type { ModalProps } from './Modal';
import ModalWrapper from './ModalWrapper';

interface ConfirmationModalProps extends ModalProps {
	closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

const ConfirmationModal = ({ closeModal, title }: ConfirmationModalProps) => {
	return (
		<ModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title}>
			<div css={styles.contentWrapper}>
				<p css={styles.content}>{__('Are you sure?', 'tutor')}</p>
				<div css={styles.footerWrapper}>
					<Button variant="secondary" onClick={() => closeModal({ action: 'CLOSE' })}>
						{__('Cancel', 'tutor')}
					</Button>
					<Button
						variant="primary"
						onClick={() => {
							closeModal({ action: 'CONFIRM' });
						}}
					>
						{__('Yes, Delete It', 'tutor')}
					</Button>
				</div>
			</div>
		</ModalWrapper>
	);
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
    border-top: 1px solid ${colorTokens.stroke.divider};
  `,
};
