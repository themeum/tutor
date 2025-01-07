import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { type Course } from '@EnrollmentServices/enrollment';
import CourseListTable from './CourseListTable';

interface CourseListModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  onSelect?: (course: Course) => void;
  selectedCourseIds: number[];
}

function CourseListModal({ title, closeModal, actions, onSelect, selectedCourseIds }: CourseListModalProps) {
  const handleSelect = (course: Course) => {
    onSelect?.(course);
    closeModal({ action: 'CONFIRM' });
  };

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title} actions={actions} maxWidth={720}>
      <CourseListTable onSelectClick={handleSelect} selectedCourseIds={selectedCourseIds} />
    </BasicModalWrapper>
  );
}

export default CourseListModal;
