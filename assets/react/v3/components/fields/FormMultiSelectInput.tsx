import Button, { ButtonVariant } from '@Atoms/Button';
import Checkbox from '@Atoms/CheckBox';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorPalate, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { usePortalPopover, Portal } from '@Hooks/usePortalPopover';
import { useTranslation } from '@Hooks/useTranslation';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { Option } from '@Utils/types';
import { noop } from '@Utils/util';
import { useCombobox } from 'downshift';
import { ReactNode, useState } from 'react';

import FormFieldWrapper from './FormFieldWrapper';

interface FormMultiSelectInputProps<T> extends FormControllerProps<T[]> {
  label?: string;
  isInlineLabel?: boolean;
  options: Option<T>[];
  placeholder?: string;
  onChange?: () => void;
  disabled?: boolean;
  loading?: boolean;
  isSearchable?: boolean;
  isHidden?: boolean;
  clearable?: boolean;
  icon?: ReactNode;
  iconPosition?: 'left' | 'right';
  hideCaret?: boolean;
  listLabel?: string;
  placeholderAsValue?: boolean;
}

const FormMultiSelectInput = <T,>({
  options,
  field,
  fieldState,
  onChange = noop,
  label,
  isInlineLabel,
  placeholder = '',
  disabled,
  loading,
  isSearchable,
  isHidden,
  clearable = false,
  icon,
  iconPosition = 'left',
  hideCaret = false,
  listLabel = '',
}: FormMultiSelectInputProps<T>) => {
  const [inputValue, setInputValue] = useState('');
  const t = useTranslation();

  const nonEmptyPlaceholder =
    field.value.length > 0
      ? t('COM_SPPAGEBUILDER_STORE_GLOBAL_MULTI_SELECT_NON_EMPTY_PLACEHOLDER', { count: field.value.length })
      : '';

  const { isOpen, openMenu, closeMenu, getInputProps, getMenuProps, getItemProps, getComboboxProps, highlightedIndex } =
    useCombobox({
      items: options,
      selectedItem: null,
      itemToString: (item) => item?.label || '',
      onInputValueChange: (changes) => {
        setInputValue(changes.inputValue || '');
      },
      onSelectedItemChange: ({ selectedItem }) => {
        if (!selectedItem) {
          return;
        }

        const fieldValue = field.value;
        const index = fieldValue.indexOf(selectedItem.value);
        if (index > 0) {
          field.onChange([...fieldValue.slice(0, index), ...fieldValue.slice(index + 1)]);
        } else if (index === 0) {
          field.onChange([...fieldValue.slice(1)]);
        } else {
          field.onChange([...fieldValue, selectedItem.value]);
        }

        onChange();
      },
      stateReducer: (state, actionAndChanges) => {
        const { changes, type } = actionAndChanges;

        switch (type) {
          case useCombobox.stateChangeTypes.InputKeyDownEnter:
          case useCombobox.stateChangeTypes.ItemClick:
            return {
              ...changes,
              isOpen: true,
              highlightedIndex: state.highlightedIndex,
              inputValue: '',
            };
          case useCombobox.stateChangeTypes.InputBlur:
            return {
              ...changes,
              highlightedIndex: state.highlightedIndex,
              inputValue: '',
            };
          default:
            return changes;
        }
      },
    });

  const { triggerRef, position, popoverRef, triggerWidth } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  return (
    <FormFieldWrapper
      fieldState={fieldState}
      field={field}
      label={label}
      isInlineLabel={isInlineLabel}
      disabled={disabled || options.length === 0}
      loading={loading}
      isHidden={isHidden}
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
                    if (!isOpen) {
                      openMenu();
                    }
                  },
                  value: inputValue || '',
                })}
                css={[inputCss, styles.input(hasIcon, iconPosition)]}
                autoComplete="off"
                readOnly={!isSearchable}
                placeholder={nonEmptyPlaceholder ? nonEmptyPlaceholder : placeholder}
              />
              {!hideCaret && (
                <button
                  type="button"
                  css={styleUtils.resetButton}
                  onClick={() => {
                    isOpen ? closeMenu() : openMenu();
                  }}
                >
                  <SVGIcon name="arrowDown" width={12} height={8} style={styles.toggleIcon(isOpen)} />
                </button>
              )}

              {icon && iconPosition === 'right' && <span css={styles.icon(iconPosition)}>{icon}</span>}
            </div>

            <div {...getMenuProps()}>
              <Portal isOpen={isOpen} onClickOutside={closeMenu}>
                <div
                  css={[styles.optionsWrapper, { left: position.left, top: position.top, maxWidth: triggerWidth }]}
                  ref={popoverRef}
                >
                  <ul css={styles.options}>
                    {!!listLabel && <li css={styles.listLabel}>{listLabel}</li>}

                    {options.map((option, index) => (
                      <li
                        key={index}
                        {...getItemProps({ index, item: option })}
                        css={styles.optionItemMulti(highlightedIndex === index)}
                      >
                        <Checkbox label={option.label} checked={field.value.includes(option.value)} />
                      </li>
                    ))}

                    {clearable && (
                      <div css={styles.clearButton}>
                        <Button
                          variant={ButtonVariant.plain}
                          disabled={field.value.length === 0}
                          onClick={() => {
                            field.onChange([]);
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

export default FormMultiSelectInput;

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
  clearButton: css`
    padding: ${spacing[8]} ${spacing[12]};

    & > button {
      padding: 0;
    }
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

    ${isHovered &&
    css`
      background-color: ${colorPalate.surface.hover};
      cursor: pointer;
    `}

    ${isSelected &&
    css`
      background-color: ${colorPalate.surface.selected.default};
    `}
  `,
  optionItemMulti: (isActive: boolean) => css`
    ${typography.body()};
    padding: ${spacing[8]} ${spacing[12]};
    cursor: pointer;

    ${isActive &&
    css`
      background-color: ${colorPalate.surface.hover};
    `}
  `,
  label: css`
    margin: ${spacing[4]} ${spacing[12]};
    cursor: pointer;
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
};
