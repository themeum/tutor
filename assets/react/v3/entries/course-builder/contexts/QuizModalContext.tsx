import { createContext, useContext, useEffect, useState, useRef } from 'react';
import { useFormContext } from 'react-hook-form';

import type { ID } from '@CourseBuilderServices/curriculum';
import type { QuizForm } from '@CourseBuilderServices/quiz';

interface QuizModalContextProps {
  activeQuestionIndex: number;
  activeQuestionId: ID;
  setActiveQuestionId: React.Dispatch<React.SetStateAction<ID>>;
}

const QuizModalContext = createContext<QuizModalContextProps | null>(null);

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
  const previousQuestionsLength = useRef<number>(questions.length);

  const activeQuestionIndex = questions.findIndex((question) => question.question_id === activeQuestionId);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (questions.length === 0) {
      setActiveQuestionId('');
    } else if (previousQuestionsLength.current !== 0 && previousQuestionsLength.current < questions.length) {
      setActiveQuestionId(questions[questions.length - 1].question_id);
    } else if (activeQuestionIndex === -1) {
      setActiveQuestionId(questions[0].question_id);
    }

    previousQuestionsLength.current = questions.length;
  }, [questions.length]);

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
