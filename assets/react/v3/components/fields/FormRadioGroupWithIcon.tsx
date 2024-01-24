import Radio from '@Atoms/Radio';
import { borderRadius, Breakpoint, colorPalate, fontSize, spacing } from '@Config/styles';
import { css, SerializedStyles } from '@emotion/react';
import { FormControllerProps } from '@Utils/form';
import { ReactNode } from 'react';

import FormFieldWrapper from './FormFieldWrapper';

interface RadioGroupWithIconOption {
  label: string;
  value: string | number;
  icon: ReactNode;
  disabled?: boolean;
}

interface FormRadioGroupWithIconProps extends FormControllerProps<string | number> {
  label?: string;
  options: RadioGroupWithIconOption[];
  disabled?: boolean;
  wrapperCss?: SerializedStyles;
}

const FormRadioGroupWithIcon = ({
  field,
  fieldState,
  label,
  options = [],
  disabled,
  wrapperCss,
}: FormRadioGroupWithIconProps) => {
  return (
    <FormFieldWrapper field={field} fieldState={fieldState} label={label} disabled={disabled}>
      {(inputProps) => {
        const { css, ...restInputProps } = inputProps;

        return (
          <div css={[styles.wrapper, wrapperCss]}>
            {options.map((option, index) => (
              <Radio
                {...restInputProps}
                key={index}
                inputCss={[...css, styles.radioInput]}
                value={option.value}
                checked={option.value === field.value}
                label={option.label}
                icon={option.icon}
                disabled={option.disabled || disabled}
                labelCss={styles.radioLabel(option.value === field.value)}
                onChange={() => {
                  field.onChange(option.value);
                }}
              />
            ))}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormRadioGroupWithIcon;

const styles = {
  wrapper: css`
    display: flex;
    gap: ${spacing[28]};
    cursor: pointer;

    ${Breakpoint.smallTablet} {
      flex-direction: column;
    }
  `,
  radioLabel: (isActive: boolean) => css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
    flex: 1;
    border: 2px solid ${colorPalate.border.disabled};
    position: relative;
    padding: ${spacing[36]} ${spacing[20]} ${spacing[24]};
    border-radius: ${borderRadius[6]};
    font-size: ${fontSize[16]};
    color: ${colorPalate.text.neutral};

    svg {
      -webkit-filter: grayscale(0.8);
      filter: grayscale(0.8);
    }

    ${isActive &&
    css`
      color: ${colorPalate.text.default};
      border: 2px solid ${colorPalate.basic.primary.default};

      svg {
        -webkit-filter: grayscale(0);
        filter: grayscale(0);
      }
    `};
  `,
  radioInput: css`
    & + span {
      position: absolute;
      top: ${spacing[12]};
      right: ${spacing[16]};
      height: 24px;
      width: 24px;
      margin: 0;
    }

    & + span::before {
      left: 5px;
      top: 5px;
      width: 10px;
      height: 10px;
    }
  `,
};
