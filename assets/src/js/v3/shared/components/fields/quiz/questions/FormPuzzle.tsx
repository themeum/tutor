import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useCallback, useMemo } from 'react';
import { Controller, useFormContext } from 'react-hook-form';

import ImageInput from '@TutorShared/atoms/ImageInput';
import FormSelectInput from '@TutorShared/components/fields/FormSelectInput';
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
import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';

interface FormPuzzleProps extends FormControllerProps<QuizQuestionOption> {
  questionId: ID;
  activeQuestionIndex?: number;
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
  gridSizeControllerProps?: FormControllerProps<number | null>;
  gridSizePath?: string;
}

const FormPuzzle = ({ field, activeQuestionIndex = 0, gridSizeControllerProps, gridSizePath }: FormPuzzleProps) => {
  const form = useFormContext();
  const option = field.value;
  const resolvedGridSizePath =
    gridSizePath ?? (`questions.${activeQuestionIndex}.question_settings.puzzle_grid_size` as const);
  const resolvedQuestionDataStatusPath = Array.isArray(form?.getValues?.('questions'))
    ? (`questions.${activeQuestionIndex}._data_status` as const)
    : ('_data_status' as const);
  const activeQuestionDataStatus = form
    ? ((form.watch(resolvedQuestionDataStatusPath) as QuizDataStatus | undefined) ?? QuizDataStatus.NO_CHANGE)
    : QuizDataStatus.NO_CHANGE;
  const gridSizeOptions = useMemo(
    () =>
      [
        { value: 2, difficulty: __('Easy', __TUTOR_TEXT_DOMAIN__) },
        { value: 3, difficulty: __('Easy', __TUTOR_TEXT_DOMAIN__) },
        { value: 4, difficulty: __('Medium', __TUTOR_TEXT_DOMAIN__) },
        { value: 5, difficulty: __('Medium', __TUTOR_TEXT_DOMAIN__) },
        { value: 6, difficulty: __('Hard', __TUTOR_TEXT_DOMAIN__) },
        { value: 7, difficulty: __('Hard', __TUTOR_TEXT_DOMAIN__) },
      ].map(({ value, difficulty }) => ({
        label: `${difficulty} - ${value}×${value} (${value * value} ${__('pieces', __TUTOR_TEXT_DOMAIN__)})`,
        value,
      })),
    [],
  );

  const updateOption = useCallback(
    (updated: QuizQuestionOption) => {
      field.onChange(updated);
    },
    [field],
  );

  const { openMediaLibrary, resetFiles } = useWPMedia({
    options: {
      type: 'image',
    },
    onChange: (file) => {
      if (file && !Array.isArray(file) && option) {
        const { id, url } = file;
        const updated: QuizQuestionOption = {
          ...option,
          ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
            _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
          }),
          image_id: id,
          image_url: url,
          answer_two_gap_match: url,
          is_saved: true,
        };
        updateOption(updated);
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
    resetFiles();
    updateOption({
      ...option,
      ...(calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) && {
        _data_status: calculateQuizDataStatus(option._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
      }),
      image_id: undefined,
      image_url: '',
      answer_two_gap_match: '',
      is_saved: true,
    });
  };

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
            buttonText={__('Upload Puzzle Image', __TUTOR_TEXT_DOMAIN__)}
            infoText={__('Upload the source image that will be split into puzzle pieces.', __TUTOR_TEXT_DOMAIN__)}
            uploadHandler={openMediaLibrary}
            clearHandler={clearImage}
            emptyImageCss={styles.imageInputEmpty}
            previewImageCss={styles.imageInputPreview}
          />
        </div>
      </div>

      <Show when={option?.image_url}>
        <div css={styles.card}>
          {gridSizeControllerProps ? (
            <FormSelectInput
              {...gridSizeControllerProps}
              label={__('Difficulty Level', __TUTOR_TEXT_DOMAIN__)}
              options={gridSizeOptions}
              wrapperCss={styles.dropdownText}
              optionItemCss={styles.dropdownOptionText}
              onChange={(selectedOption) => {
                gridSizeControllerProps.field.onChange(selectedOption.value);
                if (calculateQuizDataStatus(activeQuestionDataStatus, QuizDataStatus.UPDATE)) {
                  form.setValue(
                    resolvedQuestionDataStatusPath,
                    calculateQuizDataStatus(activeQuestionDataStatus, QuizDataStatus.UPDATE) as QuizDataStatus,
                  );
                }
              }}
            />
          ) : (
            <Controller
              control={form.control}
              name={resolvedGridSizePath}
              defaultValue={4}
              render={(gridSizeControllerProps) => (
                <FormSelectInput
                  {...gridSizeControllerProps}
                  label={__('Difficulty Level', __TUTOR_TEXT_DOMAIN__)}
                  options={gridSizeOptions}
                  wrapperCss={styles.dropdownText}
                  optionItemCss={styles.dropdownOptionText}
                  onChange={(selectedOption) => {
                    gridSizeControllerProps.field.onChange(selectedOption.value);
                    if (calculateQuizDataStatus(activeQuestionDataStatus, QuizDataStatus.UPDATE)) {
                      form.setValue(
                        resolvedQuestionDataStatusPath,
                        calculateQuizDataStatus(activeQuestionDataStatus, QuizDataStatus.UPDATE) as QuizDataStatus,
                      );
                    }
                  }}
                />
              )}
            />
          )}
        </div>
      </Show>

      <Show when={!option?.image_url}>
        <p css={styles.placeholder}>
          {__('Upload an image first, then configure the grid size for the puzzle.', __TUTOR_TEXT_DOMAIN__)}
        </p>
      </Show>
    </div>
  );
};

export default FormPuzzle;

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
    gap: ${spacing[16]};
    padding: ${spacing[20]};
    background: ${colorTokens.surface.tutor};
    border: 1px solid ${colorTokens.stroke.border};
    border-radius: ${borderRadius.card};
  `,
  imageInputWrapper: css`
    max-width: 100%;
  `,
  imageInputEmpty: css`
    background-color: ${colorTokens.background.default};
    height: 210px;
    border-radius: ${borderRadius.card};
  `,
  imageInputPreview: css`
    height: 210px;
  `,
  previewLabel: css`
    ${typography.body('medium')};
    color: ${colorTokens.text.primary};
  `,
  previewWrap: css`
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: ${borderRadius.card};
    overflow: hidden;
    border: 1px solid ${colorTokens.stroke.border};
  `,
  previewImage: css`
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
  `,
  placeholder: css`
    ${typography.caption()};
    color: ${colorTokens.text.subdued};
  `,
  dropdownText: css`
    color: ${colorTokens.text.subdued} !important;
  `,
  dropdownOptionText: css`
    button,
    button:hover,
    button:focus,
    button:active {
      color: ${colorTokens.text.subdued};
    }
  `,
};
