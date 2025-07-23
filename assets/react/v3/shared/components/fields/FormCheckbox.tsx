import AtomCheckbox from '@TutorShared/atoms/CheckBox';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { type SerializedStyles, css } from '@emotion/react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Tooltip from '@TutorShared/atoms/Tooltip';
import FormFieldWrapper from './FormFieldWrapper';

interface CheckboxProps extends FormControllerProps<boolean> {
  label?: string | React.ReactNode;
  description?: string;
  helpText?: string;
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
  helpText,
  isHidden,
  labelCss,
}: CheckboxProps) => {
  return (
    <FormFieldWrapper field={field} fieldState={fieldState} isHidden={isHidden}>
      {(inputProps) => {
        const { css, ...restInputProps } = inputProps;

        return (
          <div>
            <div css={styles.wrapper}>
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

              {helpText && (
                <Tooltip content={helpText} placement="top" allowHTML>
                  <SVGIcon name="info" width={20} height={20} />
                </Tooltip>
              )}
            </div>

            {description && <p css={styles.description}>{description}</p>}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormCheckbox;

const styles = {
  wrapper: css`
    display: flex;
    align-items: center;
    gap: ${spacing[6]};

    & > div {
      display: flex;
      color: ${colorTokens.icon.default};
    }
  `,
  description: css`
    ${typography.small()}
    color: ${colorTokens.text.hints};
    padding-left: 30px;
    margin-top: ${spacing[6]};
  `,
};
