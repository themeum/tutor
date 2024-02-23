import { borderRadius, colorPalate, colorTokens, fontSize, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { css, SerializedStyles } from '@emotion/react';
import { useDebounce } from '@Hooks/useDebounce';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import { useTranslation } from '@Hooks/useTranslation';
import { styleUtils } from '@Utils/style-utils';
import { Option } from '@Utils/types';
import { nanoid, noop } from '@Utils/util';
import { useEffect, useMemo, useState } from 'react';

import Button from './Button';
import LoadingSpinner from './LoadingSpinner';
import SVGIcon from './SVGIcon';
import useIntersectionObserver from '@Hooks/useIntersectionObserver';

type OptionsStyleVariant = 'regular' | 'small';

type SelectProps<T> = {
  options: Option<T>[];
  value?: Option<T>;
  label?: string;
  placeholder?: string;
  onChange?: (selectedOption: Option<T>) => void;
  clearOption?: () => void;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  isSearchable?: boolean;
  isInlineLabel?: boolean;
  isClearable?: boolean;
  isFontWeight?: boolean;
  caretSize?: number;
  infiniteScroll?: boolean;
  wrapperStyle?: SerializedStyles;
  inputCss?: SerializedStyles;
  optionsStyleVariant?: OptionsStyleVariant;
};

const OPTIONS_LIMIT = 20;

const Select = <T,>({
  options,
  onChange = noop,
  clearOption = noop,
  label,
  placeholder = '',
  readOnly,
  isSearchable = false,
  isClearable = true,
  isInlineLabel = false,
  isFontWeight = false,
  value,
  caretSize = 20,
  infiniteScroll = false,
  wrapperStyle,
  inputCss,
  disabled,
  optionsStyleVariant = 'regular',
}: SelectProps<T>) => {
  const t = useTranslation();
  const id = nanoid();

  const [inputValue, setInputValue] = useState('');
  const [searchText, setSearchText] = useState('');
  const [isOpen, setIsOpen] = useState(false);
  const [currentOptions, setCurrentOptions] = useState<Option<T>[]>([]);

  const debouncedSearchText = useDebounce(searchText);

  useEffect(() => {
    const updatedInputValue = options.find((item) => String(item.value) === String(value?.value))?.label ?? '';
    setInputValue(updatedInputValue);
  }, [options, value]);

  const selections = useMemo(() => {
    setCurrentOptions([]);

    if (isSearchable) {
      return options.filter(({ label }) => label.toLowerCase().startsWith(debouncedSearchText.toLowerCase()));
    }

    return options;
  }, [debouncedSearchText, isSearchable, options]);

  const { triggerRef, triggerWidth, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  const { intersectionEntry, intersectionElementRef } = useIntersectionObserver<HTMLDivElement>();
  const hasMoreItems = selections.length > currentOptions.length;

  useEffect(() => {
    if (!infiniteScroll) {
      setCurrentOptions(selections);
      return;
    }

    if (!intersectionEntry?.isIntersecting || !hasMoreItems) {
      return;
    }

    const startIndex = currentOptions.length;
    const endIndex = startIndex + OPTIONS_LIMIT;

    setCurrentOptions((previousOptions) => [...previousOptions, ...selections.slice(startIndex, endIndex)]);

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selections, intersectionEntry?.isIntersecting]);

  return (
    <div css={[styles.mainWrapper, wrapperStyle]}>
      <div css={styles.inputLabelWrapper(isInlineLabel)} ref={triggerRef}>
        {!!label && (
          <label htmlFor={id} css={styles.label(isInlineLabel)}>
            {label}
          </label>
        )}

        <div css={[styles.inputWrapper, inputCss]}>
          <input
            id={id}
            onClick={() => setIsOpen((previousState) => !previousState)}
            onFocus={() => setIsOpen(true)}
            css={styles.input({ isSearchable })}
            autoComplete='off'
            readOnly={readOnly || !isSearchable}
            placeholder={placeholder}
            value={inputValue}
            onChange={(event) => {
              setInputValue(event.target.value);
              setSearchText(event.target.value);
            }}
            disabled={disabled}
          />

          <button
            type='button'
            css={styles.caretButton}
            onClick={() => {
              setIsOpen((previousState) => !previousState);
            }}
            disabled={disabled || readOnly || options.length === 0}
          >
            <SVGIcon name='chevronDown' width={caretSize} height={caretSize} style={styles.toggleIcon({ isOpen })} />
          </button>
        </div>
      </div>

      <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)}>
        <div
          css={[styles.optionsWrapper, { left: position.left, top: position.top, maxWidth: triggerWidth }]}
          ref={popoverRef}
        >
          <ul css={styles.options}>
            {currentOptions.map((option) => (
              <li
                key={String(option.value)}
                css={styles.optionItem({
                  isSelected: option.value === value?.value,
                  optionsStyleVariant,
                  isDisabled: !!option.disabled,
                })}
              >
                <Button
                  variant='text'
                  buttonCss={styles.optionButton({ fontWeight: isFontWeight ? (option.value as number) : 'inherit' })}
                  onClick={() => {
                    setInputValue(option.label);
                    setSearchText('');
                    onChange(option);
                    setIsOpen(false);
                  }}
                >
                  {option.label}
                </Button>
              </li>
            ))}

            <div ref={intersectionElementRef} css={styles.spinnerWrapper({ isVisible: hasMoreItems })}>
              <LoadingSpinner />
            </div>
          </ul>
          {isClearable && (
            <div css={styles.clearButton({ isDisabled: inputValue === '' })}>
              <Button
                variant='text'
                disabled={inputValue === ''}
                icon={<SVGIcon name='cross' />}
                onClick={() => {
                  clearOption();
                  setInputValue('');
                  setSearchText('');
                  setIsOpen(false);
                }}
              >
                {t('COM_SPPAGEBUILDER_EDITOR_FORM_SELECT_CLEAR')}
              </Button>
            </div>
          )}
        </div>
      </Portal>
    </div>
  );
};

