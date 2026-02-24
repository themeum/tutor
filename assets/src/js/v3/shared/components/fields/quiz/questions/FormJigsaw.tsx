/**
 * Jigsaw puzzle question type form (instructor).
 * Upload image and set puzzle config: number of pieces, shape, rotation.
 * Config is stored in answer_two_gap_match as JSON.
 */

import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useCallback, useMemo } from 'react';

import ImageInput from '@TutorShared/atoms/ImageInput';

import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import useWPMedia from '@TutorShared/hooks/useWpMedia';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { calculateQuizDataStatus } from '@TutorShared/utils/quiz';
import { styleUtils } from '@TutorShared/utils/style-utils';
import {
  type ID,
  QuizDataStatus,
  type QuizQuestionOption,
  type QuizValidationErrorType,
} from '@TutorShared/utils/types';

export const JIGSAW_DEFAULT_NB_PIECES = 12;
export const JIGSAW_NB_PIECES_OPTIONS = [4, 6, 8, 12, 25, 50, 100, 200] as const;
export const JIGSAW_SHAPE_OPTIONS = [
  { value: 0, label: __('Classic', __TUTOR_TEXT_DOMAIN__) },
  { value: 1, label: __('Triangle', __TUTOR_TEXT_DOMAIN__) },
  { value: 2, label: __('Round', __TUTOR_TEXT_DOMAIN__) },
  { value: 3, label: __('Straight', __TUTOR_TEXT_DOMAIN__) },
] as const;

export interface JigsawConfig {
  nbPieces: number;
  shape: number;
  rotationAllowed: boolean;
}

export const defaultJigsawConfig: JigsawConfig = {
  nbPieces: JIGSAW_DEFAULT_NB_PIECES,
  shape: 0,
  rotationAllowed: false,
};

export function parseJigsawConfig(raw: string | undefined): JigsawConfig {
  if (!raw || typeof raw !== 'string' || raw.trim() === '') {
    return { ...defaultJigsawConfig };
  }
  try {
    const parsed = JSON.parse(raw) as Partial<JigsawConfig>;
    return {
      nbPieces:
        typeof parsed.nbPieces === 'number' &&
        JIGSAW_NB_PIECES_OPTIONS.includes(parsed.nbPieces as (typeof JIGSAW_NB_PIECES_OPTIONS)[number])
          ? parsed.nbPieces
          : defaultJigsawConfig.nbPieces,
      shape:
        typeof parsed.shape === 'number' && parsed.shape >= 0 && parsed.shape <= 3
          ? parsed.shape
          : defaultJigsawConfig.shape,
      rotationAllowed:
        typeof parsed.rotationAllowed === 'boolean' ? parsed.rotationAllowed : defaultJigsawConfig.rotationAllowed,
    };
  } catch {
    return { ...defaultJigsawConfig };
  }
}

