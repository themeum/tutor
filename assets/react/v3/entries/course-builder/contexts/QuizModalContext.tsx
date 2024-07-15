import { createContext, useContext, useEffect, useState, useRef } from 'react';
import { useFormContext } from 'react-hook-form';

import type { ID } from '@CourseBuilderServices/curriculum';
import type { QuizForm, QuizQuestion } from '@CourseBuilderServices/quiz';

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
  const previousQuestions = useRef<QuizQuestion[]>(questions);

  const activeQuestionIndex = questions.findIndex((question) => question.question_id === activeQuestionId);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (questions.length === 0) {
      setActiveQuestionId('');
    } else if (previousQuestions.current.length !== 0 && previousQuestions.current.length < questions.length) {
      const newQuestion = questions.find(
        (question) =>
          !previousQuestions.current.some((prevQuestion) => prevQuestion.question_id === question.question_id)
      );
      setActiveQuestionId(newQuestion?.question_id || '');
    } else if (activeQuestionIndex === -1) {
      setActiveQuestionId(questions[0].question_id);
    }

    previousQuestions.current = questions;
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
