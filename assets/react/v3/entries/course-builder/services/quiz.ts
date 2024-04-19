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
}

// Define a base interface for common properties
interface BaseQuizQuestion {
  ID: string;
  title: string;
  description: string;
  answer_required: boolean;
  randomize_question: boolean;
  question_mark: number;
  show_question_mark: boolean;
  markAsCorrect?: string | string[];
  options?: QuizQuestionOption[];
  answerExplanation: string;
}

interface MultipleChoiceQuizQuestion extends BaseQuizQuestion {
  type: 'multiple-choice';
  muliple_correct_answer: boolean;
}

interface MatchingQuizQuestion extends BaseQuizQuestion {
  type: 'matching';
  image_matching: boolean;
}

interface OtherQuizQuestion extends BaseQuizQuestion {
  type: Exclude<QuizQuestionType, 'multiple-choice' | 'matching'>;
}

export type QuizQuestion = MultipleChoiceQuizQuestion | MatchingQuizQuestion | OtherQuizQuestion;

const mockQuizQuestions: QuizQuestion[] = [
  {
    ID: '1',
    type: 'true-false',
    title: 'Trust me I am True / False',
    description: 'This is a true false question',
    options: [
      { ID: '1', title: 'True' },
      { ID: '2', title: 'False' },
    ],
    answer_required: false,
    question_mark: 1,
    randomize_question: false,
    show_question_mark: true,
    markAsCorrect: '1',
    answerExplanation: '',
  },
  {
    ID: '3',
    type: 'multiple-choice',
    title: 'Literally I am multiple choice don’t you see?',
    description: 'This is a multiple choice question',
    options: [
      { ID: '1', title: 'Option 1' },
      { ID: '2', title: 'Option 2' },
      { ID: '3', title: 'Option 3' },
      { ID: '4', title: 'Option 4' },
    ],
    answer_required: false,
    question_mark: 1,
    randomize_question: false,
    show_question_mark: false,
    muliple_correct_answer: false,
    markAsCorrect: '3',
    answerExplanation: 'This is the answer explanation',
  },
  {
    ID: '4',
    type: 'open-ended',
    title: 'Write an essay dude, I am open endeeed!',
    description: 'This is an open ended question',
    answer_required: false,
    question_mark: 1,
    randomize_question: false,
    show_question_mark: true,
    answerExplanation: 'This is the answer explanation',
  },
  {
    ID: '5',
    type: 'fill-in-the-blanks',
    title: 'Nature never keep spaces empty, fill in the Blanks!',
    description: 'This is a fill in the blanks question',
    answer_required: false,
    question_mark: 1,
    randomize_question: false,
    show_question_mark: true,
    options: [{ ID: '1', title: 'Fill the {dash} in time', fillinTheBlanksCorrectAnswer: ['gap'] }],
    answerExplanation: 'This is the answer explanation',
  },
  {
    ID: '6',
    type: 'short-answer',
    title: 'Keep it short!',
    description: 'This is a short answer question',
    answer_required: false,
    question_mark: 1,
    randomize_question: false,
    show_question_mark: true,
    answerExplanation: 'This is the answer explanation',
  },
  {
    ID: '7',
    type: 'matching',
    title: 'Matching matching matching',
    description: 'This is a matching question',
    answer_required: false,
    question_mark: 1,
    randomize_question: false,
    show_question_mark: true,
    image_matching: false,
    options: [
      { ID: '1', title: 'Option 1', matchedTitle: 'Matched Option 1' },
      { ID: '2', title: 'Option 2', matchedTitle: 'Matched Option 2' },
      { ID: '3', title: 'Option 3', matchedTitle: 'Matched Option 3' },
      { ID: '4', title: 'Option 4', matchedTitle: 'Matched Option 4' },
    ],
    markAsCorrect: '1',
    answerExplanation: 'This is the answer explanation',
  },
  {
    ID: '9',
    type: 'image-answering',
    title: 'Image answering is not that bad',
    description: 'This is an image answering question',
    answer_required: false,
    question_mark: 1,
    randomize_question: false,
    show_question_mark: true,
    options: [
      { ID: '1', title: 'Option 1' },
      { ID: '2', title: 'Option 2' },
      { ID: '3', title: 'Option 3' },
      { ID: '4', title: 'Option 4' },
    ],
    markAsCorrect: '3',
    answerExplanation: 'This is the answer explanation',
  },
  {
    ID: '10',
    type: 'ordering',
    title: 'Order is not chaos!',
    description: 'This is an ordering question',
    options: [
      { ID: '1', title: 'Option 5' },
      { ID: '2', title: 'Option 6' },
      { ID: '3', title: 'Option 7' },
      { ID: '4', title: 'Option 8' },
    ],
    answer_required: false,
    question_mark: 1,
    randomize_question: false,
    show_question_mark: true,
    markAsCorrect: '3',
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
