/**
 * Form field for Scale quiz question type (instructor sets target value on scale).
 *
 * @package Tutor
 * @since 4.0.0
 */

import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useCallback, useEffect, useState } from 'react';

import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
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
          <span css={styles.answerHeaderTitle}>{__('Configure Scale Question', __TUTOR_TEXT_DOMAIN__)}</span>
        </div>
        {/* Scale Configuration */}
        <div css={styles.configSection}>
          <h4 css={styles.sectionTitle}>{__('Scale Configuration', __TUTOR_TEXT_DOMAIN__)}</h4>
          <div css={styles.configGrid}>
            <div css={styles.configField}>
              <label css={styles.configLabel}>{__('Min Value', __TUTOR_TEXT_DOMAIN__)}</label>
              <input
                type="number"
                value={config.min}
                onChange={(e) => handleConfigChange('min', parseFloat(e.target.value) || 0)}
                css={styles.configInput}
              />
            </div>
            <div css={styles.configField}>
              <label css={styles.configLabel}>{__('Max Value', __TUTOR_TEXT_DOMAIN__)}</label>
              <input
                type="number"
                value={config.max}
                onChange={(e) => handleConfigChange('max', parseFloat(e.target.value) || 100)}
                css={styles.configInput}
              />
            </div>
            <div css={styles.configField}>
              <label css={styles.configLabel}>{__('Step', __TUTOR_TEXT_DOMAIN__)}</label>
              <input
                type="number"
                step="0.1"
                value={config.step}
                onChange={(e) => handleConfigChange('step', parseFloat(e.target.value) || 1)}
                css={styles.configInput}
              />
            </div>
            <div css={styles.configField}>
              <label css={styles.configLabel}>{__('Label Every', __TUTOR_TEXT_DOMAIN__)}</label>
              <input
                type="number"
                value={config.labelEvery}
                onChange={(e) => handleConfigChange('labelEvery', parseFloat(e.target.value) || 10)}
                css={styles.configInput}
              />
            </div>
          </div>
        </div>

        {/* Answer Configuration */}
        <div css={styles.answerSection}>
          <h4 css={styles.sectionTitle}>{__('Set Correct Answer', __TUTOR_TEXT_DOMAIN__)}</h4>
          <p css={styles.hint}>
            {__('Set the correct value that students should select on the scale.', __TUTOR_TEXT_DOMAIN__)}
          </p>
          <div css={styles.answerField}>
            <label css={styles.configLabel}>{__('Correct Value', __TUTOR_TEXT_DOMAIN__)}</label>
            <input
              type="number"
              step={config.step}
              min={config.min}
              max={config.max}
              value={scaleData.value}
              onChange={(e) => handleValueChange(parseFloat(e.target.value) || config.min)}
              css={styles.answerInput}
            />
          </div>
        </div>

        <p css={styles.savedHint}>
          {__('Configuration saved. Students will see this scale in the quiz.', __TUTOR_TEXT_DOMAIN__)}
        </p>
      </div>
    </div>
  );
};

export default FormScale;

const styles = {
  wrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[24]};
    padding-left: ${spacing[40]};

    ${Breakpoint.smallMobile} {
      padding-left: ${spacing[8]};
    }
  `,
  card: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[20]};
    padding: ${spacing[20]};
    background: ${colorTokens.surface.tutor};
    border: 1px solid ${colorTokens.stroke.border};
    border-radius: ${borderRadius.card};
  `,
  answerHeader: css`
    ${styleUtils.display.flex('row')};
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[12]};
  `,
  answerHeaderTitle: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.primary};
  `,
  configSection: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
  `,
  answerSection: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
    padding: ${spacing[16]};
    background: ${colorTokens.surface.tutor};
    border-radius: ${borderRadius.card};
  `,
  previewSection: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
    padding: ${spacing[16]};
    background: ${colorTokens.surface.tutor};
    border-radius: ${borderRadius.card};
  `,
  sectionTitle: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.primary};
    margin: 0;
  `,
  configGrid: css`
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: ${spacing[12]};
  `,
  configField: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
  `,
  answerField: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[4]};
    max-width: 200px;
  `,
  configLabel: css`
    ${typography.caption('medium')};
    color: ${colorTokens.text.primary};
  `,
  configInput: css`
    padding: ${spacing[8]};
    border: 1px solid ${colorTokens.stroke.border};
    border-radius: ${borderRadius.input};
    font-size: 14px;

    &:focus {
      outline: none;
      border-color: ${colorTokens.stroke.brand};
    }
  `,
  answerInput: css`
    padding: ${spacing[12]};
    border: 1px solid ${colorTokens.stroke.border};
    border-radius: ${borderRadius.input};
    font-size: 16px;
    font-weight: 600;

    &:focus {
      outline: none;
      border-color: ${colorTokens.stroke.brand};
    }
  `,
  hint: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0;
  `,
  savedHint: css`
    ${typography.caption()};
    color: ${colorTokens.text.success};
    margin: 0;
  `,
};
