import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import mockImage1 from '@Images/mock-images/mock-image-1.jpg';
import mockImage2 from '@Images/mock-images/mock-image-2.jpg';
import mockImage3 from '@Images/mock-images/mock-image-3.jpg';
import mockImage4 from '@Images/mock-images/mock-image-4.jpg';
import React, { useCallback, useContext, useState } from 'react';
import { FormProvider } from 'react-hook-form';

export type StyleType =
  | 'none'
  | 'photo'
  | 'illustration'
  | '3d'
  | 'painting'
  | 'sketch'
  | 'black-and-white'
  | 'cartoon';
export interface MagicImageGenerationFormFields {
  prompt: string;
  style: StyleType;
}

export type DropdownState = 'generation' | 'extend' | 'magic-fill' | 'erase' | 'variations' | 'extend';

interface ImageContextType {
  state: DropdownState;
  images: string[];
  setImages: React.Dispatch<React.SetStateAction<string[]>>;
  onDropdownMenuChange: (value: DropdownState) => void;
}

const mockGeneratedImages = [mockImage1, mockImage2, mockImage3, mockImage4];
export const inspirationPrompts = [
  'Create a banner image for my course 1',
  'Create a banner image for my course 2',
  'Create a banner image for my course 3',
  'Create a banner image for my course 4',
  'Create a banner image for my course 5',
  'Create a banner image for my course 6',
];

const ImageContext = React.createContext<ImageContextType | null>(null);

export const useMagicImageGeneration = () => {
  const context = useContext(ImageContext);

  if (!context) {
    throw new Error('useMagicImageGeneration must be used within MagicImageGenerationProvider.');
  }

  return context;
};

export const MagicImageGenerationProvider = ({ children }: { children: React.ReactNode }) => {
  const form = useFormWithGlobalError<MagicImageGenerationFormFields>({
    defaultValues: {
      prompt: '',
      style: 'none',
    },
  });
  const [dropdownState, setDropdownState] = useState<DropdownState>('magic-fill');
  const [images, setImages] = useState<string[]>(mockGeneratedImages);

  const onDropdownMenuChange = useCallback((value: DropdownState) => {
    setDropdownState(value);
  }, []);

  return (
    <ImageContext.Provider value={{ state: dropdownState, onDropdownMenuChange, images, setImages }}>
      <FormProvider {...form}>{children}</FormProvider>
    </ImageContext.Provider>
  );
};
