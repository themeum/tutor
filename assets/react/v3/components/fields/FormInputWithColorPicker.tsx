import Button, { ButtonVariant } from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorPalate, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { useState } from 'react';
import { SketchPicker, ColorResult } from 'react-color';

import FormFieldWrapper from './FormFieldWrapper';

const styles = {
  outerContainer: css`
    position: relative;
    display: flex;

    & input {
      ${typography.body()}
      width: 100%;
      padding: 0 ${spacing[32]};
    }
  `,
  colorPickerContainer: css`
    position: absolute;
    left: ${spacing[12]};
    top: 0;
    height: 100%;
    display: flex;
    align-items: center;
    z-index: ${zIndex.positive};
  `,
  colorPickerPlaceholder: (backgroundColor = 'transparent') => css`
    height: 16px;
    width: 16px;
    background: ${backgroundColor};

    border: 1px solid ${colorPalate.border.disabled};
    border-radius: ${borderRadius.circle};
    outline: none;
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
  backdropStyle: css`
    ${styleUtils.resetButton}
    position: fixed;
    inset: 0;
    background: transparent;
  `,
};

interface ColorFieldValue {
  color?: string;
  name?: string;
}

interface FormInputWithColorPickerProps extends FormControllerProps<ColorFieldValue | null> {
  label?: string;
  maxLimit?: number;
  disabled?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  onChange?: () => void;
  isHidden?: boolean;
}

const FormInputWithColorPicker = ({
  label,
  maxLimit,
  field,
  fieldState,
  disabled,
  loading,
  placeholder,
  helpText,
  onChange,
  isHidden,
}: FormInputWithColorPickerProps) => {
  const [isColorPickerOpen, setIsColorPickerOpen] = useState(false);

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
        return (
          <div css={styles.outerContainer}>
            <div css={styles.colorPickerContainer}>
              <button
                type="button"
                css={styles.colorPickerPlaceholder(field.value?.color)}
                onClick={() => setIsColorPickerOpen(true)}
              />
              {isColorPickerOpen && (
                <>
                  <button type="button" onClick={() => setIsColorPickerOpen(false)} css={styles.backdropStyle}></button>
                  <SketchPicker
                    color={field.value?.color}
                    onChange={(color: ColorResult) => {
                      field.onChange({
                        ...field.value,
                        color: color.hex,
                      });
                    }}
                  />
                </>
              )}
            </div>

            <input
              {...field}
              {...inputProps}
              type="text"
              value={field.value?.name || ''}
              onChange={(event) => {
                const { value } = event.target;
                if (maxLimit && value.trim().length > maxLimit) {
                  return;
                }

                field.onChange({
                  ...field.value,
                  name: value,
                });

                if (onChange) {
                  onChange();
                }
              }}
              autoComplete="off"
            />

            {(!!field.value?.name || !!field.value?.color) && (
              <div css={styles.clearButton}>
                <Button variant={ButtonVariant.plain} onClick={() => field.onChange({ name: '', color: '' })}>
                  <SVGIcon name="timesAlt" />
                </Button>
              </div>
            )}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormInputWithColorPicker;