export default Select;

const styles = {
  mainWrapper: css`
    width: 100%;
  `,
  inputLabelWrapper: (isInlineLabel: boolean) => css`
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: ${spacing[4]};

    ${isInlineLabel &&
    css`
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: space-between;

      > div {
        flex: 1;
      }
    `}
  `,
  label: (isInlineLabel: boolean) => css`
    ${typography.caption()}

    ${isInlineLabel &&
    css`
      flex: 1;
    `}
  `,
  inputWrapper: css`
    width: 100%;
    position: relative;
  `,
  input: ({ isSearchable }: { isSearchable: boolean }) => css`
    ${typography.body()};
    width: 100%;
    height: 32px;
    border-radius: ${borderRadius[5]};
    border: 1px solid ${colorTokens.stroke.neutral};
    box-shadow: ${shadow.input};
    padding: 0 ${spacing[32]} 0 ${spacing[12]};
    color: ${colorPalate.text.default};
    appearance: textfield;

    ${!isSearchable &&
    css`
      cursor: pointer;
    `}

    :focus {
      outline: none;
      box-shadow: ${shadow.input};
    }

    ::-webkit-outer-spin-button,
    ::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    ::placeholder {
      color: ${colorTokens.text.hints};
    }
  `,
  clearButton: ({ isDisabled = false }: { isDisabled: boolean }) => css`
    padding: ${spacing[4]} ${spacing[8]};
    border-top: 1px solid ${colorTokens.stroke.neutral};
    display: flex;
    justify-content: center;
    min-height: 40px;

    & > button {
      padding: 0;
      width: 100%;
      font-size: ${fontSize[12]};

      > span {
        justify-content: center;
      }

      ${!isDisabled &&
      css`
        color: ${colorTokens.text.primary};

        &:hover {
          text-decoration: underline;
        }
      `}
    }
  `,
  optionsWrapper: css`
    position: absolute;
    width: 100%;
    z-index: ${zIndex.dropdown};
    background-color: ${colorPalate.basic.white};
    box-shadow: ${shadow.popover};
    border-radius: ${borderRadius[6]};
  `,
  options: css`
    list-style-type: none;
    max-height: 500px;
    padding: ${spacing[4]} 0;
    margin: 0;
    ${styleUtils.overflowYAuto};
  `,
  optionItem: ({
    isSelected = false,
    isDisabled = false,
    optionsStyleVariant = 'regular',
  }: {
    isSelected: boolean;
    isDisabled: boolean;
    optionsStyleVariant: OptionsStyleVariant;
  }) => css`
    ${typography.body()};
    min-height: 36px;
    height: 100%;
    width: 100%;
    display: flex;
    align-items: center;
    transition: background-color 0.3s ease-in-out;
    cursor: pointer;

    ${isDisabled &&
    css`
      pointer-events: none;

      > button {
        color: ${colorPalate.text.disabled};
      }
    `}

    &:hover {
      background-color: ${colorPalate.background.hover};
    }

    ${optionsStyleVariant === 'small' &&
    css`
      > button {
        ${typography.caption()};
      }
    `}

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
        background-color: ${colorPalate.actions.primary.default};
        border-radius: 0 ${borderRadius[6]} ${borderRadius[6]} 0;
      }
    `}
  `,
  optionButton: ({ fontWeight = 'inherit' }: { fontWeight: string | number }) => css`
    width: 100%;
    padding: ${spacing[8]} ${spacing[12]};
    font-weight: ${fontWeight};
  `,
  toggleIcon: ({ isOpen = false }: { isOpen: boolean }) => css`
    color: ${colorPalate.icon.default};
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
  caretButton: css`
    ${styleUtils.resetButton};
    position: absolute;
    top: 0;
    bottom: 0;
    right: ${spacing[8]};
    margin: auto 0;
  `,
  spinnerWrapper: ({ isVisible = false }: { isVisible: boolean }) => css`
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;

    ${!isVisible &&
    css`
      display: none;
    `}
  `,
};
