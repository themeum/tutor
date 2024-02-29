import { borderRadius, colorTokens, fontSize, fontWeight, lineHeight, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css, SerializedStyles } from '@emotion/react';
import { FormControllerProps } from '@Utils/form';
import { ReactNode } from 'react';

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
  removeBorder?: boolean;
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
  removeBorder = false,
}: FormInputWithContentProps) => {
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
          <div css={[styles.inputWrapper(size, !!fieldState.error, removeBorder), wrapperCss]}>
            {contentPosition === 'left' && <div css={styles.inputLeftContent(showVerticalBar, size)}>{content}</div>}

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
              onKeyDown={(event) => onKeyDown && onKeyDown(event.key)}
              css={[inputCss, styles.input(contentPosition, showVerticalBar, size)]}
              autoComplete="off"
            />

            {contentPosition === 'right' && <div css={styles.inputRightContent(showVerticalBar, size)}>{content}</div>}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

const styles = {
  inputWrapper: (size: string, hasFieldError: boolean, removeBorder: boolean) => css`
    display: flex;
    align-items: center;

    ${!removeBorder &&
    css`
      border: 1px solid ${colorTokens.stroke.default};
      border-radius: ${borderRadius[5]};
      box-shadow: ${shadow.input};
      background-color: ${colorTokens.background.white};
    `}

    ${hasFieldError &&
    css`
      border-color: ${colorTokens.stroke.danger};
    `};

    &:focus-within {
      box-shadow: ${shadow.focus};
    }
  `,
  input: (contentPosition: string, showVerticalBar: boolean, size: string) => css`
    ${typography.body()};
    border: none;
    box-shadow: none;
    padding-inline: ${spacing[12]};
    background-color: transparent;
    ${showVerticalBar &&
    css`
        padding-${contentPosition}: ${spacing[10]};
      `};

    ${size === 'large' &&
    css`
      font-size: ${fontSize[24]};
      padding-inline: ${spacing[16]};
      font-weight: ${fontWeight.medium};
      height: 34px;
      ${showVerticalBar &&
      css`
          padding-${contentPosition}: ${spacing[12]};
        `};
    `}

    &:focus {
      box-shadow: none;
    }
  `,
  inputLeftContent: (showVerticalBar: boolean, size: string) => css`
    ${typography.small()}
    display: flex;
    justify-content: center;
    align-items: center;
    height: 40px;
    color: ${colorTokens.icon.subdued};
    padding-inline: ${spacing[12]};

    ${size === 'large' &&
    css`
      ${typography.body()}
    `}

    ${showVerticalBar &&
    css`
      border-right: 1px solid ${colorTokens.stroke.default};
    `}
  `,
  inputRightContent: (showVerticalBar: boolean, size: string) => css`
    ${typography.small()}
    display: flex;
    justify-content: center;
    align-items: center;
    height: 40px;
    color: ${colorTokens.icon.subdued};
    padding-inline: ${spacing[12]};

    ${size === 'large' &&
    css`
      ${typography.body()}
    `}

    ${showVerticalBar &&
    css`
      border-left: 1px solid ${colorTokens.stroke.default};
    `}
  `,
};

export default FormInputWithContent;
