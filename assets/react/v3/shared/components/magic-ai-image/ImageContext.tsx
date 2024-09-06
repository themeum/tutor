import type { PromiseResolvePayload } from '@Components/modals/Modal';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import React, { useCallback, useContext, useState } from 'react';
import {
  type ControllerFieldState,
  type ControllerRenderProps,
  type FieldValues,
  FormProvider,
  type Path,
} from 'react-hook-form';

export type StyleType =
  | 'none'
  | 'photo'
  | 'filmic'
  | 'neon'
  | 'dreamy'
  | 'black-and-white'
  | 'retrowave'
  | '3d'
  | 'concept_art'
  | 'sketch'
  | 'illustration'
  | 'painting';
export interface MagicImageGenerationFormFields {
  prompt: string;
  style: StyleType;
}

export type DropdownState = 'generation' | 'magic-fill' | 'magic-erase' | 'variations' | 'download';

interface ImageContextType<T extends FieldValues> {
  state: DropdownState;
  images: (string | null)[];
  currentImage: string;
  setCurrentImage: React.Dispatch<React.SetStateAction<string>>;
  setImages: React.Dispatch<React.SetStateAction<(string | null)[]>>;
  onDropdownMenuChange: (value: DropdownState) => void;
  field: ControllerRenderProps<T, Path<T>>;
  fieldState: ControllerFieldState;
  onCloseModal: (param?: PromiseResolvePayload<'CLOSE'>) => void;
}

export const inspirationPrompts = [
  'A detailed infographic of the human digestive system.',
  'An infographic displaying the process of the carbon cycle, highlighting the role of photosynthesis and respiration',
  'A visually appealing map highlighting the key trade routes of the Silk Road, with major cities and regions	',
  'A timeline chart of the major milestones in space exploration history.',
  'A visual timeline of the stages of human development, from infancy to adulthood, with key milestones',
  "A poster explaining the principles of Newton's three laws of motion with real-life examples.",
  'An illustrated timeline of major inventions and technological advancements from the 18th to the 21st century.',
  'A diagram showing the parts of a volcano.',
  'A step-by-step guide on how plants grow from seeds.',
];

// biome-ignore lint/suspicious/noExplicitAny: <explanation>
const ImageContext = React.createContext<ImageContextType<any> | null>(null);

export const useMagicImageGeneration = () => {
  const context = useContext(ImageContext);

  if (!context) {
    throw new Error('useMagicImageGeneration must be used within MagicImageGenerationProvider.');
  }

  return context;
};

export const MagicImageGenerationProvider = <T extends FieldValues>({
  children,
  field,
  fieldState,
  onCloseModal,
}: {
  children: React.ReactNode;
  field: ControllerRenderProps<T, Path<T>>;
  fieldState: ControllerFieldState;
  onCloseModal: (param?: PromiseResolvePayload<'CLOSE'>) => void;
}) => {
  const form = useFormWithGlobalError<MagicImageGenerationFormFields>({
    defaultValues: {
      prompt: '',
      style: 'none',
    },
  });
  const [dropdownState, setDropdownState] = useState<DropdownState>('generation');
  const [currentImage, setCurrentImage] = useState('');
  const [images, setImages] = useState<(string | null)[]>([null, null, null, null]);
  const onDropdownMenuChange = useCallback((value: DropdownState) => {
    setDropdownState(value);
  }, []);

  return (
    <ImageContext.Provider
      value={{
        state: dropdownState,
        onDropdownMenuChange,
        images,
        setImages,
        currentImage,
        setCurrentImage,
        field,
        fieldState,
        onCloseModal,
      }}
    >
      <FormProvider {...form}>{children}</FormProvider>
    </ImageContext.Provider>
  );
};
