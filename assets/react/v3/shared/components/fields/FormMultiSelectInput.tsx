import { borderRadius, colorTokens, lineHeight, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { useState } from 'react';

import Checkbox from '@Atoms/CheckBox';
import Chip from '@Atoms/Chip';
import Show from '@Controls/Show';
import { useDebounce } from '@Hooks/useDebounce';
import { __ } from '@wordpress/i18n';
import FormFieldWrapper from './FormFieldWrapper';
import type { Option } from '@Utils/types';
import For from '@Controls/For';

interface FormMultiSelectInputProps extends FormControllerProps<string[] | null> {
  label?: string;
  placeholder?: string;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  isHidden?: boolean;
  helpText?: string;
  removeOptionsMinWidth?: boolean;
  options: Option<string>[];
}

const FormMultiSelectInput = ({
  field,
  fieldState,
  label,
  placeholder = '',
  disabled,
  readOnly,
  loading,
  helpText,
  removeOptionsMinWidth = false,
  options,
}: FormMultiSelectInputProps) => {
  const fieldValue = field.value ?? [];
  const currentlySelectedOptions = options.filter((option) => fieldValue.includes(option.value));

  const [searchText, setSearchText] = useState('');
  const debouncedSearchText = useDebounce(searchText);
  const filteredOptions = options.filter((option) =>
    option.label.toLowerCase().includes(debouncedSearchText.toLowerCase())
  );

  const [isOpen, setIsOpen] = useState(false);

  const { triggerRef, triggerWidth, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  const handleCheckboxChange = (checked: boolean, selectedId: string) => {
    if (checked) {
      field.onChange([...fieldValue, selectedId]);
    } else {
      field.onChange(fieldValue.filter((item) => item !== selectedId));
    }
  };

  return (
    <FormFieldWrapper
      fieldState={fieldState}
      field={field}
      label={label}
      disabled={disabled}
      readOnly={readOnly}
      loading={loading}
      helpText={helpText}
    >
      {(inputProps) => {
        const { css: inputCss, ...restInputProps } = inputProps;

        return (
          <div css={styles.mainWrapper}>
            <div css={styles.inputWrapper} ref={triggerRef}>
              <input
                {...restInputProps}
                css={[inputCss, styles.input]}
                onClick={() => setIsOpen(true)}
                autoComplete="off"
                readOnly={readOnly}
                placeholder={placeholder}
                value={searchText}
                onChange={(event) => {
                  setSearchText(event.target.value);
                }}
              />
            </div>

            {fieldValue.length > 0 && (
              <div css={styles.selectedOptionsWrapper}>
                {currentlySelectedOptions.map((option) => (
                  <Chip
                    key={option.value}
                    label={option.label}
                    onClick={() => handleCheckboxChange(false, option.value)}
                  />
                ))}
              </div>
            )}

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
                  <Show
                    when={filteredOptions.length > 0}
                    fallback={<div css={styles.notTag}>{__('No option available.', 'tutor')}</div>}
                  >
                    <For each={filteredOptions}>
                      {(option) => (
                        <li key={option.value} css={styles.optionItem}>
                          <Checkbox
                            label={option.label}
                            checked={!!fieldValue.find((item) => item === option.value)}
                            onChange={(checked) => handleCheckboxChange(checked, option.value)}
                          />
                        </li>
                      )}
                    </For>
                  </Show>
                </ul>
              </div>
            </Portal>
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
  notTag: css`
    ${typography.caption()};
    min-height: 80px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: ${colorTokens.text.subdued};
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
    ${styleUtils.textEllipsis};

    :focus {
      outline: none;
      box-shadow: ${shadow.focus};
    }
  `,
  selectedOptionsWrapper: css`
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: ${spacing[4]};
    margin-top: ${spacing[8]};
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
    max-height: 400px;
    border-radius: ${borderRadius[6]};
    ${styleUtils.overflowYAuto};

    ${
      !removeOptionsMinWidth &&
      css`
      min-width: 200px;
    `
    }
  `,
  optionItem: css`
    min-height: 40px;
    height: 100%;
    width: 100%;
    display: flex;
    align-items: center;
    padding: ${spacing[8]};
    transition: background-color 0.3s ease-in-out;

    label {
      width: 100%;
    }

    &:hover {
      background-color: ${colorTokens.background.hover};
    }
  `,
  addTag: css`
    ${styleUtils.resetButton};
    ${typography.body()}
    line-height: ${lineHeight[24]};
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
    width: 100%;
    padding: ${spacing[8]};

    &:hover {
      background-color: ${colorTokens.background.hover};
    }
  `,
};
