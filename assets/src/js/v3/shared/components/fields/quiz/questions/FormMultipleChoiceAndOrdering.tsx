import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';

import Button from '@TutorShared/atoms/Button';
import ImageInput from '@TutorShared/atoms/ImageInput';
import ProBadge from '@TutorShared/atoms/ProBadge';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Tooltip from '@TutorShared/atoms/Tooltip';

import { tutorConfig } from '@TutorShared/config/config';
import { borderRadius, Breakpoint, colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import useWPMedia from '@TutorShared/hooks/useWpMedia';
import { animateLayoutChanges } from '@TutorShared/utils/dndkit';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { calculateQuizDataStatus } from '@TutorShared/utils/quiz';
import { styleUtils } from '@TutorShared/utils/style-utils';
import {
  type ID,
  isDefined,
  QuizDataStatus,
  type QuizQuestionOption,
  type QuizQuestionType,
  type QuizValidationErrorType,
} from '@TutorShared/utils/types';
import { nanoid, noop } from '@TutorShared/utils/util';

interface FormMultipleChoiceAndOrderingProps extends FormControllerProps<QuizQuestionOption> {
  index: number;
  onDuplicateOption: (option: QuizQuestionOption) => void;
  onRemoveOption: () => void;
  onCheckCorrectAnswer: () => void;
  isOverlay?: boolean;
  hasMultipleCorrectAnswer: boolean;
  questionId: ID;
  questionType: QuizQuestionType;
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

const isTutorPro = !!tutorConfig.tutor_pro_url;

const FormMultipleChoiceAndOrdering = ({
  field,
  onDuplicateOption,
  onRemoveOption,
  onCheckCorrectAnswer,
  index,
  isOverlay = false,
  questionId,
  hasMultipleCorrectAnswer = false,
  questionType = 'multiple_choice',
  validationError,
  setValidationError,
}: FormMultipleChoiceAndOrderingProps) => {
  const inputValue = field.value ?? {
    answer_id: nanoid(),
    answer_title: '',
    is_correct: '0',
    belongs_question_id: questionId,
    belongs_question_type: 'multiple_choice',
  };
  const inputRef = useRef<HTMLTextAreaElement>(null);

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
  const { openMediaLibrary, resetFiles } = useWPMedia({
    options: {
      type: 'image',
    },
    onChange: (file) => {
      if (file && !Array.isArray(file)) {
        const { id, url } = file;

        field.onChange({
          ...inputValue,
          ...(calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) && {
            _data_status: calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
          }),
          image_id: id,
          image_url: url,
          answer_view_format: 'text_image',
        });
        setIsUploadImageVisible(true);
      }
    },
    initialFiles: field.value?.image_id
      ? { id: Number(field.value.image_id), url: field.value.image_url || '', title: '' }
      : null,
  });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.3 : undefined,
  };

  const clearHandler = () => {
    resetFiles();
    field.onChange({
      ...inputValue,
      ...(calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) && {
        _data_status: calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
      }),
      image_id: '',
      image_url: '',
      answer_view_format: 'text',
    });
    setIsUploadImageVisible(false);
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
        isMultipleChoice: hasMultipleCorrectAnswer,
      })}
      tabIndex={-1}
      ref={setNodeRef}
      style={style}
    >
      <Show when={questionType === 'multiple_choice'}>
        <button
          key={inputValue.is_correct}
          css={styleUtils.optionCheckButton}
          data-check-button
          type="button"
          onClick={onCheckCorrectAnswer}
        >
          <Show
            when={hasMultipleCorrectAnswer}
            fallback={<SVGIcon name={Number(inputValue.is_correct) ? 'checkFilled' : 'check'} height={32} width={32} />}
          >
            <SVGIcon
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
          isDragging,
          isOverlay,
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
        <div
          css={styles.optionHeader({
            isEditing,
          })}
        >
          <div css={styles.optionCounterAndButton}>
            <div css={styleUtils.optionCounter({ isSelected: !!Number(inputValue.is_correct), isEditing })}>
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
                      clearHandler();
                    }}
                  >
                    {__('Remove Image', __TUTOR_TEXT_DOMAIN__)}
                  </Button>
                }
              >
                <Button
                  variant="text"
                  size="small"
                  icon={<SVGIcon name="addImage" width={24} height={24} />}
                  onClick={(event) => {
                    event.stopPropagation();
                    openMediaLibrary();
                  }}
                >
                  {__('Add Image', __TUTOR_TEXT_DOMAIN__)}
                </Button>
              </Show>
            </Show>
          </div>

          <Show when={!isEditing && inputValue.is_saved}>
            <button {...listeners} type="button" css={styleUtils.optionDragButton({ isOverlay })} data-visually-hidden>
              <SVGIcon name="dragVertical" height={24} width={24} />
            </button>

            <div css={styles.optionActions} data-visually-hidden>
              <Tooltip content={__('Edit', __TUTOR_TEXT_DOMAIN__)} delay={200}>
                <button
                  type="button"
                  css={styleUtils.actionButton}
                  onClick={(event) => {
                    event.stopPropagation();
                    setIsEditing(true);
                  }}
                >
                  <SVGIcon name="edit" width={24} height={24} />
                </button>
              </Tooltip>
              <Tooltip content={__('Duplicate', __TUTOR_TEXT_DOMAIN__)} delay={200}>
                <Show
                  when={!isTutorPro}
                  fallback={
                    <button
                      type="button"
                      css={styleUtils.actionButton}
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
                    <button disabled type="button" css={styleUtils.actionButton} onClick={noop}>
                      <SVGIcon name="copyPaste" width={24} height={24} />
                    </button>
                  </ProBadge>
                </Show>
              </Tooltip>
              <Tooltip content={__('Delete', __TUTOR_TEXT_DOMAIN__)} delay={200}>
                <button
                  type="button"
                  css={styleUtils.actionButton}
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
              <div css={styles.placeholderWrapper}>
                <Show when={inputValue.image_url}>
                  {
                    <div css={styles.imagePlaceholder}>
                      <img src={inputValue.image_url} alt={inputValue.image_url} />
                    </div>
                  }
                </Show>
                <div css={styles.optionPlaceholder}>
                  {inputValue.answer_title || __('Write answer option...', __TUTOR_TEXT_DOMAIN__)}
                </div>
              </div>
            }
          >
            <div css={styleUtils.optionInputWrapper}>
              <Show when={isUploadImageVisible}>
                <ImageInput
                  value={{
                    id: Number(inputValue.image_id),
                    url: inputValue.image_url || '',
                    title: __('Image', __TUTOR_TEXT_DOMAIN__),
                  }}
                  buttonText={__('Upload Image', __TUTOR_TEXT_DOMAIN__)}
                  infoText={__('Size: 700x430 pixels', __TUTOR_TEXT_DOMAIN__)}
                  uploadHandler={openMediaLibrary}
                  clearHandler={clearHandler}
                  emptyImageCss={styles.emptyImageInput}
                  previewImageCss={styles.previewImageInput}
                />
              </Show>

              <textarea
                {...field}
                ref={inputRef}
                placeholder={__('Write option...', __TUTOR_TEXT_DOMAIN__)}
                value={inputValue.answer_title}
                onClick={(event) => {
                  event.stopPropagation();
                }}
                onChange={(event) => {
                  const { value } = event.target;

                  field.onChange({
                    ...inputValue,
                    ...(calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) && {
                      _data_status: calculateQuizDataStatus(
                        inputValue._data_status,
                        QuizDataStatus.UPDATE,
                      ) as QuizDataStatus,
                    }),
                    answer_title: value,
                  });
                }}
                onKeyDown={async (event) => {
                  event.stopPropagation();
                  if ((event.metaKey || event.ctrlKey) && event.key === 'Enter' && inputValue.answer_title) {
                    field.onChange({
                      ...inputValue,
                      ...(calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) && {
                        _data_status: calculateQuizDataStatus(
                          inputValue._data_status,
                          QuizDataStatus.UPDATE,
                        ) as QuizDataStatus,
                        is_saved: true,
                      }),
                    });

                    if (validationError?.type === 'save_option') {
                      setValidationError?.(null);
                    }

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
                      if (validationError?.type === 'save_option') {
                        setValidationError?.(null);
                      }
                      onRemoveOption();
                    }
                  }}
                >
                  {__('Cancel', __TUTOR_TEXT_DOMAIN__)}
                </Button>
                <Button
                  variant="secondary"
                  size="small"
                  onClick={async (event) => {
                    event.stopPropagation();
                    field.onChange({
                      ...inputValue,
                      ...(calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) && {
                        _data_status: calculateQuizDataStatus(
                          inputValue._data_status,
                          QuizDataStatus.UPDATE,
                        ) as QuizDataStatus,
                      }),
                      is_saved: true,
                    });
                    setPreviousValue({
                      ...inputValue,
                      ...(calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) && {
                        _data_status: calculateQuizDataStatus(
                          inputValue._data_status,
                          QuizDataStatus.UPDATE,
                        ) as QuizDataStatus,
                      }),
                      is_saved: true,
                    });

                    if (validationError?.type === 'save_option') {
                      setValidationError?.(null);
                    }
                    setIsEditing(false);
                  }}
                  disabled={!inputValue.answer_title && !inputValue.image_url}
                >
                  {__('Ok', __TUTOR_TEXT_DOMAIN__)}
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
  option: ({ isSelected, isMultipleChoice }: { isSelected: boolean; isMultipleChoice: boolean }) => css`
    ${styleUtils.display.flex()};
    ${typography.caption('medium')};
    align-items: center;
    color: ${colorTokens.text.subdued};
    gap: ${spacing[10]};
    align-items: center;

    [data-check-button] {
      color: ${colorTokens.icon.default};
      ${!isMultipleChoice &&
      css`
        fill: none;
      `}

      &:focus-visible {
        opacity: 1;
      }
    }

    &:hover {
      [data-check-button] {
        opacity: 1;
      }
    }

    &:focus-within {
      [data-check-button] {
        opacity: 1;
      }
    }

    ${isSelected &&
    css`
      [data-check-button] {
        opacity: 1;
        color: ${colorTokens.bg.success};
        ${!isMultipleChoice &&
        css`
          fill: ${colorTokens.bg.success};
        `}
      }
    `}

    ${Breakpoint.smallTablet} {
      [data-check-button] {
        opacity: 1;
      }
    }
  `,
  optionLabel: ({
    isSelected,
    isEditing,
    isDragging,
    isOverlay,
  }: {
    isSelected: boolean;
    isEditing: boolean;
    isDragging: boolean;
    isOverlay: boolean;
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

    &:hover {
      outline: 1px solid ${colorTokens.stroke.hover};

      [data-visually-hidden] {
        opacity: 1;
      }
    }

    ${isSelected &&
    css`
      background-color: ${colorTokens.background.success.fill40};
      color: ${colorTokens.text.primary};

      &:hover {
        outline: 1px solid ${colorTokens.stroke.success.fill70};
      }
    `}
    ${isEditing &&
    css`
      background-color: ${colorTokens.background.white};
      outline: 1px solid ${isSelected ? colorTokens.stroke.success.fill70 : colorTokens.stroke.brand};
      &:hover {
        outline: 1px solid ${isSelected ? colorTokens.stroke.success.fill70 : colorTokens.stroke.brand};
      }
    `}

    ${isDragging &&
    css`
      background-color: ${colorTokens.stroke.hover};
    `}

    ${isOverlay &&
    css`
      box-shadow: ${shadow.drag};
    `}

    ${Breakpoint.smallTablet} {
      [data-visually-hidden] {
        opacity: 1;
      }
    }
  `,
  optionHeader: ({ isEditing = false }) => css`
    display: grid;
    grid-template-columns: ${!isEditing ? '1fr auto 1fr' : '1fr'};
    align-items: center;

    &:focus-within {
      [data-visually-hidden] {
        opacity: 1;
      }
    }
  `,
  optionCounterAndButton: css`
    ${styleUtils.display.flex()}
    gap: ${spacing[8]};
    place-self: center start;
    button {
      padding: 0;
      min-height: 24px;
    }
  `,
  optionActions: css`
    ${styleUtils.display.flex()}
    gap: ${spacing[8]};
    place-self: center end;
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
  optionInputButtons: css`
    ${styleUtils.display.flex()}
    justify-content: flex-end;
    gap: ${spacing[8]};
  `,
};
