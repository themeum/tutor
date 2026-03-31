import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { type UseFormReturn } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import type { ModalProps } from '@TutorShared/components/modals/Modal';

import CourseListTable from '@TutorShared/components/modals/CourseListModal/CourseListTable';
import { spacing } from '@TutorShared/config/styles';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { type Bundle, type Course } from '@TutorShared/services/course';

interface CourseListModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  form: UseFormReturn<any, any, undefined>;
  type?: 'courses' | 'course-bundle';
}

type CourseBundleCombined = Course & Bundle;

function CourseListModal({ title, closeModal, actions, form, type = 'courses' }: CourseListModalProps) {
  const addedItems = form.getValues(type) || [];
  const _form = useFormWithGlobalError<{
    courses: CourseBundleCombined[];
    'course-bundle': CourseBundleCombined[];
  }>({
    defaultValues: {
      courses: addedItems,
      'course-bundle': addedItems,
    },
  });

  const selectedItems =
    ((type === 'courses' ? _form.watch('courses') : _form.watch('course-bundle')) as CourseBundleCombined[]) || [];

  const handleAddCourses = () => {
    const selectedItems = _form.getValues(type) || [];
    form.setValue(type, [...selectedItems]);
    _form.setValue(type, []);
    closeModal({ action: 'CONFIRM' });
  };

  return (
    <BasicModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      title={
        /* translators: %s is the number of selected items */
        selectedItems.length > 0 ? sprintf(__('%s selected', __TUTOR_TEXT_DOMAIN__), selectedItems.length) : title
      }
      actions={actions}
      maxWidth={720}
    >
      <CourseListTable form={_form} type={type} />
      <div css={styles.footer}>
        <Button size="small" variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
          {__('Cancel', __TUTOR_TEXT_DOMAIN__)}
        </Button>
        <Button
          size="small"
          variant="primary"
          onClick={handleAddCourses}
          disabled={selectedItems.length === 0}
          data-cy="add-courses"
        >
          {__('Add', __TUTOR_TEXT_DOMAIN__)}
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
