import { type SerializedStyles, css } from '@emotion/react';
import { useEffect, useRef, useState } from 'react';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import FormFieldWrapper from '@TutorShared/components/fields/FormFieldWrapper';

import { borderRadius, Breakpoint, colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { isDefined } from '@TutorShared/utils/types';

import { styleUtils } from '@TutorShared/utils/style-utils';

interface FormQuestionTitleProps extends FormControllerProps<string | null> {
  maxLimit?: number;
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
  selectOnFocus?: boolean;
}

const FormQuestionTitle = ({
  maxLimit,
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
  selectOnFocus = false,
}: FormQuestionTitleProps) => {
  const inputValue = field.value ?? '';
  const inputRef = useRef<HTMLInputElement>(null);

  const [isEdit, setIsEdit] = useState<boolean>(false);
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
    if (isDefined(inputRef.current)) {
      inputRef.current.focus();
      setPreviousValue(inputValue);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isEdit, inputRef.current]);

  return (
    <div role="button" tabIndex={0} css={styles.container({ isEdit, isDisabled: disabled || false })}>
      <Show when={!isEdit}>
        <div
          css={styles.placeholder}
          onClick={() => !disabled && setIsEdit(true)}
          onKeyDown={(event) => {
            if (event.key === 'Enter' || event.key === ' ') {
              setIsEdit(true);
            }
          }}
        >
          {field.value || placeholder}
        </div>
      </Show>

      <Show when={isEdit}>
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
              <>
                <div css={styles.inputContainer(false)}>
                  <input
                    {...field}
                    {...inputProps}
                    {...additionalAttributes}
                    className="tutor-input-field"
                    type="text"
                    ref={inputRef}
                    value={inputValue}
                    onChange={(event) => {
                      const { value } = event.target;

                      field.onChange(value);

                      if (onChange) {
                        onChange(value);
                      }
                    }}
                    onKeyDown={(event) => {
                      if (event.key === 'Enter') {
                        setIsEdit(false);
                      }
                      if (event.key === 'Escape') {
                        field.onChange(previousValue);
                        setIsEdit(false);
                      }
                      onKeyDown?.(event.key);
                    }}
                    onFocus={(event) => {
                      if (selectOnFocus) {
                        event.target.select();
                      }
                    }}
                    autoComplete="off"
                  />
                </div>
              </>
            );
          }}
        </FormFieldWrapper>
      </Show>
      <div data-action-buttons css={styles.actionButtonWrapper({ isEdit })}>
        <Show
          when={isEdit}
          fallback={
            <Button buttonCss={styles.actionButton} variant="text" size="small" onClick={() => setIsEdit(true)}>
              <SVGIcon name="edit" height={24} width={24} />
            </Button>
          }
        >
          <>
            <Show when={field.value !== previousValue}>
              <Button
                buttonCss={styles.actionButton}
                variant="text"
                size="small"
                onClick={() => {
                  setIsEdit(false);
                }}
                disabled={field.value === previousValue}
              >
                <SVGIcon name="checkMark" height={24} width={24} />
              </Button>
            </Show>
            <Button
              buttonCss={styles.actionButton}
              variant="text"
              size="small"
              onClick={() => {
                field.onChange(previousValue);
                setIsEdit(false);
              }}
            >
              <SVGIcon name="lineCross" height={24} width={24} />
            </Button>
          </>
        </Show>
      </div>
    </div>
  );
};

export default FormQuestionTitle;

const styles = {
  container: ({ isEdit, isDisabled }: { isEdit: boolean; isDisabled: boolean }) => css`
    position: relative;
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    gap: ${spacing[8]};
    min-height: 50px;
    height: 100%;
    width: 100%;
    cursor: text;
    padding-inline: ${spacing[8]} ${spacing[16]};
    padding-block: ${spacing[8]};
    border-radius: ${borderRadius[6]};
    transition: box-shadow 0.15s ease-in-out;
    border: 1px solid transparent;

    ${!isDisabled &&
    css`
      &:hover {
        background-color: ${colorTokens.background.white};
        color: ${colorTokens.text.subdued};

        [data-action-buttons] {
          opacity: ${!isDisabled ? 1 : 0};
        }
      }

      &:focus-within {
        ${isEdit && styleUtils.inputFocus}

        [data-action-buttons] {
          opacity: 1;
        }
      }

      &:focus-visible {
        outline: 2px solid ${colorTokens.stroke.brand};
      }
    `}

    ${isEdit &&
    css`
      background-color: ${colorTokens.background.white};
      color: ${colorTokens.text.subdued};
      padding-block: ${spacing[4]};
      border: 1px solid ${colorTokens.stroke.default};
      cursor: default;
    `}

    ${Breakpoint.smallTablet} {
      [data-action-buttons] {
        opacity: 1;
      }
    }
  `,
  inputContainer: (isClearable: boolean) => css`
    position: relative;
    display: flex;
    transition: background 0.15s ease-in-out;

    & input {
      ${typography.heading6()}
      color: ${colorTokens.text.primary};
      border: none;
      background: none;
      padding: 0;
      ${isClearable && `padding-right: ${spacing[36]};`};
      width: 100%;

      &.tutor-input-field {
        border: none;
        box-shadow: none;
        padding-inline: 0;

        &:focus {
          border: none;
          box-shadow: none;
          outline: none;
        }
      }
    }
  `,
  clearButton: css`
    position: absolute;
    right: ${spacing[2]};
    top: ${spacing[2]};
    width: 36px;
    height: 36px;
    border-radius: ${borderRadius[2]};
    background: transparent;

    button {
      padding: ${spacing[10]};
    }
  `,
  placeholder: css`
    ${typography.heading6()}
    color: ${colorTokens.text.hints};
    border-radius: ${borderRadius[6]};
  `,
  actionButtonWrapper: ({ isEdit }: { isEdit: boolean }) => css`
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
