import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { type Course, type Enrollment } from '@EnrollmentServices/enrollment';
import { type UseFormReturn } from 'react-hook-form';
import CourseListTable from './CourseListTable';

interface CourseListModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  form: UseFormReturn<Enrollment, any, undefined>;
}

function CourseListModal({ title, closeModal, actions, form }: CourseListModalProps) {
  function handleSelect(item: Course) {
    form.setValue('course', item, { shouldValidate: true });
    closeModal({ action: 'CONFIRM' });
  }

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title} actions={actions} maxWidth={720}>
      <CourseListTable onSelectClick={handleSelect} />
    </BasicModalWrapper>
  );
}

export default CourseListModal;
