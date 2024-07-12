import { createContext, useContext, useEffect, useState } from 'react';
import { useFormContext } from 'react-hook-form';

import type { QuizForm } from '@CourseBuilderComponents/modals/QuizModal';
import type { ID } from '@CourseBuilderServices/curriculum';
interface QuizModalContextProps {
  activeQuestionIndex: number;
  activeQuestionId: ID;
  setActiveQuestionId: React.Dispatch<React.SetStateAction<ID>>;
}

const QuizModalContext = createContext<QuizModalContextProps | null>({
  activeQuestionIndex: 0,
  activeQuestionId: '',
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
  children,
}: {
  children: React.ReactNode;
}) => {
  const [activeQuestionId, setActiveQuestionId] = useState<ID>('');
  const form = useFormContext<QuizForm>();
  const questions = form.watch('questions') || [];

  const activeQuestionIndex = questions.findIndex((question) => question.question_id === activeQuestionId);

  useEffect(() => {
    if (questions.length > 0 && !activeQuestionId) {
      setActiveQuestionId(questions[0].question_id);
    }
  }, [questions, activeQuestionId]);

  useEffect(() => {
    if (activeQuestionIndex === -1 && activeQuestionId) {
      setActiveQuestionId('');
    }
  }, [activeQuestionIndex, activeQuestionId]);

  return (
    <QuizModalContext.Provider value={{ activeQuestionIndex, activeQuestionId, setActiveQuestionId }}>
      {children}
    </QuizModalContext.Provider>
  );
};
