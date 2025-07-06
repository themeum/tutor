import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { FormProvider } from 'react-hook-form';

import Button from '@TutorShared/atoms/Button';
import BasicModalWrapper from '@TutorShared/components/modals/BasicModalWrapper';
import type { ModalProps } from '@TutorShared/components/modals/Modal';

import CollectionListTable from '@CourseBuilderComponents/modals/ContentBankContentSelectModal/CollectionListTable';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { spacing } from '@TutorShared/config/styles';
import Show from '@TutorShared/controls/Show';
import { useFormWithGlobalError } from '@TutorShared/hooks/useFormWithGlobalError';
import { type Collection, type ContentBankContent, type QuizQuestion } from '@TutorShared/utils/types';
import ContentListTable from './ContentListTable';
import QuestionListTable from './QuestionListTable';

interface CourseListModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  onAddContent?: (
    contents: (ContentBankContent & {
      question?: QuizQuestion;
    })[],
  ) => void;
  type: 'lesson_assignment' | 'question';
}

export interface ContentSelectionForm {
  selectedCollection: Collection | null;
  contents: (ContentBankContent & {
    question?: QuizQuestion;
  })[];
}

const CollectionListModal = ({ closeModal, actions, onAddContent, type }: CourseListModalProps) => {
  const form = useFormWithGlobalError<ContentSelectionForm>({
    defaultValues: {
      selectedCollection: null,
      contents: [],
    },
  });

  const handleAddContent = (data: ContentSelectionForm) => {
    onAddContent?.(data.contents);
    closeModal({ action: 'CONFIRM' });
  };

  const selectedCollection = form.watch('selectedCollection');

  return (
    <FormProvider {...form}>
      <BasicModalWrapper
        onClose={() => closeModal({ action: 'CLOSE' })}
        title={__('Content Bank', 'tutor')}
        entireHeader={selectedCollection && <>&nbsp;</>}
        icon={<SVGIcon name="contentBank" height={24} width={24} />}
        actions={actions}
        maxWidth={720}
      >
        <Show
          when={!selectedCollection}
          fallback={
            <Show when={type === 'lesson_assignment'} fallback={<QuestionListTable />}>
              <ContentListTable />
            </Show>
          }
        >
          <CollectionListTable type={type} />
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
              disabled={form.watch('contents').length === 0}
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
