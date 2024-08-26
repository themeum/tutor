import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import Button from '@Atoms/Button';
import ImageInput from '@Atoms/ImageInput';
import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import { useQuizModalContext } from '@CourseBuilderContexts/QuizModalContext';
import {
  type QuizDataStatus,
  type QuizForm,
  type QuizQuestionOption,
  calculateQuizDataStatus,
} from '@CourseBuilderServices/quiz';
import { animateLayoutChanges } from '@Utils/dndkit';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';
import { nanoid } from '@Utils/util';

interface FormMultipleChoiceAndOrderingProps extends FormControllerProps<QuizQuestionOption> {
  index: number;
  onDuplicateOption: (option: QuizQuestionOption) => void;
  onRemoveOption: () => void;
  onCheckCorrectAnswer: () => void;
}

const FormMultipleChoiceAndOrdering = ({
  field,
  onDuplicateOption,
  onRemoveOption,
  onCheckCorrectAnswer,
  index,
}: FormMultipleChoiceAndOrderingProps) => {
  const form = useFormContext<QuizForm>();
  const { activeQuestionId, activeQuestionIndex, validationError, setValidationError } = useQuizModalContext();
  const inputValue = field.value ?? {
    answer_id: nanoid(),
    answer_title: '',
    is_correct: '0',
    belongs_question_id: activeQuestionId,
    belongs_question_type: 'multiple_choice',
  };
  const inputRef = useRef<HTMLTextAreaElement>(null);

  const hasMultipleCorrectAnswer = useWatch({
    control: form.control,
    name: `questions.${activeQuestionIndex}.question_settings.has_multiple_correct_answer` as 'questions.0.question_settings.has_multiple_correct_answer',
    defaultValue: false,
  });
  const currentQuestionType = form.watch(`questions.${activeQuestionIndex}.question_type`);

  const [isEditing, setIsEditing] = useState(
    !inputValue.is_saved || (!inputValue.answer_title && !inputValue.image_url),
  );
  const [isUploadImageVisible, setIsUploadImageVisible] = useState(
    isDefined(inputValue.image_id) && isDefined(inputValue.image_url),
  );
  const [previousValue, setPreviousValue] = useState<QuizQuestionOption>(inputValue);

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: field.value?.answer_id || 0,
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
    <div
      {...attributes}
      css={styles.option({
        isSelected: !!Number(inputValue.is_correct),
        isEditing,
        isMultipleChoice: hasMultipleCorrectAnswer,
      })}
      ref={setNodeRef}
      style={style}
    >
      <Show when={currentQuestionType === 'multiple_choice'}>
        <button
          key={inputValue.is_correct}
          css={styleUtils.resetButton}
          data-check-button
          type="button"
          onClick={onCheckCorrectAnswer}
        >
          <Show
            when={hasMultipleCorrectAnswer}
            fallback={
              <SVGIcon
                data-check-icon
                name={Number(inputValue.is_correct) ? 'checkFilled' : 'check'}
                height={32}
                width={32}
              />
            }
          >
            <SVGIcon
              data-check-icon
              name={Number(inputValue.is_correct) ? 'checkSquareFilled' : 'checkSquare'}
              height={32}
              width={32}
            />
          </Show>
        </button>
      </Show>
      <div
        css={styles.optionLabel({
          isSelected: !!Number(inputValue.is_correct),
          isEditing,
        })}
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
          <div css={styles.optionCounterAndButton}>
            <div css={styles.optionCounter({ isSelected: !!Number(inputValue.is_correct), isEditing })}>
              {String.fromCharCode(65 + index)}
            </div>
            <Show when={isEditing}>
              <Show
                when={!isUploadImageVisible}
                fallback={
                  <Button
                    variant="text"
                    icon={<SVGIcon name="removeImage" width={24} height={24} />}
                    onClick={(event) => {
                      event.stopPropagation();
                      field.onChange({
                        ...inputValue,
                        ...(calculateQuizDataStatus(inputValue._data_status, 'update') && {
                          _data_status: calculateQuizDataStatus(inputValue._data_status, 'update') as QuizDataStatus,
                        }),
                        image_id: '',
                        image_url: '',
                      });
                      setIsUploadImageVisible(false);
                    }}
                  >
                    {__('Remove Image', 'tutor')}
                  </Button>
                }
              >
                <Button
                  variant="text"
                  icon={<SVGIcon name="addImage" width={24} height={24} />}
                  onClick={(event) => {
                    event.stopPropagation();
                    setIsUploadImageVisible(true);
                  }}
                >
                  {__('Add Image', 'tutor')}
                </Button>
              </Show>
            </Show>
          </div>

          <Show when={!isEditing && inputValue.is_saved}>
            <button {...listeners} type="button" css={styles.optionDragButton} data-visually-hidden>
              <SVGIcon name="dragVertical" height={24} width={24} />
            </button>

            <div css={styles.optionActions}>
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
              <button
                type="button"
                css={styles.actionButton}
                data-visually-hidden
                onClick={(event) => {
                  event.stopPropagation();
                  onDuplicateOption(inputValue);
                }}
              >
                <SVGIcon name="copyPaste" width={24} height={24} />
              </button>
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
            </div>
          </Show>
        </div>
        <div css={styles.optionBody}>
          <Show
            when={isEditing}
            fallback={
              <div css={styles.placeholderWrapper}>
                <Show when={isUploadImageVisible && !isDefined(inputValue.image_url)}>
                  <div css={styles.imagePlaceholder}>
                    <SVGIcon name="imagePreview" height={48} width={48} />
                  </div>
                </Show>
                <Show when={inputValue.image_url}>
                  {(image) => (
                    <div css={styles.imagePlaceholder}>
                      <img src={inputValue.image_url} alt={inputValue.image_url} />
                    </div>
                  )}
                </Show>
                <div css={styles.optionPlaceholder}>
                  {inputValue.answer_title || __('Write answer option...', 'tutor')}
                </div>
              </div>
            }
          >
            <div css={styles.optionInputWrapper}>
              <Show when={isUploadImageVisible}>
                <ImageInput
                  value={{
                    id: Number(inputValue.image_id),
                    url: inputValue.image_url || '',
                    title: 'Image',
                  }}
                  infoText={__('Size: 700x430 pixels', 'tutor')}
                  uploadHandler={uploadHandler}
                  clearHandler={clearHandler}
                  emptyImageCss={styles.emptyImageInput}
                  previewImageCss={styles.previewImageInput}
                />
              </Show>

              <textarea
                {...field}
                ref={inputRef}
                css={styles.optionInput}
                placeholder={__('Write option...', 'tutor')}
                value={inputValue.answer_title}
                onClick={(event) => {
                  event.stopPropagation();
                }}
                onChange={(event) => {
                  const { value } = event.target;

                  field.onChange({
                    ...inputValue,
                    ...(calculateQuizDataStatus(inputValue._data_status, 'update') && {
                      _data_status: calculateQuizDataStatus(inputValue._data_status, 'update') as QuizDataStatus,
                    }),
                    answer_title: value,
                  });
                }}
                onKeyDown={async (event) => {
                  event.stopPropagation();
                  if ((event.metaKey || event.ctrlKey) && event.key === 'Enter' && inputValue.answer_title) {
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
                  onClick={async (event) => {
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
                  disabled={!inputValue.answer_title && !inputValue.image_url}
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

export default FormMultipleChoiceAndOrdering;

const styles = {
  optionWrapper: css`
      ${styleUtils.display.flex('column')};
      gap: ${spacing[12]};
    `,
  option: ({
    isSelected,
    isEditing,
    isMultipleChoice,
  }: {
    isSelected: boolean;
    isEditing: boolean;
    isMultipleChoice: boolean;
  }) => css`
      ${styleUtils.display.flex()};
      ${typography.caption('medium')};
      align-items: center;
      color: ${colorTokens.text.subdued};
      gap: ${spacing[10]};
      align-items: center;
  
      [data-check-icon] {
        opacity: 0;
        pointer-events: none;
        color: ${colorTokens.icon.default};
        ${
          !isMultipleChoice &&
          css`
            fill: none;
          `
        }
      }
  
      &:hover {
        [data-check-icon] {
          opacity: ${isEditing ? 0 : 1};
        }

        [data-check-button] {
          visibility: ${isEditing ? 'hidden' : 'visible'};
        }
      }
  
  
      ${
        isSelected &&
        css`
          [data-check-icon] {
            opacity: 1;
            color: ${colorTokens.bg.success};
            ${
              !isMultipleChoice &&
              css`
                fill: ${colorTokens.bg.success};
              `
            }
          }
        `
      }
    `,
  optionLabel: ({
    isSelected,
    isEditing,
  }: {
    isSelected: boolean;
    isEditing: boolean;
  }) => css`
      display: flex;
      flex-direction: column;
      gap: ${spacing[12]};
      width: 100%;
      border-radius: ${borderRadius.card};
      padding: ${spacing[12]} ${spacing[16]};
      background-color: ${colorTokens.background.white};

      [data-visually-hidden] {
        opacity: 0;
      }

      [data-edit-button] {
        opacity: 0;
      }
  
      &:hover {
        box-shadow: 0 0 0 1px ${colorTokens.stroke.hover};

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
        isSelected &&
        css`
          background-color: ${colorTokens.background.success.fill40};
          color: ${colorTokens.text.primary};
  
          &:hover {
            box-shadow: 0 0 0 1px ${colorTokens.stroke.success.fill70};
          }
        `
      }
      ${
        isEditing &&
        css`
          background-color: ${colorTokens.background.white};
          box-shadow: 0 0 0 1px ${isSelected ? colorTokens.stroke.success.fill70 : colorTokens.stroke.brand};
          &:hover {
            box-shadow: 0 0 0 1px ${isSelected ? colorTokens.stroke.success.fill70 : colorTokens.stroke.brand};
          }
        `
      }
    `,
  optionHeader: css`
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
  `,
  optionCounterAndButton: css`
    ${styleUtils.display.flex()}
    gap: ${spacing[8]};
    place-self: center start;
    button {
      padding: 0;
    }
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
    color: ${colorTokens.icon.default};
    ${styleUtils.display.flex()}
  `,
  optionBody: css`
    ${styleUtils.display.flex()}
  `,
  placeholderWrapper: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[12]};
    width: 100%;
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
      ${styleUtils.inputFocus}
    }
  `,
  optionInputButtons: css`
    ${styleUtils.display.flex()}
    justify-content: flex-end;
    gap: ${spacing[8]};
  `,
};
