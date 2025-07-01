import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { type UseFormReturn } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import type { ModalProps } from '@TutorShared/components/modals/Modal';

import CollectionListTable from '@ImportExport/components/modals/CollectionList/CollectionListTable';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { spacing } from '@TutorShared/config/styles';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { type Course } from '@TutorShared/services/course';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type Collection } from '@TutorShared/utils/types';

interface BulkSelectionFormData {
  courses: Course[];
  'course-bundle': Course[];
  collections: Collection[];
}

interface CourseListModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  form: UseFormReturn<any, any, undefined>;
}

export interface ContentSelectionForm {
  selectedCollection: Collection | null;
  contents: string[];
}

const CollectionListModal = ({ closeModal, actions, form }: CourseListModalProps) => {
  const addedItems = form.getValues('collections') || [];
  const _form = useFormWithGlobalError<BulkSelectionFormData>({
    defaultValues: {
      collections: addedItems,
    },
  });

  const selectedItems = (_form.watch('collections') as Collection[]) || [];

  const handleAddContent = () => {
    const selectedItems = _form.getValues('collections') || [];
    form.setValue('collections', [...selectedItems]);
    _form.setValue('collections', []);
    closeModal({ action: 'CONFIRM' });
  };

  return (
    <BasicModalWrapper
      onClose={() => closeModal({ action: 'CLOSE' })}
      title={
        /* translators: %s is the number of selected items */
        selectedItems.length > 0
          ? sprintf(__('%d selected', 'tutor'), selectedItems.length)
          : __('Content Bank', 'tutor')
      }
      icon={<SVGIcon name="contentBank" height={24} width={24} />}
      actions={actions}
      maxWidth={720}
    >
      <div css={styles.tableWrapper}>
        <CollectionListTable form={_form} />
      </div>

      <div css={styles.footer}>
        <Button size="small" variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
          {__('Cancel', 'tutor')}
        </Button>
        <Button size="small" variant="primary" onClick={handleAddContent} disabled={selectedItems.length === 0}>
          {__('Add', 'tutor')}
        </Button>
      </div>
    </BasicModalWrapper>
  );
};

export default CollectionListModal;

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
  tableWrapper: css`
    padding: ${spacing[20]};
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
  `,
};
