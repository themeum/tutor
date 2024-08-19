import { noop } from '@Utils/util';
import React, { useCallback, useContext, useState, type ReactNode } from 'react';

export type CourseContentStep = 'prompt' | 'generation';

export interface Content {
  name: string;
  content: {
    type: 'lesson' | 'quiz' | 'assignment';
    title: string;
  }[];
}
export interface CourseContent {
  title: string;
  description: string;
  image: string;
  content: Content[];
}

export interface Loading {
  title: boolean;
  image: boolean;
  description: boolean;
  content: boolean;
}
interface ContextType {
  currentStep: CourseContentStep;
  setCurrentStep: React.Dispatch<React.SetStateAction<CourseContentStep>>;
  content: CourseContent;
  updateContent: (value: Partial<CourseContent>) => void;
  loading: Loading;
  updateLoading: (value: Partial<Loading>) => void;
}

const defaultContent: CourseContent = {
  title: '',
  description: '',
  image: '',
  content: [],
};

const Context = React.createContext<ContextType>({
  currentStep: 'prompt',
  setCurrentStep: noop,
  content: defaultContent,
  updateContent: noop,
  loading: {
    title: false,
    image: false,
    description: false,
    content: false,
  },
  updateLoading: noop,
});
export const useContentGenerationContext = () => useContext(Context);

const ContentGenerationContextProvider = ({ children }: { children: ReactNode }) => {
  const [currentStep, setCurrentStep] = useState<CourseContentStep>('prompt');
  const [content, setContent] = useState<CourseContent>(defaultContent);
  const [loading, setLoading] = useState<Loading>({
    title: false,
    image: false,
    description: false,
    content: false,
  });

  const updateContent = useCallback((value: Partial<CourseContent>) => {
    setContent((previous) => ({ ...previous, ...value }));
  }, []);

  const updateLoading = useCallback((value: Partial<Loading>) => {
    setLoading((previous) => ({ ...previous, ...value }));
  }, []);

  return (
    <Context.Provider value={{ currentStep, setCurrentStep, content, updateContent, loading, updateLoading }}>
      {children}
    </Context.Provider>
  );
};

export default ContentGenerationContextProvider;
