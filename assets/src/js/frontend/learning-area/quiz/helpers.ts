import { QUIZ_LAYOUT_KEYS, QUIZ_REVEAL_CONFIG } from './constants';

export const decodeHexString = (encoded: string): string | null => {
  const bytes = encoded.match(/.{1,2}/g);
  if (!bytes) {
    return null;
  }
  return bytes.map((byte) => String.fromCharCode(parseInt(byte, 16))).join('');
};

export const decodeExplanationContent = (encoded: string): string => {
  if (!encoded) {
    return '';
  }
  try {
    const hexDecoded = decodeHexString(encoded);
    if (hexDecoded) {
      return decodeURIComponent(hexDecoded);
    }
  } catch {
    // fall through to legacy decode
  }
  try {
    return decodeURIComponent(encoded.split('').reverse().join(''));
  } catch {
    return '';
  }
};

export const revealQuestionWithAnswers = (wrapper: HTMLElement, revealAnswerIds: number[]): void => {
  const question = wrapper.querySelector(QUIZ_REVEAL_CONFIG.QUESTION_SELECTOR) as HTMLElement | null;
  if (!question) {
    return;
  }
  if (question.getAttribute(QUIZ_REVEAL_CONFIG.DATA_REVEALED_ATTR) === '1') {
    return;
  }

  const explanationTrigger = wrapper.querySelector<HTMLButtonElement>(QUIZ_REVEAL_CONFIG.EXPLANATION_TRIGGER_SELECTOR);
  const explanation = wrapper.querySelector<HTMLElement>(QUIZ_REVEAL_CONFIG.EXPLANATION_SELECTOR);
  const explanationBody = explanation?.querySelector<HTMLElement>(QUIZ_REVEAL_CONFIG.EXPLANATION_BODY_SELECTOR) ?? null;
  const encodedExplanation = explanation?.dataset?.[QUIZ_REVEAL_CONFIG.EXPLANATION_CONTENT_DATASET] ?? '';
  if (explanationBody && encodedExplanation && !explanationBody.innerHTML.trim()) {
    const decoded = decodeExplanationContent(encodedExplanation);
    if (decoded) {
      explanationBody.innerHTML = decoded;
    }
  }
  if (explanationTrigger && explanationTrigger.getAttribute('aria-expanded') !== 'true') {
    explanationTrigger.click();
  }

  const inputs = Array.from(question.querySelectorAll<HTMLInputElement>('input[type="radio"], input[type="checkbox"]'));
  const selectedAnswerIds = new Set<number>();
  const correctAnswerIds = new Set<number>();

  inputs.forEach((input) => {
    const option = input.closest(QUIZ_REVEAL_CONFIG.OPTION_SELECTOR) as HTMLElement | null;
    if (!option) {
      return;
    }

    const answerId = Number(input.value);
    if (Number.isNaN(answerId)) {
      return;
    }

    if (input.checked) {
      selectedAnswerIds.add(answerId);
    }

    const isCorrect = revealAnswerIds.includes(answerId);
    if (isCorrect) {
      correctAnswerIds.add(answerId);
    }

    if (isCorrect) {
      option.setAttribute(QUIZ_REVEAL_CONFIG.DATA_OPTION_ATTR, QUIZ_REVEAL_CONFIG.DATA_OPTION_CORRECT);
    } else if (input.checked) {
      option.setAttribute(QUIZ_REVEAL_CONFIG.DATA_OPTION_ATTR, QUIZ_REVEAL_CONFIG.DATA_OPTION_INCORRECT);
    }

    input.disabled = true;
  });

  const hasMatchingSelection =
    selectedAnswerIds.size > 0 &&
    selectedAnswerIds.size === correctAnswerIds.size &&
    Array.from(selectedAnswerIds).every((id) => correctAnswerIds.has(id));

  question.setAttribute(
    QUIZ_REVEAL_CONFIG.DATA_RESULT_ATTR,
    hasMatchingSelection ? QUIZ_REVEAL_CONFIG.DATA_OPTION_CORRECT : QUIZ_REVEAL_CONFIG.DATA_OPTION_INCORRECT,
  );
  question.setAttribute(QUIZ_REVEAL_CONFIG.DATA_REVEALED_ATTR, '1');
};

export const getAttemptedQuestionCount = (values: Record<string, unknown>): number => {
  const questionIdsEntry = Object.entries(values).find(([key]) => key.includes('[quiz_question_ids]'));
  if (!questionIdsEntry) {
    return 0;
  }

  const questionIds = Array.isArray(questionIdsEntry[1]) ? questionIdsEntry[1] : [];
  let count = 0;

  for (const id of questionIds) {
    const needle = `${QUIZ_LAYOUT_KEYS.QUESTION_VALUE_PREFIX}[${id}]`;
    const hasAnswer = Object.entries(values).some(([key, val]) => {
      if (!key.includes(needle)) {
        return false;
      }

      if (val === '' || val === null || val === undefined) {
        return false;
      }
      if (Array.isArray(val) && val.length === 0) {
        return false;
      }
      return true;
    });

    if (hasAnswer) {
      count++;
    }
  }

  return count;
};

export const getAttemptedQuestionCountFromForm = (formId: string): number => {
  const form = window.TutorCore?.form;
  if (!form || !formId || !form.hasForm(formId)) {
    return 0;
  }

  const values = form.getFormState(formId).values ?? {};
  return getAttemptedQuestionCount(values);
};
