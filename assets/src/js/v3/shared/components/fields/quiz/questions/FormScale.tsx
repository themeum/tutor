import { useCallback, useRef, useState } from 'react';
import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import TextInput from '@TutorShared/atoms/TextInput';

import {
  borderRadius,
  Breakpoint,
  colorTokens,
  fontFamily,
  fontSize,
  fontWeight,
  letterSpacing,
  lineHeight,
  spacing,
} from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import type { FormControllerProps } from '@TutorShared/utils/form';
import {
  calculateQuizDataStatus,
  getScaleValidationError,
  scaleCorrectValueRangeErrorMessage,
  scaleLabelEntryRangeErrorMessage,
  scaleRangeErrorMessage,
} from '@TutorShared/utils/quiz';
import { styleUtils } from '@TutorShared/utils/style-utils';
import {
  type ID,
  QuizDataStatus,
  type QuizQuestionOption,
  type QuizValidationErrorType,
} from '@TutorShared/utils/types';

interface FormScaleProps extends FormControllerProps<QuizQuestionOption> {
  questionId: ID;
  validationError?: {
    message: string;
    type: QuizValidationErrorType;
  } | null;
  setValidationError?: React.Dispatch<
    React.SetStateAction<{
      message: string;
      type: QuizValidationErrorType;
    } | null>
  >;
}

interface ScaleInputs {
  min: string;
  max: string;
  labelEvery: string;
  value: string;
}

interface StoredScalePayload {
  value?: number | null;
  config?: {
    min?: number | null;
    max?: number | null;
    step?: number;
    defaultValue?: number;
    pxPerUnit?: number;
    labelEvery?: number | null;
    minorTickEvery?: number;
    precision?: number;
  };
}

type ScaleField = 'min' | 'max' | 'labelEvery' | 'value';

interface ScaleFieldError {
  field: ScaleField;
  message: string;
}

const defaultInputs: ScaleInputs = {
  min: '0',
  max: '100',
  labelEvery: '10',
  value: '50',
};

function parseStoredScaleInputs(value: string): ScaleInputs | null {
  if (!value || typeof value !== 'string') return null;
  try {
    const data = JSON.parse(value) as StoredScalePayload;
    if (data && typeof data === 'object') {
      return {
        min: typeof data.config?.min === 'number' && Number.isFinite(data.config.min) ? String(data.config.min) : '',
        max: typeof data.config?.max === 'number' && Number.isFinite(data.config.max) ? String(data.config.max) : '',
        labelEvery:
          typeof data.config?.labelEvery === 'number' && Number.isFinite(data.config.labelEvery)
            ? String(data.config.labelEvery)
            : '',
        value: typeof data.value === 'number' && Number.isFinite(data.value) ? String(data.value) : '',
      };
    }
  } catch {
    // ignore
  }
  return null;
}

const clearScaleRangeValidationError = (setValidationError: FormScaleProps['setValidationError']) => {
  setValidationError?.((prev) => {
    const isScaleRangeError =
      prev?.type === 'question' &&
      [
        __('This field is required', __TUTOR_TEXT_DOMAIN__),
        scaleRangeErrorMessage,
        scaleLabelEntryRangeErrorMessage,
        scaleCorrectValueRangeErrorMessage,
      ].includes(prev?.message);

    return isScaleRangeError ? null : prev;
  });
};

