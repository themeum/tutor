import { borderRadius, colorTokens, fontSize, fontWeight, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import type { FormControllerProps } from '@Utils/form';
import { type SerializedStyles, css } from '@emotion/react';
import { type ReactNode, useRef } from 'react';

import { styleUtils } from '@Utils/style-utils';
import FormFieldWrapper from './FormFieldWrapper';

interface FormInputWithContentProps extends FormControllerProps<string | number | null | undefined> {
  content: string | ReactNode;
  contentPosition?: 'left' | 'right';
  showVerticalBar?: boolean;
  type?: 'number' | 'text';
  size?: 'regular' | 'large';
  label?: string;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: (value: number | string) => void;
  onKeyDown?: (value: string) => void;
  isHidden?: boolean;
  wrapperCss?: SerializedStyles;
  contentCss?: SerializedStyles;
  removeBorder?: boolean;
  selectOnFocus?: boolean;
}

const FormInputWithContent = ({
  label,
  content,
  contentPosition = 'left',
  showVerticalBar = true,
  size = 'regular',
  type = 'text',
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
  wrapperCss,
  contentCss,
  removeBorder = false,
  selectOnFocus = false,
}: FormInputWithContentProps) => {
  const ref = useRef<HTMLInputElement>(null);
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
      removeBorder={removeBorder}
    >
      {(inputProps) => {
        const { css: inputCss, ...restInputProps } = inputProps;
        return (
          <div css={[styles.inputWrapper(!!fieldState.error, removeBorder), wrapperCss]}>
            {contentPosition === 'left' && (
              <div css={[styles.inputLeftContent(showVerticalBar, size), contentCss]}>{content}</div>
            )}

            <input
              {...field}
              {...restInputProps}
              type="text"
              value={field.value ?? ''}
              onChange={(e) => {
                const value: string | number =
                  type === 'number'
                    ? e.target.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')
                    : e.target.value;

                field.onChange(value);

                if (onChange) {
                  onChange(value);
                }
              }}
              onKeyDown={(event) => onKeyDown?.(event.key)}
              css={[inputCss, styles.input(contentPosition, showVerticalBar, size)]}
              autoComplete="off"
              ref={(element) => {
                field.ref(element);
                // @ts-ignore
                ref.current = element; // this is not ideal but it is the only way to set ref to the input element
              }}
              onFocus={() => {
                if (!selectOnFocus || !ref.current) {
                  return;
                }
                ref.current.select();
              }}
              data-input
            />

            {contentPosition === 'right' && <div css={styles.inputRightContent(showVerticalBar, size)}>{content}</div>}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

const styles = {
  inputWrapper: (hasFieldError: boolean, removeBorder: boolean) => css`
    display: flex;
    align-items: center;

    ${
      !removeBorder &&
      css`
        border: 1px solid ${colorTokens.stroke.default};
        border-radius: ${borderRadius[6]};
        box-shadow: ${shadow.input};
        background-color: ${colorTokens.background.white};
      `
    }

    ${
      hasFieldError &&
      css`
        border-color: ${colorTokens.stroke.danger};
        background-color: ${colorTokens.background.status.errorFail};
      `
    };

    &:focus-within {
      ${styleUtils.inputFocus};

      ${
        hasFieldError &&
        css`
        border-color: ${colorTokens.stroke.danger};
      `
      }
    }
  `,
  input: (contentPosition: string, showVerticalBar: boolean, size: string) => css`
    /** Increasing the css specificity */
    &[data-input] {
      ${typography.body()};
      border: none;
      box-shadow: none;
      background-color: transparent;
      padding-${contentPosition}: 0;
  
      ${
        showVerticalBar &&
        css`
          padding-${contentPosition}: ${spacing[10]};
        `
      };
  
      ${
        size === 'large' &&
        css`
        font-size: ${fontSize[24]};
        font-weight: ${fontWeight.medium};
        height: 34px;
        ${
          showVerticalBar &&
          css`
            padding-${contentPosition}: ${spacing[12]};
          `
        };
      `
      }
  
      &:focus {
        box-shadow: none;
        outline: none;
      }
    }
  `,
  inputLeftContent: (showVerticalBar: boolean, size: string) => css`
    ${typography.small()}
    ${styleUtils.flexCenter()}
    height: 40px;
    min-width: 48px;
    color: ${colorTokens.icon.subdued};
    padding-inline: ${spacing[12]};

    ${
      size === 'large' &&
      css`
        ${typography.body()}
      `
    }

    ${
      showVerticalBar &&
      css`
        border-right: 1px solid ${colorTokens.stroke.default};
      `
    }
  `,
  inputRightContent: (showVerticalBar: boolean, size: string) => css`
    ${typography.small()}
    ${styleUtils.flexCenter()}
    height: 40px;
    min-width: 48px;
    color: ${colorTokens.icon.subdued};
    padding-inline: ${spacing[12]};

    ${
      size === 'large' &&
      css`
        ${typography.body()}
      `
    }

    ${
      showVerticalBar &&
      css`
        border-left: 1px solid ${colorTokens.stroke.default};
      `
    }
  `,
};

export default FormInputWithContent;
