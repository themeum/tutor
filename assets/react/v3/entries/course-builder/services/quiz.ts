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
export interface QuizQuestion {
  ID: string;
  title: string;
  description: string;
  type: QuizQuestionType;
  answer_required: boolean;
  randomize_question: boolean;
  question_mark: number;
  show_question_mark: boolean;
  muliple_correct_answer: boolean;
  image_matching: boolean;
  markAsCorrect?: string | string[];
  options?: QuizQuestionOption[];
}

const mockQuizQuestions: QuizQuestion[] = [
  {
    ID: '1',
    type: 'true-false',
    title: 'Trust me I am True / False',
    description: 'This is a true false question',
    options: [
      { ID: '1', title: 'True'},
      { ID: '2', title: 'False' },
    ],
    answer_required: false,
    question_mark: 1,
    randomize_question: false,
    show_question_mark: true,
    muliple_correct_answer: false,
    image_matching: false,
    markAsCorrect: '1',
  },
  {
    ID: '3',
    type: 'multiple-choice',
    title: 'Literally I am multiple choice don’t you see?',
    description: 'This is a multiple choice question',
    options: [
      { ID: '1', title: 'Option 1' },
      { ID: '2', title: 'Option 2' },
      { ID: '3', title: 'Option 3',},
      { ID: '4', title: 'Option 4' },
    ],
    answer_required: false,
    question_mark: 1,
    randomize_question: false,
    show_question_mark: false,
    muliple_correct_answer: false,
    image_matching: false,
    markAsCorrect: '3',
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
    muliple_correct_answer: false,
    image_matching: false,
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
    muliple_correct_answer: false,
    image_matching: false,
    options:[
      { ID: '1', title: 'Fill the {dash} in time', fillinTheBlanksCorrectAnswer: ['gap'] },
    ]
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
    muliple_correct_answer: false,
    image_matching: false,
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
    muliple_correct_answer: false,
    image_matching: false,
    options: [
      { ID: '1', title: 'Option 1', matchedTitle: 'Matched Option 1' },
      { ID: '2', title: 'Option 2', matchedTitle: 'Matched Option 2' },
      { ID: '3', title: 'Option 3', matchedTitle: 'Matched Option 3' },
      { ID: '4', title: 'Option 4', matchedTitle: 'Matched Option 4' },
    ],
    markAsCorrect: '1'
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
    muliple_correct_answer: false,
    image_matching: false,
    options: [
      { ID: '1', title: 'Option 1' },
      { ID: '2', title: 'Option 2' },
      { ID: '3', title: 'Option 3',},
      { ID: '4', title: 'Option 4' },
    ],
    markAsCorrect: '3'
  },
  {
    ID: '10',
    type: 'ordering',
    title: 'Order is not chaos!',
    description: 'This is an ordering question',
    options: [
      { ID: '1', title: 'Option 5', },
      { ID: '2', title: 'Option 6' },
      { ID: '3', title: 'Option 7'},
      { ID: '4', title: 'Option 8' },
    ],
    answer_required: false,
    question_mark: 1,
    randomize_question: false,
    show_question_mark: true,
    muliple_correct_answer: false,
    image_matching: false,
    markAsCorrect: '3'
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
