import type { QuizContent } from '@CourseBuilderServices/magic-ai';
import { isDefined } from '@Utils/types';
import { noop } from '@Utils/util';
import React, { useCallback, useContext, useMemo, useState, type ReactNode } from 'react';

export type CourseContentStep = 'prompt' | 'generation';

export interface TopicContent {
  type: 'lesson' | 'quiz' | 'assignment';
  title: string;
  description: string;
  questions?: QuizContent[];
}

export interface Topic {
  title: string;
  contents: TopicContent[];
}
export interface CourseContent {
  prompt: string;
  title: string;
  description: string;
  featured_image: string;
  topics: Topic[];
  counts?: {
    topics: number;
    lessons: number;
    quizzes: number;
    assignments: number;
  };
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
  updateContents: (value: Partial<CourseContent>, forcePointer?: number) => void;
  loading: Loading[];
  updateLoading: (value: Partial<Loading>, forcePointer?: number) => void;
  currentContent: CourseContent;
  currentLoading: Loading;
  appendContent: () => void;
  removeContent: () => void;
  appendLoading: () => void;
  removeLoading: () => void;
}

export const defaultContent: CourseContent = {
  prompt: '',
  title: '',
  description: '',
  featured_image: '',
  topics: [],
};

export const defaultLoading: Loading = {
  title: false,
  description: false,
  image: false,
  topic: false,
  content: false,
  quiz: false,
};

const Context = React.createContext<ContextType>({
  currentStep: 'prompt',
  setCurrentStep: noop,
  contents: [defaultContent],
  updateContents: noop,
  pointer: 0,
  setPointer: noop,
  appendContent: noop,
  removeContent: noop,
  appendLoading: noop,
  removeLoading: noop,
  currentContent: {} as CourseContent,
  loading: [
    {
      title: false,
      image: false,
      description: false,
      content: false,
      topic: false,
      quiz: false,
    },
  ],
  currentLoading: {} as Loading,
  updateLoading: noop,
});
export const useContentGenerationContext = () => useContext(Context);

const ContentGenerationContextProvider = ({ children }: { children: ReactNode }) => {
  const [currentStep, setCurrentStep] = useState<CourseContentStep>('prompt');
  const [contents, setContents] = useState<CourseContent[]>([defaultContent]);
  const [pointer, setPointer] = useState(0);
  const [loading, setLoading] = useState<Loading[]>([
    {
      title: false,
      image: false,
      description: false,
      content: false,
      topic: false,
      quiz: false,
    },
  ]);

  const currentContent = useMemo(() => {
    return contents[pointer];
  }, [pointer, contents]);

  const currentLoading = useMemo(() => {
    return loading[pointer];
  }, [pointer, loading]);

  const updateContents = useCallback(
    (value: Partial<CourseContent>, forcePointer?: number) => {
      const totalTopics = value.topics?.length ?? 0;
      const totalLessons =
        value.topics?.reduce((acc, curr) => {
          return acc + curr.contents.filter((item) => item.type === 'lesson').length;
        }, 0) ?? 0;

      const totalQuizzes =
        value.topics?.reduce((acc, curr) => {
          return acc + curr.contents.filter((item) => item.type === 'quiz').length;
        }, 0) ?? 0;

      const totalAssignments =
        value.topics?.reduce((acc, curr) => {
          return acc + curr.contents.filter((item) => item.type === 'assignment').length;
        }, 0) ?? 0;

      const pointerValue = isDefined(forcePointer) ? forcePointer : pointer;
      console.log({ pointerValue });

      setContents((previous) => {
        const copy = [...previous];
        copy[pointerValue] ||= defaultContent;
        copy[pointerValue] = {
          ...copy[pointerValue],
          ...value,
          counts: { topics: totalTopics, lessons: totalLessons, quizzes: totalQuizzes, assignments: totalAssignments },
        };

        return copy;
      });
    },
    [pointer],
  );

  const updateLoading = useCallback(
    (value: Partial<Loading>, forcePointer?: number) => {
      const pointerValue = isDefined(forcePointer) ? forcePointer : pointer;
      setLoading((previous) => {
        const copy = [...previous];
        copy[pointerValue] ||= defaultLoading;
        copy[pointerValue] = { ...copy[pointerValue], ...value };
        return copy;
      });
    },
    [pointer],
  );

  const appendContent = useCallback(() => {
    setContents((previous) => [...previous, defaultContent]);
  }, []);

  const removeContent = useCallback(() => {
    setContents((previous) => [...previous].slice(0, -1));
  }, []);

  const appendLoading = useCallback(() => {
    setLoading((previous) => [...previous, defaultLoading]);
  }, []);

  const removeLoading = useCallback(() => {
    setLoading((previous) => [...previous].slice(0, -1));
  }, []);

  const providerValue = {
    currentStep,
    setCurrentStep,
    contents,
    currentContent,
    pointer,
    setPointer,
    updateContents,
    loading,
    currentLoading,
    updateLoading,
    appendContent,
    removeContent,
    appendLoading,
    removeLoading,
  };

  return <Context.Provider value={providerValue}>{children}</Context.Provider>;
};

export default ContentGenerationContextProvider;
