import { useQuery } from '@tanstack/react-query';

export type QuizQuestionType =
  | 'true-false'
  | 'single-choice'
  | 'multiple-choice'
  | 'open-ended'
  | 'fill-in-the-blanks'
  | 'short-answer'
  | 'matching'
  | 'image-matching'
  | 'image-answering'
  | 'ordering';

export interface QuizQuestion {
  ID: number;
  title: string;
  type: QuizQuestionType;
}

const mockQuizQuestions: QuizQuestion[] = [
  {
    ID: 1,
    type: 'true-false',
    title: 'Trust me I am True / False',
  },
  {
    ID: 2,
    type: 'single-choice',
    title: 'I am definitely single choice',
  },
  {
    ID: 3,
    type: 'multiple-choice',
    title: 'Literally I am multiple choice don’t you see?',
  },
  {
    ID: 4,
    type: 'open-ended',
    title: 'Write an essay dude, I am open endeeed!',
  },
  {
    ID: 5,
    type: 'fill-in-the-blanks',
    title: 'Nature never keep spaces empty, fill in the Blanks!',
  },
  {
    ID: 6,
    type: 'short-answer',
    title: 'Keep it short!',
  },
  {
    ID: 7,
    type: 'matching',
    title: 'Matching matching matching',
  },
  {
    ID: 8,
    type: 'image-matching',
    title: 'Match the images see if it seems right',
  },
  {
    ID: 9,
    type: 'image-answering',
    title: 'Image answering is not that bad',
  },
  {
    ID: 10,
    type: 'ordering',
    title: 'Order is not chaos!',
  },
];

const getQuizQuestions = () => {
  return Promise.resolve({ data: mockQuizQuestions });
};

export const useGetQuizQuestionsQuery = () => {
  return useQuery({
    queryKey: ['GetQuizQuestions'],
    queryFn: () => getQuizQuestions().then(response => response.data),
  });
};
