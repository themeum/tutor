import React, { useEffect, useRef } from 'react';
import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { FormControllerProps } from '@Utils/form';

import FormFieldWrapper from './FormFieldWrapper';
import Show from '@Controls/Show';

const styles = {
  container: (isClearable: boolean) => css`
    position: relative;
    display: flex;

    & input {
      ${isClearable && `padding-right: ${spacing[36]};`};
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
  unit: css`
    ${typography.small()}
    color: ${colorTokens.text.hints};
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    padding-inline: ${spacing[8]};
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
  unit?: string;
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
  unit,
}: FormInputProps) => {
  let inputValue = field.value ?? '';
  let characterCount;

  if (type === 'number') {
    inputValue = `${inputValue}`.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
  }
  if (maxLimit) {
    characterCount = { maxLimit, inputCharacter: inputValue.toString().length };
  }

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
    >
      {(inputProps) => {
        return (
          <>
            <div css={styles.container(isClearable)}>
              <input
                {...field}
                {...inputProps}
                type='text'
                value={inputValue}
                onChange={(event) => {
                  const { value } = event.target;

                  const fieldValue: string | number =
                    type === 'number' ? value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1') : value;

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
              <Show when={isClearable && !!field.value}>
                <div css={styles.clearButton}>
                  <Button variant='text' onClick={() => field.onChange(null)}>
                    <SVGIcon name='timesAlt' />
                  </Button>
                </div>
              </Show>
              <Show when={unit}>
                <div css={styles.unit}>{unit}</div>
              </Show>
            </div>
          </>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormInput;
