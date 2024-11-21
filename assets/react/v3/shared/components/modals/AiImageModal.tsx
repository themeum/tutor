import {
  MagicImageGenerationProvider,
  type StyleType,
  useMagicImageGeneration,
} from '@Components/magic-ai-image/ImageContext';
import { ImageGeneration } from '@Components/magic-ai-image/ImageGeneration';
import MagicFill from '@Components/magic-ai-image/MagicFill';
import type { ControllerFieldState, ControllerRenderProps, FieldValues, Path } from 'react-hook-form';
import BasicModalWrapper from './BasicModalWrapper';
import type { ModalProps } from './Modal';

interface AIImageModalProps<T extends FieldValues> extends ModalProps {
  field: ControllerRenderProps<T, Path<T>>;
  fieldState: ControllerFieldState;
}

export interface GenerateAiImageFormFields {
  prompt: string;
  style: StyleType;
}

function RenderModalContent() {
  const { state } = useMagicImageGeneration();

  switch (state) {
    case 'generation':
      return <ImageGeneration />;
    case 'magic-fill':
      return <MagicFill />;
    default:
      return null;
  }
}

const AIImageModal = <T extends FieldValues>({ title, icon, closeModal, field, fieldState }: AIImageModalProps<T>) => {
  return (
    <BasicModalWrapper onClose={closeModal} title={title} icon={icon}>
      <MagicImageGenerationProvider field={field} fieldState={fieldState} onCloseModal={closeModal}>
        <RenderModalContent />
      </MagicImageGenerationProvider>
    </BasicModalWrapper>
  );
};

export default AIImageModal;
