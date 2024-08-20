import type { ModalProps } from '@Components/modals/Modal';
import BasicPrompt from '@CourseBuilderComponents/ai-course-modal/BasicPrompt';
import ContentGeneration from '@CourseBuilderComponents/ai-course-modal/ContentGeneration';
import ContentGenerationContextProvider, {
  useContentGenerationContext,
} from '@CourseBuilderComponents/ai-course-modal/ContentGenerationContext';
import { css } from '@emotion/react';
import { useEffect } from 'react';

interface AICourseBuilderModalProps extends ModalProps {}

const Component = ({ closeModal }: { closeModal: () => void }) => {
  const { currentStep } = useContentGenerationContext();

  if (currentStep === 'prompt') {
    return <BasicPrompt onClose={closeModal} />;
  }
  return <ContentGeneration onClose={closeModal} />;
};

const AICourseBuilderModal = ({ closeModal }: AICourseBuilderModalProps) => {
  useEffect(() => {
    document.body.style.overflow = 'hidden';

    return () => {
      document.body.style.overflow = 'initial';
    };
  }, []);

  return (
    <ContentGenerationContextProvider>
      <div css={styles.wrapper}>
        <Component closeModal={closeModal} />
      </div>
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
