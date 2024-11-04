import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { DateFormats } from '@Config/constants';
import { borderRadius, colorTokens, fontSize, shadow, spacing } from '@Config/styles';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { format, isAfter, isBefore, isValid, parseISO } from 'date-fns';
import { useState } from 'react';
import { DayPicker } from 'react-day-picker';
import 'react-day-picker/dist/style.css';

import FormFieldWrapper from './FormFieldWrapper';

interface FormDateInputProps extends FormControllerProps<string> {
  label?: string | React.ReactNode;
  disabled?: boolean;
  disabledBefore?: string;
  disabledAfter?: string;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  isClearable?: boolean;
  onChange?: (value: string) => void;
  dateFormat?: string;
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
  onChange,
  dateFormat = DateFormats.yearMonthDay,
}: FormDateInputProps) => {
  const [isOpen, setIsOpen] = useState(false);
  const fieldValue = isValid(new Date(field.value)) ? format(new Date(field.value), dateFormat) : '';

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
                ref={field.ref}
                type="text"
                onFocus={() => setIsOpen(true)}
                value={fieldValue}
                onChange={(event) => {
                  const { value } = event.target;

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

                  if (onChange) {
                    onChange(value);
                  }
                }}
                autoComplete="off"
                data-input
              />
              <SVGIcon name="calendarLine" width={30} height={32} style={styles.icon} />

              {isClearable && field.value && (
                <Button
                  variant="text"
                  buttonCss={styles.clearButton}
                  onClick={() => {
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

                      field.onChange(formattedDate);
                      setIsOpen(false);

                      if (onChange) {
                        onChange(formattedDate);
                      }
                    }
                  }}
                  showOutsideDays
                  captionLayout="dropdown-buttons"
                  defaultMonth={isValid(new Date(field.value)) ? new Date(field.value) : new Date()}
                  fromMonth={disabledBefore ? new Date(disabledBefore) : new Date(new Date().getFullYear() - 10, 0)}
                  toMonth={disabledAfter ? new Date(disabledAfter) : new Date(new Date().getFullYear() + 10, 11)}
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
		&[data-input] {
			padding-left: ${spacing[40]};
		}
	`,
  icon: css`
		position: absolute;
		top: 50%;
		left: ${spacing[8]};
		transform: translateY(-50%);
		color: ${colorTokens.icon.default};
	`,
  pickerWrapper: css`
		position: absolute;
		background-color: ${colorTokens.background.white};
		box-shadow: ${shadow.popover};
		border-radius: ${borderRadius[6]};
		color: ${colorTokens.text.primary};

		.rdp {
			--rdp-cell-size: 40px; /* Size of the day cells. */
			--rdp-caption-font-size: ${fontSize[18]}; /* Font size for the caption labels. */
			--rdp-accent-color: ${colorTokens.action.primary.default}; /* Accent color for the background of selected days. */
			--rdp-background-color: ${colorTokens.background.hover}; /* Background color for the hovered/focused elements. */
			--rdp-accent-color-dark: ${colorTokens.action.primary.active}; /* Accent color for the background of selected days (to use in dark-mode). */
			--rdp-background-color-dark: ${colorTokens.action.primary.hover}; /* Background color for the hovered/focused elements (to use in dark-mode). */
			--rdp-outline: 2px solid var(--rdp-accent-color); /* Outline border for focused elements */
			--rdp-outline-selected: 3px solid var(--rdp-accent-color); /* Outline border for focused _and_ selected elements */
			--rdp-selected-color: ${colorTokens.text.white}; /* Color of selected day text */
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
			background-color: ${colorTokens.background.hover};
		}
	`,
};
