import Button, { ButtonVariant } from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { DateFormats } from '@Config/constants';
import { borderRadius, colorPalate, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { format, isAfter, isBefore, isValid, parseISO } from 'date-fns';
import { useState } from 'react';
import { DayPicker } from 'react-day-picker';
import 'react-day-picker/dist/style.css';

import FormFieldWrapper from './FormFieldWrapper';

interface FormDateInputProps extends FormControllerProps<string> {
  label?: string;
  disabled?: boolean;
  disabledBefore?: string;
  disabledAfter?: string;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  isClearable?: boolean;
}

const FormDateInput = ({
  label,
  field,
  fieldState,
  disabled,
  disabledBefore,
  disabledAfter,
  loading,
  placeholder,
  helpText,
  isClearable = true,
}: FormDateInputProps) => {
  const [isOpen, setIsOpen] = useState(false);
  const [inputValue, setInputValue] = useState(field.value);

  const { triggerRef, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

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
        const { css, ...restInputProps } = inputProps;
        return (
          <div>
            <div css={styles.wrapper} ref={triggerRef}>
              <input
                {...restInputProps}
                css={[css, styles.input]}
                type="text"
                onFocus={() => setIsOpen(true)}
                value={inputValue}
                onChange={(event) => {
                  const { value } = event.target;

                  setInputValue(value);

                  const currentDate = parseISO(value);
                  const disabledBeforeDate = disabledBefore && parseISO(disabledBefore);
                  const disabledAfterDate = disabledAfter && parseISO(disabledAfter);

                  if (
                    !isValid(currentDate) ||
                    (disabledBeforeDate && isAfter(disabledBeforeDate, currentDate)) ||
                    (disabledAfterDate && isBefore(disabledAfterDate, currentDate))
                  ) {
                    return;
                  }

                  field.onChange(value);
                }}
                autoComplete="off"
              />
              <SVGIcon name="calendar" width={18} height={18} style={styles.icon} />

              {isClearable && field.value && (
                <Button
                  variant={ButtonVariant.plain}
                  buttonCss={styles.clearButton}
                  onClick={() => {
                    setInputValue('');
                    field.onChange('');
                  }}
                >
                  <SVGIcon name="times" width={12} height={12} />
                </Button>
              )}
            </div>

            <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)}>
              <div css={[styles.pickerWrapper, { left: position.left, top: position.top }]} ref={popoverRef}>
                <DayPicker
                  mode="single"
                  disabled={[
                    !!disabledBefore && { before: new Date(disabledBefore) },
                    !!disabledAfter && { after: new Date(disabledAfter) },
                  ]}
                  selected={isValid(new Date(field.value)) ? new Date(field.value) : undefined}
                  onSelect={(value) => {
                    if (value) {
                      const formattedDate = format(value, DateFormats.yearMonthDay);

                      setInputValue(formattedDate);
                      field.onChange(formattedDate);
                      setIsOpen(false);
                    }
                  }}
                  showOutsideDays
                />
              </div>
            </Portal>
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormDateInput;

const styles = {
  wrapper: css`
    position: relative;

    :hover {
      & > button {
        opacity: 1;
      }
    }
  `,
  input: css`
    padding-left: ${spacing[40]};
  `,
  icon: css`
    position: absolute;
    top: 50%;
    left: ${spacing[12]};
    transform: translateY(-50%);
  `,
  pickerWrapper: css`
    position: absolute;
    background-color: ${colorPalate.basic.white};
    box-shadow: ${shadow.popover};
    border-radius: ${borderRadius[6]};

    .rdp-caption_label {
      ${typography.heading6()};
    }

    .rdp-day_outside {
      color: ${colorPalate.text.disabled};
    }
  `,
  clearButton: css`
    position: absolute;
    top: 50%;
    right: ${spacing[4]};
    transform: translateY(-50%);
    width: 32px;
    height: 32px;
    ${styleUtils.flexCenter()};
    opacity: 0;
    transition: background-color 0.3s ease-in-out, opacity 0.3s ease-in-out;

    :hover {
      background-color: ${colorPalate.background.hover};
    }
  `,
};
