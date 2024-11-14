import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { DateFormats } from '@Config/constants';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { eachMinuteOfInterval, format, setHours, setMinutes } from 'date-fns';
import { useEffect, useMemo, useRef, useState } from 'react';

import { useSelectKeyboardNavigation } from '../../hooks/useSelectKeyboardNavigation';
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

  const activeItemRef = useRef<HTMLLIElement | null>(null);

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

  const { triggerRef, triggerWidth, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  const { activeIndex, setActiveIndex } = useSelectKeyboardNavigation({
    options: options.map((option) => ({ label: option, value: option })),
    isOpen,
    selectedValue: field.value,
    onSelect: (selectedOption) => {
      field.onChange(selectedOption.value);
      setIsOpen(false);
    },
    onClose: () => setIsOpen(false),
  });

  useEffect(() => {
    if (isOpen && activeIndex >= 0 && activeItemRef.current) {
      activeItemRef.current.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
  }, [isOpen, activeIndex]);

  console.log(activeIndex);

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
                ref={field.ref}
                css={[css, styles.input]}
                type="text"
                onFocus={() => setIsOpen(true)}
                value={field.value ?? ''}
                onChange={(event) => {
                  const { value } = event.target;
                  field.onChange(value);
                }}
                autoComplete="off"
                data-input
                onBlur={() => {
                  setIsOpen(false);
                }}
              />
              <SVGIcon name="clock" width={32} height={32} style={styles.icon} />

              {isClearable && field.value && (
                <Button variant="text" buttonCss={styles.clearButton} onClick={() => field.onChange('')}>
                  <SVGIcon name="times" width={12} height={12} />
                </Button>
              )}
            </div>

            <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)} onEscape={() => setIsOpen(false)}>
              <div
                css={[styles.popover, { left: position.left, top: position.top, maxWidth: triggerWidth }]}
                ref={popoverRef}
              >
                <ul css={styles.list}>
                  {options.map((option, index) => {
                    return (
                      <li
                        key={index}
                        css={styles.listItem}
                        ref={activeIndex === index ? activeItemRef : null}
                        data-active={activeIndex === index}
                      >
                        <button
                          type="button"
                          css={styles.itemButton}
                          onClick={() => {
                            field.onChange(option);
                            setIsOpen(false);
                          }}
                          onMouseOver={() => setActiveIndex(index)}
                          onFocus={() => setActiveIndex(index)}
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
  popover: css`
    position: absolute;
    width: 100%;
    background-color: ${colorTokens.background.white};
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
    height: 40px;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: background-color 0.3s ease-in-out;

    &[data-active='true'] {
      background-color: ${colorTokens.background.hover};
    }

    :hover {
      background-color: ${colorTokens.background.hover};
    }
  `,
  itemButton: css`
    ${styleUtils.resetButton};
    ${typography.body()};
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
      background-color: ${colorTokens.background.hover};
    }
  `,
};
