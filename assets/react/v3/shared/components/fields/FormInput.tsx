import { useState } from 'react';
import { type SerializedStyles, css } from '@emotion/react';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import FormFieldWrapper from '@Components/fields/FormFieldWrapper';

import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';

import Show from '@Controls/Show';

import type { FormControllerProps } from '@Utils/form';
import { isDefined } from '@Utils/types';
import { parseNumberOnly } from '@Utils/util';
import { styleUtils } from '@Utils/style-utils';

interface FormInputProps extends FormControllerProps<string | number | null> {
  label?: string;
  type?: 'number' | 'text' | 'password';
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
  isSecondary?: boolean;
  removeBorder?: boolean;
  dataAttribute?: string;
  isInlineLabel?: boolean;
  isPassword?: boolean;
  style?: SerializedStyles;
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
  isSecondary = false,
  removeBorder,
  dataAttribute,
  isInlineLabel = false,
  isPassword = false,
  style,
}: FormInputProps) => {
  const [fieldType, setFieldType] = useState<typeof type>(type);

  let inputValue = field.value ?? '';
  let characterCount:
    | {
        maxLimit: number;
        inputCharacter: number;
      }
    | undefined = undefined;

  if (fieldType === 'number') {
    inputValue = parseNumberOnly(`${inputValue}`).replace(/(\..*)\./g, '$1');
  }

  if (maxLimit) {
    characterCount = { maxLimit, inputCharacter: inputValue.toString().length };
  }

  const additionalAttributes = {
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
      isSecondary={isSecondary}
      removeBorder={removeBorder}
      isInlineLabel={isInlineLabel}
      inputStyle={style}
    >
      {(inputProps) => {
        return (
          <>
            <div css={styles.container(isClearable)}>
              <input
                {...field}
                {...inputProps}
                {...additionalAttributes}
                type={fieldType === 'number' ? 'text' : fieldType}
                value={inputValue}
                onChange={(event) => {
                  const { value } = event.target;

                  const fieldValue: string | number = fieldType === 'number' ? parseNumberOnly(value) : value;

                  field.onChange(fieldValue);

                  if (onChange) {
                    onChange(fieldValue);
                  }
                }}
                onKeyDown={(event) => {
                  event.stopPropagation();
                  onKeyDown?.(event.key);
                }}
                autoComplete="off"
              />
              {isClearable && !!field.value && (
                <div css={styles.clearButton}>
                  <Button variant="text" onClick={() => field.onChange(null)}>
                    <SVGIcon name="timesAlt" />
                  </Button>
                </div>
              )}
              <Show when={isPassword}>
                <div css={styles.eyeButtonWrapper}>
                  <button
                    type="button"
                    css={styles.eyeButton({ type: fieldType })}
                    onClick={() => setFieldType((prev) => (prev === 'password' ? 'text' : 'password'))}
                  >
                    <SVGIcon name="eye" height={24} width={24} />
                  </button>
                </div>
              </Show>
              <Show when={isClearable && !!field.value && fieldType !== 'password'}>
                <div css={styles.clearButton}>
                  <Button variant="text" onClick={() => field.onChange(null)}>
                    <SVGIcon name="timesAlt" />
                  </Button>
                </div>
              </Show>
            </div>
          </>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormInput;

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
  eyeButtonWrapper: css`
    position: absolute;
    right: ${spacing[4]};
    top: -${spacing[2]};
    width: 36px;
    height: 36px;
    border-radius: ${borderRadius[2]};
    background: transparent;
  `,

  eyeButton: ({
    type,
  }: {
    type: 'password' | 'text' | 'number';
  }) => css`
    ${styleUtils.resetButton}
    padding: ${spacing[10]};
    color: ${colorTokens.icon.default};

    ${
      type !== 'password' &&
      css`
        color: ${colorTokens.icon.brand};
      `
    }
  `,
};