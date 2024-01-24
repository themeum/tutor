import { borderRadius, colorPalate, fontSize, fontWeight, lineHeight, shadow, spacing } from '@Config/styles';
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
    padding: ${spacing[6]} ${spacing[12]};

    ${!removeBorder &&
    css`
      border: 1px solid ${colorPalate.border.neutral};
      border-radius: ${borderRadius[5]};
      box-shadow: ${shadow.input};
      background-color: ${colorPalate.basic.white};
    `}

    ${size === 'large' &&
    css`
      padding: ${spacing[10]} ${spacing[16]};
    `};

    ${hasFieldError &&
    css`
      border-color: ${colorPalate.basic.critical};
      background-color: ${colorPalate.surface.critical.neutral};
    `};
  `,
  input: (contentPosition: string, showVerticalBar: boolean, size: string) => css`
    ${typography.body()};
    height: 22px;
    border: none;
    box-shadow: none;
    padding: 0;
    background-color: transparent;
    ${showVerticalBar &&
    css`
        padding-${contentPosition}: ${spacing[10]};
      `};

    ${size === 'large' &&
    css`
      font-size: ${fontSize[24]};
      font-weight: ${fontWeight.medium};
      height: 34px;
      ${showVerticalBar &&
      css`
          padding-${contentPosition}: ${spacing[12]};
        `};
    `}
  `,
  inputLeftContent: (showVerticalBar: boolean, size: string) => css`
    color: ${colorPalate.text.neutral};
    padding-right: ${spacing[8]};
    font-size: ${fontSize[16]};
    line-height: ${lineHeight[20]};
    ${size === 'large' &&
    css`
      padding-right: ${spacing[12]};
      font-size: ${fontSize[24]};
      line-height: ${lineHeight[32]};
    `}
    ${showVerticalBar &&
    css`
      border-right: 1px solid ${colorPalate.border.neutral};
    `}
  `,
  inputRightContent: (showVerticalBar: boolean, size: string) => css`
    color: ${colorPalate.text.neutral};
    padding-left: ${spacing[8]};
    font-size: ${fontSize[16]};
    line-height: ${lineHeight[20]};
    ${size === 'large' &&
    css`
      padding-left: ${spacing[12]};
      font-size: ${fontSize[24]};
      line-height: ${lineHeight[32]};
    `}
    ${showVerticalBar &&
    css`
      border-left: 1px solid ${colorPalate.border.neutral};
    `}
  `,
};

export default FormInputWithContent;
