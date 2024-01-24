import { borderRadius, colorPalate, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { ClassNames, css } from '@emotion/react';
import { useTranslation } from '@Hooks/useTranslation';
import { FormControllerProps } from '@Utils/form';
import PhoneInput from 'react-phone-input-2';
import 'react-phone-input-2/lib/style.css';

import FormFieldWrapper from './FormFieldWrapper';

interface FormPhoneInputProps extends FormControllerProps<string> {
  label?: string;
  readOnly?: boolean;
  loading?: boolean;
  disabled?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: (value: string) => void;
  isHidden?: boolean;
}

const FormPhoneInput = ({
  label,
  field,
  fieldState,
  disabled,
  readOnly,
  loading,
  placeholder,
  helpText,
  onChange,
  isHidden,
}: FormPhoneInputProps) => {
  const t = useTranslation();

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
    >
      {(inputProps) => {
        const { css: inputCss, ...otherProps } = inputProps;

        return (
          <ClassNames>
            {({ css }) => {
              return (
                <PhoneInput
                  {...otherProps}
                  country="us"
                  inputProps={{ ...inputProps, placeholder }}
                  autoFormat={false}
                  value={field.value}
                  onChange={(value) => {
                    field.onChange(value);

                    if (onChange) {
                      onChange(value);
                    }
                  }}
                  enableSearch={true}
                  disableSearchIcon
                  inputClass={css(inputCss, styles.input)}
                  buttonClass={css(styles.button)}
                  searchClass={css(styles.search)}
                  searchPlaceholder={t('COM_SPPAGEBUILDER_STORE_PHONE_SEARCH_PLACEHOLDER')}
                />
              );
            }}
          </ClassNames>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormPhoneInput;

const styles = {
  container: css`
    position: relative;
    display: flex;

    & input {
      ${typography.body()}
      width: 100%;
    }
  `,
  clearButton: css`
    position: absolute;
    right: ${spacing[4]};
    top: ${spacing[6]};
    width: 26px;
    height: 26px;
    border-radius: ${borderRadius[2]};
    background: 'transparent';

    &:hover {
      background: ${colorPalate.surface.hover};
    }
  `,
  button: css`
    background-color: ${colorPalate.surface.default} !important;
  `,
  input: css`
    width: 100% !important;
    height: 36px !important;
  `,
  search: css`
    padding: ${spacing[10]} ${spacing[16]} ${spacing[6]} ${spacing[8]} !important;

    input {
      margin-left: 0 !important;
    }

    .search-box {
      width: 100%;
    }
  `,
};
