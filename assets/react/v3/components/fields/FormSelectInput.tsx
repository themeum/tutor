import Button, { ButtonVariant } from '@Atoms/Button';
import Radio from '@Atoms/Radio';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorPalate, fontSize, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import { useTranslation } from '@Hooks/useTranslation';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { Option } from '@Utils/types';
import { noop } from '@Utils/util';
import { useCombobox } from 'downshift';
import { ReactNode, useMemo, useState } from 'react';

import FormFieldWrapper from './FormFieldWrapper';

interface FormSelectInputProps<T> extends FormControllerProps<T | null> {
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
  clearable?: boolean;
  icon?: ReactNode;
  iconPosition?: 'left' | 'right';
  hideCaret?: boolean;
  listLabel?: string;
  removeBorder?: boolean;
}

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
  isSearchable,
  isHidden,
  showRadio,
  isInlineLabel,
  clearable,
  icon,
  iconPosition = 'left',
  hideCaret,
  listLabel,
  removeBorder = false,
}: FormSelectInputProps<T>) => {
  const t = useTranslation();
  const getInitialValue = () => {
    return options.find((item) => String(item.value) === String(field.value))?.label || '';
  };

  const [inputValue, setInputValue] = useState(getInitialValue);
  const [searchText, setSearchText] = useState('');

  const selections = useMemo(() => {
    if (isSearchable) {
      return options.filter(({ label }) => label.toLowerCase().startsWith(searchText.toLowerCase()));
    }

    return options;
  }, [searchText, isSearchable, options]);

  const {
    isOpen,
    openMenu,
    closeMenu,
    getInputProps,
    getMenuProps,
    getItemProps,
    getComboboxProps,
    highlightedIndex,
    selectedItem,
  } = useCombobox({
    items: selections,
    selectedItem: options.find((option) => String(option.value) === String(field.value)) || null,
    itemToString: (item) => item?.label || '',
    onInputValueChange: (changes) => {
      setInputValue(changes.inputValue || '');
      setSearchText(changes.inputValue || '');
    },
    onSelectedItemChange: (item) => {
      if (item.selectedItem) {
        field.onChange(item.selectedItem.value);
        setInputValue(item.selectedItem.label);
        onChange(item.selectedItem);
      }
    },
    stateReducer: (_, actionAndChanges) => {
      const { type, changes } = actionAndChanges;
      switch (type) {
        case useCombobox.stateChangeTypes.InputKeyDownEnter:
        case useCombobox.stateChangeTypes.InputBlur:
          return {
            ...changes,
            isOpen: true,
          };
        case useCombobox.stateChangeTypes.ItemClick:
          return {
            ...changes,
            ...(changes.selectedItem ? { inputValue: changes.selectedItem.label } : { inputValue: '' }),
          };
        default:
          return changes;
      }
    },
  });

  const { triggerRef, triggerWidth, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  return (
    <FormFieldWrapper
      fieldState={fieldState}
      field={field}
      label={label}
      disabled={disabled || options.length === 0}
      readOnly={readOnly}
      loading={loading}
      isHidden={isHidden}
      isInlineLabel={isInlineLabel}
      removeBorder={removeBorder}
    >
      {(inputProps) => {
        const { css: inputCss, ...restInputProps } = inputProps;
        const hasIcon = !!icon;
        return (
          <div css={styles.mainWrapper} {...getComboboxProps()}>
            <div css={styles.inputWrapper} ref={triggerRef}>
              {icon && iconPosition === 'left' && <span css={styles.icon(iconPosition)}>{icon}</span>}
              <input
                {...restInputProps}
                {...getInputProps({
                  onClick: () => {
                    if (!readOnly && !isOpen) {
                      setSearchText('');
                      openMenu();
                    }
                  },
                  value: inputValue || '',
                })}
                css={[inputCss, styles.input(hasIcon, iconPosition)]}
                autoComplete="off"
                readOnly={readOnly || !isSearchable}
                placeholder={placeholder}
              />

              {!hideCaret && (
                <button
                  type="button"
                  css={styleUtils.resetButton}
                  onClick={() => {
                    isOpen ? closeMenu() : openMenu();
                  }}
                  disabled={readOnly}
                >
                  <SVGIcon name="arrowDown" width={12} height={8} style={styles.toggleIcon(isOpen)} />
                </button>
              )}
            </div>

            <div {...getMenuProps()}>
              <Portal isOpen={isOpen} onClickOutside={closeMenu}>
                <div
                  css={[styles.optionsWrapper, { left: position.left, top: position.top, maxWidth: triggerWidth }]}
                  ref={popoverRef}
                >
                  <ul css={[styles.options]}>
                    {!!listLabel && <li css={styles.listLabel}>{listLabel}</li>}
                    {selections.map((option, index) => (
                      <li
                        key={index}
                        {...getItemProps({ index, item: option })}
                        css={styles.optionItem({
                          isHovered: highlightedIndex === index,
                          isSelected: selectedItem?.value === option.value,
                        })}
                      >
                        <p css={styles.label}>
                          {showRadio ? (
                            <Radio label={option.label} checked={option.value === field.value} readOnly />
                          ) : (
                            option.label
                          )}
                        </p>
                      </li>
                    ))}

                    {clearable && (
                      <div css={styles.clearButton(hasIcon)}>
                        <Button
                          variant={ButtonVariant.plain}
                          disabled={inputValue === ''}
                          onClick={() => {
                            field.onChange(null);
                            setInputValue('');
                            setSearchText('');
                          }}
                        >
                          {t('COM_SPPAGEBUILDER_STORE_FORM_SELECT_CLEAR')}
                        </Button>
                      </div>
                    )}
                  </ul>
                </div>
              </Portal>
            </div>
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

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
  input: (hasIcon: boolean, position: 'left' | 'right') => css`
    ${typography.body()};
    width: 100%;
    cursor: pointer;

    ${hasIcon &&
    position === 'left' &&
    css`
      padding-left: ${spacing[40]};
    `}

    ${hasIcon &&
    position === 'right' &&
    css`
      padding-right: ${spacing[40]};
    `}
  `,
  icon: (position: 'left' | 'right') => css`
    position: absolute;
    top: ${spacing[8]};
    height: 20px;
    width: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: ${colorPalate.icon.default};

    ${position === 'left' &&
    css`
      left: ${spacing[16]};
    `}

    ${position === 'right' &&
    css`
      right: ${spacing[16]};
    `}
  `,

  listLabel: css`
    ${typography.body()};
    color: ${colorPalate.text.neutral};
    min-height: 40px;
    display: flex;
    align-items: center;
    padding-left: ${spacing[16]};
  `,
  clearButton: (hasOptionIcon: boolean) => css`
    padding: ${spacing[8]} ${spacing[12]};

    & > button {
      padding: 0;
    }

    ${hasOptionIcon &&
    css`
      padding-left: ${spacing[32]};
    `}
  `,
  optionIcon: css`
    font-size: ${fontSize[24]};
  `,
  optionsWrapper: css`
    position: absolute;
    width: 100%;
  `,
  options: css`
    z-index: ${zIndex.dropdown};
    background-color: ${colorPalate.basic.white};
    list-style-type: none;
    box-shadow: ${shadow.popover};
    padding: ${spacing[4]} 0;
    margin: 0;
    max-height: 500px;
    overflow-y: auto;
    border-radius: ${borderRadius[6]};

    ::-webkit-scrollbar {
      background-color: ${colorPalate.basic.white};
      width: 10px;
    }

    ::-webkit-scrollbar-thumb {
      background-color: ${colorPalate.basic.secondary};
      border-radius: ${borderRadius[6]};
    }
  `,
  optionItem: ({ isHovered, isSelected }: { isHovered: boolean; isSelected: boolean }) => css`
    ${typography.body()};
    min-height: 40px;
    display: flex;
    align-items: center;
    transition: background-color 0.3s ease-in-out;
    cursor: pointer;

    ${isHovered &&
    css`
      background-color: ${colorPalate.surface.hover};
    `}

    ${isSelected &&
    css`
      background-color: ${colorPalate.surface.selected.default};
      position: relative;
      &::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 3px;
        height: 100%;
        background-color: ${colorPalate.actions.primary.default};
        border-radius: 0 ${borderRadius[6]} ${borderRadius[6]} 0;
      }
    `}
  `,
  label: css`
    margin: ${spacing[4]} ${spacing[12]};
    display: flex;
    gap: ${spacing[8]};
  `,
  toggleIcon: (isOpen: boolean) => css`
    position: absolute;
    top: 14px;
    right: ${spacing[12]};
    transition: transform 0.3s ease-in-out;

    ${isOpen &&
    css`
      transform: rotate(180deg);
    `}
  `,
  optionsContainer: css`
    position: absolute;
    overflow: hidden auto;
    min-width: 16px;
    min-height: 16px;
    max-width: calc(100% - 32px);
    max-height: calc(100% - 32px);
  `,
};

export default FormSelectInput;
