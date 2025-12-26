import { type SerializedStyles, css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import FormFieldWrapper from '@TutorShared/components/fields/FormFieldWrapper';

import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { isDefined } from '@TutorShared/utils/types';

type Size = 'small' | 'regular';

interface FormQuestionTitleProps extends FormControllerProps<string | null> {
  maxLimit?: number;
  maxHeight?: number;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: (value: string | number) => void;
  onKeyDown?: (keyName: string) => void;
  isHidden?: boolean;
  isSecondary?: boolean;
  removeBorder?: boolean;
  dataAttribute?: string;
  isInlineLabel?: boolean;
  style?: SerializedStyles;
  wrapperCss?: SerializedStyles;
  selectOnFocus?: boolean;
  size?: Size;
  isEdit?: boolean;
  onToggleEdit?: (isEdit: boolean) => void;
}

const FormQuestionTitle = ({
  maxLimit,
  maxHeight,
  field,
  fieldState,
  disabled,
  readOnly,
  loading,
  placeholder,
  helpText,
  onChange,
  onKeyDown,
  isHidden,
  isSecondary = false,
  removeBorder,
  dataAttribute,
  isInlineLabel = false,
  style,
  wrapperCss,
  selectOnFocus = false,
  size = 'regular',
  isEdit: propsIsEdit,
  onToggleEdit,
}: FormQuestionTitleProps) => {
  const inputValue = field.value ?? '';
  const inputRef = useRef<HTMLTextAreaElement>(null);

  const isControlled = isDefined(propsIsEdit);
  const [internalIsEdit, setInternalIsEdit] = useState(!inputValue ? true : false);

  const isEdit = isControlled ? propsIsEdit : internalIsEdit;
  const [previousValue, setPreviousValue] = useState<string>(inputValue);

  let characterCount:
    | {
        maxLimit: number;
        inputCharacter: number;
      }
    | undefined = undefined;

  if (maxLimit) {
    characterCount = { maxLimit, inputCharacter: inputValue.toString().length };
  }

  const additionalAttributes = {
    ...(isDefined(dataAttribute) && { [dataAttribute]: true }),
  };

  useEffect(() => {
    if (isEdit && inputRef.current) {
      inputRef.current.focus();
      setPreviousValue(inputValue);
    }
    adjustHeight();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isEdit]);

  const handleToggleEdit = (newEditState: boolean) => {
    if (!isControlled) {
      setInternalIsEdit(newEditState);
    }
    onToggleEdit?.(newEditState);
  };

  const handleSave = () => {
    handleToggleEdit(false);
  };

  const handleCancel = () => {
    field.onChange(previousValue);
    handleToggleEdit(false);
  };

  const adjustHeight = () => {
    if (inputRef.current) {
      const textarea = inputRef.current;
      const currentHeight = textarea.offsetHeight;
      const scrollHeight = textarea.scrollHeight;

      if (scrollHeight > currentHeight) {
        if (maxHeight && scrollHeight > maxHeight) {
          textarea.style.height = `${maxHeight}px`;
          textarea.style.overflowY = 'auto';
        } else {
          textarea.style.height = `${scrollHeight}px`;
          textarea.style.overflowY = 'hidden';
        }
      }
    }
  };

  return (
    <FormFieldWrapper
      field={field}
      fieldState={fieldState}
      disabled={disabled}
      readOnly={readOnly}
      loading={loading}
      helpText={helpText}
      isHidden={isHidden}
      characterCount={characterCount}
      isSecondary={isSecondary}
      removeBorder={removeBorder}
      isInlineLabel={isInlineLabel}
      inputStyle={style}
    >
      {(inputProps) => {
        return (
          <div
            role="button"
            css={[styles.container({ isEdit, isDisabled: disabled || false, size }), wrapperCss]}
            aria-label={__('Question title field', __TUTOR_TEXT_DOMAIN__)}
          >
            <Show when={!isEdit}>
              <div
                data-placeholder
                css={styles.placeholder({ size })}
                onClick={() => !disabled && handleToggleEdit(true)}
                onKeyDown={(event) => {
                  if (event.key === 'Enter' || event.key === ' ') {
                    handleToggleEdit(true);
                  }
                }}
              >
                {field.value || placeholder}
              </div>
            </Show>

            <Show when={isEdit}>
              <div
                css={styles.inputContainer({
                  hasValueChanged: !!previousValue && field.value !== previousValue,
                  size,
                })}
              >
                <textarea
                  {...field}
                  {...inputProps}
                  {...additionalAttributes}
                  className="tutor-input-field"
                  rows={2}
                  ref={(element) => {
                    field.ref(element);
                    // @ts-ignore
                    inputRef.current = element; // this is not ideal but it is the only way to set ref to the input element
                  }}
                  value={inputValue}
                  placeholder={placeholder}
                  onChange={(event) => {
                    const { value } = event.target;

                    field.onChange(value);

                    if (onChange) {
                      onChange(value);
                    }
                  }}
                  onKeyDown={(event) => {
                    if (event.key === 'Enter' && (event.ctrlKey || event.metaKey)) {
                      event.preventDefault();
                      handleSave();
                    } else if (event.key === 'Escape') {
                      event.preventDefault();
                      handleCancel();
                    }
                    onKeyDown?.(event.key);
                  }}
                  onInput={adjustHeight}
                  onPaste={adjustHeight}
                  onFocus={(event) => {
                    if (selectOnFocus) {
                      event.target.select();
                    }
                  }}
                  autoComplete="off"
                />
              </div>
            </Show>
            <div data-action-buttons css={styles.actionButtonWrapper({ isEdit })}>
              <Show
                when={isEdit}
                fallback={
                  <Button
                    buttonCss={styles.actionButton}
                    variant="text"
                    size="small"
                    onClick={() => handleToggleEdit(true)}
                    aria-label={__('Edit question title', __TUTOR_TEXT_DOMAIN__)}
                    data-question-title-edit-button
                  >
                    <SVGIcon name="edit" height={24} width={24} />
                  </Button>
                }
              >
                <>
                  <Show when={field.value !== previousValue && field.value}>
                    <Button
                      buttonCss={styles.actionButton}
                      variant="text"
                      size="small"
                      onClick={handleSave}
                      disabled={field.value === previousValue}
                      aria-label={__('Save question title', __TUTOR_TEXT_DOMAIN__)}
                    >
                      <SVGIcon name="checkMark" height={24} width={24} />
                    </Button>
                  </Show>
                  <Show when={!!inputValue && previousValue}>
                    <Button
                      buttonCss={styles.actionButton}
                      variant="text"
                      size="small"
                      onClick={handleCancel}
                      aria-label={__('Cancel question title', __TUTOR_TEXT_DOMAIN__)}
                    >
                      <SVGIcon name="lineCross" height={24} width={24} />
                    </Button>
                  </Show>
                </>
              </Show>
            </div>
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormQuestionTitle;

const styles = {
  container: ({ isEdit, isDisabled, size }: { isEdit: boolean; isDisabled: boolean; size: Size }) => css`
    display: grid;
    align-items: center;
    gap: ${spacing[8]};
    grid-template-columns: 1fr auto;
    min-height: 50px;
    padding-inline: ${spacing[8]} ${spacing[16]};
    padding-block: ${spacing[8]};
    border-radius: ${borderRadius[6]};

    ${size === 'small' &&
    css`
      min-height: 40px;
      padding-inline: ${spacing[4]} ${spacing[8]};
      border-radius: ${borderRadius[4]};
    `}

    ${!isDisabled &&
    css`
      &:hover {
        background-color: ${colorTokens.background.white};
        color: ${colorTokens.text.subdued};

        [data-action-buttons] {
          opacity: ${!isDisabled ? 1 : 0};
        }
      }
    `}

    ${isEdit &&
    css`
      display: block;
      background-color: ${colorTokens.background.white};
      color: ${colorTokens.text.subdued};
      padding: 0;
      cursor: default;
    `}

    ${Breakpoint.smallTablet} {
      [data-action-buttons] {
        opacity: 1;
      }
    }
  `,
  inputContainer: ({ hasValueChanged, size }: { hasValueChanged: boolean; size: Size }) => css`
    position: relative;
    display: flex;
    transition: background 0.15s ease-in-out;

    & textarea {
      ${typography.heading6()}
      color: ${colorTokens.text.primary};
      border: none;
      background: none;
      padding: 0;
      width: 100%;
      padding: ${spacing[8]} ${spacing[16]};
      background-color: ${colorTokens.background.white};

      &.tutor-input-field {
        padding-right: ${hasValueChanged ? spacing[64] : spacing[32]};
      }

      ${size === 'small' &&
      css`
        ${typography.caption()}
      `}
    }
  `,
  placeholder: ({ size }: { size: Size }) => css`
    ${typography.heading6()}
    color: ${colorTokens.text.hints};
    border-radius: ${borderRadius[6]};

    ${size === 'small' &&
    css`
      ${typography.caption()}
      padding: ${spacing[4]} ${spacing[8]};
      border-radius: ${borderRadius[4]};
    `}
  `,
  actionButtonWrapper: ({ isEdit }: { isEdit: boolean }) => css`
    position: ${isEdit ? 'absolute' : 'static'};
    height: 100%;
    right: ${spacing[4]};
    top: 0;
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    opacity: 0;
    transition: opacity 0.15s ease-in-out;

    ${isEdit &&
    css`
      opacity: 1;
    `}
  `,
  actionButton: css`
    padding: 0;
    color: ${colorTokens.icon.subdued};
  `,
};
