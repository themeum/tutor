import { type SerializedStyles, css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import type { ReactNode } from 'react';

import LoadingSpinner from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import Tooltip from '@Atoms/Tooltip';

import { tutorConfig } from '@Config/config';
import { borderRadius, colorTokens, lineHeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import Show from '@Controls/Show';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';
import { nanoid } from '@Utils/util';

interface InputOptions {
  variant: unknown;
  hasFieldError: boolean;
  removeBorder: boolean;
  readOnly: boolean;
  hasHelpText: boolean;
  isSecondary: boolean;
  isMagicAi: boolean;
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
  generateWithAi?: boolean;
  onClickAiButton?: () => void;
  isMagicAi?: boolean;
  replaceEntireLabel?: boolean;
}

const isOpenAiEnabled = tutorConfig.settings?.chatgpt_enable === 'on';

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
  onClickAiButton,
  isMagicAi = false,
  generateWithAi = false,
  replaceEntireLabel = false,
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
      isMagicAi,
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
              <label htmlFor={id} css={styles.label(isInlineLabel, replaceEntireLabel)}>
                {label}
                <Show when={generateWithAi}>
                  <button
                    type="button"
                    onClick={() => {
                      onClickAiButton?.();
                    }}
                    css={styles.aiButton}
                  >
                    <SVGIcon name="magicAiColorize" width={32} height={32} />
                  </button>
                </Show>
              </label>
            )}

            {helpText && !replaceEntireLabel && (
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
            content={
              characterCount.maxLimit - characterCount.inputCharacter >= 0
                ? characterCount.maxLimit - characterCount.inputCharacter
                : __('Limit exceeded', 'tutor')
            }
          >
            {inputContent}
          </Tooltip>
        ) : (
          inputContent
        )}
      </div>
      {fieldState.error?.message && (
        <p css={styles.errorLabel(!!fieldState.error)}>
          <SVGIcon style={styles.alertIcon} name="info" width={20} height={20} /> {fieldState.error.message}
        </p>
      )}
    </div>
  );
};

export default FormFieldWrapper;

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
        ${styleUtils.inputFocus};

        ${
          options.isMagicAi &&
          css`
          outline-color: ${colorTokens.stroke.magicAi};
          background-color: ${colorTokens.background.magicAi[8]};
        `
        } 

        ${
          options.hasFieldError &&
          css`
          border-color: ${colorTokens.stroke.danger};
        `
        }
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
        border-color: ${colorTokens.stroke.danger};
        background-color: ${colorTokens.background.status.errorFail};
      `
      }

      ${
        options.readOnly &&
        css`
        border-color: ${colorTokens.background.disable};
        background-color: ${colorTokens.background.disable};
      `
      }
    }
    
  `,
  errorLabel: (hasError: boolean) => css`
    ${typography.small()};
    line-height: ${lineHeight[20]};
    display: flex;
    align-items: start;
    margin-top: ${spacing[4]};
    ${
      hasError &&
      css`
      color: ${colorTokens.text.status.onHold};
    `
    }
    & svg {
      margin-right: ${spacing[2]};
      transform: rotate(180deg);
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
  label: (isInlineLabel: boolean, replaceEntireLabel: boolean) => css`
    ${typography.caption()};
    width: ${replaceEntireLabel ? '100%' : 'auto'};
    color: ${colorTokens.text.title};
    display: flex;
    align-items: center;
    gap: ${spacing[4]};

    ${
      isInlineLabel &&
      css`
      ${typography.caption()};
    `
    }
  `,
  aiButton: css`
    ${styleUtils.resetButton};
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    
    :disabled {
      cursor: not-allowed;
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
