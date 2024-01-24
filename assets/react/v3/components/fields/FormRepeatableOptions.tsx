import { borderRadius, colorPalate, spacing } from '@Config/styles';
import { css } from '@emotion/react';
import { ColorField, ListField } from '@Molecules/VariationValueFields';
import { FormControllerProps } from '@Utils/form';
import { ProductOptionType, ProductOptionValue } from '@Utils/types';
import { range } from '@Utils/util';
import produce from 'immer';

import FormFieldWrapper from './FormFieldWrapper';

interface FormRepeatableOptionsProps extends FormControllerProps<ProductOptionValue[] | undefined> {
  label?: string;
  disabled?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  fieldType?: ProductOptionType;
  onChange?: (value: ProductOptionValue) => void;
}

const emptyValue = { name: '', color: '' };

const fieldTypeComponentMap = {
  color: ColorField,
  list: ListField,
};

const FormRepeatableOptions = ({
  label,
  field,
  fieldState,
  disabled,
  loading,
  placeholder,
  helpText,
  fieldType = 'color',
  onChange,
}: FormRepeatableOptionsProps) => {
  const numberOfItems = field.value?.length || 0;
  const FieldToRender = fieldTypeComponentMap[fieldType];

  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
      disabled={disabled}
      loading={loading}
      placeholder={placeholder}
      helpText={helpText}
    >
      {(inputProps) => {
        return (
          <div css={styles.fieldContainer}>
            {range(numberOfItems + 1).map((index) => {
              const fieldValue = !field.value || index === numberOfItems ? emptyValue : field.value[index];

              return (
                <FieldToRender
                  key={index}
                  inputCss={inputProps.css}
                  fieldValue={fieldValue}
                  placeholder={placeholder}
                  onClear={() => {
                    field.onChange(
                      produce(field.value, (draft) => {
                        draft?.splice(index, 1);
                      }),
                    );
                  }}
                  onChange={(value) => {
                    if (onChange) {
                      onChange(value);
                    }

                    if (!field.value) {
                      return field.onChange([value]);
                    }

                    if (index === numberOfItems) {
                      return field.onChange(
                        produce(field.value, (draft) => {
                          draft.push(value);
                        }),
                      );
                    }

                    field.onChange(
                      produce(field.value, (draft) => {
                        draft[index] = value;
                      }),
                    );
                  }}
                />
              );
            })}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormRepeatableOptions;

const styles = {
  fieldContainer: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
  `,
  clearButton: css`
    position: absolute;
    right: ${spacing[4]};
    top: 50%;
    transform: translateY(-50%);
    width: 26px;
    height: 26px;
    border-radius: ${borderRadius[2]};
    background: 'transparent';
    display: flex;
    justify-content: center;
    align-items: center;

    &:hover {
      background: ${colorPalate.surface.hover};
    }
  `,
};
