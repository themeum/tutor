import Checkbox from '@Atoms/CheckBox';
import LoadingSpinner from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorPalate, fontSize, fontWeight, lineHeight, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { useIsShiftHolding } from '@Hooks/useIsShiftHolding';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import { useTranslation } from '@Hooks/useTranslation';
import { useAllTagListQuery, useCreateTagMutation } from '@Services/tags';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { Option } from '@Utils/types';
import { useMemo, useState } from 'react';

import FormFieldWrapper from './FormFieldWrapper';

interface FormTagsInputProps extends FormControllerProps<number[]> {
  label: string;
  placeholder?: string;
  disabled?: boolean;
  loading?: boolean;
  isHidden?: boolean;
}

const getOptionByID = (options: Option<number>[], id: number) => options.find((option) => option.value === id);

const FormTagsInput = ({
  field,
  fieldState,
  label,
  placeholder = '',
  disabled,
  loading,
  isHidden,
}: FormTagsInputProps) => {
  const t = useTranslation();
  const [inputValue, setInputValue] = useState('');
  const [isOpen, setIsOpen] = useState(false);

  const [lastSelectedIndex, setLastSelectedIndex] = useState(-1);
  const isShiftHolding = useIsShiftHolding();

  const allTagListQuery = useAllTagListQuery();
  const createTagMutation = useCreateTagMutation();

  const options = useMemo(() => {
    if (!allTagListQuery.data) {
      return [];
    }

    return allTagListQuery.data.map((item) => ({ label: item.name, value: item.id }));
  }, [allTagListQuery.data]);

  const items = useMemo(() => {
    return options.filter((option) => {
      return option.label.toLowerCase().includes(inputValue.toLowerCase());
    });
  }, [inputValue, options]);

  const { triggerRef, triggerWidth, popoverRef, position } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  const toggleCheckbox = (item: number, currentIndex: number) => (checked: boolean) => {
    setLastSelectedIndex(currentIndex);

    if (isShiftHolding && lastSelectedIndex > -1) {
      const itemsSlice = items
        .slice(Math.min(lastSelectedIndex, currentIndex), Math.max(lastSelectedIndex, currentIndex) + 1)
        .map(({ value }) => value);

      if (checked) {
        field.onChange([...new Set([...field.value, ...itemsSlice])]);
      } else {
        field.onChange(field.value.filter((value) => !itemsSlice.includes(value)));
      }
    } else if (checked) {
      field.onChange([...field.value, item]);
    } else {
      field.onChange(field.value.filter((value) => value !== item));
    }
  };

  return (
    <FormFieldWrapper
      fieldState={fieldState}
      field={field}
      label={label}
      disabled={disabled}
      loading={loading}
      isHidden={isHidden}
    >
      {(inputProps) => {
        const { css: inputCss, ...restInputProps } = inputProps;

        return (
          <div>
            <div css={styles.mainWrapper}>
              <div ref={triggerRef}>
                <input
                  {...restInputProps}
                  value={inputValue}
                  onChange={(event) => setInputValue(event.target.value)}
                  onFocus={() => setIsOpen(true)}
                  css={[
                    inputCss,
                    css`
                      width: 100%;
                    `,
                  ]}
                  autoComplete="off"
                  placeholder={placeholder}
                />
              </div>

              <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)}>
                <div
                  css={[styles.optionsWrapper, { left: position.left, top: position.top, minWidth: triggerWidth }]}
                  ref={popoverRef}
                >
                  <ul css={styles.options}>
                    {inputValue && !options.find((item) => item.label.toLowerCase() === inputValue.toLowerCase()) && (
                      <li css={styles.optionAddNew}>
                        <button
                          onClick={async () => {
                            const { data } = await createTagMutation.mutateAsync({
                              name: inputValue,
                            });

                            if (!data.id) {
                              return;
                            }

                            field.onChange([...field.value, data.id]);
                            setInputValue('');
                          }}
                          disabled={createTagMutation.isLoading}
                          type="button"
                        >
                          {createTagMutation.isLoading ? (
                            <span css={styles.loadingSpinner}>
                              <LoadingSpinner size={24} />
                            </span>
                          ) : (
                            <>
                              <SVGIcon name="circledPlus" width={24} height={24} />
                              <span>{t('COM_SPPAGEBUILDER_STORE_ADD_TAGS_BUTTON')}</span> {inputValue}
                            </>
                          )}
                        </button>
                      </li>
                    )}

                    {items.map((option, index) => (
                      <li key={option.value} css={styles.optionItem(false)}>
                        <Checkbox
                          label={option.label}
                          checked={field.value.includes(option.value)}
                          value={option.label}
                          onChange={toggleCheckbox(option.value, index)}
                        />
                      </li>
                    ))}
                  </ul>
                </div>
              </Portal>

              {field.value.length > 0 && (
                <div css={styles.chipsWrapper}>
                  {field.value.map((item, index) => (
                    <div key={index} css={styles.chipsItem}>
                      <span>{getOptionByID(options, item)?.label}</span>
                      <button
                        type="button"
                        css={styles.chipsRemove}
                        onClick={() => {
                          field.onChange(field.value.filter((option) => option !== item));
                        }}
                      >
                        <SVGIcon name="times" width={10} height={10} />
                      </button>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormTagsInput;

const styles = {
  mainWrapper: css`
    width: 100%;
    position: relative;
  `,
  inputWrapper: css`
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
  `,
  optionsWrapper: css`
    position: absolute;
    width: 100%;
    max-width: 0;
  `,
  options: css`
    width: 100%;
    z-index: ${zIndex.dropdown};
    background-color: ${colorPalate.basic.white};
    list-style-type: none;
    box-shadow: ${shadow.popover};
    border-radius: ${borderRadius[6]};
    padding: ${spacing[8]} 0;
    margin: 0;
    max-height: 500px;
    overflow-y: auto;

    ::-webkit-scrollbar {
      background-color: ${colorPalate.basic.white};
      width: 10px;
    }

    ::-webkit-scrollbar-thumb {
      background-color: ${colorPalate.basic.secondary};
      border-radius: ${borderRadius[6]};
    }
  `,
  optionItem: (isActive?: boolean) => css`
    padding: ${spacing[8]} ${spacing[12]};
    cursor: pointer;

    ${isActive &&
    css`
      background-color: ${colorPalate.surface.hover};
    `}
  `,
  optionAddNew: css`
    padding: ${spacing[8]} ${spacing[12]};
    background-color: ${colorPalate.surface.selected.default};
    color: ${colorPalate.text.default};
    font-size: ${fontSize[14]};
    line-height: ${lineHeight[18]};

    & button {
      ${styleUtils.resetButton};
      display: flex;
      align-items: center;
      width: 100%;
    }

    & svg {
      color: ${colorPalate.icon.default};
      margin-right: ${spacing[8]};
    }

    & span {
      font-weight: ${fontWeight.bold};
      margin-right: ${spacing[4]};
    }
  `,
  loadingSpinner: css`
    width: 100%;
    ${styleUtils.flexCenter()};
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
  chipsWrapper: css`
    display: flex;
    margin-top: ${spacing[16]};
    gap: ${spacing[8]} ${spacing[12]};
    flex-wrap: wrap;
  `,
  chipsItem: css`
    background-color: ${colorPalate.surface.selected.default};
    padding: ${spacing[4]} ${spacing[8]} ${spacing[4]} ${spacing[12]};
    border: 1px solid ${colorPalate.border.neutral};
    border-radius: ${borderRadius[50]};
    ${typography.body()};
    color: ${colorPalate.text.neutral};
    display: flex;
    justify-content: center;
    align-items: center;
    gap: ${spacing[8]};
    transition: color 0.3s ease-in-out;

    :hover {
      color: ${colorPalate.text.default};
    }
  `,

  chipsRemove: css`
    ${styleUtils.resetButton};
    color: ${colorPalate.icon.neutral};
    width: 24px;
    height: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: ${borderRadius.circle};
    transition: all 0.3s ease-in-out;

    :hover {
      background-color: ${colorPalate.surface.selected.pressed};
      color: ${colorPalate.icon.hover};
    }
  `,
};
