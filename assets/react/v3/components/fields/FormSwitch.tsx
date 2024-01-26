import Switch from "@Atoms/Switch";
import { colorPalate, lineHeight, spacing } from "@Config/styles";
import { typography } from "@Config/typography";
import { css, SerializedStyles } from "@emotion/react";
import { FormControllerProps } from "@Utils/form";

import FormFieldWrapper from "./FormFieldWrapper";

export type labelPositionType = "left" | "right";

interface FormSwitchProps extends FormControllerProps<boolean> {
  label?: string;
  title?: string;
  subTitle?: string;
  disabled?: boolean;
  loading?: boolean;
  labelPosition?: labelPositionType;
  helpText?: string;
  isHidden?: boolean;
  labelCss?: SerializedStyles;
}

const FormSwitch = ({
  field,
  fieldState,
  label,
  disabled,
  loading,
  labelPosition = "left",
  helpText,
  isHidden,
  labelCss,
}: FormSwitchProps) => {
  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
      disabled={disabled}
      loading={loading}
      helpText={helpText}
      isHidden={isHidden}
      isInlineLabel={true}
    >
      {(inputProps) => {
        return (
          <div css={styles.wrapper}>
            <Switch
              {...field}
              {...inputProps}
              checked={field.value}
              labelCss={labelCss}
              labelPosition={labelPosition}
              onChange={() => {
                field.onChange(!field.value);
              }}
            />
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormSwitch;

const styles = {
  wrapper: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[40]};
  `,
};
