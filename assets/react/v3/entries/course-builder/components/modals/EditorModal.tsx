import { css } from '@emotion/react';
import { useState } from 'react';

import { LoadingOverlay } from '@Atoms/LoadingSpinner';
import BasicModalWrapper from '@Components/modals/BasicModalWrapper';
import type { ModalProps } from '@Components/modals/Modal';
import Show from '@Controls/Show';
import type { Editor } from '@CourseBuilderServices/course';

export interface EditorModalProps extends ModalProps {
  closeModal: (props?: { action: 'CONFIRM' | 'CLOSE' }) => void;
  editorUsed: Editor;
  onEditorContentChange?: (content: string) => void;
}

// This is the skeleton of the EditorModal component to load custom editors using iframes.
// Will be updated with the actual implementation later.

const EditorModal = ({ closeModal, title, subtitle, editorUsed, icon, onEditorContentChange }: EditorModalProps) => {
  const [isLoaded, setIsLoaded] = useState(false);

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
      fullScreen
    >
      <div css={styles.wrapper}>
        <Show when={!isLoaded}>
          <LoadingOverlay />
        </Show>

        <iframe
          css={styles.iframe}
          src={editorUsed.link}
          title={editorUsed.name}
          onLoad={() => {
            setIsLoaded(true);
          }}
        />
      </div>
    </BasicModalWrapper>
  );
};

export default EditorModal;

const styles = {
  wrapper: css`
    width: 100%;
    height: 100%;
  `,
  iframe: css`
    width: 100%;
    height: 100%;
    border: none;
  `,
};
