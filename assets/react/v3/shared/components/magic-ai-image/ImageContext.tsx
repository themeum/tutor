import type { PromiseResolvePayload } from '@Components/modals/Modal';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { __ } from '@wordpress/i18n';
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
  __('A serene classroom setting with books and a chalkboard', 'tutor'),
  __('An abstract representation of innovation and creativity', 'tutor'),
  __('A vibrant workspace with a laptop and coffee cup', 'tutor'),
  __('A modern design with digital learning icons', 'tutor'),
  __('A futuristic cityscape with a glowing pathway', 'tutor'),
  __('A peaceful nature scene with soft colors', 'tutor'),
  __('A professional boardroom with sleek visuals', 'tutor'),
  __('A stack of books with warm, inviting lighting', 'tutor'),
  __('A dynamic collage of technology and education themes', 'tutor'),
  __('A bold and minimalistic design with striking colors', 'tutor'),
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
