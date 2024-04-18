import { createContext, useContext } from 'react';

interface QuizModalContextProps {
  activeQuestionIndex: number;
}

const QuizModalContext = createContext<QuizModalContextProps | null>({
  activeQuestionIndex: 0,
});

export const useQuizModalContext = () => {
  const context = useContext(QuizModalContext);
  if (!context) {
    throw new Error('useQuizModalContext must be used within a QuizModalContextProvider');
  }
  return context;
};

export const QuizModalContextProvider = ({
  activeQuestionIndex,
  children,
}: {
  activeQuestionIndex: number;
  children: React.ReactNode;
}) => {
  return <QuizModalContext.Provider value={{ activeQuestionIndex }}>{children}</QuizModalContext.Provider>;
};
