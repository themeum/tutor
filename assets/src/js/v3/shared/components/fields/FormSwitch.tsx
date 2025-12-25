import { type SerializedStyles, css } from '@emotion/react';
import type { ReactNode } from 'react';

import Switch from '@TutorShared/atoms/Switch';
import { spacing } from '@TutorShared/config/styles';
import { withVisibilityControl } from '@TutorShared/hoc/withVisibilityControl';
import type { FormControllerProps } from '@TutorShared/utils/form';

import FormFieldWrapper from './FormFieldWrapper';

export type labelPositionType = 'left' | 'right';

interface FormSwitchProps extends FormControllerProps<boolean> {
  label?: string | ReactNode;
  title?: string;
  subTitle?: string;
  disabled?: boolean;
  loading?: boolean;
  labelPosition?: labelPositionType;
  helpText?: string;
  isHidden?: boolean;
  labelCss?: SerializedStyles;
  onChange?: (value: boolean) => void;
}

const FormSwitch = ({
  field,
  fieldState,
  label,
  disabled,
  loading,
  labelPosition = 'left',
  helpText,
  isHidden,
  labelCss,
  onChange,
}: FormSwitchProps) => {
  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
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
              disabled={disabled}
              checked={field.value}
              labelCss={labelCss}
              labelPosition={labelPosition}
              onChange={() => {
                field.onChange(!field.value);
                onChange?.(!field.value);
              }}
            />
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default withVisibilityControl(FormSwitch);

const styles = {
  wrapper: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: ${spacing[40]};
  `,
};
