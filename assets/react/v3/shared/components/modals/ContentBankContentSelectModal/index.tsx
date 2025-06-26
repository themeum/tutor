import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { FormProvider } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import type { ModalProps } from '@TutorShared/components/modals/Modal';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import CollectionListTable from '@TutorShared/components/modals/ContentBankContentSelectModal/CollectionListTable';
import { spacing } from '@TutorShared/config/styles';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { type Collection } from '@TutorShared/utils/types';
import ContentListTable from './ContentListTable';

interface CourseListModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  onAddContent?: (contents: { lessons: string[]; assignments: string[] }) => void;
}

export interface ContentSelectionForm {
  selectedCollection: Collection | null;
  lessons: string[];
  assignments: string[];
}

const CollectionListModal = ({ title, closeModal, actions, onAddContent }: CourseListModalProps) => {
  const form = useFormWithGlobalError<ContentSelectionForm>({
    defaultValues: {
      selectedCollection: null,
      lessons: [],
      assignments: [],
    },
  });

  const handleAddContent = (data: ContentSelectionForm) => {
    onAddContent?.({
      lessons: data.lessons,
      assignments: data.assignments,
    });
  };

  const selectedCollection = form.watch('selectedCollection');

  return (
    <FormProvider {...form}>
      <BasicModalWrapper
        onClose={() => closeModal({ action: 'CLOSE' })}
        title={title}
        entireHeader={selectedCollection && <>&nbsp;</>}
        icon={<SVGIcon name="contentBank" height={24} width={24} />}
        actions={actions}
        maxWidth={720}
      >
        <Show when={!selectedCollection} fallback={<ContentListTable />}>
          <CollectionListTable />
        </Show>
        <Show when={form.watch('selectedCollection')}>
          <div css={styles.footer}>
            <Button size="small" variant="text" onClick={() => closeModal({ action: 'CLOSE' })}>
              {__('Cancel', 'tutor')}
            </Button>
            <Button
              size="small"
              variant="primary"
              onClick={form.handleSubmit(handleAddContent)}
              disabled={form.watch('lessons').length === 0}
            >
              {__('Add', 'tutor')}
            </Button>
          </div>
        </Show>
      </BasicModalWrapper>
    </FormProvider>
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
};
