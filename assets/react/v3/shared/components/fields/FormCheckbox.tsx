import AtomCheckbox from '@TutorShared/atoms/CheckBox';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { type SerializedStyles, css } from '@emotion/react';

import FormFieldWrapper from './FormFieldWrapper';

interface CheckboxProps extends FormControllerProps<boolean> {
  label?: string | React.ReactNode;
  description?: string;
  value?: string;
  onChange?: (value: boolean) => void;
  disabled?: boolean;
  isHidden?: boolean;
  labelCss?: SerializedStyles;
}

const FormCheckbox = ({
  field,
  fieldState,
  disabled,
  value,
  onChange,
  label,
  description,
  isHidden,
  labelCss,
}: CheckboxProps) => {
  return (
    <FormFieldWrapper field={field} fieldState={fieldState} isHidden={isHidden}>
      {(inputProps) => {
        const { css, ...restInputProps } = inputProps;

        return (
          <div>
            <AtomCheckbox
              {...field}
              {...restInputProps}
              inputCss={css}
              labelCss={labelCss}
              value={value}
              disabled={disabled}
              checked={field.value}
              label={label}
              onChange={() => {
                field.onChange(!field.value);

                if (onChange) {
                  onChange(!field.value);
                }
              }}
            />
            {description && <p css={styles.description}>{description}</p>}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormCheckbox;

const styles = {
  description: css`
    ${typography.small()}
    color: ${colorTokens.text.hints};
    padding-left: 30px;
    margin-top: ${spacing[6]};
  `,
};
