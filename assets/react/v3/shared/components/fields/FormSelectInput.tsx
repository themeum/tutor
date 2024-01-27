import Button, { ButtonVariant } from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, fontSize, lineHeight, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { Option } from '@Utils/types';
import { useCallback, useEffect, useMemo, useState } from 'react';

import FormFieldWrapper from './FormFieldWrapper';
import { noop } from '@Utils/util';
import { __ } from '@wordpress/i18n';

type FormSelectInputProps<T> = {
  label?: string;
  options: Option<T>[];
  placeholder?: string;
  onChange?: (selectedOption: Option<T>) => void;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  isSearchable?: boolean;
  isHidden?: boolean;
  showRadio?: boolean;
  isInlineLabel?: boolean;
  hideCaret?: boolean;
  listLabel?: string;
  removeBorder?: boolean;
  isClearable?: boolean;
  responsive?: boolean;
  showArrowUpDown?: boolean;
  helpText?: string;
  removeOptionsMinWidth?: boolean;
} & FormControllerProps<T | null>;

const FormSelectInput = <T,>({
  options,
  field,
  fieldState,
  onChange = noop,
  label,
  placeholder = '',
  disabled,
  readOnly,
  loading,
  isSearchable = false,
  isInlineLabel,
  hideCaret,
  listLabel,
  isClearable = true,
  showArrowUpDown = false,
  helpText,
  removeOptionsMinWidth = false,
}: FormSelectInputProps<T>) => {
  const getInitialValue = useCallback(() => {
    return options.find(item => item.value === field.value)?.label || '';
  }, [options, field.value]);

  const [inputValue, setInputValue] = useState(getInitialValue);
  const [searchText, setSearchText] = useState('');
  const [isOpen, setIsOpen] = useState(false);

  const selections = useMemo(() => {
    if (isSearchable) {
      return options.filter(({ label }) => label.toLowerCase().startsWith(searchText.toLowerCase()));
    }

    return options;
  }, [searchText, isSearchable, options]);

  const { triggerRef, triggerWidth, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  useEffect(() => {
    setInputValue(getInitialValue);
  }, [field.value, getInitialValue]);

  useEffect(() => {
    if (isOpen) {
      setInputValue(getInitialValue);
    }
  }, [getInitialValue, isOpen]);

  return (
    <FormFieldWrapper
      fieldState={fieldState}
      field={field}
      label={label}
      disabled={disabled || options.length === 0}
      readOnly={readOnly}
      loading={loading}
      isInlineLabel={isInlineLabel}
      helpText={helpText}
    >
      {inputProps => {
        const { css: inputCss, ...restInputProps } = inputProps;

        return (
          <div css={styles.mainWrapper}>
            <div css={styles.inputWrapper} ref={triggerRef}>
              <input
                {...restInputProps}
                onClick={() => setIsOpen(previousState => !previousState)}
                css={[inputCss, styles.input]}
                autoComplete="off"
                readOnly={readOnly || !isSearchable}
                placeholder={placeholder}
                value={inputValue}
                onChange={event => {
                  setInputValue(event.target.value);
                  setSearchText(event.target.value);
                }}
              />

              {!hideCaret && (
                <button
                  type="button"
                  css={styles.caretButton}
                  onClick={() => {
                    setIsOpen(previousState => !previousState);
                  }}
                  disabled={readOnly || options.length === 0}
                >
                  {showArrowUpDown ? (
                    <SVGIcon name="chevronDown" width={20} height={20} style={styles.arrowUpDown} />
                  ) : (
                    <SVGIcon name="chevronDown" width={20} height={20} style={styles.toggleIcon({ isOpen })} />
                  )}
                </button>
              )}
            </div>

            <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)}>
              <div
                css={[
                  styles.optionsWrapper,
                  {
                    left: position.left,
                    top: position.top,
                    maxWidth: triggerWidth,
                  },
                ]}
                ref={popoverRef}
              >
                <ul css={[styles.options(removeOptionsMinWidth)]}>
                  {!!listLabel && <li css={styles.listLabel}>{listLabel}</li>}
                  {selections.map(option => (
                    <li
                      key={String(option.value)}
                      css={styles.optionItem({
                        isSelected: option.value === field.value,
                      })}
                    >
                      <button
                        css={styles.label}
                        onClick={() => {
                          field.onChange(option.value);
                          setSearchText('');
                          onChange(option);
                          setIsOpen(false);
                        }}
                      >
                        {option.label}
                      </button>
                    </li>
                  ))}

                  {isClearable && (
                    <div
                      css={styles.clearButton({
                        isDisabled: inputValue === '',
                      })}
                    >
                      <Button
                        variant={ButtonVariant.text}
                        disabled={inputValue === ''}
                        icon={<SVGIcon name="delete" />}
                        onClick={() => {
                          field.onChange(null);
                          setInputValue('');
                          setSearchText('');
                          setIsOpen(false);
                        }}
                      >
                        {__('Clear', 'tutor')}
                      </Button>
                    </div>
                  )}
                </ul>
              </div>
            </Portal>
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormSelectInput;

const styles = {
  mainWrapper: css`
    width: 100%;
  `,
  inputWrapper: css`
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
  `,
  input: css`
    ${typography.body()};
    width: 100%;
    cursor: pointer;
    padding-right: ${spacing[32]};
    ${styleUtils.textEllipsis};

    :focus {
      outline: none;
      box-shadow: ${shadow.focus};
    }
  `,
  listLabel: css`
    ${typography.body()};
    color: ${colorTokens.text.subdued};
    min-height: 40px;
    display: flex;
    align-items: center;
    padding-left: ${spacing[16]};
  `,
  clearButton: ({ isDisabled = false }: { isDisabled: boolean }) => css`
    padding: ${spacing[4]} ${spacing[8]};
    border-top: 1px solid ${colorTokens.stroke.default};

    & > button {
      padding: 0;
      width: 100%;
      font-size: ${fontSize[12]};

      > span {
        justify-content: center;
      }

      ${!isDisabled &&
      css`
        color: ${colorTokens.text.title};

        &:hover {
          text-decoration: underline;
        }
      `}
    }
  `,
  optionsWrapper: css`
    position: absolute;
    width: 100%;
  `,
  options: (removeOptionsMinWidth: boolean) => css`
    z-index: ${zIndex.dropdown};
    background-color: ${colorTokens.background.white};
    list-style-type: none;
    box-shadow: ${shadow.popover};
    padding: ${spacing[4]} 0;
    margin: 0;
    max-height: 500px;
    border-radius: ${borderRadius[6]};
    ${styleUtils.overflowYAuto};

    ${!removeOptionsMinWidth &&
    css`
      min-width: 200px;
    `}
  `,
  optionItem: ({ isSelected = false }: { isSelected: boolean }) => css`
    ${typography.body()};
    min-height: 36px;
    height: 100%;
    width: 100%;
    display: flex;
    align-items: center;
    transition: background-color 0.3s ease-in-out;
    cursor: pointer;

    &:hover {
      background-color: ${colorTokens.background.hover};
    }

    ${isSelected &&
    css`
      background-color: ${colorTokens.background.active};
      position: relative;

      &::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 3px;
        height: 100%;
        background-color: ${colorTokens.action.primary.default};
        border-radius: 0 ${borderRadius[6]} ${borderRadius[6]} 0;
      }
    `}
  `,
  label: css`
    ${styleUtils.resetButton};
    width: 100%;
    height: 100%;
    display: flex;
    gap: ${spacing[8]};
    margin: 0 ${spacing[12]};
    padding: ${spacing[6]} 0;
    text-align: left;
    line-height: ${lineHeight[24]};
    word-break: break-all;
    cursor: pointer;
  `,
  toggleIcon: ({ isOpen = false }: { isOpen: boolean }) => css`
    color: ${colorTokens.icon.default};
    transition: transform 0.3s ease-in-out;

    ${isOpen &&
    css`
      transform: rotate(180deg);
    `}
  `,
  arrowUpDown: css`
    color: ${colorTokens.icon.default};
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: ${spacing[2]};
  `,
  optionsContainer: css`
    position: absolute;
    overflow: hidden auto;
    min-width: 16px;
    min-height: 16px;
    max-width: calc(100% - 32px);
    max-height: calc(100% - 32px);
  `,
  caretButton: css`
    ${styleUtils.resetButton};
    position: absolute;
    top: 0;
    bottom: 0;
    right: ${spacing[8]};
    margin: auto 0;
    display: flex;
    align-items: center;
  `,
};
