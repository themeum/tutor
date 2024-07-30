import Button from '@Atoms/Button';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import { modal } from '@Config/constants';
import type { Editor } from '@CourseBuilderServices/course';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';

export interface EditorModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  editorUsed: Editor;
  onEditorContentChange?: (content: string) => void;
}

// This is the skeleton of the EditorModal component to load custom editors using iframes.
// Will be updated with the actual implementation later.

const EditorModal = ({ closeModal, title, subtitle, editorUsed, icon, onEditorContentChange }: EditorModalProps) => {
  return (
    <BasicModalWrapper
      onClose={() =>
        closeModal({
          action: 'CLOSE',
        })
      }
      title={title}
      subtitle={subtitle}
      icon={icon}
    >
      <div css={styles.wrapper}>
        <div css={styles.body}>
          <Button
            type="button"
            onClick={() => {
              window.open(editorUsed.link, '_blank');
              closeModal({
                action: 'CONFIRM',
              });
            }}
          >
            {editorUsed.label}
          </Button>
        </div>
      </div>
    </BasicModalWrapper>
  );
};

export default EditorModal;

const styles = {
  wrapper: css`
    display: flex;
    width: 1218px;
    height: calc(100vh - ${modal.BASIC_MODAL_HEADER_HEIGHT}px);
  `,
  body: css`
    width: 100%;
    height: 100%;
    ${styleUtils.flexCenter()}
  `,
};
