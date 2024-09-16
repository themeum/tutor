import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import Button from '@Atoms/Button';
import ImageInput from '@Atoms/ImageInput';
import SVGIcon from '@Atoms/SVGIcon';

import {
  type QuizDataStatus,
  type QuizForm,
  type QuizQuestionOption,
  calculateQuizDataStatus,
} from '@CourseBuilderServices/quiz';

import ProBadge from '@Atoms/ProBadge';
import Tooltip from '@Atoms/Tooltip';
import { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import { animateLayoutChanges } from '@Utils/dndkit';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';
import { noop } from '@Utils/util';

interface FormMatchingProps extends FormControllerProps<QuizQuestionOption> {
  index: number;
  onDuplicateOption: (option: QuizQuestionOption) => void;
  onRemoveOption: () => void;
}

const isTutorPro = !!tutorConfig.tutor_pro_url;

const FormMatching = ({ index, onDuplicateOption, onRemoveOption, field }: FormMatchingProps) => {
  const { activeQuestionId, activeQuestionIndex, validationError, setValidationError } = useQuizModalContext();
  const form = useFormContext<QuizForm>();

  const inputValue = field.value ?? {
    answer_id: '',
    answer_title: '',
    answer_two_gap_match: '',
    is_correct: '0',
    belongs_question_id: activeQuestionId,
    belongs_question_type: 'matching',
  };
  const inputRef = useRef<HTMLInputElement>(null);

  const imageMatching = useWatch({
    control: form.control,
    name: `questions.${activeQuestionIndex}.question_settings.is_image_matching` as 'questions.0.question_settings.is_image_matching',
    defaultValue: false,
  });

  const [isEditing, setIsEditing] = useState(
    !inputValue.answer_title || imageMatching ? !inputValue.image_url : !inputValue.answer_two_gap_match,
  );

  const [previousValue, setPreviousValue] = useState<QuizQuestionOption>(inputValue);

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: field.value.answer_id || 0,
    animateLayoutChanges,
  });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.3 : undefined,
  };

  const wpMedia = window.wp.media({
    library: { type: 'image' },
  });

  const uploadHandler = () => {
    wpMedia.open();
  };

  wpMedia.on('select', () => {
    const attachment = wpMedia.state().get('selection').first().toJSON();
    const { id, url, title } = attachment;

    field.onChange({
      ...inputValue,
      ...(calculateQuizDataStatus(inputValue._data_status, 'update') && {
        _data_status: calculateQuizDataStatus(inputValue._data_status, 'update') as QuizDataStatus,
      }),
      image_id: id,
      image_url: url,
    });
  });

  const clearHandler = () => {
    field.onChange({
      ...inputValue,
      ...(calculateQuizDataStatus(inputValue._data_status, 'update') && {
        _data_status: calculateQuizDataStatus(inputValue._data_status, 'update') as QuizDataStatus,
      }),
      image_id: '',
      image_url: '',
    });
  };

  useEffect(() => {
    if (isDefined(inputRef.current) && isEditing) {
      inputRef.current.focus();
    }
  }, [isEditing]);

  return (
    <div {...attributes} css={styles.option({ isEditing })} ref={setNodeRef} style={style}>
      <div
        css={styles.optionLabel({ isEditing })}
        onClick={() => {
          setIsEditing(true);
        }}
        onKeyDown={(event) => {
          event.stopPropagation();
          if (event.key === 'Enter' || event.key === ' ') {
            setIsEditing(true);
          }
        }}
      >
        <div css={styles.optionHeader}>
          <div css={styles.optionCounter({ isSelected: !!Number(inputValue.is_correct), isEditing })}>
            {String.fromCharCode(65 + index)}
          </div>

          <Show when={!isEditing && inputValue.is_saved}>
            <button {...listeners} type="button" css={styles.optionDragButton} data-visually-hidden>
              <SVGIcon name="dragVertical" height={24} width={24} />
            </button>

            <div css={styles.optionActions} data-visually-hidden>
              <Tooltip content={__('Edit', 'tutor')} delay={200}>
                <button
                  type="button"
                  css={styles.actionButton}
                  data-edit-button
                  onClick={(event) => {
                    event.stopPropagation();
                    setIsEditing(true);
                  }}
                >
                  <SVGIcon name="edit" width={24} height={24} />
                </button>
              </Tooltip>
              <Tooltip content={__('Duplicate', 'tutor')} delay={200}>
                <Show
                  when={!isTutorPro}
                  fallback={
                    <button
                      type="button"
                      css={styles.actionButton}
                      onClick={(event) => {
                        event.stopPropagation();
                        onDuplicateOption(inputValue);
                      }}
                    >
                      <SVGIcon name="copyPaste" width={24} height={24} />
                    </button>
                  }
                >
                  <ProBadge size="tiny">
                    <button disabled type="button" css={styles.actionButton} onClick={noop}>
                      <SVGIcon name="copyPaste" width={24} height={24} />
                    </button>
                  </ProBadge>
                </Show>
              </Tooltip>
              <Tooltip content={__('Delete', 'tutor')} delay={200}>
                <button
                  type="button"
                  css={styles.actionButton}
                  data-visually-hidden
                  onClick={(event) => {
                    event.stopPropagation();
                    onRemoveOption();
                  }}
                >
                  <SVGIcon name="delete" width={24} height={24} />
                </button>
              </Tooltip>
            </div>
          </Show>
        </div>
        <div css={styles.optionBody}>
          <Show
            when={isEditing}
            fallback={
              <div css={styles.placeholderWrapper({ isImageMatching: imageMatching })}>
                <Show
                  when={imageMatching}
                  fallback={
                    <div css={styles.optionPlaceholder}>
                      {inputValue.answer_title || __('Answer title...', 'tutor')}
                    </div>
                  }
                >
                  <Show
                    when={inputValue.image_url}
                    fallback={
                      <div css={styles.imagePlaceholder}>
                        <SVGIcon name="imagePreview" height={48} width={48} />
                      </div>
                    }
                  >
                    {(image) => (
                      <div css={styles.imagePlaceholder}>
                        <img src={inputValue.image_url} alt={inputValue.image_url} />
                      </div>
                    )}
                  </Show>
                </Show>
                <div css={styles.optionPlaceholder}>
                  {!imageMatching ? inputValue.answer_two_gap_match : inputValue.answer_title}
                </div>
              </div>
            }
          >
            <div css={styles.optionInputWrapper}>
              <Show when={imageMatching}>
                <ImageInput
                  value={{
                    id: Number(inputValue.image_id),
                    url: inputValue.image_url || '',
                    title: inputValue.image_url || '',
                  }}
                  infoText={__('Standard Size: 700x430 pixels', 'tutor')}
                  uploadHandler={uploadHandler}
                  clearHandler={clearHandler}
                  emptyImageCss={styles.emptyImageInput}
                  previewImageCss={styles.previewImageInput}
                />
              </Show>
              <input
                {...field}
                type="text"
                ref={inputRef}
                css={styles.optionInput}
                placeholder={__('Write anything..', 'tutor')}
                value={inputValue.answer_title}
                onClick={(event) => {
                  event.stopPropagation();
                }}
                onChange={(event) => {
                  field.onChange({
                    ...inputValue,
                    ...(calculateQuizDataStatus(inputValue._data_status, 'update') && {
                      _data_status: calculateQuizDataStatus(inputValue._data_status, 'update') as QuizDataStatus,
                    }),
                    answer_title: event.target.value,
                  });
                }}
                onKeyDown={async (event) => {
                  event.stopPropagation();
                  if (
                    (event.metaKey || event.ctrlKey) &&
                    event.key === 'Enter' &&
                    inputValue.answer_title &&
                    inputValue.answer_two_gap_match
                  ) {
                    field.onChange({
                      ...inputValue,
                      ...(calculateQuizDataStatus(inputValue._data_status, 'update') && {
                        _data_status: calculateQuizDataStatus(inputValue._data_status, 'update') as QuizDataStatus,
                      }),
                    });
                    setIsEditing(false);
                  }
                }}
              />
              <Show when={!imageMatching}>
                <input
                  {...field}
                  type="text"
                  css={styles.optionInput}
                  placeholder={__('Matched option..', 'tutor')}
                  value={inputValue.answer_two_gap_match}
                  onClick={(event) => {
                    event.stopPropagation();
                  }}
                  onChange={(event) => {
                    field.onChange({
                      ...inputValue,
                      ...(calculateQuizDataStatus(inputValue._data_status, 'update') && {
                        _data_status: calculateQuizDataStatus(inputValue._data_status, 'update') as QuizDataStatus,
                      }),
                      answer_two_gap_match: event.target.value,
                    });
                  }}
                  onKeyDown={(event) => {
                    event.stopPropagation();
                    if (
                      (event.metaKey || event.ctrlKey) &&
                      event.key === 'Enter' &&
                      inputValue.answer_title &&
                      inputValue.answer_two_gap_match
                    ) {
                      field.onChange({
                        ...inputValue,
                        ...(calculateQuizDataStatus(inputValue._data_status, 'update') && {
                          _data_status: calculateQuizDataStatus(inputValue._data_status, 'update') as QuizDataStatus,
                          is_saved: true,
                        }),
                      });

                      if (validationError?.type === 'save_option') {
                        setValidationError(null);
                      }

                      setIsEditing(false);
                    }
                  }}
                />
              </Show>
              <div css={styles.optionInputButtons}>
                <Button
                  variant="text"
                  size="small"
                  onClick={(event) => {
                    event.stopPropagation();
                    setIsEditing(false);
                    field.onChange(previousValue);

                    if (!inputValue.is_saved) {
                      onRemoveOption();
                    }
                  }}
                >
                  {__('Cancel', 'tutor')}
                </Button>
                <Button
                  variant="secondary"
                  size="small"
                  onClick={(event) => {
                    event.stopPropagation();
                    field.onChange({
                      ...inputValue,
                      ...(calculateQuizDataStatus(inputValue._data_status, 'update') && {
                        _data_status: calculateQuizDataStatus(inputValue._data_status, 'update') as QuizDataStatus,
                      }),
                      is_saved: true,
                    });

                    setPreviousValue({
                      ...inputValue,
                      ...(calculateQuizDataStatus(inputValue._data_status, 'update') && {
                        _data_status: calculateQuizDataStatus(inputValue._data_status, 'update') as QuizDataStatus,
                      }),
                      is_saved: true,
                    });

                    if (validationError?.type === 'save_option') {
                      setValidationError(null);
                    }
                    setIsEditing(false);
                  }}
                  disabled={
                    !inputValue.answer_title ||
                    (imageMatching ? !inputValue.image_id : !inputValue.answer_two_gap_match)
                  }
                >
                  {__('Ok', 'tutor')}
                </Button>
              </div>
            </div>
          </Show>
        </div>
      </div>
    </div>
  );
};

