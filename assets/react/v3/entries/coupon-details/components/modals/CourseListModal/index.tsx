import Button from '@Atoms/Button';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import CourseList from './CourseList';

interface CourseListModalProps extends ModalProps {
	closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
}

function CourseListModal({ title, closeModal, actions }: CourseListModalProps) {
	return (
		<BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title} actions={actions}>
			<CourseList selectedCourseIds={[]} />
			<div css={styles.footer}>
				<Button size="small" variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
					{__('Cancel', 'tutor')}
				</Button>
				<Button type="submit" size="small" variant="primary">
					{__('Apply', 'tutor')}
				</Button>
			</div>
		</BasicModalWrapper>
	);
}

export default CourseListModal;

const styles = {
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
