import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { type Course } from '@EnrollmentServices/enrollment';
import CourseListTable from './CourseListTable';

interface CourseListModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  onAddCourses?: (course: Course[]) => void;
  selectedCourseIds: number[];
}

function CourseListModal({ title, closeModal, actions, onAddCourses, selectedCourseIds }: CourseListModalProps) {
  const handleSelect = (items: Course[]) => {
    if (onAddCourses) {
      onAddCourses(items);
    }
    closeModal({ action: 'CONFIRM' });
  };

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title} actions={actions} maxWidth={720}>
      <CourseListTable
        onAdd={handleSelect}
        onCancel={() => closeModal({ action: 'CLOSE' })}
        selectedCourseIds={selectedCourseIds}
      />
    </BasicModalWrapper>
  );
}

export default CourseListModal;
