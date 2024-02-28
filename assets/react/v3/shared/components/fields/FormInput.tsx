import React from 'react';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { FormControllerProps } from '@Utils/form';

import FormFieldWrapper from './FormFieldWrapper';
import { parseNumberOnly } from '@Utils/util';
import { isDefined } from '@Utils/types';

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
    right: ${spacing[2]};
    top: ${spacing[2]};
    width: 36px;
    height: 36px;
    border-radius: ${borderRadius[2]};
    background: transparent;

    button {
      padding: ${spacing[10]};
    }
  `,
};

interface FormInputProps extends FormControllerProps<string | number | null> {
  label?: string;
  type?: 'number' | 'text';
  maxLimit?: number;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: (value: string | number) => void;
  onKeyDown?: (keyName: string) => void;
  isHidden?: boolean;
  isClearable?: boolean;
  removeBorder?: boolean;
  dataAttribute?: string;
}

const FormInput = ({
  label,
  type = 'text',
  maxLimit,
  field,
  fieldState,
  disabled,
  readOnly,
  loading,
  placeholder,
  helpText,
  onChange,
  onKeyDown,
  isHidden,
  isClearable = false,
  removeBorder,
  dataAttribute,
}: FormInputProps) => {
  let inputValue = field.value ?? '';
  let characterCount;

  if (type === 'number') {
    inputValue = parseNumberOnly(`${inputValue}`).replace(/(\..*)\./g, '$1');
  }
  if (maxLimit) {
    characterCount = { maxLimit, inputCharacter: inputValue.toString().length };
  }

  const attribute = {
    ...(isDefined(dataAttribute) && { [dataAttribute]: true }),
  };

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
      characterCount={characterCount}
      removeBorder={removeBorder}
    >
      {(inputProps) => {
        return (
          <>
            <div css={styles.container}>
              <input
                {...field}
                {...inputProps}
                {...attribute}
                type='text'
                value={inputValue}
                onChange={(event) => {
                  const { value } = event.target;

                  const fieldValue: string | number = type === 'number' ? parseNumberOnly(value) : value;

                  field.onChange(fieldValue);

                  if (onChange) {
                    onChange(fieldValue);
                  }
                }}
                onKeyDown={(event) => {
                  onKeyDown && onKeyDown(event.key);
                }}
                autoComplete='off'
              />
              {isClearable && !!field.value && (
                <div css={styles.clearButton}>
                  <Button variant='text' onClick={() => field.onChange(null)}>
                    <SVGIcon name='timesAlt' />
                  </Button>
                </div>
              )}
            </div>
          </>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormInput;
