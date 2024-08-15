import { noop } from '@Utils/util';
import React, { useContext, useState, type ReactNode } from 'react';

export type CourseContentStep = 'prompt' | 'generation';

interface ContextType {
  currentStep: CourseContentStep;
  setCurrentStep: React.Dispatch<React.SetStateAction<CourseContentStep>>;
}

const Context = React.createContext<ContextType>({
  currentStep: 'prompt',
  setCurrentStep: noop,
});
export const useContentGenerationContext = () => useContext(Context);

const ContentGenerationContextProvider = ({ children }: { children: ReactNode }) => {
  const [currentStep, setCurrentStep] = useState<CourseContentStep>('prompt');
  return <Context.Provider value={{ currentStep, setCurrentStep }}>{children}</Context.Provider>;
};

export default ContentGenerationContextProvider;
