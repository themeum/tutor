import { spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import type { FormControllerProps } from '@Utils/form';
import { css } from '@emotion/react';

import FormFieldWrapper from './FormFieldWrapper';
import WPEditor from '@Atoms/WPEditor';

interface FormWPEditorProps extends FormControllerProps<string | null> {
  label?: string;
  maxLimit?: number;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: (value: string) => void;
  isHidden?: boolean;
  enableResize?: boolean;
}

const DEFAULT_ROWS = 6;

const FormWPEditor = ({
  label,
  maxLimit,
  field,
  fieldState,
  disabled,
  readOnly,
  loading,
  placeholder,
  helpText,
  onChange,
  isHidden,
  enableResize = false,
}: FormWPEditorProps) => {
  const inputValue = field.value ?? '';

  let characterCount: { maxLimit: number; inputCharacter: number } | undefined = undefined;

  if (maxLimit) {
    characterCount = { maxLimit, inputCharacter: inputValue.toString().length };
  }

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
    >
      {(inputProps) => {
        return (
          <>
            <div css={styles.container(enableResize)}>
              <WPEditor
                value={field.value ?? ''}
                onChange={(value) => {
                  field.onChange(value);

                  if (onChange) {
                    onChange(value);
                  }
                }}
              />
            </div>
          </>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormWPEditor;

const styles = {
  container: (enableResize = false) => css`
    position: relative;
    display: flex;

    textarea {
      ${typography.body()};
      height: auto;
      padding: ${spacing[8]} ${spacing[12]};
      resize: none;

      ${enableResize &&
      css`
        resize: vertical;
      `}
    }
  `,
};
