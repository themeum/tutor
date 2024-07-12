import LoadingSpinner from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import Tooltip from '@Atoms/Tooltip';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import type { FormControllerProps } from '@Utils/form';
import { isDefined } from '@Utils/types';
import { nanoid } from '@Utils/util';
import { type SerializedStyles, css } from '@emotion/react';
import type { ReactNode } from 'react';

interface InputOptions {
  variant: unknown;
  hasFieldError: boolean;
  removeBorder: boolean;
  readOnly: boolean;
  hasHelpText: boolean;
  isSecondary: boolean;
}

interface InputProps {
  id: string;
  name: string;
  css: SerializedStyles[];
  'aria-invalid': 'true' | 'false';
  disabled: boolean;
  readOnly: boolean;
  placeholder?: string;
  className?: string;
}

interface FormFieldWrapperProps<T> extends FormControllerProps<T> {
  label?: string | ReactNode;
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
  isSecondary?: boolean;
  inputStyle?: SerializedStyles;
}

const styles = {
  container: ({ disabled, isHidden }: { disabled: boolean; isHidden: boolean }) => css`
    display: flex;
    flex-direction: column;
    position: relative;
    background: inherit;
    width: 100%;

    ${
      disabled &&
      css`
      opacity: 0.5;
    `
    }

    ${
      isHidden &&
      css`
      display: none;
    `
    }
  `,
  inputContainer: (isInlineLabel: boolean) => css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[4]};
    width: 100%;

    ${
      isInlineLabel &&
      css`
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
      gap: ${spacing[12]};
    `
    }
  `,
  input: (options: InputOptions) => css`
    &.tutor-input-field {
      width: 100%;
      border-radius: ${borderRadius[6]};
      border: 1px solid ${colorTokens.stroke.default};
      padding: ${spacing[8]} ${spacing[16]};
      color: ${colorTokens.text.title};
      appearance: textfield;

      &:not(textarea) {
        height: 40px;
      }

      ${
        options.hasHelpText &&
        css`
        padding: 0 ${spacing[32]} 0 ${spacing[12]};
      `
      }

      ${
        options.removeBorder &&
        css`
        border-radius: 0;
        border: none;
        box-shadow: none;
      `
      }

      ${
        options.isSecondary &&
        css`
        border-color: transparent;
      `
      }

      :focus {
        outline: none;
        box-shadow: ${shadow.focus};
      }

      ::-webkit-outer-spin-button,
      ::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
      }

      ::placeholder {
        ${typography.caption('regular')};
        color: ${colorTokens.text.hints};

        ${
          options.isSecondary &&
          css`
          color: ${colorTokens.text.hints};
        `
        }
      }

      ${
        options.hasFieldError &&
        css`
        border: 1px solid ${colorTokens.stroke.danger};
      `
      }

      ${
        options.readOnly &&
        css`
        border: 1px solid ${colorTokens.background.disable};
        background-color: ${colorTokens.background.disable};
      `
      }
    }
    
  `,
  errorLabel: (hasError: boolean) => css`
    ${typography.small()};
    display: flex;
    align-items: start;
    margin-top: ${spacing[4]};
    ${
      hasError &&
      css`
      color: ${colorTokens.color.danger.main};
    `
    }
    & svg {
      margin-right: ${spacing[8]};
    }
  `,
  labelContainer: css`
    display: flex;
    align-items: center;
    gap: ${spacing[4]};

    > div {
      display: flex;
      color: ${colorTokens.color.black[30]};
    }
  `,
  label: (isInlineLabel: boolean) => css`
    ${typography.caption()};
    color: ${colorTokens.text.title};

    ${
      isInlineLabel &&
      css`
      ${typography.caption()};
    `
    }
  `,
  inputWrapper: css`
    position: relative;
  `,
  loader: css`
    position: absolute;
    top: 50%;
    right: ${spacing[12]};
    transform: translateY(-50%);
    display: flex;
  `,
  alertIcon: css`
    flex-shrink: 0;
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
  isSecondary = false,
  inputStyle,
}: FormFieldWrapperProps<T>) => {
  const id = nanoid();

  const inputCss = [
    styles.input({
      variant,
      hasFieldError: !!fieldState.error,
      removeBorder,
      readOnly,
      hasHelpText: !!helpText,
      isSecondary,
    }),
  ];

  if (isDefined(inputStyle)) {
    inputCss.push(inputStyle);
  }

  const inputContent = (
    <div css={styles.inputWrapper}>
      {children({
        id,
        name: field.name,
        css: inputCss,
        'aria-invalid': fieldState.error ? 'true' : 'false',
        disabled: disabled,
        readOnly: readOnly,
        placeholder,
        className: 'tutor-input-field',
      })}

      {loading && (
        <div css={styles.loader}>
          <LoadingSpinner size={20} color={colorTokens.icon.default} />
        </div>
      )}
    </div>
  );

  return (
    <div css={styles.container({ disabled, isHidden })}>
      <div css={styles.inputContainer(isInlineLabel)}>
        {(label || helpText) && (
          <div css={styles.labelContainer}>
            {label && (
              <label htmlFor={id} css={styles.label(isInlineLabel)}>
                {label}
              </label>
            )}

            {helpText && (
              <Tooltip content={helpText} placement="top" allowHTML>
                <SVGIcon name="info" width={20} height={20} />
              </Tooltip>
            )}
          </div>
        )}

        {characterCount ? (
          <Tooltip
            placement="right"
            hideOnClick={false}
            content={characterCount.maxLimit - characterCount.inputCharacter}
          >
            {inputContent}
          </Tooltip>
        ) : (
          inputContent
        )}
      </div>
      {fieldState.error?.message && (
        <p css={styles.errorLabel(!!fieldState.error)}>
          <SVGIcon style={styles.alertIcon} name="alert" width={20} height={20} /> {fieldState.error.message}
        </p>
      )}
    </div>
  );
};

export default FormFieldWrapper;
