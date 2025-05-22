import { css } from '@emotion/react';
import CourseListTable from '@ImportExport/components/modals/CourseListModal/CourseListTable';
import Button from '@TutorShared/atoms/Button';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import type { ModalProps } from '@TutorShared/components/modals/Modal';
import { spacing } from '@TutorShared/config/styles';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { __, sprintf } from '@wordpress/i18n';
import { type UseFormReturn } from 'react-hook-form';

interface CourseListModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  form: UseFormReturn<any, any, undefined>;
  type?: 'courses' | 'course-bundle';
}

function CourseListModal({ title, closeModal, actions, form, type }: CourseListModalProps) {
  const addedItems = form.getValues(type as 'courses' | 'course-bundle') || [];
  const _form = useFormWithGlobalError({
    defaultValues: {
      courses: addedItems,
      'course-bundle': addedItems,
    },
  });

  const selectedItems = _form.watch(type as 'courses' | 'course-bundle') || [];

  const handleAddCourses = () => {
    const selectedItems = _form.getValues(type as 'courses' | 'course-bundle') || [];
    form.setValue(type as 'courses' | 'course-bundle', [...selectedItems]);
    _form.setValue(type as 'courses' | 'course-bundle', []);
    closeModal({ action: 'CONFIRM' });
  };

  return (
    <BasicModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      title={selectedItems.length > 0 ? sprintf(__('%s selected', 'tutor'), selectedItems.length) : title}
      actions={actions}
      maxWidth={720}
    >
      <CourseListTable form={_form} type={type} />
      <div css={styles.footer}>
        <Button size="small" variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
          {__('Cancel', 'tutor')}
        </Button>
        <Button size="small" variant="primary" onClick={handleAddCourses} disabled={selectedItems.length === 0}>
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