const FormScale = ({ field, setValidationError }: FormScaleProps) => {
  const option = field.value;
  const storedScalePayload = option?.answer_two_gap_match ?? '';

  const lastPersistedPayloadRef = useRef<string | null>(storedScalePayload || null);
  const [inputs, setInputs] = useState<ScaleInputs>(() => parseStoredScaleInputs(storedScalePayload) || defaultInputs);
  const [lastRangeField, setLastRangeField] = useState<'min' | 'max'>('max');

  // If the stored answer payload was updated externally (e.g. form reset, switching question),
  // sync state during render without an asynchronous effect.
  if (storedScalePayload !== lastPersistedPayloadRef.current) {
    lastPersistedPayloadRef.current = storedScalePayload;
    const parsed = parseStoredScaleInputs(storedScalePayload);
    if (parsed) {
      setInputs(parsed);
    }
  }

  const updateOption = useCallback(
    (updated: QuizQuestionOption) => {
      field.onChange(updated);
    },
    [field],
  );

  const saveScaleData = useCallback(
    (currentInputs: ScaleInputs) => {
      if (!option) return;

      const pMin =
        currentInputs.min.trim() === '' || currentInputs.min.trim() === '-' ? null : parseFloat(currentInputs.min);
      const pMax =
        currentInputs.max.trim() === '' || currentInputs.max.trim() === '-' ? null : parseFloat(currentInputs.max);
      const pLabel = currentInputs.labelEvery.trim() === '' ? null : parseFloat(currentInputs.labelEvery);
      const pVal =
        currentInputs.value.trim() === '' || currentInputs.value.trim() === '-'
          ? null
          : parseFloat(currentInputs.value);

      const validMin = pMin !== null && Number.isFinite(pMin) ? pMin : null;
      const validMax = pMax !== null && Number.isFinite(pMax) ? pMax : null;
      const validLabel = pLabel !== null && Number.isFinite(pLabel) ? pLabel : null;
      const validVal = pVal !== null && Number.isFinite(pVal) ? pVal : null;

      const payload: StoredScalePayload = {
        value: validVal,
        config: {
          min: validMin,
          max: validMax,
          step: 1,
          defaultValue: validVal ?? (validMin !== null && validMax !== null ? (validMin + validMax) / 2 : 50),
          pxPerUnit: 10,
          labelEvery: validLabel,
          minorTickEvery: 5,
          precision: 0,
        },
      };

      const serializedPayload = JSON.stringify(payload);
      if (serializedPayload === lastPersistedPayloadRef.current) return;

      lastPersistedPayloadRef.current = serializedPayload;
      updateOption({
        ...option,
        ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
          _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
        }),
        answer_two_gap_match: serializedPayload,
        is_saved: true,
      });
    },
    [option, updateOption],
  );

  const handleInputChange = useCallback(
    (fieldKey: ScaleField, rawValue: string) => {
      const nextInputs = { ...inputs, [fieldKey]: rawValue };
      setInputs(nextInputs);
      if (fieldKey === 'min' || fieldKey === 'max') {
        setLastRangeField(fieldKey);
      }
      clearScaleRangeValidationError(setValidationError);
      saveScaleData(nextInputs);
    },
    [inputs, saveScaleData, setValidationError],
  );

  const handleInputBlur = useCallback(
    (fieldKey: ScaleField, rawValue: string) => {
      const trimmed = rawValue.trim();
      if (trimmed === '' || trimmed === '-') {
        return;
      }

      const parsed = parseFloat(trimmed);
      if (Number.isFinite(parsed)) {
        setInputs((prev) => {
          if (prev[fieldKey] === String(parsed)) return prev;
          const next = { ...prev, [fieldKey]: String(parsed) };
          saveScaleData(next);
          return next;
        });
      }
    },
    [saveScaleData],
  );

  const parsedMin = parseFloat(inputs.min);
  const parsedMax = parseFloat(inputs.max);
  const parsedLabelEvery = parseFloat(inputs.labelEvery);
  const parsedValue = parseFloat(inputs.value);

  const hasAllNumbers =
    Number.isFinite(parsedMin) &&
    Number.isFinite(parsedMax) &&
    Number.isFinite(parsedLabelEvery) &&
    Number.isFinite(parsedValue);

  const scaleError = hasAllNumbers
    ? getScaleValidationError({ min: parsedMin, max: parsedMax, labelEvery: parsedLabelEvery }, parsedValue)
    : null;

  const fieldError: ScaleFieldError | null = scaleError
    ? { field: scaleError.field === 'range' ? lastRangeField : scaleError.field, message: scaleError.message }
    : null;

  const renderFieldError = (fieldKey: ScaleField) => {
    const rawVal = inputs[fieldKey].trim();
    const isFieldEmpty = rawVal === '' || rawVal === '-';
    const message = isFieldEmpty
      ? __('This field is required', __TUTOR_TEXT_DOMAIN__)
      : fieldError?.field === fieldKey
        ? fieldError.message
        : null;

    return message ? (
      <div css={styles.errorMessage} role="alert">
        <SVGIcon name="info" height={20} width={20} />
        <p>{message}</p>
      </div>
    ) : null;
  };

  if (!option) {
    return null;
  }

  const isRangeValid = Number.isFinite(parsedMin) && Number.isFinite(parsedMax) && parsedMax > parsedMin;

  return (
    <div css={styles.wrapper}>
      <div css={styles.card}>
        <div css={styles.answerHeader}>
          <span css={styles.answerHeaderTitle}>{__('Scale range', __TUTOR_TEXT_DOMAIN__)}</span>
        </div>
        {/* Scale Configuration */}
        <div css={styles.configSection}>
          <div css={styles.configGrid}>
            <div css={styles.configField}>
              <TextInput
                label={__('Min value', __TUTOR_TEXT_DOMAIN__)}
                type="number"
                allowNegative
                whole
                size="small"
                value={inputs.min}
                onChange={(v) => handleInputChange('min', v)}
                onBlur={(v) => handleInputBlur('min', v)}
              />
              {renderFieldError('min')}
            </div>
            <div css={styles.configField}>
              <TextInput
                label={__('Max value', __TUTOR_TEXT_DOMAIN__)}
                type="number"
                allowNegative
                whole
                size="small"
                value={inputs.max}
                onChange={(v) => handleInputChange('max', v)}
                onBlur={(v) => handleInputBlur('max', v)}
              />
              {renderFieldError('max')}
            </div>
            <div css={styles.configField}>
              <TextInput
                label={__('Label entry', __TUTOR_TEXT_DOMAIN__)}
                type="number"
                whole
                size="small"
                value={inputs.labelEvery}
                onChange={(v) => handleInputChange('labelEvery', v)}
                onBlur={(v) => handleInputBlur('labelEvery', v)}
              />
              {renderFieldError('labelEvery')}
            </div>
          </div>
        </div>
      </div>

      <div css={styles.card}>
        <div css={styles.answerSection}>
          {/* Answer Configuration */}
          <div css={styles.configField}>
            <TextInput
              label={__('Correct Value', __TUTOR_TEXT_DOMAIN__)}
              type="number"
              allowNegative
              whole
              size="small"
              value={inputs.value}
              onChange={(v) => handleInputChange('value', v)}
              onBlur={(v) => handleInputBlur('value', v)}
              placeholder={
                isRangeValid
                  ? sprintf(
                      // translators: %1$s is the minimum scale value, %2$s is the maximum scale value
                      __('Between %1$s and %2$s', __TUTOR_TEXT_DOMAIN__),
                      inputs.min,
                      inputs.max,
                    )
                  : undefined
              }
            />
            {renderFieldError('value')}
          </div>
        </div>
      </div>
    </div>
  );
};

