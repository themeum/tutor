export type RevealQuestionType = (typeof QUIZ_REVEAL_CONFIG.SUPPORTED_TYPES)[number];

export const QUIZ_REVEAL_CONFIG = {
  ANSWER_CONTEXT_ID: 'tutor-quiz-context',
  DEFAULT_WAIT_MS: 2000,
  SUPPORTED_TYPES: ['true_false', 'single_choice', 'multiple_choice', 'scale'] as const,
  OPTION_SELECTOR: '.tutor-quiz-question-option',
  QUESTION_SELECTOR: '.tutor-quiz-question',
  EXPLANATION_SELECTOR: '[data-quiz-explanation]',
  EXPLANATION_TRIGGER_SELECTOR: '[data-quiz-explanation-toggle]',
  EXPLANATION_BODY_SELECTOR: '.tutor-quiz-explanation-body',
  EXPLANATION_CONTENT_DATASET: 'quizExplanationContent',
  DATA_OPTION_ATTR: 'data-option',
  DATA_REVEALED_ATTR: 'data-revealed',
  DATA_RESULT_ATTR: 'data-reveal-result',
  DATA_OPTION_CORRECT: 'correct',
  DATA_OPTION_INCORRECT: 'incorrect',
} as const;

export const QUIZ_ABANDON_CONFIG = {
  NAVIGATION_EVENT: 'click',
  IGNORE_ANCHOR_PREFIXES: ['#', 'javascript:'],
} as const;

export const QuestionTimeoutAction = {
  AUTO_ABANDON: 'auto_abandon',
  AUTO_SUBMIT: 'auto_submit',
} as const;

export const QuizLayoutType = {
  QUESTION_BELOW_EACH_OTHER: 'question_below_each_other',
  QUESTION_PAGINATION: 'question_pagination',
  SINGLE_QUESTION: 'single_question',
} as const;

export const ERROR_MESSAGES = {
  SUBMIT_FAILED: 'Failed to submit quiz',
  ABANDON_FAILED: 'Failed to abandon quiz',
  REQUIRED_QUESTIONS: 'Please answer all required questions before submitting.',
} as const;

export const QUIZ_LAYOUT_SELECTORS = {
  QUESTION_WRAPPER_ATTR: 'data-quiz-question-index',
  QUESTION_WRAPPER: '.tutor-quiz-question-wrapper',
} as const;

export const QUIZ_LAYOUT_KEYS = {
  QUESTION_VALUE_PREFIX: '[quiz_question]',
} as const;
