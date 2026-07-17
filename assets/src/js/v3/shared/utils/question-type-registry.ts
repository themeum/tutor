import { __ } from '@wordpress/i18n';

import { type IconCollection } from '@TutorShared/icons/types';
import { type QuizQuestionType } from '@TutorShared/utils/types';

export interface QuestionTypeConfig {
  label: string;
  icon: IconCollection;
  isPro: boolean;
  category: 'basic' | 'interactive';
  legacyExcluded?: true;
  aiSupported?: true;
  supportsRandomize?: true;
}

type RegistryQuestionType = Exclude<QuizQuestionType, 'single_choice' | 'image_matching'>;

const questionTypeRegistry: Record<RegistryQuestionType, QuestionTypeConfig> = {
  true_false: {
    label: __('True/False', __TUTOR_TEXT_DOMAIN__),
    icon: 'quizTrueFalse',
    isPro: false,
    category: 'basic',
    aiSupported: true,
  },
  multiple_choice: {
    label: __('Multiple Choice', __TUTOR_TEXT_DOMAIN__),
    icon: 'quizMultiChoice',
    isPro: false,
    category: 'basic',
    aiSupported: true,
    supportsRandomize: true,
  },
  open_ended: {
    label: __('Open Ended/Essay', __TUTOR_TEXT_DOMAIN__),
    icon: 'quizEssay',
    isPro: false,
    category: 'basic',
    aiSupported: true,
  },
  fill_in_the_blank: {
    label: __('Fill in the Blanks', __TUTOR_TEXT_DOMAIN__),
    icon: 'quizFillInTheBlanks',
    isPro: false,
    category: 'basic',
  },
  short_answer: {
    label: __('Short Answer', __TUTOR_TEXT_DOMAIN__),
    icon: 'quizShortAnswer',
    isPro: true,
    category: 'basic',
    aiSupported: true,
  },
  matching: {
    label: __('Matching', __TUTOR_TEXT_DOMAIN__),
    icon: 'quizImageMatching',
    isPro: true,
    category: 'interactive',
    supportsRandomize: true,
  },
  image_answering: {
    label: __('Image Answering', __TUTOR_TEXT_DOMAIN__),
    icon: 'quizImageAnswer',
    supportsRandomize: true,
    isPro: true,
    category: 'interactive',
  },
  ordering: {
    label: __('Ordering', __TUTOR_TEXT_DOMAIN__),
    icon: 'quizOrdering',
    isPro: true,
    category: 'interactive',
  },
  draw_image: {
    label: __('Image Marking', __TUTOR_TEXT_DOMAIN__),
    icon: 'quizMarkInTheImage',
    isPro: true,
    category: 'interactive',
    legacyExcluded: true,
  },
  scale: {
    label: __('Range', __TUTOR_TEXT_DOMAIN__),
    icon: 'quizRange',
    isPro: true,
    category: 'interactive',
    legacyExcluded: true,
  },
  pin_image: {
    label: __('Pin', __TUTOR_TEXT_DOMAIN__),
    icon: 'quizPin',
    isPro: true,
    category: 'interactive',
    legacyExcluded: true,
  },
  coordinates: {
    label: __('Graph', __TUTOR_TEXT_DOMAIN__),
    icon: 'quizGraph',
    isPro: true,
    category: 'interactive',
    legacyExcluded: true,
  },
  puzzle: {
    label: __('Puzzle', __TUTOR_TEXT_DOMAIN__),
    icon: 'quizPuzzle',
    isPro: true,
    category: 'interactive',
    legacyExcluded: true,
  },
  h5p: {
    label: __('H5P', __TUTOR_TEXT_DOMAIN__),
    icon: 'quizH5p',
    isPro: false,
    category: 'interactive',
  },
};

export const legacyQuestionTypeMap: Partial<Record<QuizQuestionType, RegistryQuestionType>> = {
  single_choice: 'multiple_choice',
  image_matching: 'matching',
};

const isSelectableQuestionType = (type: RegistryQuestionType) => type !== 'h5p';

export const basicQuestionTypes = Object.entries(questionTypeRegistry)
  .filter(([type, config]) => config.category === 'basic' && isSelectableQuestionType(type as RegistryQuestionType))
  .map(([type, config]) => ({ value: type as RegistryQuestionType, ...config }));

export const interactiveQuestionTypes = Object.entries(questionTypeRegistry)
  .filter(
    ([type, config]) => config.category === 'interactive' && isSelectableQuestionType(type as RegistryQuestionType),
  )
  .map(([type, config]) => ({ value: type as RegistryQuestionType, ...config }));

export const allQuestionTypes = Object.entries(questionTypeRegistry)
  .filter(([type]) => isSelectableQuestionType(type as RegistryQuestionType))
  .map(([type, config]) => ({
    value: type as RegistryQuestionType,
    ...config,
  }));

export const aiQuestionTypes = Object.entries(questionTypeRegistry)
  .filter(([, config]) => config.aiSupported)
  .map(([type, config]) => ({ value: type as RegistryQuestionType, ...config }));

export function getQuestionTypeConfig(type: QuizQuestionType): QuestionTypeConfig | undefined {
  if (type in questionTypeRegistry) {
    return questionTypeRegistry[type as RegistryQuestionType];
  }
  const mapped = legacyQuestionTypeMap[type];
  if (mapped) {
    return questionTypeRegistry[mapped];
  }
  return undefined;
}

export function getEffectiveQuestionType(type: QuizQuestionType): RegistryQuestionType {
  if (type in questionTypeRegistry) {
    return type as RegistryQuestionType;
  }
  return legacyQuestionTypeMap[type] ?? (type as RegistryQuestionType);
}
