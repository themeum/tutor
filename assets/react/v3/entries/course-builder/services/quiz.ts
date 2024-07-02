import type { Media } from '@Atoms/ImageInput';
import { useQuery } from '@tanstack/react-query';

export type QuizQuestionType =
  | 'true-false'
  | 'multiple-choice'
  | 'open-ended'
  | 'fill-in-the-blanks'
  | 'short-answer'
  | 'matching'
  | 'image-answering'
  | 'ordering';

export interface QuizQuestionOption {
  ID: string;
  title: string;
  image?: Media;
  matchedTitle?: string;
  fillinTheBlanksCorrectAnswer?: string[];
  isCorrect?: boolean;
}

// Define a base interface for common properties
interface BaseQuizQuestion {
  ID: string;
  title: string;
  description: string;
  answerRequired: boolean;
  randomizeQuestion: boolean;
  questionMark: number;
  showQuestionMark: boolean;
  answerExplanation: string;
}

interface TrueFalseQuizQuestion extends BaseQuizQuestion {
  type: 'true-false';
  options: QuizQuestionOption[];
}

export interface MultipleChoiceQuizQuestion extends BaseQuizQuestion {
  type: 'multiple-choice';
  multipleCorrectAnswer: boolean;
  options: QuizQuestionOption[];
}

interface MatchingQuizQuestion extends BaseQuizQuestion {
  type: 'matching';
  imageMatching: boolean;
  options: QuizQuestionOption[];
}

interface ImageAnsweringQuizQuestion extends BaseQuizQuestion {
  type: 'image-answering';
  options: QuizQuestionOption[];
}

interface FillInTheBlanksQuizQuestion extends BaseQuizQuestion {
  type: 'fill-in-the-blanks';
  options: QuizQuestionOption[];
}

export interface OrderingQuizQuestion extends BaseQuizQuestion {
  type: 'ordering';
  options: QuizQuestionOption[];
}

interface OtherQuizQuestion extends BaseQuizQuestion {
  type: Exclude<
    QuizQuestionType,
    'true-false' | 'multiple-choice' | 'matching' | 'image-answering' | 'fill-in-the-blanks' | 'ordering'
  >;
}

export type QuizQuestion =
  | TrueFalseQuizQuestion
  | MultipleChoiceQuizQuestion
  | MatchingQuizQuestion
  | ImageAnsweringQuizQuestion
  | FillInTheBlanksQuizQuestion
  | OrderingQuizQuestion
  | OtherQuizQuestion;

const mockQuizQuestions: QuizQuestion[] = [
  {
    ID: '3',
    type: 'multiple-choice',
    title: 'Literally I am multiple choice don’t you see?',
    description: 'This is a multiple choice question',
    options: [
      { ID: '1', title: 'Option 1', isCorrect: true },
      { ID: '2', title: 'Option 2', isCorrect: false },
      { ID: '3', title: 'Option 3', isCorrect: false },
      { ID: '4', title: 'Option 4', isCorrect: false },
    ],
    answerRequired: false,
    questionMark: 1,
    randomizeQuestion: false,
    showQuestionMark: false,
    multipleCorrectAnswer: false,
    answerExplanation: 'This is the answer explanation',
  },
  {
    ID: '1',
    type: 'true-false',
    title: 'Trust me I am True / False',
    description: 'This is a true false question',
    options: [
      { ID: '1', title: 'True', isCorrect: true },
      { ID: '2', title: 'False', isCorrect: false },
    ],
    answerRequired: false,
    questionMark: 1,
    randomizeQuestion: false,
    showQuestionMark: true,
    answerExplanation: '',
  },
  {
    ID: '4',
    type: 'open-ended',
    title: 'Write an essay dude, I am open endeeed!',
    description: 'This is an open ended question',
    answerRequired: false,
    questionMark: 1,
    randomizeQuestion: false,
    showQuestionMark: true,
    answerExplanation: 'This is the answer explanation',
  },
  {
    ID: '5',
    type: 'fill-in-the-blanks',
    title: 'Nature never keep spaces empty, fill in the Blanks!',
    description: 'This is a fill in the blanks question',
    answerRequired: false,
    questionMark: 1,
    randomizeQuestion: false,
    showQuestionMark: true,
    options: [{ ID: '1', title: 'Fill the {dash} in time', fillinTheBlanksCorrectAnswer: ['gap'] }],
    answerExplanation: 'This is the answer explanation',
  },
  {
    ID: '6',
    type: 'short-answer',
    title: 'Keep it short!',
    description: 'This is a short answer question',
    answerRequired: false,
    questionMark: 1,
    randomizeQuestion: false,
    showQuestionMark: true,
    answerExplanation: 'This is the answer explanation',
  },
  {
    ID: '7',
    type: 'matching',
    title: 'Matching matching matching',
    description: 'This is a matching question',
    answerRequired: false,
    questionMark: 1,
    randomizeQuestion: false,
    showQuestionMark: true,
    imageMatching: false,
    options: [
      { ID: '1', title: 'Option 1', matchedTitle: 'Matched Option 1', isCorrect: true },
      { ID: '2', title: 'Option 2', matchedTitle: 'Matched Option 2', isCorrect: false },
      { ID: '3', title: 'Option 3', matchedTitle: 'Matched Option 3', isCorrect: false },
      { ID: '4', title: 'Option 4', matchedTitle: 'Matched Option 4', isCorrect: false },
    ],
    answerExplanation: 'This is the answer explanation',
  },
  {
    ID: '9',
    type: 'image-answering',
    title: 'Image answering is not that bad',
    description: 'This is an image answering question',
    answerRequired: false,
    questionMark: 1,
    randomizeQuestion: false,
    showQuestionMark: true,
    options: [
      { ID: '1', title: 'Option 1', isCorrect: true },
      { ID: '2', title: 'Option 2', isCorrect: false },
      { ID: '3', title: 'Option 3', isCorrect: false },
      { ID: '4', title: 'Option 4', isCorrect: false },
    ],
    answerExplanation: 'This is the answer explanation',
  },
  {
    ID: '10',
    type: 'ordering',
    title: 'Order is not chaos!',
    description: 'This is an ordering question',
    options: [
      { ID: '1', title: 'Option 5', isCorrect: true },
      { ID: '2', title: 'Option 6', isCorrect: false },
      { ID: '3', title: 'Option 7', isCorrect: false },
      { ID: '4', title: 'Option 8', isCorrect: false },
    ],
    answerRequired: false,
    questionMark: 1,
    randomizeQuestion: false,
    showQuestionMark: true,
    answerExplanation: 'This is the answer explanation',
  },
];

const getQuizQuestions = () => {
  return Promise.resolve({ data: mockQuizQuestions });
};

export const useGetQuizQuestionsQuery = () => {
  return useQuery({
    queryKey: ['GetQuizQuestions'],
    queryFn: () => getQuizQuestions().then((response) => response.data),
  });
};
