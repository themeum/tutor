import { borderRadius, colorTokens, spacing } from '@Config/styles';
import type { FormControllerProps } from '@Utils/form';
import { css } from '@emotion/react';

import { typography } from '@Config/typography';
import { styleUtils } from '@Utils/style-utils';
import type { OptionWithImage } from '@Utils/types';
import FormFieldWrapper from './FormFieldWrapper';

interface FormImageRadioGroupProps<T> extends FormControllerProps<T> {
  label?: string;
  options: OptionWithImage<T>[];
  disabled?: boolean;
}

const FormImageRadioGroup = <T,>({ field, fieldState, label, options = [], disabled }: FormImageRadioGroupProps<T>) => {
  return (
    <FormFieldWrapper field={field} fieldState={fieldState} label={label} disabled={disabled}>
      {() => {
        return (
          <div css={styles.wrapper}>
            {options.map((option, index) => (
              <button
                type="button"
                key={index}
                css={styles.item(field.value === option.value)}
                onClick={() => {
                  field.onChange(option.value);
                }}
                disabled={disabled}
              >
                <img src={option.image} alt={option.label} width={64} height={64} />
                <p>{option.label}</p>
              </button>
            ))}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormImageRadioGroup;

const styles = {
  wrapper: css`
    display: grid;
    grid-template-columns: repeat(4, minmax(64px, 1fr));
    gap: ${spacing[12]};
    margin-top: ${spacing[4]};
  `,
  item: (isActive: boolean) => css`
    ${styleUtils.resetButton};
    display: flex;
    flex-direction: column;
    gap: ${spacing[4]};
    width: 100%;
    cursor: pointer;

    input {
      appearance: none;
    }

    p {
      ${typography.small()};
      width: 100%;
      ${styleUtils.textEllipsis};
      color: ${colorTokens.text.subdued};
      text-align: center;
    }

    &:hover,
    &:focus-visible {
      ${!isActive &&
      css`
        img {
          border-color: ${colorTokens.stroke.hover};
        }
      `}
    }

    img {
      border-radius: ${borderRadius[6]};
      border: 2px solid ${colorTokens.stroke.border};
      outline: 2px solid transparent;
      outline-offset: 2px;
      transition: border-color 0.3s ease;

      ${isActive &&
      css`
        outline-color: ${colorTokens.stroke.magicAi};
      `}
    }
  `,
};
