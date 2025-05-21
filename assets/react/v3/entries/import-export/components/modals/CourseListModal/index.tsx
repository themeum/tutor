import { css } from '@emotion/react';
import CourseListTable from '@ImportExport/components/modals/CourseListModal/CourseListTable';
import Button from '@TutorShared/atoms/Button';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import type { ModalProps } from '@TutorShared/components/modals/Modal';
import { spacing } from '@TutorShared/config/styles';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { type Course } from '@TutorShared/services/course';
import { __, sprintf } from '@wordpress/i18n';
import { type UseFormReturn } from 'react-hook-form';

interface CourseListModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  form: UseFormReturn<any, any, undefined>;
  addedCourses: Course[];
  onSelect: (course: Course[]) => void;
}

function CourseListModal({ title, closeModal, actions, form, addedCourses, onSelect }: CourseListModalProps) {
  const _form = useFormWithGlobalError({
    defaultValues: {
      courses: addedCourses,
    },
  });

  const selectedCourses = _form.watch('courses');

  const handleAddCourses = () => {
    const selectedCourses = _form.getValues('courses');
    form.setValue('courses', [...selectedCourses]);
    _form.setValue('courses', []);
    onSelect(selectedCourses);
    closeModal({ action: 'CONFIRM' });
  };

  return (
    <BasicModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      title={selectedCourses.length > 0 ? sprintf(__('%s selected', 'tutor'), selectedCourses.length) : title}
      actions={actions}
      maxWidth={720}
    >
      <CourseListTable form={_form} />
      <div css={styles.footer}>
        <Button size="small" variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
          {__('Cancel', 'tutor')}
        </Button>
        <Button size="small" variant="primary" onClick={handleAddCourses} disabled={selectedCourses.length === 0}>
          {__('Add', 'tutor')}
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
