import type { QuizContent } from '@CourseBuilderServices/magic-ai';
import { noop } from '@Utils/util';
import React, { useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';

export type CourseContentStep = 'prompt' | 'generation';

export interface TopicContent {
  type: 'lesson' | 'quiz' | 'assignment';
  title: string;
  questions?: QuizContent[];
}

export interface Topic {
  name: string;
  content: TopicContent[];
}
export interface CourseContent {
  title: string;
  description: string;
  image: string;
  topics: Topic[];
}

export interface Loading {
  title: boolean;
  image: boolean;
  description: boolean;
  topic: boolean;
  content: boolean;
  quiz: boolean;
}
interface ContextType {
  currentStep: CourseContentStep;
  setCurrentStep: React.Dispatch<React.SetStateAction<CourseContentStep>>;
  contents: CourseContent[];
  pointer: number;
  setPointer: React.Dispatch<React.SetStateAction<number>>;
  updateContents: (value: Partial<CourseContent>) => void;
  loading: Loading;
  updateLoading: (value: Partial<Loading>) => void;
  currentContent: CourseContent;
}

const defaultContents: CourseContent[] = [
  {
    title: '',
    description: '',
    image: '',
    topics: [],
  },
];

const Context = React.createContext<ContextType>({
  currentStep: 'prompt',
  setCurrentStep: noop,
  contents: defaultContents,
  updateContents: noop,
  pointer: 0,
  setPointer: noop,
  currentContent: defaultContents[0],
  loading: {
    title: false,
    image: false,
    description: false,
    content: false,
    topic: false,
    quiz: false,
  },
  updateLoading: noop,
});
export const useContentGenerationContext = () => useContext(Context);

const ContentGenerationContextProvider = ({ children }: { children: ReactNode }) => {
  const [currentStep, setCurrentStep] = useState<CourseContentStep>('prompt');
  const [contents, setContents] = useState<CourseContent[]>(defaultContents);
  const [pointer, setPointer] = useState(0);
  const [loading, setLoading] = useState<Loading>({
    title: false,
    image: false,
    description: false,
    content: false,
    topic: false,
    quiz: false,
  });

  const currentContent = useMemo(() => {
    return contents[pointer];
  }, [pointer, contents]);

  const updateContents = useCallback(
    (value: Partial<CourseContent>) => {
      setContents((previous) => {
        const copy = [...previous];
        copy[pointer] ||= defaultContents[0];
        copy[pointer] = { ...copy[pointer], ...value };
        return copy;
      });
    },
    [pointer],
  );

  const updateLoading = useCallback((value: Partial<Loading>) => {
    setLoading((previous) => ({ ...previous, ...value }));
  }, []);

  useEffect(() => {
    console.log({ contents });
  }, [contents]);

  return (
    <Context.Provider
      value={{
        currentStep,
        setCurrentStep,
        contents,
        currentContent,
        pointer,
        setPointer,
        updateContents,
        loading,
        updateLoading,
      }}
    >
      {children}
    </Context.Provider>
  );
};

export default ContentGenerationContextProvider;
