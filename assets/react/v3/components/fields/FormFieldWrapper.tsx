import LoadingSpinner from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import Tooltip from '@Atoms/Tooltip';
import { borderRadius, colorPalate, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css, SerializedStyles } from '@emotion/react';
import { FormControllerProps } from '@Utils/form';
import { nanoid } from '@Utils/util';
import { ReactNode } from 'react';

interface InputOptions {
  variant: unknown;
  hasFieldError: boolean;
  removeBorder: boolean;
  readOnly: boolean;
  hasHelpText: boolean;
}

interface InputProps {
  id: string;
  name: string;
  css: SerializedStyles[];
  'aria-invalid': 'true' | 'false';
  disabled: boolean;
  readOnly: boolean;
  placeholder?: string;
}

interface FormFieldWrapperProps<T> extends FormControllerProps<T> {
  label?: string;
  isInlineLabel?: boolean;
  children: (inputProps: InputProps) => ReactNode;
  placeholder?: string;
  variant?: unknown;
  loading?: boolean;
  disabled?: boolean;
  readOnly?: boolean;
  helpText?: string;
  isHidden?: boolean;
  removeBorder?: boolean;
  characterCount?: { maxLimit: number; inputCharacter: number };
}

const styles = {
  container: ({ disabled, isHidden }: { disabled: boolean; isHidden: boolean }) => css`
    display: flex;
    flex-direction: column;
    position: relative;
    background: inherit;

    ${disabled &&
    css`
      opacity: 0.5;
    `}

    ${isHidden &&
    css`
      display: none;
    `}
  `,
  inputContainer: (isInlineLabel: boolean) => css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[4]};
    width: 100%;

    ${isInlineLabel &&
    css`
      flex-direction: row;
      align-items: center;
      gap: ${spacing[12]};
    `}
  `,
  input: (options: InputOptions) => css`
    width: 100%;
    height: 36px;
    border-radius: ${borderRadius[5]};
    border: 1px solid ${colorPalate.border.neutral};
    box-shadow: ${shadow.input};
    padding: 0 ${spacing[12]};
    color: ${colorPalate.text.default};
    appearance: textfield;

    ${options.hasHelpText &&
    css`
      padding: 0 ${spacing[32]} 0 ${spacing[12]};
    `}

    ${options.removeBorder &&
    css`
      border-radius: 0;
      border: none;
      box-shadow: none;
    `}

    :focus {
      outline: none;
      box-shadow: none;
    }

    ::-webkit-outer-spin-button,
    ::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    ::placeholder {
      color: ${colorPalate.text.disabled};
    }

    ${options.hasFieldError &&
    css`
      border: 1px solid ${colorPalate.basic.critical};
      background-color: ${colorPalate.surface.critical.neutral};
    `}

    ${options.readOnly &&
    css`
      border: 1px solid ${colorPalate.background.hover};
      background-color: ${colorPalate.background.hover};
    `}
  `,
  errorLabel: (hasError: boolean) => css`
    ${typography.body()};
    display: flex;
    align-items: center;
    margin-top: ${spacing[4]};
    ${hasError &&
    css`
      color: ${colorPalate.basic.critical};
    `}
    & svg {
      margin-right: ${spacing[8]};
    }
  `,
  labelContainer: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
  `,
  label: (isInlineLabel: boolean) => css`
    ${typography.heading6()}
    color: ${colorPalate.text.default};

    ${isInlineLabel &&
    css`
      ${typography.body()}
      color: ${colorPalate.text.neutral};
    `}
  `,
  characterCount: (isExceedMaxLimit = false) => css`
    ${typography.body()};
    color: ${colorPalate.text.neutral};
    text-transform: lowercase;
    margin-left: auto;

    ${isExceedMaxLimit &&
    css`
      color: ${colorPalate.text.critical};
    `}
  `,
  inputAndHelpText: css`
    position: relative;
  `,
  helpTextAndLoading: css`
    position: absolute;
    top: 50%;
    right: ${spacing[12]};
    transform: translateY(-50%);
    display: flex;
    gap: ${spacing[4]};
    align-items: center;
  `,
};

const FormFieldWrapper = <T,>({
  field,
  fieldState,
  children,
  disabled = false,
  readOnly = false,
  label,
  isInlineLabel = false,
  variant,
  loading,
  placeholder,
  helpText,
  isHidden = false,
  removeBorder = false,
  characterCount,
}: FormFieldWrapperProps<T>) => {
  const id = nanoid();
  return (
    <div css={styles.container({ disabled, isHidden })}>
      <div css={styles.inputContainer(isInlineLabel)}>
        {(label || characterCount) && (
          <div css={styles.labelContainer}>
            {label && (
              <label htmlFor={id} css={styles.label(isInlineLabel)}>
                {label}
              </label>
            )}
            {characterCount && (
              <p css={styles.characterCount(characterCount.inputCharacter > characterCount.maxLimit)}>
                {characterCount.inputCharacter} / {characterCount.maxLimit}
              </p>
            )}
          </div>
        )}

        <div css={styles.inputAndHelpText}>
          {children({
            id,
            name: field.name,
            css: [
              styles.input({
                variant,
                hasFieldError: !!fieldState.error,
                removeBorder,
                readOnly,
                hasHelpText: !!helpText,
              }),
            ],
            'aria-invalid': fieldState.error ? 'true' : 'false',
            disabled: disabled,
            readOnly: readOnly,
            placeholder,
          })}

          <div css={styles.helpTextAndLoading}>
            {loading && <LoadingSpinner size={20} color={colorPalate.icon.default} />}
            {helpText && (
              <Tooltip content={helpText} allowHTML>
                <SVGIcon name="questionCircle" width={18} height={18} />
              </Tooltip>
            )}
          </div>
        </div>
      </div>
      {fieldState.error?.message && (
        <p css={styles.errorLabel(!!fieldState.error)}>
          <SVGIcon name="alert" width={20} height={20} /> {fieldState.error.message}
        </p>
      )}
    </div>
  );
};

export default FormFieldWrapper;
