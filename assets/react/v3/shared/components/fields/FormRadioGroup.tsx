import Radio from '@Atoms/Radio';
import { colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import type { FormControllerProps } from '@Utils/form';
import { type SerializedStyles, css } from '@emotion/react';

import type { ReactNode } from 'react';
import FormFieldWrapper from './FormFieldWrapper';

interface OptionWithDisabled {
  label: string;
  value: string | number;
  disabled?: boolean;
  legend?: string;
}

interface FormRadioGroupProps extends FormControllerProps<string | number> {
  label?: string;
  options: OptionWithDisabled[];
  disabled?: boolean;
  wrapperCss?: SerializedStyles;
  onSelect?: (value: OptionWithDisabled) => void;
  onSelectRender?: (value: OptionWithDisabled) => ReactNode;
}

const FormRadioGroup = ({
  field,
  fieldState,
  label,
  options = [],
  disabled,
  wrapperCss,
  onSelect,
  onSelectRender,
}: FormRadioGroupProps) => {
  return (
    <FormFieldWrapper field={field} fieldState={fieldState} label={label} disabled={disabled}>
      {(inputProps) => {
        const { css, ...restInputProps } = inputProps;

        return (
          <div css={wrapperCss}>
            {options.map((option, index) => (
              <div key={index}>
                <Radio
                  {...restInputProps}
                  inputCss={css}
                  value={option.value}
                  label={option.label}
                  disabled={option.disabled || disabled}
                  checked={field.value === option.value}
                  onChange={() => {
                    field.onChange(option.value);

                    if (onSelect) {
                      onSelect(option);
                    }
                  }}
                />
                {onSelectRender && field.value === option.value && onSelectRender(option)}
                {option.legend && <span css={styles.radioLegend}>{option.legend}</span>}
              </div>
            ))}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormRadioGroup;

const styles = {
  radioLegend: css`
		margin-left: ${spacing[28]};
		${typography.body()};
		color: ${colorTokens.text.subdued};
	`,
};
