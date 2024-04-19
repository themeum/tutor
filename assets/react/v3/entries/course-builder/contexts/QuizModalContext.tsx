import { createContext, useContext } from 'react';
import { useFormContext } from 'react-hook-form';

import type { QuizForm } from '@CourseBuilderComponents/modals/QuizModal';

interface QuizModalContextProps {
  activeQuestionIndex: number;
  activeQuestionId: string | null;
  setActiveQuestionId: React.Dispatch<React.SetStateAction<string | null>>;
}

const QuizModalContext = createContext<QuizModalContextProps | null>({
  activeQuestionIndex: 0,
  activeQuestionId: null,
  setActiveQuestionId: () => {},
});

export const useQuizModalContext = () => {
  const context = useContext(QuizModalContext);
  if (!context) {
    throw new Error('useQuizModalContext must be used within a QuizModalContextProvider');
  }
  return context;
};

export const QuizModalContextProvider = ({
  activeQuestionId,
  setActiveQuestionId,
  children,
}: {
  activeQuestionId: string | null;
  setActiveQuestionId: React.Dispatch<React.SetStateAction<string | null>>;
  children: React.ReactNode;
}) => {
  const form = useFormContext<QuizForm>();

  const activeQuestionIndex = form.watch('questions').findIndex((question) => question.ID === activeQuestionId);

  return (
    <QuizModalContext.Provider value={{ activeQuestionIndex, activeQuestionId, setActiveQuestionId }}>
      {children}
    </QuizModalContext.Provider>
  );
};
