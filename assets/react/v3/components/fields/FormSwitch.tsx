import Switch from '@Atoms/Switch';
import { colorPalate, lineHeight, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css, SerializedStyles } from '@emotion/react';
import { FormControllerProps } from '@Utils/form';

import FormFieldWrapper from './FormFieldWrapper';

export type labelPositionType = 'left' | 'right';

interface FormSwitchProps extends FormControllerProps<boolean> {
  label?: string;
  title?: string;
  subTitle?: string;
  disabled?: boolean;
  loading?: boolean;
  labelPosition?: labelPositionType;
  isHidden?: boolean;
  labelCss?: SerializedStyles;
}

const FormSwitch = ({
  field,
  fieldState,
  label,
  title,
  subTitle,
  disabled,
  loading,
  labelPosition = 'left',
  isHidden,
  labelCss,
}: FormSwitchProps) => {
  return (
    <FormFieldWrapper field={field} fieldState={fieldState} disabled={disabled} loading={loading} isHidden={isHidden}>
      {(inputProps) => {
        return (
          <div css={styles.wrapper}>
            {(title || subTitle) && (
              <div>
                {!!title && <h6 css={styles.title}>{title}</h6>}
                {!!subTitle && <p css={styles.subTitle}>{subTitle}</p>}
              </div>
            )}
            <Switch
              {...field}
              {...inputProps}
              label={label}
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
  title: css`
    ${typography.heading6()};
    line-height: ${lineHeight[20]};
    margin-bottom: ${spacing[4]};
  `,
  subTitle: css`
    ${typography.body()};
    color: ${colorPalate.text.neutral};
  `,
};
