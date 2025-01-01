import Button from '@Atoms/Button';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { spacing } from '@Config/styles';
import Show from '@Controls/Show';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import type { UseFormReturn } from 'react-hook-form';
import CategoryListTable from './CategoryListTable';
import CourseListTable from './CourseListTable';

interface CourseCategorySelectModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  form: UseFormReturn<any>;
  type: 'bundles' | 'courses' | 'categories';
}

function CourseCategorySelectModal({ title, closeModal, actions, form, type }: CourseCategorySelectModalProps) {
  const _form = useFormWithGlobalError({
    defaultValues: form.getValues(),
  });

  function handleApply() {
    form.setValue(type, _form.getValues(type));
    closeModal({ action: 'CONFIRM' });
  }

  return (
    <BasicModalWrapper onClose={() => closeModal({ action: 'CLOSE' })} title={title} actions={actions} maxWidth={720}>
      <Show
        when={type === 'categories'}
        fallback={<CourseListTable form={_form} type={type === 'bundles' ? 'bundles' : 'courses'} />}
      >
        <CategoryListTable form={_form} />
      </Show>
      <div css={styles.footer}>
        <Button size="small" variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
          {__('Cancel', 'tutor')}
        </Button>
        <Button size="small" variant="primary" onClick={handleApply}>
          {__('Apply', 'tutor')}
        </Button>
      </div>
    </BasicModalWrapper>
  );
}

export default CourseCategorySelectModal;

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
