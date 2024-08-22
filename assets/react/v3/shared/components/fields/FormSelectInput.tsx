import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { type ReactNode, useEffect, useMemo, useRef, useState } from 'react';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, colorTokens, fontSize, lineHeight, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { type IconCollection, type Option, isDefined } from '@Utils/types';

import Show from '@Controls/Show';
import { noop } from '@Utils/util';
import FormFieldWrapper from './FormFieldWrapper';

type FormSelectInputProps<T> = {
  label?: React.ReactNode;
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
  helpText?: string;
  removeOptionsMinWidth?: boolean;
  leftIcon?: ReactNode;
  dataAttribute?: string;
  isSecondary?: boolean;
  isMagicAi?: boolean;
  isAiOutline?: boolean;
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
  isClearable = false,
  helpText,
  removeOptionsMinWidth = false,
  leftIcon,
  removeBorder,
  dataAttribute,
  isSecondary = false,
  isMagicAi = false,
  isAiOutline = false,
}: FormSelectInputProps<T>) => {
  const getInitialValue = () =>
    options.find((item) => item.value === field.value) || {
      label: '',
      value: '',
      description: '',
    };

  const hasDescription = useMemo(() => options.some((option) => isDefined(option.description)), [options]);

  const [inputValue, setInputValue] = useState(getInitialValue()?.label);
  const [searchText, setSearchText] = useState('');
  const [isOpen, setIsOpen] = useState(false);

  const inputRef = useRef<HTMLInputElement>(null);

  const selections = useMemo(() => {
    if (isSearchable) {
      return options.filter(({ label }) => label.toLowerCase().includes(searchText.toLowerCase()));
    }

    return options;
  }, [searchText, isSearchable, options]);

  const selectedItem = useMemo(() => {
    return options.find((item) => item.value === field.value);
  }, [field.value, options]);

  const { triggerRef, triggerWidth, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  const additionalAttributes = {
    ...(isDefined(dataAttribute) && { [dataAttribute]: true }),
  };

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    setInputValue(getInitialValue()?.label);
    // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  }, [field.value, getInitialValue]);

  useEffect(() => {
    if (isOpen) {
      setInputValue(getInitialValue()?.label);
    }
    // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
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
      removeBorder={removeBorder}
      isSecondary={isSecondary}
      isMagicAi={isMagicAi}
    >
      {(inputProps) => {
        const { css: inputCss, ...restInputProps } = inputProps;

        return (
          <div css={styles.mainWrapper}>
            <div css={styles.inputWrapper(isAiOutline)} ref={triggerRef}>
              <div css={styles.leftIcon({ hasDescription })}>
                <Show when={leftIcon}>{leftIcon}</Show>
                <Show when={selectedItem?.icon}>
                  {(iconName) => <SVGIcon name={iconName as IconCollection} width={32} height={32} />}
                </Show>
              </div>

              <div
                css={{
                  width: '100%',
                }}
                onClick={() => {
                  setIsOpen((previousState) => !previousState);
                  inputRef.current?.focus();
                }}
                onKeyDown={(event) => {
                  if (event.key === 'Enter' || event.key === '') {
                    setIsOpen((previousState) => !previousState);
                    inputRef.current?.focus();
                  }
                }}
              >
                <input
                  {...restInputProps}
                  {...additionalAttributes}
                  ref={inputRef}
                  className="tutor-input-field"
                  css={[
                    inputCss,
                    styles.input({
                      hasLeftIcon: !!leftIcon || !!selectedItem?.icon,
                      hasDescription,
                      hasError: !!fieldState.error,
                      isMagicAi,
                      isAiOutline,
                    }),
                  ]}
                  autoComplete="off"
                  readOnly={readOnly || !isSearchable}
                  placeholder={placeholder}
                  value={searchText || inputValue}
                  title={inputValue}
                  onChange={(event) => {
                    setInputValue(event.target.value);
                    if (isSearchable) {
                      setSearchText(event.target.value);
                    }
                  }}
                  data-select
                />

                <Show when={hasDescription}>
                  <span css={styles.description({ hasLeftIcon: !!leftIcon })} title={getInitialValue()?.description}>
                    {getInitialValue()?.description}
                  </span>
                </Show>
              </div>

              {!hideCaret && !loading && (
                <button
                  type="button"
                  css={styles.caretButton({ isOpen })}
                  onClick={() => {
                    setIsOpen((previousState) => !previousState);
                    inputRef.current?.focus();
                  }}
                  disabled={readOnly || options.length === 0}
                >
                  <SVGIcon name="chevronDown" width={20} height={20} />
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
                  {selections.map((option) => (
                    <li
                      key={String(option.value)}
                      css={styles.optionItem({
                        isSelected: option.value === field.value,
                      })}
                    >
                      <button
                        type="button"
                        css={styles.label}
                        onClick={() => {
                          field.onChange(option.value);
                          setSearchText('');
                          onChange(option);
                          setIsOpen(false);
                        }}
                        title={option.label}
                      >
                        <Show when={option.icon}>
                          <SVGIcon name={option.icon as IconCollection} width={32} height={32} />
                        </Show>
                        <span>{option.label}</span>
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
                        variant="text"
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
  inputWrapper: (isAiOutline = false) => css`
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    background-color: ${colorTokens.background.white};
    
    ${
      isAiOutline &&
      css`
      &::before {
        content: '';
        position: absolute;
        inset: 0;
        background: ${colorTokens.ai.gradient_1};
        color: ${colorTokens.text.primary};
        border: 1px solid transparent;
        -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        border-radius: 6px;
      }
    `
    }
  `,
  leftIcon: ({
    hasDescription = false,
  }: {
    hasDescription: boolean;
  }) => css`
    position: absolute;
    left: ${spacing[8]};
    top: ${spacing[4]};
    color: ${colorTokens.icon.default};

		${
      hasDescription &&
      css`
			top: calc(${spacing[12]});
		`
    }
		
  `,
  input: ({
    hasLeftIcon,
    hasDescription,
    hasError = false,
    isMagicAi = false,
    isAiOutline = false,
  }: {
    hasLeftIcon: boolean;
    hasDescription: boolean;
    hasError: boolean;
    isMagicAi: boolean;
    isAiOutline: boolean;
  }) => css`
    &[data-select] {
      ${typography.body()};
      width: 100%;
      cursor: pointer;
      padding-right: ${spacing[32]};
      ${styleUtils.textEllipsis};
      background-color: transparent;
      background-color: ${colorTokens.background.white};

      ${
        hasLeftIcon &&
        css`
          padding-left: ${spacing[48]};
        `
      }

      ${
        hasDescription &&
        css`
          &.tutor-input-field {
            height: 56px;
            padding-bottom: ${spacing[24]}
          };
        `
      }

      ${
        hasError &&
        css`
        background-color: ${colorTokens.background.status.errorFail};
      `
      }

      ${
        isAiOutline &&
        css`
        position: relative;
        border: none;
        background: transparent;
      `
      }

      :focus {
        ${styleUtils.inputFocus};

        ${
          isMagicAi &&
          css`
          outline-color: ${colorTokens.stroke.magicAi};
          background-color: ${colorTokens.background.magicAi[8]};
        `
        }

        ${
          hasError &&
          css`
          border-color: ${colorTokens.stroke.danger};
          background-color: ${colorTokens.background.status.errorFail};
        `
        }
      }
    }
  `,
  description: ({
    hasLeftIcon,
  }: {
    hasLeftIcon: boolean;
  }) => css`
		${typography.small()};
		${styleUtils.text.ellipsis(1)}
		color: ${colorTokens.text.hints};
		position: absolute;
		bottom: ${spacing[8]};
		padding-inline: calc(${spacing[16]} + 1px) ${spacing[32]};

		${
      hasLeftIcon &&
      css`
			padding-left: calc(${spacing[48]} + 1px);
		`
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

      ${
        !isDisabled &&
        css`
        color: ${colorTokens.text.title};

        &:hover {
          text-decoration: underline;
        }
      `
      }
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

    ${
      !removeOptionsMinWidth &&
      css`
      min-width: 200px;
    `
    }
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

    ${
      isSelected &&
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
    `
    }
  `,
  label: css`
    ${styleUtils.resetButton};
		${styleUtils.text.ellipsis(1)}
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    gap: ${spacing[8]};
    margin: 0 ${spacing[12]};
    padding: ${spacing[6]} 0;
    text-align: left;
    line-height: ${lineHeight[24]};
    word-break: break-all;
    cursor: pointer;

    span {
      flex-shrink: 0;
      ${styleUtils.text.ellipsis(1)}
      width: 100%;
    }
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
    max-width: calc(100% - 32px);
  `,
  caretButton: ({ isOpen = false }: { isOpen: boolean }) => css`
    ${styleUtils.resetButton};
    position: absolute;
    top: 0;
    bottom: 0;
    right: ${spacing[8]};
    margin: auto 0;
    display: flex;
    align-items: center;
		transition: transform 0.3s ease-in-out;
		color: ${colorTokens.icon.default};
		
		${
      isOpen &&
      css`
      transform: rotate(180deg);
    `
    }
  `,
};
