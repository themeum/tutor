import { css } from '@emotion/react';
import { useEffect, useRef, useState } from 'react';

import Button from '@Atoms/Button';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { FormControllerProps } from '@Utils/form';
import { isDefined } from '@Utils/types';

import FormFieldWrapper from './FormFieldWrapper';

interface FormQuestionDescriptionProps extends FormControllerProps<string | null> {
  label?: string;
  rows?: number;
  columns?: number;
  maxLimit?: number;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: (value: string | number) => void;
  onKeyDown?: (keyName: string) => void;
  isHidden?: boolean;
  enableResize?: boolean;
  isSecondary?: boolean;
}

const DEFAULT_ROWS = 6;

const FormQuestionDescription = ({
  label,
  rows = DEFAULT_ROWS,
  columns,
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
  enableResize = false,
  isSecondary = false,
}: FormQuestionDescriptionProps) => {
  const inputValue = field.value ?? '';
  const textareaRef = useRef<HTMLTextAreaElement>(null);

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

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (isDefined(textareaRef.current)) {
      textareaRef.current.focus();
      setPreviousValue(inputValue);
    }
  }, [isEdit, textareaRef.current]);

  return (
    <div
      css={styles.container({ isEdit })}
      onClick={() => {
        if (!isEdit) {
          setIsEdit(true);
        }
      }}
      onKeyDown={(event) => {
        if ((event.key === 'Enter' || event.key === ' ') && !isEdit) {
          setIsEdit(true);
        }
      }}
    >
      <Show
        when={isEdit}
        fallback={
          <div css={styles.placeholder} dangerouslySetInnerHTML={{ __html: field.value || placeholder || '' }} />
        }
      >
        <FormFieldWrapper
          label={label}
          field={field}
          fieldState={fieldState}
          disabled={disabled}
          readOnly={readOnly}
          loading={loading}
          helpText={helpText}
          isHidden={isHidden}
          characterCount={characterCount}
          isSecondary={isSecondary}
        >
          {(inputProps) => {
            return (
              <>
                <div css={styles.inputContainer(enableResize)}>
                  <textarea
                    {...field}
                    {...inputProps}
                    ref={textareaRef}
                    value={inputValue}
                    onChange={(event) => {
                      const { value } = event.target;
                      if (maxLimit && value.trim().length > maxLimit) {
                        return;
                      }

                      field.onChange(value);

                      if (onChange) {
                        onChange(value);
                      }
                    }}
                    onKeyDown={(event) => {
                      if (event.key === 'Escape') {
                        field.onChange(previousValue);
                        setIsEdit(false);
                      }
                      onKeyDown?.(event.key);
                    }}
                    autoComplete="off"
                    rows={rows}
                    cols={columns}
                  />
                </div>
              </>
            );
          }}
        </FormFieldWrapper>
        <div data-action-buttons css={styles.actionButtonWrapper({ isEdit })}>
          <Button
            variant="text"
            size="small"
            onClick={() => {
              field.onChange(previousValue);
              setIsEdit(false);
            }}
          >
            Cancel
          </Button>
          <Button
            variant="secondary"
            size="small"
            onClick={() => {
              setIsEdit(false);
            }}
            disabled={field.value === previousValue}
          >
            Ok
          </Button>
        </div>
      </Show>
    </div>
  );
};

export default FormQuestionDescription;

const styles = {
  container: ({
    isEdit,
  }: {
    isEdit: boolean;
  }) => css`
    position: relative;
    display: flex;
    flex-direction: column;
    gap: ${spacing[16]};
    min-height: 64px;
    height: 100%;
    width: 100%;
    inset: 0;
    padding-inline: ${spacing[8]} ${spacing[16]};
    border-radius: ${borderRadius[6]};
    transition: background 0.15s ease-in-out;

    & label {
      ${typography.caption()}
      margin-bottom: ${spacing[6]};
      color: ${colorTokens.text.title};
    }

    &:hover {
      background-color: ${colorTokens.background.white};
      color: ${colorTokens.text.subdued};

			[data-action-buttons] {
				opacity: 1;
			}

      ${
        isEdit &&
        css`
          background-color: transparent;
        `
      }
      
    };

    ${
      isEdit &&
      css`
        padding-inline: 0;
      `
    }
  `,
  inputContainer: (enableResize: boolean) => css`
    position: relative;
    display: flex;
    cursor: text;

    & textarea {
      ${typography.heading6()}
      height: auto;
      resize: none;

      &.tutor-input-field {
        padding: ${spacing[8]};
      }

      ${
        enableResize &&
        css`
        resize: vertical;
      `
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
    height: 100%;
    inset: 0;
  `,
  actionButtonWrapper: ({
    isEdit,
  }: {
    isEdit: boolean;
  }) => css`
    display: flex;
		justify-content: flex-end;
		gap: ${spacing[8]};
    opacity: 0;
    transition: opacity 0.15s ease-in-out;

		${
      isEdit &&
      css`
				opacity: 1;
			`
    }

  `,
};
