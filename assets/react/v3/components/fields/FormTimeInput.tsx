import Button, { ButtonVariant } from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { DateFormats } from '@Config/constants';
import { borderRadius, colorPalate, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { eachMinuteOfInterval, format, setHours, setMinutes } from 'date-fns';
import { useMemo, useState } from 'react';

import FormFieldWrapper from './FormFieldWrapper';

interface FormTimeInputProps extends FormControllerProps<string> {
  label?: string;
  interval?: number;
  disabled?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  isClearable?: boolean;
}

const FormTimeInput = ({
  label,
  field,
  fieldState,
  interval = 30,
  disabled,
  loading,
  placeholder,
  helpText,
  isClearable = true,
}: FormTimeInputProps) => {
  const [isOpen, setIsOpen] = useState(false);

  const { triggerRef, triggerWidth, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  const options = useMemo(() => {
    const start = setMinutes(setHours(new Date(), 0), 0);
    const end = setMinutes(setHours(new Date(), 23), 59);

    const range = eachMinuteOfInterval(
      {
        start,
        end,
      },
      { step: interval },
    );

    return range.map((date) => format(date, DateFormats.hoursMinutes));
  }, [interval]);

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
                value={field.value}
                onChange={(event) => {
                  const { value } = event.target;
                  field.onChange(value);
                }}
                autoComplete="off"
              />
              <SVGIcon name="clock" width={18} height={18} style={styles.icon} />

              {isClearable && field.value && (
                <Button variant={ButtonVariant.plain} buttonCss={styles.clearButton} onClick={() => field.onChange('')}>
                  <SVGIcon name="times" width={12} height={12} />
                </Button>
              )}
            </div>

            <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)}>
              <div
                css={[styles.popover, { left: position.left, top: position.top, maxWidth: triggerWidth }]}
                ref={popoverRef}
              >
                <ul css={styles.list}>
                  {options.map((option, index) => {
                    return (
                      <li key={index} css={styles.listItem}>
                        <button
                          type="button"
                          css={styles.itemButton}
                          onClick={() => {
                            field.onChange(option);
                            setIsOpen(false);
                          }}
                        >
                          {option}
                        </button>
                      </li>
                    );
                  })}
                </ul>
              </div>
            </Portal>
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormTimeInput;

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
  popover: css`
    position: absolute;
    width: 100%;
    background-color: ${colorPalate.basic.white};
    box-shadow: ${shadow.popover};
    height: 380px;
    overflow-y: auto;
    border-radius: ${borderRadius[6]};
  `,
  list: css`
    list-style: none;
    padding: 0;
    margin: 0;
  `,
  listItem: css`
    width: 100%;
    height: 42px;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: background-color 0.3s ease-in-out;

    :hover {
      background-color: ${colorPalate.surface.hover};
    }
  `,
  itemButton: css`
    ${styleUtils.resetButton};
    ${typography.heading6()};
    margin: ${spacing[4]} ${spacing[12]};
    width: 100%;
    height: 100%;
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
