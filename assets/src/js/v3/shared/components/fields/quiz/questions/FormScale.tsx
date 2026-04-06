/**
 * Form field for Scale quiz question type (instructor sets target value on scale).
 *
 * @package Tutor
 * @since 4.0.0
 */

import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useCallback, useEffect, useState } from 'react';

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
import type { FormControllerProps } from '@TutorShared/utils/form';
import { calculateQuizDataStatus } from '@TutorShared/utils/quiz';
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

const DEFAULT_SCALE_MIN = 1;

function normalizeScaleConfig(cfg: ScaleConfig, changedField?: keyof ScaleConfig): ScaleConfig {
  const step = cfg.step > 0 ? cfg.step : 1;
  let min = Math.max(DEFAULT_SCALE_MIN, cfg.min);
  let max = cfg.max;

  if (max <= min) {
    if (changedField === 'max') {
      min = Math.max(DEFAULT_SCALE_MIN, max - step);
      if (min >= max) {
        max = min + step;
      }
    } else {
      max = min + step;
    }
  }

  return {
    ...cfg,
    min,
    max,
    step,
  };
}

function parseStoredScaleData(value: string): ScaleData | null {
  if (!value || typeof value !== 'string') return null;
  try {
    const data = JSON.parse(value) as Partial<ScaleData>;
    if (typeof data.value === 'number' && data.config) {
      const rawConfig: ScaleConfig = {
        min: data.config.min ?? DEFAULT_SCALE_MIN,
        max: data.config.max ?? 100,
        step: data.config.step ?? 1,
        defaultValue: data.config.defaultValue ?? 50,
        pxPerUnit: data.config.pxPerUnit ?? 10,
        labelEvery: data.config.labelEvery ?? 10,
        minorTickEvery: data.config.minorTickEvery ?? 5,
        precision: data.config.precision ?? 0,
      };
      const config = normalizeScaleConfig(rawConfig);
      return {
        value: Math.max(config.min, Math.min(config.max, data.value)),
        config,
      };
    }
  } catch {
    // ignore
  }
  return null;
}

const FormScale = ({ field }: FormScaleProps) => {
  const option = field.value;
  const [scaleData, setScaleData] = useState<ScaleData>(() => {
    const parsed = parseStoredScaleData(option?.answer_two_gap_match ?? '');
    return (
      parsed || {
        value: 50,
        config: {
          min: DEFAULT_SCALE_MIN,
          max: 100,
          step: 1,
          defaultValue: 50,
          pxPerUnit: 10,
          labelEvery: 10,
          minorTickEvery: 5,
          precision: 0,
        },
      }
    );
  });

  const [config, setConfig] = useState<ScaleConfig>(scaleData.config);

  useEffect(() => {
    const parsed = parseStoredScaleData(option?.answer_two_gap_match ?? '');
    if (parsed) {
      setScaleData(parsed);
      setConfig(parsed.config);
    }
  }, [option?.answer_two_gap_match]);

  const updateOption = useCallback(
    (updated: QuizQuestionOption) => {
      field.onChange(updated);
    },
    [field],
  );

  const saveScaleData = useCallback(
    (data: ScaleData) => {
      if (!option) return;

      const json = JSON.stringify(data);
      updateOption({
        ...option,
        ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
          _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
        }),
        answer_two_gap_match: json,
        is_saved: true,
      });
    },
    [option, updateOption],
  );

  const handleConfigChange = useCallback(
    (fieldKey: keyof ScaleConfig, value: number) => {
      const newConfig = normalizeScaleConfig({ ...config, [fieldKey]: value }, fieldKey);
      setConfig(newConfig);

      const newScaleData = {
        ...scaleData,
        config: newConfig,
        value: Math.max(newConfig.min, Math.min(newConfig.max, scaleData.value)),
      };
      setScaleData(newScaleData);
      saveScaleData(newScaleData);
    },
    [config, scaleData, saveScaleData],
  );

  const parseConfigNumber = (raw: string, fallback: number) => {
    const n = parseFloat(String(raw));
    return Number.isFinite(n) ? n : fallback;
  };

  const handleValueChange = useCallback(
    (value: number) => {
      const clamped = Math.max(config.min, Math.min(config.max, value));
      const newScaleData = { ...scaleData, value: clamped };
      setScaleData(newScaleData);
      saveScaleData(newScaleData);
    },
    [config.min, config.max, scaleData, saveScaleData],
  );

  if (!option) {
    return null;
  }

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
              <label css={styles.configLabel}>{__('Min value', __TUTOR_TEXT_DOMAIN__)}</label>
              <TextInput
                type="number"
                size="small"
                value={config.min}
                onChange={(v) => handleConfigChange('min', parseConfigNumber(v, DEFAULT_SCALE_MIN))}
              />
            </div>
            <div css={styles.configField}>
              <label css={styles.configLabel}>{__('Max value', __TUTOR_TEXT_DOMAIN__)}</label>
              <TextInput
                type="number"
                size="small"
                value={config.max}
                onChange={(v) => handleConfigChange('max', parseConfigNumber(v, 100))}
              />
            </div>
            <div css={styles.configField}>
              <label css={styles.configLabel}>{__('Steps', __TUTOR_TEXT_DOMAIN__)}</label>
              <TextInput
                type="number"
                size="small"
                value={config.step}
                onChange={(v) => handleConfigChange('step', parseConfigNumber(v, 1))}
              />
            </div>
            <div css={styles.configField}>
              <label css={styles.configLabel}>{__('Label entry', __TUTOR_TEXT_DOMAIN__)}</label>
              <TextInput
                type="number"
                size="small"
                value={config.labelEvery}
                onChange={(v) => handleConfigChange('labelEvery', parseConfigNumber(v, 10))}
              />
            </div>
          </div>
        </div>
      </div>

      <div css={styles.card}>
        <div css={styles.answerSection}>
          {/* Answer Configuration */}
          <div css={styles.configField}>
            <label css={styles.configLabel}>{__('Correct Value', __TUTOR_TEXT_DOMAIN__)}</label>
            <TextInput
              type="number"
              size="small"
              value={scaleData.value}
              onChange={(v) => handleValueChange(parseConfigNumber(v, config.min))}
            />
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
  configLabel: css`
    font-family: ${fontFamily.sfProDisplay};
    font-weight: ${fontWeight.regular};
    font-size: ${fontSize[15]};
    line-height: ${lineHeight[24]};
    letter-spacing: ${letterSpacing.normal};
    color: ${colorTokens.text.title};
  `,
};
