import React, { useCallback, useContext, useMemo, useRef, useState, type ReactNode } from 'react';

import type { QuizContent } from '@CourseBuilderServices/magic-ai';
import { isDefined } from '@Utils/types';
import { noop } from '@Utils/util';

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
  topics: Topic[];
  counts?: {
    topics: number;
    lessons: number;
    quizzes: number;
    assignments: number;
  };
  time: number;
}

export interface Loading {
  title: boolean;
  description: boolean;
  topic: boolean;
  content: boolean;
  quiz: boolean;
}

export interface Errors {
  title: string;
  description: string;
  topic: string;
  content: string;
  quiz: string;
}
interface ContextType {
  abortControllerRef: React.MutableRefObject<AbortController | null>;
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
  currentErrors: Errors;
  errors: Errors[];
  updateErrors: (value: Partial<Errors>, forcePointer?: number) => void;
  appendContent: () => void;
  removeContent: () => void;
  appendLoading: () => void;
  appendErrors: () => void;
  removeErrors: () => void;
  removeLoading: () => void;
}

export const defaultContent: CourseContent = {
  prompt: '',
  title: '',
  description: '',
  topics: [],
  time: 0,
};

export const defaultLoading: Loading = {
  title: false,
  description: false,
  topic: false,
  content: false,
  quiz: false,
};

export const defaultErrors: Errors = {
  title: '',
  description: '',
  topic: '',
  content: '',
  quiz: '',
};

const Context = React.createContext<ContextType>({
  abortControllerRef: { current: null },
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
  appendErrors: noop,
  removeErrors: noop,
  currentContent: {} as CourseContent,
  currentErrors: {} as Errors,
  loading: [
    {
      title: false,
      description: false,
      content: false,
      topic: false,
      quiz: false,
    },
  ],
  errors: [
    {
      title: '',
      description: '',
      topic: '',
      content: '',
      quiz: '',
    },
  ],
  updateErrors: noop,
  currentLoading: {} as Loading,
  updateLoading: noop,
});
export const useContentGenerationContext = () => useContext(Context);

const ContentGenerationContextProvider = ({ children }: { children: ReactNode }) => {
  const abortControllerRef = useRef<AbortController | null>(null);
  const [currentStep, setCurrentStep] = useState<CourseContentStep>('prompt');
  const [contents, setContents] = useState<CourseContent[]>([defaultContent]);
  const [pointer, setPointer] = useState(0);
  const [loading, setLoading] = useState<Loading[]>([
    {
      title: false,
      description: false,
      content: false,
      topic: false,
      quiz: false,
    },
  ]);
  const [errors, setErrors] = useState<Errors[]>([
    {
      title: '',
      description: '',
      topic: '',
      content: '',
      quiz: '',
    },
  ]);

  const currentContent = useMemo(() => {
    return contents[pointer];
  }, [pointer, contents]);

  const currentLoading = useMemo(() => {
    return loading[pointer];
  }, [pointer, loading]);

  const currentErrors = useMemo(() => {
    return errors[pointer];
  }, [pointer, errors]);

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

  const updateErrors = useCallback(
    (value: Partial<Errors>, forcePointer?: number) => {
      const pointerValue = isDefined(forcePointer) ? forcePointer : pointer;
      setErrors((previous) => {
        const copy = [...previous];
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

  const appendErrors = useCallback(() => {
    setErrors((previous) => [...previous, defaultErrors]);
  }, []);

  const removeErrors = useCallback(() => {
    setErrors((previous) => [...previous].slice(0, -1));
  }, []);

  const providerValue = {
    abortControllerRef,
    currentStep,
    setCurrentStep,
    contents,
    loading,
    errors,
    pointer,
    setPointer,
    updateContents,
    updateLoading,
    updateErrors,
    currentContent,
    currentLoading,
    currentErrors,
    appendContent,
    removeContent,
    appendLoading,
    removeLoading,
    appendErrors,
    removeErrors,
  };

  return <Context.Provider value={providerValue}>{children}</Context.Provider>;
};

export default ContentGenerationContextProvider;
