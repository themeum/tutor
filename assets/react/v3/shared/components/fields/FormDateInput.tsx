import { css } from '@emotion/react';
import { format, isValid, parseISO } from 'date-fns';
import { useRef, useState } from 'react';
import { DayPicker, type Formatters } from 'react-day-picker';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';

import { tutorConfig } from '@TutorShared/config/config';
import { DateFormats, isRTL } from '@TutorShared/config/constants';
import { borderRadius, colorTokens, fontSize, shadow, spacing } from '@TutorShared/config/styles';
import { Portal, usePortalPopover } from '@TutorShared/hooks/usePortalPopover';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { convertWordPressLocaleToDateFns } from '@TutorShared/utils/util';

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

const NUMBERING_SYSTEMS: Record<string, string> = {
  ar: 'arab', // Arabic numerals
  fa: 'arabext', // Extended Arabic-Indic
  bn: 'beng', // Bengali
  hi: 'deva', // Devanagari
  mr: 'deva', // Devanagari
  ne: 'deva', // Devanagari
  pa: 'guru', // Gurmukhi
  gu: 'gujr', // Gujarati
  or: 'orya', // Oriya
  ta: 'taml', // Tamil
  te: 'telu', // Telugu
  kn: 'knda', // Kannada
  ml: 'mlym', // Malayalam
  th: 'thai', // Thai
  lo: 'laoo', // Lao
  km: 'khmr', // Khmer
  my: 'mymr', // Myanmar
  si: 'sinh', // Sinhala
  bo: 'tibt', // Tibetan
  dz: 'tibt', // Tibetan
};

const getNumberingSystem = (wpLocale: string): string | undefined => {
  const [language] = wpLocale.toLowerCase().split('_');
  return NUMBERING_SYSTEMS[language];
};

// Create a locale string with numbering system extension
const getNumberingLocale = (wpLocale: string): string => {
  const numberingSystem = getNumberingSystem(wpLocale);
  const baseLocale = wpLocale.replace('_', '-');
  return numberingSystem ? `${baseLocale}-u-nu-${numberingSystem}` : baseLocale;
};

// Format numbers according to WordPress locale

// Create DayPicker formatters based on WordPress locale
export function createFormatters(wpLocale: string): Formatters {
  const dateFnsLocale = convertWordPressLocaleToDateFns(wpLocale);
  const numberingLocale = getNumberingLocale(wpLocale);

  const formatNumber = (value: number, options?: Intl.NumberFormatOptions) =>
    value.toLocaleString(numberingLocale, options);

  return {
    formatDay: (day) => formatNumber(day.getDate()),
    formatWeekNumber: (weekNumber) => formatNumber(weekNumber),
    formatMonthCaption: (date, options) => {
      const m = format(date, 'LLLL', { ...options, locale: dateFnsLocale });
      return `${m}`;
    },
    formatCaption: (date, options) => {
      const m = format(date, 'LLLL', { ...options, locale: dateFnsLocale });
      return `${m}`;
    },
    formatWeekdayName: (weekday) => format(weekday, 'EEEEEE', { locale: dateFnsLocale }),
    formatYearCaption: (date) =>
      formatNumber(date.getFullYear(), {
        useGrouping: false,
      }),
  };
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
  const inputRef = useRef<HTMLInputElement>(null);
  const [isOpen, setIsOpen] = useState(false);
  const parsedISODate = parseISO(field.value);
  const isValidDate = isValid(new Date(field.value));
  const fieldValue = isValidDate ? format(parsedISODate, dateFormat) : '';

  const { triggerRef, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  const handleClosePortal = () => {
    setIsOpen(false);
    inputRef.current?.focus();
  };

  const locale = convertWordPressLocaleToDateFns(tutorConfig.local);

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
                  formatters={createFormatters(tutorConfig.local)}
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
                  locale={locale}
                  showOutsideDays
                  captionLayout="dropdown-buttons"
                  initialFocus={true}
                  defaultMonth={isValidDate ? parsedISODate : new Date()}
                  fromMonth={disabledBefore ? parseISO(disabledBefore) : new Date(new Date().getFullYear() - 10, 0)}
                  toMonth={disabledAfter ? parseISO(disabledAfter) : new Date(new Date().getFullYear() + 10, 11)}
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
      --rdp-accent-color-dark: ${colorTokens.action.primary
        .active}; /* Accent color for the background of selected days (to use in dark-mode). */
      --rdp-background-color-dark: ${colorTokens.action.primary
        .hover}; /* Background color for the hovered/focused elements (to use in dark-mode). */
      --rdp-outline: 2px solid var(--rdp-accent-color); /* Outline border for focused elements */
      --rdp-outline-selected: 3px solid var(--rdp-accent-color); /* Outline border for focused _and_ selected elements */
      --rdp-selected-color: ${colorTokens.text.white}; /* Color of selected day text */
    }

    .rdp-button:focus-visible:not([disabled]) {
      color: var(--rdp-selected-color);
      opacity: 1;
      background-color: var(--rdp-accent-color);
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
