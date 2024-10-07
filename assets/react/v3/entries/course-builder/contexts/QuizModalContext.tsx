import type React from 'react';
import { createContext, useContext, useEffect, useRef, useState } from 'react';
import { useFormContext } from 'react-hook-form';

import type { ContentType, ID } from '@CourseBuilderServices/curriculum';
import type { QuizForm, QuizQuestion } from '@CourseBuilderServices/quiz';

interface QuizModalContextProps {
  activeQuestionIndex: number;
  activeQuestionId: ID;
  setActiveQuestionId: React.Dispatch<React.SetStateAction<ID>>;
  quizId: ID;
  contentType: ContentType;
  validationError: {
    message: string;
    type: 'question' | 'quiz' | 'correct_option' | 'add_option' | 'save_option';
  } | null;
  setValidationError: React.Dispatch<
    React.SetStateAction<{
      message: string;
      type: 'question' | 'quiz' | 'correct_option' | 'add_option' | 'save_option';
    } | null>
  >;
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
  quizId,
  contentType,
  validationError: propsValidationError,
}: {
  children:
    | React.ReactNode
    | ((
        item: NonNullable<number>,
        setValidationError: React.Dispatch<
          React.SetStateAction<{
            message: string;
            type: 'question' | 'quiz' | 'correct_option' | 'add_option' | 'save_option';
          } | null>
        >,
      ) => React.ReactNode);
  quizId: ID;
  contentType: ContentType;
  validationError?: {
    message: string;
    type: 'question' | 'quiz' | 'correct_option' | 'add_option' | 'save_option';
  } | null;
}) => {
  const [activeQuestionId, setActiveQuestionId] = useState<ID>('');
  const form = useFormContext<QuizForm>();
  const questions = form.watch('questions') || [];
  const previousQuestions = useRef<QuizQuestion[]>(questions);
  const [validationError, setValidationError] = useState<{
    message: string;
    type: 'question' | 'quiz' | 'correct_option' | 'add_option' | 'save_option';
  } | null>(propsValidationError || null);

  const activeQuestionIndex = questions.findIndex((question) => question.question_id === activeQuestionId);

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (questions.length === 0) {
      setActiveQuestionId('');
    } else if (previousQuestions.current.length !== 0 && previousQuestions.current.length < questions.length) {
      const newQuestion = questions.find(
        (question) =>
          !previousQuestions.current.some(
            (prevQuestion) => String(prevQuestion.question_id) === String(question.question_id),
          ),
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
    <QuizModalContext.Provider
      value={{
        activeQuestionIndex,
        activeQuestionId,
        setActiveQuestionId,
        quizId,
        validationError,
        setValidationError,
        contentType,
      }}
    >
      {typeof children === 'function' ? children(activeQuestionIndex, setValidationError) : children}
    </QuizModalContext.Provider>
  );
};
