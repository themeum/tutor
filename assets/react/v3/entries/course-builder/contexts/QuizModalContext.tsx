import { createContext, useContext, useEffect, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

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
  children,
}: {
  children: React.ReactNode;
}) => {
  const [activeQuestionId, setActiveQuestionId] = useState<string | null>(null);
  const form = useFormContext<QuizForm>();
  const questions = useWatch({ control: form.control, name: 'questions', defaultValue: [] });

  const activeQuestionIndex = questions.findIndex((question) => question.ID === activeQuestionId);

  useEffect(() => {
    if (questions.length > 0 && !activeQuestionId) {
      setActiveQuestionId(questions[0].ID);
    }

    if (activeQuestionIndex === -1 && questions.length === 0) {
      setActiveQuestionId(null);
    }
  }, [questions, activeQuestionId, activeQuestionIndex]);

  return (
    <QuizModalContext.Provider value={{ activeQuestionIndex, activeQuestionId, setActiveQuestionId }}>
      {children}
    </QuizModalContext.Provider>
  );
};
