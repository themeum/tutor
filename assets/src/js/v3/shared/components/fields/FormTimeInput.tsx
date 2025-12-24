import { css } from '@emotion/react';
import { eachMinuteOfInterval, format, setHours, setMinutes } from 'date-fns';
import { useEffect, useMemo, useRef, useState } from 'react';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Popover from '@TutorShared/molecules/Popover';

import { DateFormats } from '@TutorShared/config/constants';
import { colorTokens, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { type FormControllerProps } from '@TutorShared/utils/form';
import { styleUtils } from '@TutorShared/utils/style-utils';

import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { useSelectKeyboardNavigation } from '@TutorShared/hooks/useSelectKeyboardNavigation';

import { __ } from '@wordpress/i18n';
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

  const triggerRef = useRef<HTMLDivElement>(null);
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
                onClick={(event) => {
                  event.stopPropagation();
                  setIsOpen((previousState) => !previousState);
                }}
                onKeyDown={(event) => {
                  if (event.key === 'Enter') {
                    event.preventDefault();
                    setIsOpen((previousState) => !previousState);
                  }

                  if (event.key === 'Tab') {
                    setIsOpen(false);
                  }
                }}
                value={field.value ?? ''}
                onChange={(event) => {
                  const { value } = event.target;
                  field.onChange(value);
                }}
                autoComplete="off"
                data-input
              />
              <SVGIcon name="clock" width={32} height={32} style={styles.icon} />

              {isClearable && field.value && (
                <Button
                  isIconOnly
                  aria-label={__('Clear', __TUTOR_TEXT_DOMAIN__)}
                  size="small"
                  variant="text"
                  buttonCss={styleUtils.inputClearButton}
                  onClick={() => field.onChange('')}
                  icon={<SVGIcon name="times" width={12} height={12} />}
                />
              )}
            </div>

            <Popover
              triggerRef={triggerRef}
              isOpen={isOpen}
              closePopover={() => setIsOpen(false)}
              animationType={AnimationType.slideDown}
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
                        onMouseLeave={() => index !== activeIndex && setActiveIndex(-1)}
                        onFocus={() => setActiveIndex(index)}
                      >
                        {option}
                      </button>
                    </li>
                  );
                })}
              </ul>
            </Popover>
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
  list: css`
    height: 380px;
    list-style: none;
    padding: 0;
    margin: 0;
    ${styleUtils.overflowYAuto};
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

    &:focus,
    &:active,
    &:hover {
      background: none;
      color: ${colorTokens.text.primary};
    }
  `,
};
