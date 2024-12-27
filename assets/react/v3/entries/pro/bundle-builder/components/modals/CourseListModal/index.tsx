import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { type Course } from '@EnrollmentServices/enrollment';
import CourseListTable from './CourseListTable';

interface CourseListModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  onAddCourse?: (course: Course) => void;
}

function CourseListModal({ title, closeModal, actions, onAddCourse }: CourseListModalProps) {
  function handleSelect(item: Course) {
    if (onAddCourse) {
      onAddCourse(item);
    }
    closeModal({ action: 'CONFIRM' });
  }

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title} actions={actions} maxWidth={720}>
      <CourseListTable onSelectClick={handleSelect} />
    </BasicModalWrapper>
  );
}

export default CourseListModal;
