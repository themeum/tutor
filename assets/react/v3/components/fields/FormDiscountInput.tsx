import { borderRadius, colorPalate } from '@Config/styles';
import { css } from '@emotion/react';
import { useFormatters } from '@Hooks/useFormatters';
import { Discount } from '@Services/order';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { isDefined, ProductDiscount } from '@Utils/types';

import FormFieldWrapper from './FormFieldWrapper';

interface FormDiscountInputProps extends FormControllerProps<ProductDiscount | null> {
  price?: number;
  label: string;
  disabled?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: (discount: Discount) => void;
  isHidden?: boolean;
}

const FormDiscountInput = ({
  price,
  label,
  field,
  fieldState,
  disabled,
  loading,
  placeholder,
  helpText,
  onChange,
  isHidden,
}: FormDiscountInputProps) => {
  const active = field.value?.type || 'percent';
  const { getCurrency } = useFormatters();

  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
      disabled={disabled}
      loading={loading}
      placeholder={placeholder}
      helpText={helpText}
      isHidden={isHidden}
    >
      {(inputProps) => {
        const { css: inputCss, ...restInputProps } = inputProps;

        return (
          <div css={styles.inputWrapper(!!fieldState.error)}>
            <input
              {...field}
              {...restInputProps}
              type="text"
              inputMode="numeric"
              placeholder={placeholder}
              value={field.value?.amount ?? ''}
              onChange={(event) => {
                let { value } = event.target;

                value = value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');

                let nextValue: Discount = {
                  type: field.value?.type || 'percent',
                  amount: null,
                };

                if (!value) {
                  field.onChange(nextValue);

                  if (onChange) {
                    onChange(nextValue);
                  }
                  return;
                }

                let amount = Number(value);

                if (field.value?.type === 'percent' && amount > 100) {
                  amount = Math.min(100, amount);
                }
                if (isDefined(price) && field.value?.type === 'amount') {
                  amount = Math.min(price, amount);
                }

                nextValue = {
                  type: field.value?.type || 'percent',
                  amount,
                };

                field.onChange(nextValue);

                if (onChange) {
                  onChange(nextValue);
                }
              }}
              css={[inputCss, styles.input]}
              autoComplete="off"
            />
            <div css={styles.actionBar}>
              <div css={styles.buttonGroup}>
                <button
                  type="button"
                  css={styles.buttonItem({ isActive: active === 'percent' })}
                  onClick={() => {
                    const discount: Discount = { amount: Math.min(100, field.value?.amount || 0), type: 'percent' };
                    field.onChange(discount);
                    if (onChange) {
                      onChange(discount);
                    }
                  }}
                >
                  %
                </button>
                <button
                  type="button"
                  css={styles.buttonItem({ isActive: active === 'amount' })}
                  onClick={() => {
                    const amount = isDefined(price) ? Math.min(price, field.value?.amount || 0) : field.value?.amount;
                    const discount: Discount = { amount: amount || null, type: 'amount' };
                    field.onChange(discount);
                    if (onChange) {
                      onChange(discount);
                    }
                  }}
                >
                  {getCurrency().currency_symbol}
                </button>
              </div>
            </div>
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormDiscountInput;

const styles = {
  inputWrapper: (hasError: boolean) => css`
    height: 36px;
    width: 100%;
    border: 1px solid ${colorPalate.border.neutral};
    border-radius: ${borderRadius[6]};
    overflow: hidden;
    display: grid;
    grid-template-columns: auto 72px;

    ${hasError &&
    css`
      border-color: ${colorPalate.basic.critical};
    `}
  `,
  input: css`
    width: 100%;
    border: none;
    height: 100%;
  `,
  actionBar: css`
    background-color: ${colorPalate.basic.surface};
    border-left: 1px solid ${colorPalate.surface.neutral.hover};
    display: flex;
    justify-content: center;
    align-items: center;
  `,
  buttonGroup: css`
    display: flex;
  `,
  buttonItem: ({ isActive }: { isActive: boolean }) => css`
    ${styleUtils.resetButton};
    width: 26px;
    height: 26px;
    text-align: center;
    color: ${colorPalate.icon.neutral};
    border-radius: ${borderRadius[4]};

    ${isActive &&
    css`
      background-color: ${colorPalate.basic.primary.default};
      color: ${colorPalate.basic.white};
    `}
  `,
};
