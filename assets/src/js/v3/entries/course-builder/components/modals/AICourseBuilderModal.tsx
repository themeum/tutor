import BasicPrompt from '@CourseBuilderComponents/ai-course-modal/BasicPrompt';
import ContentGeneration from '@CourseBuilderComponents/ai-course-modal/ContentGeneration';
import ContentGenerationContextProvider, {
  useContentGenerationContext,
} from '@CourseBuilderComponents/ai-course-modal/ContentGenerationContext';
import { css } from '@emotion/react';
import FocusTrap from '@TutorShared/components/FocusTrap';
import type { ModalProps } from '@TutorShared/components/modals/Modal';

type AICourseBuilderModalProps = ModalProps;

const Component = ({ closeModal }: { closeModal: () => void }) => {
  const { currentStep } = useContentGenerationContext();

  if (currentStep === 'prompt') {
    return <BasicPrompt onClose={closeModal} />;
  }
  return <ContentGeneration onClose={closeModal} />;
};

const AICourseBuilderModal = ({ closeModal }: AICourseBuilderModalProps) => {
  return (
    <ContentGenerationContextProvider>
      <FocusTrap>
        <div css={styles.wrapper}>
          <Component closeModal={closeModal} />
        </div>
      </FocusTrap>
    </ContentGenerationContextProvider>
  );
};

export default AICourseBuilderModal;

const styles = {
  wrapper: css`
    width: 100vw;
    height: 100vh;
    position: relative;
  `,
};
