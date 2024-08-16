import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { Course } from '@CouponServices/coupon';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { UseFormReturn } from 'react-hook-form';
import CourseListTable from './CourseListTable';
import { Enrollment } from '@EnrollmentServices/enrollment';

interface CourseListModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  form: UseFormReturn<Enrollment, any, undefined>;
}

function CourseListModal({ title, closeModal, actions, form }: CourseListModalProps) {
  function handleSelect(item: Course) {
    form.setValue('course', item, { shouldValidate: true });
    closeModal({ action: 'CONFIRM' });
  }

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title} actions={actions}>
      <div css={styles.modalWrapper}>
        <CourseListTable onSelectClick={handleSelect} />
      </div>
    </BasicModalWrapper>
  );
}

export default CourseListModal;

const styles = {
  modalWrapper: css`
    width: 720px;
  `,
};
