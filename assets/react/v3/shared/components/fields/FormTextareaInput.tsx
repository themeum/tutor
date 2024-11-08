import { spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import type { FormControllerProps } from '@Utils/form';
import { type SerializedStyles, css } from '@emotion/react';

import { useLayoutEffect, useRef } from 'react';
import FormFieldWrapper from './FormFieldWrapper';

interface FormTextareaInputProps extends FormControllerProps<string | null> {
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
  isMagicAi?: boolean;
  inputCss?: SerializedStyles;
  maxHeight?: number;
  autoResize?: boolean;
}

const DEFAULT_ROWS = 6;

const FormTextareaInput = ({
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
  enableResize = true,
  isSecondary = false,
  isMagicAi = false,
  inputCss,
  maxHeight,
  autoResize = false,
}: FormTextareaInputProps) => {
  const inputValue = field.value ?? '';

  const ref = useRef<HTMLTextAreaElement>(null);

  let characterCount: { maxLimit: number; inputCharacter: number } | undefined = undefined;

  if (maxLimit) {
    characterCount = { maxLimit, inputCharacter: inputValue.toString().length };
  }

  const adjustHeight = () => {
    if (ref.current) {
      if (maxHeight) {
        ref.current.style.maxHeight = `${maxHeight}px`;
      }

      ref.current.style.height = 'auto';
      ref.current.style.height = `${ref.current.scrollHeight}px`;
    }
  };

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useLayoutEffect(() => {
    if (autoResize) {
      adjustHeight();
    }
  }, []);

  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
      disabled={disabled}
      readOnly={readOnly}
      loading={loading}
      placeholder={placeholder}
      helpText={helpText}
      isHidden={isHidden}
      characterCount={characterCount}
      isSecondary={isSecondary}
      isMagicAi={isMagicAi}
    >
      {(inputProps) => {
        return (
          <>
            <div css={styles.container(enableResize, inputCss)}>
              <textarea
                {...field}
                {...inputProps}
                ref={(element) => {
                  field.ref(element);
                  // @ts-ignore
                  ref.current = element; // this is not ideal but it is the only way to set ref to the input element
                }}
                style={{ maxHeight: maxHeight ? `${maxHeight}px` : 'none' }}
                className="tutor-input-field"
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

                  if (autoResize) {
                    adjustHeight();
                  }
                }}
                onKeyDown={(event) => {
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
  );
};

export default FormTextareaInput;

const styles = {
  container: (enableResize = false, inputCss?: SerializedStyles) => css`
    position: relative;
    display: flex;

    textarea {
      ${typography.body()};
      height: auto;
      padding: ${spacing[8]} ${spacing[12]};
      resize: none;

      &.tutor-input-field {
        ${inputCss};
      }

      ${
        enableResize &&
        css`
          resize: vertical;
        `
      }
    }
  `,
};