interface FormJigsawProps extends FormControllerProps<QuizQuestionOption> {
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

const FormJigsaw = ({ field }: FormJigsawProps) => {
  const option = field.value;
  const config = useMemo(() => parseJigsawConfig(option?.answer_two_gap_match), [option?.answer_two_gap_match]);

  const updateOption = useCallback(
    (updated: Partial<QuizQuestionOption>, configOverride?: JigsawConfig) => {
      if (!option) return;
      const nextConfig = configOverride ?? config;
      const answerTwoGapMatch = configOverride !== undefined ? JSON.stringify(nextConfig) : option.answer_two_gap_match;
      field.onChange({
        ...option,
        ...updated,
        answer_two_gap_match:
          updated.answer_two_gap_match !== undefined ? updated.answer_two_gap_match : answerTwoGapMatch,
      });
    },
    [field, option, config],
  );

  const { openMediaLibrary, resetFiles } = useWPMedia({
    options: {
      type: 'image',
    },
    onChange: (file) => {
      if (file && !Array.isArray(file) && option) {
        updateOption({
          ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
            _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
          }),
          image_id: file.id,
          image_url: file.url,
          answer_two_gap_match: JSON.stringify({ ...config }),
        });
      }
    },
    initialFiles: option?.image_id
      ? {
          id: Number(option.image_id),
          url: option.image_url || '',
          title: option.image_url || '',
        }
      : null,
  });

  const clearImage = () => {
    if (!option) {
      return;
    }

    const nextStatus = calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE);

    updateOption({
      ...(nextStatus && {
        _data_status: nextStatus as QuizDataStatus,
      }),
      image_id: undefined,
      image_url: '',
      answer_two_gap_match: JSON.stringify({ ...config }),
    });
    resetFiles();
  };

  const setConfig = useCallback(
    (next: Partial<JigsawConfig>) => {
      if (!option) {
        return;
      }

      const nextConfig = { ...config, ...next };
      const nextStatus = calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE);

      updateOption(
        {
          ...(nextStatus && {
            _data_status: nextStatus as QuizDataStatus,
          }),
          answer_two_gap_match: JSON.stringify(nextConfig),
          is_saved: true,
        },
        nextConfig,
      );
    },
    [config, option, updateOption],
  );

  if (!option) {
    return null;
  }

  return (
    <div css={styles.wrapper}>
      <div css={styles.card}>
        <div css={styles.imageInputWrapper}>
          <ImageInput
            value={
              option?.image_id
                ? {
                    id: Number(option.image_id),
                    url: option.image_url || '',
                    title: option.image_url || '',
                  }
                : null
            }
            buttonText={__('Upload Image', __TUTOR_TEXT_DOMAIN__)}
            infoText={__('Upload the image students will solve as a jigsaw puzzle.', __TUTOR_TEXT_DOMAIN__)}
            uploadHandler={openMediaLibrary}
            clearHandler={clearImage}
            emptyImageCss={styles.imageInput}
            previewImageCss={styles.imageInput}
          />
        </div>
      </div>

      <Show when={option?.image_url}>
        <div css={styles.card}>
          <p css={styles.sectionTitle}>{__('Puzzle settings', __TUTOR_TEXT_DOMAIN__)}</p>

          <div css={styles.configRow}>
            <label css={styles.label} htmlFor="jigsaw-nb-pieces">
              {__('Number of pieces', __TUTOR_TEXT_DOMAIN__)}
            </label>
            <select
              id="jigsaw-nb-pieces"
              css={styles.select}
              value={config.nbPieces}
              onChange={(e) => setConfig({ nbPieces: Number(e.target.value) })}
              aria-label={__('Number of pieces', __TUTOR_TEXT_DOMAIN__)}
            >
              {JIGSAW_NB_PIECES_OPTIONS.map((n) => (
                <option key={n} value={n}>
                  {n}
                </option>
              ))}
            </select>
          </div>

          <div css={styles.configRow}>
            <label css={styles.label} htmlFor="jigsaw-shape">
              {__('Piece shape', __TUTOR_TEXT_DOMAIN__)}
            </label>
            <select
              id="jigsaw-shape"
              css={styles.select}
              value={config.shape}
              onChange={(e) => setConfig({ shape: Number(e.target.value) })}
              aria-label={__('Piece shape', __TUTOR_TEXT_DOMAIN__)}
            >
              {JIGSAW_SHAPE_OPTIONS.map(({ value, label }) => (
                <option key={value} value={value}>
                  {label}
                </option>
              ))}
            </select>
          </div>

          <div css={styles.configRow}>
            <label css={styles.switchLabel}>
              <input
                type="checkbox"
                css={styles.checkbox}
                checked={config.rotationAllowed}
                onChange={(e) => setConfig({ rotationAllowed: e.target.checked })}
                aria-label={__('Enable rotation', __TUTOR_TEXT_DOMAIN__)}
              />
              <span>{__('Enable rotation', __TUTOR_TEXT_DOMAIN__)}</span>
            </label>
            <p css={styles.hint}>{__('Pieces can be rotated; students tap/click to rotate.', __TUTOR_TEXT_DOMAIN__)}</p>
          </div>
        </div>
      </Show>

      <Show when={!option?.image_url}>
        <p css={styles.placeholder}>
          {__(
            'Upload an image to create a jigsaw puzzle. Then set the number of pieces and shape.',
            __TUTOR_TEXT_DOMAIN__,
          )}
        </p>
      </Show>
    </div>
  );
};

export default FormJigsaw;

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
    gap: ${spacing[16]};
    padding: ${spacing[20]};
    background: ${colorTokens.surface.tutor};
    border: 1px solid ${colorTokens.stroke.border};
    border-radius: ${borderRadius.card};
  `,
  imageInputWrapper: css`
    max-width: 100%;
  `,
  imageInput: css`
    border-radius: ${borderRadius.card};
  `,
  sectionTitle: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.primary};
    margin: 0;
  `,
  configRow: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[8]};
  `,
  label: css`
    ${typography.caption('medium')};
    color: ${colorTokens.text.primary};
  `,
  select: css`
    ${typography.body()};
    max-width: 200px;
    padding: ${spacing[8]} ${spacing[12]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[6]};
    background: ${colorTokens.background.white};
    color: ${colorTokens.text.primary};
  `,
  switchLabel: css`
    ${typography.body()};
    ${styleUtils.display.flex('row')};
    align-items: center;
    gap: ${spacing[8]};
    cursor: pointer;
    color: ${colorTokens.text.primary};
  `,
  checkbox: css`
    width: 18px;
    height: 18px;
  `,
  hint: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
    margin: 0;
  `,
  placeholder: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
  `,
};
