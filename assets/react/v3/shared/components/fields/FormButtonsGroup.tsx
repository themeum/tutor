import { borderRadius, colorPalate, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import type { OptionWithIcon } from '@Utils/types';
import { css } from '@emotion/react';

import FormFieldWrapper from './FormFieldWrapper';

interface FormButtonsGroupProps<T> extends FormControllerProps<T> {
  label?: string;
  disabled?: boolean;
  helpText?: string;
  onChange?: () => void;
  options: OptionWithIcon<T>[];
}

const FormButtonsGroup = <T,>({
  label,
  field,
  fieldState,
  disabled,
  helpText,
  onChange,
  options,
}: FormButtonsGroupProps<T>) => {
  return (
    <FormFieldWrapper label={label} field={field} fieldState={fieldState} disabled={disabled} helpText={helpText}>
      {() => {
        return (
          <div css={styles.container}>
            {options.map((option) => (
              <button
                key={String(option.value)}
                type="button"
                css={styles.itemButton({
                  isActive: field.value === option.value,
                })}
                onClick={() => {
                  field.onChange(option.value);

                  if (onChange) {
                    onChange();
                  }
                }}
              >
                <span>{option.icon}</span>
                <span>{option.label}</span>
              </button>
            ))}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormButtonsGroup;

const styles = {
  container: css`
    display: inline-flex;
    border-radius: ${borderRadius[6]};
    border: 1px solid ${colorPalate.border.neutral};
  `,
  itemButton: ({ isActive }: { isActive: boolean }) => css`
    ${styleUtils.resetButton};
    ${typography.body()};
    min-width: 40px;
    width: 100%;
    padding: ${spacing[8]} ${spacing[24]};
    display: flex;
    align-items: center;
    gap: ${spacing[12]};
    border: 1.5px solid transparent;
    margin: -1px;
    color: ${colorPalate.text.neutral};
    transition: border 0.3s ease-in-out, border-radius 0.3s ease-in-out;

    ${
      isActive &&
      css`
      border: 1.5px solid ${colorPalate.basic.primary.default};
      border-radius: ${borderRadius[6]};
      color: ${colorPalate.text.default};
    `
    }
  `,
};