export default FormMatching;

const styles = {
  option: ({
    isEditing,
  }: {
    isEditing: boolean;
  }) => css`
      ${styleUtils.display.flex()};
      ${typography.caption('medium')};
      align-items: center;
      color: ${colorTokens.text.subdued};
      gap: ${spacing[10]};
      align-items: center;

      [data-visually-hidden] {
        opacity: 0;
      }

      [data-edit-button] {
        opacity: 0;
      }
  
      &:hover {
        [data-visually-hidden] {
          opacity: 1;
        }

        ${
          !isEditing &&
          css`
          [data-edit-button] {
            opacity: 1;
          }
        `
        }
      }

      ${
        isEditing &&
        css`
        [data-edit-button] {
          opacity: 0;
        }
      `
      }
    `,
  optionLabel: ({
    isEditing,
  }: {
    isEditing: boolean;
  }) => css`
      display: flex;
      flex-direction: column;
      gap: ${spacing[12]};
      width: 100%;
      border-radius: ${borderRadius.card};
      padding: ${spacing[12]} ${spacing[16]};
      background-color: ${colorTokens.background.white};
  
      &:hover {
        box-shadow: 0 0 0 1px ${colorTokens.stroke.hover};
      }

      ${
        isEditing &&
        css`
          background-color: ${colorTokens.background.white};
          box-shadow: 0 0 0 1px ${colorTokens.stroke.brand};

          &:hover {
            box-shadow: 0 0 0 1px ${colorTokens.stroke.brand};
          }
        `
      }
    `,
  optionHeader: css`
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
  `,
  optionCounter: ({
    isSelected,
    isEditing,
  }: {
    isSelected: boolean;
    isEditing: boolean;
  }) => css`
    height: ${spacing[24]};
    width: ${spacing[24]};
    border-radius: ${borderRadius.min};
    ${typography.caption('medium')};
    color: ${colorTokens.text.subdued};
    background-color: ${colorTokens.background.default};
    text-align: center;
    place-self: center start;

    ${
      isSelected &&
      !isEditing &&
      css`
        background-color: ${colorTokens.bg.white};
      `
    }
  `,
  optionDragButton: css`
    ${styleUtils.resetButton}
    ${styleUtils.flexCenter()}
    transform: rotate(90deg);
    color: ${colorTokens.icon.default};
    cursor: grab;
    place-self: center center;
  `,
  optionActions: css`
    ${styleUtils.display.flex()}
    gap: ${spacing[8]};
    place-self: center end;
  `,
  actionButton: css`
    ${styleUtils.resetButton};
    ${styleUtils.display.flex()}
    color: ${colorTokens.icon.default};
    cursor: pointer;

    :disabled {
      cursor: not-allowed;
      color: ${colorTokens.icon.disable.background};
    }
  `,
  optionBody: css`
    ${styleUtils.display.flex()}
  `,
  placeholderWrapper: ({
    isImageMatching,
  }: {
    isImageMatching: boolean;
  }) => css`
    ${styleUtils.display.flex('column')}
    width: 100%;

    ${
      isImageMatching &&
      css`
        gap: ${spacing[12]};
      `
    }
  `,
  imagePlaceholder: css`
    ${styleUtils.flexCenter()};
    height: 210px;
    width: 100%;
    background-color: ${colorTokens.background.default};
    border-radius: ${borderRadius.card};
    overflow: hidden;
    svg {
      color: ${colorTokens.icon.hints};
    }

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
    }
  `,
  emptyImageInput: css`
    background-color: ${colorTokens.background.default};
    height: 210px;
  `,
  previewImageInput: css`
    height: 210px;
  `,
  optionPlaceholder: css`
    ${typography.body()};
    color: ${colorTokens.text.subdued};
    padding-block: ${spacing[4]};
  `,
  optionInputWrapper: css`
    ${styleUtils.display.flex('column')}
    width: 100%;
    gap: ${spacing[12]};
  `,
  optionInput: css`
    ${styleUtils.resetButton};
    ${typography.caption()};
    flex: 1;
    color: ${colorTokens.text.subdued};
    padding: ${spacing[4]} ${spacing[10]};
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[6]};
    resize: vertical;
    cursor: text;

    &:focus {
      ${styleUtils.inputFocus};
    }
  `,
  optionInputButtons: css`
    ${styleUtils.display.flex()}
    justify-content: flex-end;
    gap: ${spacing[8]};
  `,
};
