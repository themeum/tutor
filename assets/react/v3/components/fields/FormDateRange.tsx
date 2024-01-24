import Button, { ButtonVariant } from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { DateFormats } from '@Config/constants';
import { borderRadius, colorPalate, fontSize, fontWeight, lineHeight, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import { useTranslation } from '@Hooks/useTranslation';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';
import { getActiveDateRange } from '@Utils/util';
import {
  differenceInDays,
  endOfMonth,
  endOfYear,
  format,
  isToday,
  isValid,
  startOfMonth,
  startOfYear,
  subDays,
  subMonths,
  subYears,
} from 'date-fns';
import { ChangeEvent, useEffect, useState } from 'react';
import { DateRange, DayPicker } from 'react-day-picker';
import 'react-day-picker/dist/style.css';

import FormFieldWrapper from './FormFieldWrapper';

type Variant = 'primary' | 'plain';

interface FormDateRangeProps extends FormControllerProps<DateRange | undefined> {
  label?: string;
  disabled?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  variant?: Variant;
}

const FormDateRange = ({
  label,
  field,
  fieldState,
  disabled,
  loading,
  placeholder,
  helpText,
  variant = 'primary',
}: FormDateRangeProps) => {
  const t = useTranslation();
  const [isOpen, setIsOpen] = useState(false);
  const [range, setRange] = useState<DateRange | undefined>(field.value);
  const [inputValues, setInputValues] = useState({ from: '', to: '' });
  const [activeRangeItem, setActiveRangeItem] = useState(getActiveDateRange(field.value));
  const [selectedType, setSelectedType] = useState(getActiveDateRange(field.value));

  const { triggerRef, position, popoverRef } = usePortalPopover<HTMLButtonElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  useEffect(() => {
    setInputValues({
      from: range?.from ? format(range.from, DateFormats.yearMonthDay) : '',
      to: range?.to ? format(range.to, DateFormats.yearMonthDay) : '',
    });
  }, [range]);

  const rangeItemList = [
    {
      type: 'today',
      label: t('COM_SPPAGEBUILDER_STORE_DATE_RANGE_TODAY'),
    },
    {
      type: 'yesterday',
      label: t('COM_SPPAGEBUILDER_STORE_DATE_RANGE_YESTERDAY'),
    },
    {
      type: 'last_seven_days',
      label: t('COM_SPPAGEBUILDER_STORE_DATE_RANGE_LAST_SEVEN_DAYS'),
    },
    {
      type: 'last_thirty_days',
      label: t('COM_SPPAGEBUILDER_STORE_DATE_RANGE_LAST_THIRTY_DAYS'),
    },
    {
      type: 'last_ninety_days',
      label: t('COM_SPPAGEBUILDER_STORE_DATE_RANGE_LAST_NINETY_DAYS'),
    },
    {
      type: 'last_month',
      label: t('COM_SPPAGEBUILDER_STORE_DATE_RANGE_LAST_MONTH'),
    },
    {
      type: 'last_year',
      label: t('COM_SPPAGEBUILDER_STORE_DATE_RANGE_LAST_YEAR'),
    },
  ];

  const handleItemClick = (type: string) => {
    setActiveRangeItem(type);

    switch (type) {
      case 'today':
        setRange({ from: new Date(), to: undefined });
        break;
      case 'yesterday':
        setRange({ from: subDays(new Date(), 1), to: undefined });
        break;
      case 'last_seven_days':
        setRange({ from: subDays(new Date(), 6), to: new Date() });
        break;
      case 'last_thirty_days':
        setRange({ from: subDays(new Date(), 29), to: new Date() });
        break;
      case 'last_ninety_days':
        setRange({ from: subDays(new Date(), 89), to: new Date() });
        break;
      case 'last_month':
        setRange({
          from: startOfMonth(subMonths(new Date(), 1)),
          to: endOfMonth(subMonths(new Date(), 1)),
        });
        break;
      case 'last_year':
        setRange({
          from: startOfYear(subYears(new Date(), 1)),
          to: endOfYear(subYears(new Date(), 1)),
        });
        break;
    }
  };

  const getButtonTitle = () => {
    if (!isDefined(field.value)) {
      return t('COM_SPPAGEBUILDER_STORE_DATE_RANGE_SELECT_RANGE');
    }

    if (rangeItemList.find((item) => item.type === selectedType)) {
      return rangeItemList.find((item) => item.type === selectedType)?.label;
    }

    if (field.value.from && field.value.to && isToday(field.value.to)) {
      return t('COM_SPPAGEBUILDER_STORE_DATE_RANGE_LAST_NUMBER_DAYS', {
        number: differenceInDays(field.value.to, field.value.from) + 1,
      });
    }

    if (field.value.from) {
      return t('COM_SPPAGEBUILDER_STORE_DATE_RANGE_NUMBER_DAYS', {
        number: field.value.to ? differenceInDays(field.value.to, field.value.from) + 1 : 1,
      });
    }
  };

  const isInputDateValid = (inputDate: string) => {
    const splitDate = inputDate.split('-');
    return (
      splitDate.length === 3 &&
      splitDate.every((item, index) => (index === 0 && item.length === 4) || (index !== 0 && item.length === 2)) &&
      isValid(new Date(inputDate))
    );
  };

  const handleChangeToFromDate = (dateRange: 'from' | 'to') => (event: ChangeEvent<HTMLInputElement>) => {
    const { from, to } = inputValues;
    const inputDate = event.target.value;
    const values = dateRange === 'from' ? { from: inputDate, to } : { from, to: inputDate };
    setInputValues(values);

    if (isInputDateValid(inputDate)) {
      const date = new Date(inputDate);
      const newRange = dateRange === 'from' ? { from: date, to: range?.to } : { from: range?.from, to: date };
      setRange(newRange);
      setActiveRangeItem('');
    }
  };

  const renderTriggerButton = () => {
    if (variant === 'plain') {
      return (
        <button css={styles.plainTriggerButton(isOpen)} ref={triggerRef} type="button" onClick={() => setIsOpen(true)}>
          <span>{getButtonTitle()}</span>
          <SVGIcon name="chevronDown" />
        </button>
      );
    }

    return (
      <div css={styles.wrapper}>
        <button ref={triggerRef} type="button" onClick={() => setIsOpen(true)}>
          <SVGIcon name="calendar" width={32} height={32} />
          <div>
            <h6>{getButtonTitle()}</h6>

            <p>
              {field.value
                ? `${field.value.from ? format(field.value.from, DateFormats.monthDayYear) : ''} ${
                    field.value.to ? ` - ${format(field.value.to, DateFormats.monthDayYear)}` : ''
                  }`
                : t('COM_SPPAGEBUILDER_STORE_DATE_RANGE_NO_RANGE_SELECTED')}
            </p>
          </div>
          <SVGIcon name="chevronDown" width={24} height={24} style={styles.rightIcon(isOpen)} />
        </button>
      </div>
    );
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
      {() => {
        return (
          <div>
            {renderTriggerButton()}

            <Portal
              isOpen={isOpen}
              onClickOutside={() => {
                setRange(field.value);
                setActiveRangeItem(getActiveDateRange(field.value));
                setIsOpen(false);
              }}
            >
              <div css={[styles.pickerWrapper, { left: position.left, top: position.top }]} ref={popoverRef}>
                <div css={styles.pickerContent}>
                  <ul css={styles.sidebar}>
                    {rangeItemList.map((item, index) => (
                      <li key={index}>
                        <button
                          type="button"
                          onClick={() => handleItemClick(item.type)}
                          css={styles.sidebarButton(item.type === activeRangeItem)}
                        >
                          {item.label}
                        </button>
                      </li>
                    ))}
                  </ul>

                  <div>
                    <div css={styles.rangeInputs}>
                      <input
                        type="text"
                        value={inputValues.from}
                        onChange={handleChangeToFromDate('from')}
                        autoComplete="off"
                      />
                      <span />
                      <input
                        type="text"
                        value={inputValues.to}
                        onChange={handleChangeToFromDate('to')}
                        autoComplete="off"
                      />
                    </div>
                    <DayPicker
                      mode="range"
                      defaultMonth={subMonths(new Date(), 1)}
                      numberOfMonths={2}
                      toDate={new Date()}
                      selected={range}
                      onSelect={(value) => {
                        setRange(value);
                        setActiveRangeItem(getActiveDateRange(value));
                      }}
                    />
                  </div>
                </div>
                <div css={styles.pickerFooter}>
                  <Button
                    type="button"
                    variant={ButtonVariant.secondary}
                    onClick={() => {
                      setRange(field.value);
                      setActiveRangeItem(getActiveDateRange(field.value));
                      setIsOpen(false);
                    }}
                  >
                    {t('COM_SPPAGEBUILDER_STORE_CANCEL')}
                  </Button>
                  <Button
                    type="button"
                    onClick={() => {
                      field.onChange(range);
                      setSelectedType(getActiveDateRange(range));
                      setIsOpen(false);
                    }}
                  >
                    {t('COM_SPPAGEBUILDER_STORE_APPLY')}
                  </Button>
                </div>
              </div>
            </Portal>
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormDateRange;

const styles = {
  wrapper: css`
    position: relative;

    button {
      ${styleUtils.resetButton};
      width: 100%;
      max-width: 354px;
      background-color: ${colorPalate.surface.default};
      padding: ${spacing[8]} ${spacing[16]};
      border-radius: ${borderRadius[6]};
      box-shadow: ${shadow.card};
      display: flex;
      align-items: center;
      gap: ${spacing[10]};
      position: relative;
    }

    h6 {
      ${typography.heading6('medium')};
      line-height: ${lineHeight[20]};
      margin-bottom: ${spacing[2]};
    }

    p {
      ${typography.body()};
      color: ${colorPalate.text.neutral};
    }
  `,
  plainTriggerButton: (isOpen: boolean) => css`
    ${styleUtils.resetButton}
    ${typography.body()}
    color: ${colorPalate.interactive.default};

    display: flex;
    align-items: center;
    gap: ${spacing[6]};

    svg {
      transition: transform 0.3s ease-in-out;

      ${isOpen &&
      css`
        transform: rotate(180deg);
      `}
    }
  `,
  rightIcon: (isOpen: boolean) => css`
    position: absolute;
    top: 50%;
    right: ${spacing[16]};
    transform: translateY(-50%);
    color: ${colorPalate.icon.default};
    transition: transform 0.3s ease-in-out;

    ${isOpen &&
    css`
      transform: translateY(-50%) rotate(180deg);
    `}
  `,
  pickerWrapper: css`
    position: absolute;
    background-color: ${colorPalate.basic.white};
    box-shadow: ${shadow.popover};
    border-radius: ${borderRadius[6]};

    .rdp {
      margin: ${spacing[20]};
    }

    .rdp-caption_label {
      ${typography.heading6()};
    }

    .rdp-day_outside {
      color: ${colorPalate.text.disabled};
    }

    .rdp-day_today:not(.rdp-day_outside) {
      font-weight: ${fontWeight.regular};
      border: 1px solid ${colorPalate.border.neutral};
    }

    .rdp-button:hover:not([disabled]):not(.rdp-day_selected) {
      background-color: ${colorPalate.surface.selected.pressed};
    }

    .rdp-day_selected,
    .rdp-day_selected:focus-visible,
    .rdp-day_selected:hover {
      background-color: ${colorPalate.surface.selected.pressed};
      color: ${colorPalate.text.neutral};
    }

    .rdp-day_range_start,
    .rdp-day_range_start:focus-visible,
    .rdp-day_range_start:hover,
    .rdp-day_range_end,
    .rdp-day_range_end:focus-visible,
    .rdp-day_range_end:hover {
      background-color: ${colorPalate.actions.primary.default};
      color: ${colorPalate.basic.white};
    }
  `,
  pickerContent: css`
    display: grid;
    grid-template-columns: 176px auto;
  `,
  sidebar: css`
    list-style: none;
    margin: 0;
    padding: ${spacing[8]} 0;
    border-right: 1px solid ${colorPalate.border.neutral};
  `,
  sidebarButton: (isActive: boolean) => css`
    ${styleUtils.resetButton};
    width: 100%;
    font-size: ${fontSize[14]};
    line-height: ${lineHeight[24]};
    padding: ${spacing[8]} ${spacing[16]};
    transition: background-color 0.3s ease-in-out;

    &:hover {
      background-color: ${colorPalate.surface.pressed};
    }

    ${isActive &&
    css`
      background-color: ${colorPalate.surface.pressed};
    `}
  `,
  rangeInputs: css`
    display: grid;
    grid-template-columns: auto 8px auto;
    align-items: center;
    gap: ${spacing[16]};
    padding: ${spacing[24]} ${spacing[20]} ${spacing[16]};

    input {
      height: 36px;
      border: 1px solid ${colorPalate.border.neutral};
      border-radius: ${borderRadius[6]};
      font-size: ${fontSize[14]};
      outline: none;
      padding: ${spacing[8]} ${spacing[12]};
    }

    span {
      height: 2px;
      width: 8px;
      background-color: ${colorPalate.icon.neutral};
    }
  `,
  pickerFooter: css`
    display: flex;
    justify-content: flex-end;
    gap: ${spacing[8]};
    padding: ${spacing[16]};
    box-shadow: ${shadow.dividerTop};
  `,
};
