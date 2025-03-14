import { css } from '@emotion/react';
import { format, isValid, parseISO } from 'date-fns';
import { useRef, useState } from 'react';
import { DayPicker, type Formatters } from 'react-day-picker';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { DateFormats, isRTL } from '@TutorShared/config/constants';
import { borderRadius, colorTokens, fontSize, fontWeight, shadow, spacing } from '@TutorShared/config/styles';
import { Portal, usePortalPopover } from '@TutorShared/hooks/usePortalPopover';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { styleUtils } from '@TutorShared/utils/style-utils';

import 'react-day-picker/style.css';

import { typography } from '@TutorShared/config/typography';
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

// Create DayPicker formatters based on WordPress locale
const createFormatters = (): Partial<Formatters> | undefined => {
  if (!wp.date) {
    return;
  }

  const { format } = wp.date;

  return {
    formatMonthDropdown: (date) => format('F', date),
    formatMonthCaption: (date) => format('F', date),
    formatCaption: (date) => format('F', date),
    formatWeekdayName: (date) => format('D', date),
  };
};

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
  const inputRef = useRef<HTMLInputElement>(null);
  const [isOpen, setIsOpen] = useState(false);
  const isValidDate = isValid(new Date(field.value));
  const parsedISODate = isValidDate ? parseISO(format(new Date(field.value), DateFormats.yearMonthDay)) : new Date();
  const fieldValue = isValidDate ? format(parsedISODate, dateFormat) : '';

  const { triggerRef, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  const handleClosePortal = () => {
    setIsOpen(false);
    inputRef.current?.focus();
  };

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
                ref={(element) => {
                  field.ref(element);
                  // @ts-ignore
                  inputRef.current = element;
                }}
                type="text"
                value={fieldValue}
                onClick={(event) => {
                  event.stopPropagation();
                  setIsOpen((previousState) => !previousState);
                }}
                onKeyDown={(event) => {
                  if (event.key === 'Enter') {
                    event.preventDefault();
                    setIsOpen((previousState) => !previousState);
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

            <Portal isOpen={isOpen} onClickOutside={handleClosePortal} onEscape={handleClosePortal}>
              <div
                css={[styles.pickerWrapper, { [isRTL ? 'right' : 'left']: position.left, top: position.top }]}
                ref={popoverRef}
              >
                <DayPicker
                  dir={isRTL ? 'rtl' : 'ltr'}
                  mode="single"
                  formatters={createFormatters()}
                  disabled={[
                    !!disabledBefore && { before: parseISO(disabledBefore) },
                    !!disabledAfter && { after: parseISO(disabledAfter) },
                  ]}
                  selected={isValidDate ? parsedISODate : undefined}
                  onSelect={(value) => {
                    if (value) {
                      const formattedDate = format(value, DateFormats.yearMonthDay);

                      field.onChange(formattedDate);
                      handleClosePortal();

                      if (onChange) {
                        onChange(formattedDate);
                      }
                    }
                  }}
                  showOutsideDays
                  captionLayout="dropdown"
                  autoFocus
                  defaultMonth={isValidDate ? parsedISODate : new Date()}
                  startMonth={disabledBefore ? parseISO(disabledBefore) : new Date(new Date().getFullYear() - 10, 0)}
                  endMonth={disabledAfter ? parseISO(disabledAfter) : new Date(new Date().getFullYear() + 10, 11)}
                  weekStartsOn={wp.date?.getSettings().l10n.startOfWeek}
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

    &:hover,
    &:focus-within {
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
    ${typography.body('regular')};
    position: absolute;
    background-color: ${colorTokens.background.white};
    box-shadow: ${shadow.popover};
    border-radius: ${borderRadius[6]};

    .rdp-root {
      --rdp-day-height: 40px; /* Height of the day cells. */
      --rdp-day-width: 40px; /* Width of the day cells. */
      --rdp-day_button-height: 40px; /* Height of the day buttons. */
      --rdp-day_button-width: 40px; /* Width of the day buttons. */
      --rdp-nav-height: 40px; /* Height of the navigation buttons. */
      --rdp-today-color: ${colorTokens.text.title}; /* Color of today's date. */
      --rdp-caption-font-size: ${fontSize[18]}; /* Font size for the caption labels. */
      --rdp-accent-color: ${colorTokens.action.primary.default}; /* Accent color for the background of selected days. */
      --rdp-background-color: ${colorTokens.background.hover}; /* Background color for the hovered/focused elements. */
      --rdp-accent-color-dark: ${colorTokens.action.primary
        .active}; /* Accent color for the background of selected days (to use in dark-mode). */
      --rdp-background-color-dark: ${colorTokens.action.primary
        .hover}; /* Background color for the hovered/focused elements (to use in dark-mode). */
      --rdp-selected-color: ${colorTokens.text.white}; /* Color of selected day text */
      --rdp-day_button-border-radius: ${borderRadius.circle}; /* Border radius of the day buttons */
      --rdp-outside-opacity: 0.5; /* Opacity of the outside days */
      --rdp-disabled-opacity: 0.25; /* Opacity of the disabled days */
    }

    .rdp-months {
      margin: ${spacing[16]};
    }

    .rdp-month_grid {
      margin: 0px;
    }

    .rdp-day {
      padding: 0px;
    }

    .rdp-nav {
      --rdp-accent-color: ${colorTokens.text.primary};

      button {
        border-radius: ${borderRadius.circle};

        &:hover,
        &:focus,
        &:active {
          background-color: ${colorTokens.background.hover};
          color: ${colorTokens.text.primary};
        }

        &:focus-visible:not(:disabled) {
          --rdp-accent-color: ${colorTokens.text.white};
          background-color: ${colorTokens.background.brand};
        }
      }
    }

    .rdp-dropdown_root {
      .rdp-caption_label {
        padding: ${spacing[8]};
      }
    }

    .rdp-today {
      .rdp-day_button {
        font-weight: ${fontWeight.bold};
      }
    }

    .rdp-selected {
      color: var(--rdp-selected-color);
      background-color: var(--rdp-accent-color);
      border-radius: ${borderRadius.circle};
      font-weight: ${fontWeight.regular};
      .rdp-day_button {
        &:hover,
        &:focus,
        &:active {
          background-color: var(--rdp-accent-color);
          color: ${colorTokens.text.primary};
        }

        &:focus-visible {
          outline: 2px solid var(--rdp-accent-color);
          outline-offset: 2px;
        }

        &:not(.rdp-outside) {
          color: var(--rdp-selected-color);
        }
      }
    }

    .rdp-day_button {
      &:hover,
      &:focus,
      &:active {
        background-color: var(--rdp-background-color);
        color: ${colorTokens.text.primary};
      }

      &:focus-visible:not([disabled]) {
        color: var(--rdp-selected-color);
        opacity: 1;
        background-color: var(--rdp-accent-color);
      }
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
    transition:
      background-color 0.3s ease-in-out,
      opacity 0.3s ease-in-out;
    border-radius: ${borderRadius[2]};

    :hover {
      background-color: ${colorTokens.background.hover};
    }
  `,
};
