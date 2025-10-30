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
  type QuizValidationErrorType,
} from '@TutorShared/utils/types';
import { noop } from '@TutorShared/utils/util';

interface FormMatchingProps extends FormControllerProps<QuizQuestionOption> {
  index: number;
  onDuplicateOption: (option: QuizQuestionOption) => void;
  onRemoveOption: () => void;
  isOverlay?: boolean;
  isImageMatching: boolean;
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

const isTutorPro = !!tutorConfig.tutor_pro_url;

const FormMatching = ({
  index,
  onDuplicateOption,
  onRemoveOption,
  field,
  isOverlay = false,
  isImageMatching,
  questionId,
  validationError,
  setValidationError,
}: FormMatchingProps) => {
  const inputValue = field.value ?? {
    answer_id: '',
    answer_title: '',
    answer_two_gap_match: '',
    is_correct: '0',
    belongs_question_id: questionId,
    belongs_question_type: 'matching',
  };
  const inputRef = useRef<HTMLInputElement>(null);

  const [isEditing, setIsEditing] = useState(
    !inputValue.answer_title || isImageMatching ? !inputValue.image_url : !inputValue.answer_two_gap_match,
  );

  const [previousValue, setPreviousValue] = useState<QuizQuestionOption>(inputValue);

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: field.value.answer_id || 0,
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
        });
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
    field.onChange({
      ...inputValue,
      ...(calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) && {
        _data_status: calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) as QuizDataStatus,
      }),
      image_id: '',
      image_url: '',
    });
    resetFiles();
  };

  useEffect(() => {
    if (isDefined(inputRef.current) && isEditing) {
      inputRef.current.focus();
    }
  }, [isEditing]);

  return (
    <div {...attributes} css={styles.option} ref={setNodeRef} tabIndex={-1} style={style}>
      <div
        css={styles.optionLabel({ isEditing, isDragging, isOverlay })}
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
          <div css={styleUtils.optionCounter({ isEditing })}>{String.fromCharCode(65 + index)}</div>

          <Show when={!isEditing && inputValue.is_saved}>
            <button
              {...listeners}
              type="button"
              css={styleUtils.optionDragButton({
                isOverlay,
              })}
              data-visually-hidden
            >
              <SVGIcon name="dragVertical" height={24} width={24} />
            </button>

            <div css={styles.optionActions} data-visually-hidden>
              <Tooltip content={__('Edit', __TUTOR_TEXT_DOMAIN__)} delay={200}>
                <button
                  type="button"
                  css={styleUtils.actionButton}
                  data-edit-button
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
              <div css={styles.placeholderWrapper({ isImageMatching: isImageMatching })}>
                <Show
                  when={isImageMatching}
                  fallback={
                    <div css={styles.optionPlaceholder}>
                      {inputValue.answer_title || __('Answer title...', __TUTOR_TEXT_DOMAIN__)}
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
                    {() => (
                      <div css={styles.imagePlaceholder}>
                        <img src={inputValue.image_url} alt={inputValue.image_url} />
                      </div>
                    )}
                  </Show>
                </Show>
                <div css={styles.optionPlaceholder}>
                  {!isImageMatching ? inputValue.answer_two_gap_match : inputValue.answer_title}
                </div>
              </div>
            }
          >
            <div css={styleUtils.optionInputWrapper}>
              <Show when={isImageMatching}>
                <ImageInput
                  value={{
                    id: Number(inputValue.image_id),
                    url: inputValue.image_url || '',
                    title: inputValue.image_url || '',
                  }}
                  infoText={__('Standard Size: 700x430 pixels', __TUTOR_TEXT_DOMAIN__)}
                  uploadHandler={openMediaLibrary}
                  clearHandler={clearHandler}
                  emptyImageCss={styles.emptyImageInput}
                  previewImageCss={styles.previewImageInput}
                />
              </Show>
              <input
                {...field}
                type="text"
                ref={inputRef}
                placeholder={
                  !isImageMatching
                    ? __('Question', __TUTOR_TEXT_DOMAIN__)
                    : __('Image matched text..', __TUTOR_TEXT_DOMAIN__)
                }
                value={inputValue.answer_title}
                onClick={(event) => {
                  event.stopPropagation();
                }}
                onChange={(event) => {
                  field.onChange({
                    ...inputValue,
                    ...(calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) && {
                      _data_status: calculateQuizDataStatus(
                        inputValue._data_status,
                        QuizDataStatus.UPDATE,
                      ) as QuizDataStatus,
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
                      ...(calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) && {
                        _data_status: calculateQuizDataStatus(
                          inputValue._data_status,
                          QuizDataStatus.UPDATE,
                        ) as QuizDataStatus,
                      }),
                    });
                    setIsEditing(false);
                  }
                }}
              />
              <Show when={!isImageMatching}>
                <input
                  {...field}
                  type="text"
                  placeholder={__('Matched option..', __TUTOR_TEXT_DOMAIN__)}
                  value={inputValue.answer_two_gap_match}
                  onClick={(event) => {
                    event.stopPropagation();
                  }}
                  onChange={(event) => {
                    field.onChange({
                      ...inputValue,
                      ...(calculateQuizDataStatus(inputValue._data_status, QuizDataStatus.UPDATE) && {
                        _data_status: calculateQuizDataStatus(
                          inputValue._data_status,
                          QuizDataStatus.UPDATE,
                        ) as QuizDataStatus,
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
                  onClick={(event) => {
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
                  disabled={
                    !inputValue.answer_title ||
                    (isImageMatching ? !inputValue.image_id : !inputValue.answer_two_gap_match)
                  }
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

export default FormMatching;

const styles = {
  option: css`
    ${styleUtils.display.flex()};
    ${typography.caption('medium')};
    align-items: center;
    color: ${colorTokens.text.subdued};
    gap: ${spacing[10]};
    align-items: center;
  `,
  optionLabel: ({
    isEditing,
    isDragging,
    isOverlay,
  }: {
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

    ${isEditing &&
    css`
      background-color: ${colorTokens.background.white};
      outline: 1px solid ${colorTokens.stroke.brand};

      &:hover {
        outline: 1px solid ${colorTokens.stroke.brand};
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
  optionHeader: css`
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;

    &:focus-within {
      [data-visually-hidden] {
        opacity: 1;
      }
    }
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

    &:disabled {
      cursor: not-allowed;
      color: ${colorTokens.icon.disable.background};
    }
  `,
  optionBody: css`
    ${styleUtils.display.flex()}
  `,
  placeholderWrapper: ({ isImageMatching }: { isImageMatching: boolean }) => css`
    ${styleUtils.display.flex('column')}
    width: 100%;

    ${isImageMatching &&
    css`
      gap: ${spacing[12]};
    `}
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
  optionInputButtons: css`
    ${styleUtils.display.flex()}
    justify-content: flex-end;
    gap: ${spacing[8]};
  `,
};
