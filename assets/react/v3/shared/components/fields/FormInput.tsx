import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import type { FormControllerProps } from '@Utils/form';
import { type SerializedStyles, css } from '@emotion/react';

import Show from '@Controls/Show';
import { isDefined } from '@Utils/types';
import { parseNumberOnly } from '@Utils/util';
import FormFieldWrapper from './FormFieldWrapper';

const styles = {
  container: (isClearable: boolean) => css`
    position: relative;
    display: flex;

    & input {
      ${isClearable && `padding-right: ${spacing[36]};`};
      ${typography.body()}
      width: 100%;
      border: 1px solid ${colorTokens.stroke.default};
      border-radius: ${borderRadius[6]};

      :focus {
        border-color: transparent;
        box-shadow: ${shadow.focus};
      }
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
  isSecondary?: boolean;
  removeBorder?: boolean;
  dataAttribute?: string;
  isInlineLabel?: boolean;
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
  style,
}: FormInputProps) => {
  let inputValue = field.value ?? '';
  let characterCount:
    | {
        maxLimit: number;
        inputCharacter: number;
      }
    | undefined = undefined;

  if (type === 'number') {
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
        console.log(inputProps);
        return (
          <>
            <div css={styles.container(isClearable)}>
              <input
                {...field}
                {...inputProps}
                {...additionalAttributes}
                type="text"
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
              <Show when={isClearable && !!field.value}>
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
