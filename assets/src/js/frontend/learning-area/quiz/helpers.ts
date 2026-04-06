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

const hasRenderableAnswerValue = (value: unknown): boolean => {
  if (value === '' || value === null || value === undefined) {
    return false;
  }

  if (Array.isArray(value) && value.length === 0) {
    return false;
  }

  return true;
};

const isScaleFieldName = (formId: string, fieldName: string): boolean => {
  if (!formId || !fieldName || typeof document === 'undefined') {
    return false;
  }

  const formElement = document.getElementById(formId);
  if (!formElement) {
    return false;
  }

  const escapedName =
    typeof CSS !== 'undefined' && typeof CSS.escape === 'function' ? CSS.escape(fieldName) : fieldName;
  const fieldElements = Array.from(formElement.querySelectorAll<HTMLElement>(`[name="${escapedName}"]`));

  if (!fieldElements.length) {
    return false;
  }

  return fieldElements.some(
    (element) => element.closest('.tutor-quiz-question')?.getAttribute('data-question') === 'scale',
  );
};

const scaleInteractionByForm = new Map<
  string,
  {
    initialValueByField: Record<string, string>;
    interactedByField: Record<string, boolean>;
  }
>();

const getScaleInteractionState = (formId: string) => {
  const existing = scaleInteractionByForm.get(formId);
  if (existing) {
    return existing;
  }

  const initialState = {
    initialValueByField: {} as Record<string, string>,
    interactedByField: {} as Record<string, boolean>,
  };

  scaleInteractionByForm.set(formId, initialState);
  return initialState;
};

export const hasAttemptedFieldValue = ({
  formId,
  fieldName,
  value,
}: {
  formId?: string;
  fieldName: string;
  value: unknown;
}): boolean => {
  if (!hasRenderableAnswerValue(value)) {
    return false;
  }

  // Scale questions can have prefilled defaults; count only after at least one value change.
  if (formId && isScaleFieldName(formId, fieldName)) {
    const state = getScaleInteractionState(formId);
    const currentValue = String(value);

    if (!(fieldName in state.initialValueByField)) {
      state.initialValueByField[fieldName] = currentValue;
      state.interactedByField[fieldName] = false;
      return false;
    }

    if (!state.interactedByField[fieldName] && state.initialValueByField[fieldName] !== currentValue) {
      state.interactedByField[fieldName] = true;
    }

    return state.interactedByField[fieldName];
  }

  return true;
};

export const getAttemptedQuestionCount = (values: Record<string, unknown>, options?: { formId?: string }): number => {
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

      return hasAttemptedFieldValue({
        formId: options?.formId,
        fieldName: key,
        value: val,
      });
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

  const formState = form.getFormState(formId);
  const values = formState.values ?? {};
  return getAttemptedQuestionCount(values, { formId });
};
