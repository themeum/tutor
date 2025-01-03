import Button from '@Atoms/Button';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { spacing } from '@Config/styles';
import type { Enrollment } from '@EnrollmentServices/enrollment';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import type { UseFormReturn } from 'react-hook-form';
import StudentListTable from './StudentListTable';

interface SelectStudentModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  form: UseFormReturn<Enrollment, any, undefined>;
}

function SelectStudentModal({ title, closeModal, actions, form }: SelectStudentModalProps) {
  const _form = useFormWithGlobalError<Enrollment>({
    defaultValues: form.getValues(),
  });
  const selectedStudents = _form.watch('students') || [];

  function handleApply() {
    form.setValue('students', selectedStudents, { shouldValidate: true });
    closeModal({ action: 'CONFIRM' });
  }

  return (
    <BasicModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      title={selectedStudents.length ? sprintf(__('%d Selected', 'tutor'), selectedStudents.length) : title}
      actions={actions}
      maxWidth={480}
    >
      <StudentListTable form={_form} />
      <div css={styles.footer}>
        <Button size="small" variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
          {__('Cancel', 'tutor')}
        </Button>
        <Button type="submit" size="small" variant="primary" onClick={handleApply}>
          {__('Add', 'tutor')}
        </Button>
      </div>
    </BasicModalWrapper>
  );
}

export default SelectStudentModal;

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