export default FormScale;

const styles = {
  wrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[24]};

    ${Breakpoint.smallMobile} {
      padding-left: ${spacing[8]};
    }
  `,
  card: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
    padding: ${spacing[16]};
    background: ${colorTokens.surface.tutor};
    border-radius: ${borderRadius.input};
  `,
  answerHeader: css`
    ${styleUtils.display.flex('row')};
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[12]};
  `,
  answerHeaderTitle: css`
    font-family: ${fontFamily.sfProDisplay};
    font-weight: ${fontWeight.medium};
    font-size: ${fontSize[15]};
    line-height: ${lineHeight[24]};
    letter-spacing: ${letterSpacing.normal};
    color: ${colorTokens.text.title};
  `,
  configSection: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
  `,
  answerSection: css`
    ${styleUtils.display.flex('column')};
  `,
  configGrid: css`
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: ${spacing[8]};

    ${Breakpoint.smallTablet} {
      grid-template-columns: 1fr;
    }
  `,
  configField: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
  `,
  errorMessage: css`
    display: flex;
    gap: ${spacing[4]};
    ${typography.small()};
    color: ${colorTokens.text.error};
    align-items: flex-start;

    svg {
      flex-shrink: 0;
      color: ${colorTokens.icon.error};
    }
  `,
};
