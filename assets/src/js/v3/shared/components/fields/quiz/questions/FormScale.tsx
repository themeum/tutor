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

interface ScaleConfig {
  min: number;
  max: number;
  step: number;
  defaultValue: number;
  pxPerUnit: number;
  labelEvery: number;
  minorTickEvery: number;
  precision: number;
}

interface ScaleData {
  value: number;
  config: ScaleConfig;
}

type ScaleField = 'min' | 'max' | 'labelEvery' | 'value';

interface ScaleFieldError {
  field: ScaleField;
  message: string;
}

function normalizeScaleConfig(config: ScaleConfig): ScaleConfig {
  return {
    ...config,
    step: 1,
  };
}

function parseStoredScaleData(value: string): ScaleData | null {
  if (!value || typeof value !== 'string') return null;
  try {
    const data = JSON.parse(value) as Partial<ScaleData>;
    if (typeof data.value === 'number' && data.config) {
      return {
        value: data.value,
        config: normalizeScaleConfig({
          min: data.config.min ?? 0,
          max: data.config.max ?? 100,
          step: 1,
          defaultValue: data.config.defaultValue ?? 50,
          pxPerUnit: data.config.pxPerUnit ?? 10,
          labelEvery: data.config.labelEvery ?? 10,
          minorTickEvery: data.config.minorTickEvery ?? 5,
          precision: data.config.precision ?? 0,
        }),
      };
    }
  } catch {
    // ignore
  }
  return null;
}

const toScaleInputs = (data: ScaleData) => ({
  min: String(data.config.min),
  max: String(data.config.max),
  labelEvery: String(data.config.labelEvery),
  value: String(data.value),
});

const clearScaleRangeValidationError = (setValidationError: FormScaleProps['setValidationError']) => {
  setValidationError?.((prev) => {
    const isScaleRangeError =
      prev?.type === 'question' &&
      [scaleRangeErrorMessage, scaleLabelEntryRangeErrorMessage, scaleCorrectValueRangeErrorMessage].includes(
        prev?.message,
      );

    return isScaleRangeError ? null : prev;
  });
};

const defaultScaleData: ScaleData = {
  value: 50,
  config: normalizeScaleConfig({
    min: 0,
    max: 100,
    step: 1,
    defaultValue: 50,
    pxPerUnit: 10,
    labelEvery: 10,
    minorTickEvery: 5,
    precision: 0,
  }),
};

const FormScale = ({ field, setValidationError }: FormScaleProps) => {
  const option = field.value;
  const storedScalePayload = option?.answer_two_gap_match ?? '';

  const baseScaleData = useRef<ScaleData>(parseStoredScaleData(storedScalePayload) || defaultScaleData);
  const lastPersistedPayloadRef = useRef<string | null>(storedScalePayload || null);

  const [inputs, setInputs] = useState(() => toScaleInputs(baseScaleData.current));
  const [lastRangeField, setLastRangeField] = useState<'min' | 'max'>('max');

  // If the stored answer payload was updated externally (e.g. form reset, switching question),
  // sync state during render without an asynchronous effect.
  if (storedScalePayload !== lastPersistedPayloadRef.current) {
    lastPersistedPayloadRef.current = storedScalePayload;
    const parsed = parseStoredScaleData(storedScalePayload);
    if (parsed) {
      baseScaleData.current = parsed;
      setInputs(toScaleInputs(parsed));
    }
  }

  const parsedMin = parseFloat(inputs.min);
  const parsedMax = parseFloat(inputs.max);
  const parsedLabelEvery = parseFloat(inputs.labelEvery);
  const parsedValue = parseFloat(inputs.value);

  const currentScaleData: ScaleData = {
    value: Number.isFinite(parsedValue) ? parsedValue : baseScaleData.current.value,
    config: normalizeScaleConfig({
      ...baseScaleData.current.config,
      min: Number.isFinite(parsedMin) ? parsedMin : baseScaleData.current.config.min,
      max: Number.isFinite(parsedMax) ? parsedMax : baseScaleData.current.config.max,
      labelEvery: Number.isFinite(parsedLabelEvery) ? parsedLabelEvery : baseScaleData.current.config.labelEvery,
    }),
  };

  const config = currentScaleData.config;

  const updateOption = useCallback(
    (updated: QuizQuestionOption) => {
      field.onChange(updated);
    },
    [field],
  );

  const saveScaleData = useCallback(
    (data: ScaleData) => {
      if (!option) return;

      const serializedPayload = JSON.stringify(data);
      if (serializedPayload === lastPersistedPayloadRef.current) return;

      lastPersistedPayloadRef.current = serializedPayload;
      baseScaleData.current = data;
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
      setInputs((prev) => ({ ...prev, [fieldKey]: rawValue }));
      if (fieldKey === 'min' || fieldKey === 'max') {
        setLastRangeField(fieldKey);
      }
      clearScaleRangeValidationError(setValidationError);

      const parsed = parseFloat(rawValue);
      if (!Number.isFinite(parsed)) return;

      if (fieldKey === 'value') {
        if (parsed === baseScaleData.current.value) return;

        const nextScaleData = { ...baseScaleData.current, value: parsed };
        saveScaleData(nextScaleData);
        return;
      }

      if (parsed === baseScaleData.current.config[fieldKey]) return;

      const nextScaleData = {
        ...baseScaleData.current,
        config: normalizeScaleConfig({ ...baseScaleData.current.config, [fieldKey]: parsed }),
      };
      saveScaleData(nextScaleData);
    },
    [saveScaleData, setValidationError],
  );

  const handleInputBlur = useCallback((fieldKey: ScaleField, rawValue: string) => {
    if (rawValue.trim() === '') {
      return;
    }

    const committedValue = fieldKey === 'value' ? baseScaleData.current.value : baseScaleData.current.config[fieldKey];
    setInputs((prev) => ({ ...prev, [fieldKey]: String(committedValue) }));
  }, []);

  const scaleError = getScaleValidationError(config, currentScaleData.value);
  const fieldError: ScaleFieldError | null = scaleError
    ? { field: scaleError.field === 'range' ? lastRangeField : scaleError.field, message: scaleError.message }
    : null;

  const renderFieldError = (fieldKey: ScaleField) => {
    const message =
      inputs[fieldKey].trim() === ''
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

  const isRangeInvalid = config.max <= config.min;

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
                isRangeInvalid
                  ? undefined
                  : sprintf(
                      // translators: %1$s is the minimum scale value, %2$s is the maximum scale value
                      __('Between %1$s and %2$s', __TUTOR_TEXT_DOMAIN__),
                      String(config.min),
                      String(config.max),
                    )
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
