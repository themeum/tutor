/**
 * Form field for Scale quiz question type (instructor sets target value on scale).
 *
 * @package Tutor
 * @since 4.0.0
 */

import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useCallback, useEffect, useState } from 'react';

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

function parseStoredScaleData(value: string): ScaleData | null {
  if (!value || typeof value !== 'string') return null;
  try {
    const data = JSON.parse(value) as Partial<ScaleData>;
    if (typeof data.value === 'number' && data.config) {
      return {
        value: data.value,
        config: {
          min: data.config.min ?? 0,
          max: data.config.max ?? 100,
          step: data.config.step ?? 1,
          defaultValue: data.config.defaultValue ?? 50,
          pxPerUnit: data.config.pxPerUnit ?? 10,
          labelEvery: data.config.labelEvery ?? 10,
          minorTickEvery: data.config.minorTickEvery ?? 5,
          precision: data.config.precision ?? 0,
        },
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
          min: 0,
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
    (field: keyof ScaleConfig, value: number) => {
      const newConfig = { ...config, [field]: value };
      setConfig(newConfig);

      // Update scale data with new config
      const newScaleData = {
        ...scaleData,
        config: newConfig,
        // Ensure value is within new range
        value: Math.max(newConfig.min, Math.min(newConfig.max, scaleData.value)),
      };
      setScaleData(newScaleData);
      saveScaleData(newScaleData);
    },
    [config, scaleData, saveScaleData],
  );

  const handleValueChange = useCallback(
    (value: number) => {
      const newScaleData = { ...scaleData, value };
      setScaleData(newScaleData);
      saveScaleData(newScaleData);
    },
    [scaleData, saveScaleData],
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
              <input
                type="number"
                value={config.min}
                onChange={(e) => handleConfigChange('min', parseFloat(e.target.value) || 0)}
                className="tutor-scale-config-input"
              />
            </div>
            <div css={styles.configField}>
              <label css={styles.configLabel}>{__('Max value', __TUTOR_TEXT_DOMAIN__)}</label>
              <input
                type="number"
                value={config.max}
                onChange={(e) => handleConfigChange('max', parseFloat(e.target.value) || 100)}
                className="tutor-scale-config-input"
              />
            </div>
            <div css={styles.configField}>
              <label css={styles.configLabel}>{__('Steps', __TUTOR_TEXT_DOMAIN__)}</label>
              <input
                type="number"
                step="0.1"
                value={config.step}
                onChange={(e) => handleConfigChange('step', parseFloat(e.target.value) || 1)}
                className="tutor-scale-config-input"
              />
            </div>
            <div css={styles.configField}>
              <label css={styles.configLabel}>{__('Label entry', __TUTOR_TEXT_DOMAIN__)}</label>
              <input
                type="number"
                value={config.labelEvery}
                onChange={(e) => handleConfigChange('labelEvery', parseFloat(e.target.value) || 10)}
                className="tutor-scale-config-input"
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
            <input
              type="number"
              step={config.step}
              min={config.min}
              max={config.max}
              value={scaleData.value}
              onChange={(e) => handleValueChange(parseFloat(e.target.value) || config.min)}
              className="tutor-scale-config-input"
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

    & .tutor-scale-config-input {
      padding: ${spacing[8]};
      border: 1px solid ${colorTokens.stroke.default};
      border-radius: ${borderRadius.input};
      font-family: ${fontFamily.sfProDisplay};
      font-weight: ${fontWeight.regular};
      font-size: ${fontSize[16]};
      line-height: ${lineHeight[24]};
      letter-spacing: ${letterSpacing.normal};
      color: ${colorTokens.text.subdued};
    }
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
