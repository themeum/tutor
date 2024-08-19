import Button from '@Atoms/Button';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { spacing } from '@Config/styles';
import { Coupon } from '@CouponServices/coupon';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { UseFormReturn } from 'react-hook-form';
import StudentListTable from './StudentListTable';
import { Enrollment } from '@EnrollmentServices/enrollment';

interface SelectStudentModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  form: UseFormReturn<Enrollment, any, undefined>;
}

function SelectStudentModal({ title, closeModal, actions, form }: SelectStudentModalProps) {
  const _form = useFormWithGlobalError<Enrollment>({
    defaultValues: form.getValues(),
  });

  function handleApply() {
    form.setValue('students', _form.getValues('students'), { shouldValidate: true });
    closeModal({ action: 'CONFIRM' });
  }

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title} actions={actions}>
      <div css={styles.modalWrapper}>
        <StudentListTable form={_form} />
        <div css={styles.footer}>
          <Button size="small" variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
            {__('Cancel', 'tutor')}
          </Button>
          <Button type="submit" size="small" variant="primary" onClick={handleApply}>
            {__('Add', 'tutor')}
          </Button>
        </div>
      </div>
    </BasicModalWrapper>
  );
}

export default SelectStudentModal;

const styles = {
  modalWrapper: css`
    width: 480px;
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
