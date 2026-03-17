import { QUIZ_REVEAL_CONFIG } from './constants';

export const decodeHexString = (encoded: string): string | null => {
  const bytes = encoded.match(/.{1,2}/g);
  if (!bytes) {
    return null;
  }
  return bytes.map((byte) => String.fromCharCode(parseInt(byte, 16))).join('');
};

/**
 * Decode scale correct values from encoded script (same pattern as tutor-quiz-context for choice types).
 */
export const getScaleCorrectContext = (): Record<string, number> => {
  const script = document.getElementById(QUIZ_REVEAL_CONFIG.SCALE_CONTEXT_ID);
  if (!script?.textContent?.trim()) {
    return {};
  }
  try {
    const decoded = decodeHexString(script.textContent.trim());
    if (!decoded) {
      return {};
    }
    const parsed = JSON.parse(decoded) as Record<string, number>;
    return typeof parsed === 'object' && parsed !== null ? parsed : {};
  } catch {
    return {};
  }
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

  const questionType = question.dataset?.question ?? '';
  // Scale: show correct value from encoded context (same as true/false), then reveal wrapper.
  if (questionType === 'scale') {
    const scaleEl = wrapper.querySelector<HTMLElement>('.tutor-scale-question');
    const questionId = scaleEl?.dataset?.questionId;
    const refWrapper = wrapper.querySelector<HTMLElement>('.tutor-scale-reference-wrapper');
    const valueEl = refWrapper?.querySelector<HTMLElement>('.tutor-scale-reference-value');
    if (refWrapper && questionId !== undefined) {
      const scaleContext = getScaleCorrectContext();
      const correctValue = scaleContext[questionId];
      if (valueEl && typeof correctValue === 'number' && !Number.isNaN(correctValue)) {
        valueEl.textContent = String(correctValue);
      }
      refWrapper.classList.remove('tutor-d-none');
      refWrapper.style.display = '';
    }
    question.setAttribute(QUIZ_REVEAL_CONFIG.DATA_REVEALED_ATTR, '1');
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
