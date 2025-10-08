import Button from '@TutorShared/atoms/Button';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import type { ModalProps } from '@TutorShared/components/modals/Modal';
import { spacing } from '@TutorShared/config/styles';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import type { UseFormReturn } from 'react-hook-form';
import CategoryListTable from './CategoryListTable';
import CourseListTable from './CourseListTable';

interface CourseCategorySelectModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  form: UseFormReturn<any>;
  type: 'bundles' | 'courses' | 'categories';
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  onSelect?: (items: any[]) => void;
}

function CourseCategorySelectModal({
  title,
  closeModal,
  actions,
  form,
  type,
  onSelect,
}: CourseCategorySelectModalProps) {
  const _form = useFormWithGlobalError({
    defaultValues: form.getValues(),
  });

  const selectedItems = _form.watch(type) || [];

  function handleApply() {
    form.setValue(type, selectedItems, {
      shouldDirty: true,
    });
    onSelect?.(selectedItems);
    closeModal({ action: 'CONFIRM' });
  }

  return (
    <BasicModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      title={selectedItems.length ? sprintf(__('%d Selected', __TUTOR_TEXT_DOMAIN__), selectedItems.length) : title}
      actions={actions}
      maxWidth={720}
    >
      <Show
        when={type === 'categories'}
        fallback={<CourseListTable form={_form} type={type === 'bundles' ? 'bundles' : 'courses'} />}
      >
        <CategoryListTable form={_form} />
      </Show>
      <div css={styles.footer}>
        <Button size="small" variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
          {__('Cancel', __TUTOR_TEXT_DOMAIN__)}
        </Button>
        <Button size="small" variant="primary" onClick={handleApply}>
          {__('Apply', __TUTOR_TEXT_DOMAIN__)}
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
