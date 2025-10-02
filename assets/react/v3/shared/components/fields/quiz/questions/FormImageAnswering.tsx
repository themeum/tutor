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
import { borderRadius, Breakpoint, colorTokens, fontWeight, shadow, spacing } from '@TutorShared/config/styles';
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
import { nanoid, noop } from '@TutorShared/utils/util';

interface FormImageAnsweringProps extends FormControllerProps<QuizQuestionOption> {
  index: number;
  onDuplicateOption: (option: QuizQuestionOption) => void;
  onRemoveOption: () => void;
  isOverlay?: boolean;
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

const FormImageAnswering = ({
  index,
  onDuplicateOption,
  onRemoveOption,
  field,
  isOverlay = false,
  questionId,
  validationError,
  setValidationError,
}: FormImageAnsweringProps) => {
  const inputValue = field.value ?? {
    answer_id: nanoid(),
    answer_title: '',
    is_correct: '0',
    belongs_question_id: questionId,
    belongs_question_type: 'image_answering',
  };
  const inputRef = useRef<HTMLInputElement>(null);

  const [isEditing, setIsEditing] = useState(!inputValue.answer_title && !inputValue.image_id && !inputValue.image_url);
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
    initialFiles: field.value.image_id
      ? {
          id: Number(inputValue.image_id),
          url: inputValue.image_url || '',
          title: inputValue.image_url || '',
        }
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
    <div {...attributes} css={styles.option} ref={setNodeRef} style={style} tabIndex={-1}>
      <div
        css={styles.optionLabel({ isEditing, isOverlay, isDragging })}
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
          <div
            css={styleUtils.optionCounter({
              isEditing,
            })}
          >
            {String.fromCharCode(65 + index)}
          </div>

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
                <div css={styles.optionPlaceholder}>
                  {inputValue.answer_title || __('Write answer option...', __TUTOR_TEXT_DOMAIN__)}
                </div>
              </div>
            }
          >
            <div css={styleUtils.optionInputWrapper}>
              <ImageInput
                value={{
                  id: Number(inputValue.image_id),
                  url: inputValue.image_url || '',
                  title: inputValue.image_url || '',
                }}
                buttonText={__('Upload Image', __TUTOR_TEXT_DOMAIN__)}
                infoText={__('Standard Size: 700x430 pixels', __TUTOR_TEXT_DOMAIN__)}
                uploadHandler={openMediaLibrary}
                clearHandler={clearHandler}
                emptyImageCss={styles.emptyImageInput}
                previewImageCss={styles.previewImageInput}
              />
              <div css={styles.inputWithHints}>
                <input
                  {...field}
                  ref={inputRef}
                  type="text"
                  placeholder={__('Input answer here', __TUTOR_TEXT_DOMAIN__)}
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

                <div css={styles.inputHints}>
                  <SVGIcon name="info" height={20} width={20} />
                  <p>
                    {__(
                      'Students need to type their answers exactly as you write them here. Use ',
                      __TUTOR_TEXT_DOMAIN__,
                    )}
                    <span css={{ fontWeight: fontWeight.semiBold }}>{__('small caps', __TUTOR_TEXT_DOMAIN__)}</span>
                    {__(' when writing the answer.', __TUTOR_TEXT_DOMAIN__)}
                  </p>
                </div>
              </div>
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

                      if (validationError?.type === 'save_option') {
                        setValidationError?.(null);
                      }
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
                  disabled={!inputValue.answer_title || !inputValue.image_id}
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

export default FormImageAnswering;

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
    isOverlay,
    isDragging,
  }: {
    isEditing: boolean;
    isOverlay: boolean;
    isDragging: boolean;
  }) => css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[20]};
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

    :focus-within {
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
  inputWithHints: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[8]};
  `,
  inputHints: css`
    ${styleUtils.display.flex()}
    gap: ${spacing[4]};
    ${typography.small()};
    color: ${colorTokens.text.hints};
    align-items: flex-start;

    svg {
      flex-shrink: 0;
    }
  `,
  optionInputButtons: css`
    ${styleUtils.display.flex()}
    justify-content: flex-end;
    gap: ${spacing[8]};
  `,
};
