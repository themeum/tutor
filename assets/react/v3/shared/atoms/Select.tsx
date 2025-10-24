import { type SerializedStyles, css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useRef, useState } from 'react';

import Popover from '@TutorShared/molecules/Popover';

import { borderRadius, colorTokens, fontSize, shadow, spacing } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { useDebounce } from '@TutorShared/hooks/useDebounce';
import useIntersectionObserver from '@TutorShared/hooks/useIntersectionObserver';
import { styleUtils } from '@TutorShared/utils/style-utils';
import type { Option } from '@TutorShared/utils/types';
import { nanoid, noop } from '@TutorShared/utils/util';

import Button from './Button';
import LoadingSpinner from './LoadingSpinner';
import SVGIcon from './SVGIcon';

type OptionsStyleVariant = 'regular' | 'small';
type Size = 'regular' | 'small';

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
  size?: Size;
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
  size = 'regular',
  optionsStyleVariant = 'regular',
}: SelectProps<T>) => {
  const id = nanoid();

  const [inputValue, setInputValue] = useState('');
  const [searchText, setSearchText] = useState('');
  const [isOpen, setIsOpen] = useState(false);
  const [currentOptions, setCurrentOptions] = useState<Option<T>[]>([]);
  const triggerRef = useRef<HTMLDivElement>(null);

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
            css={styles.input({ isSearchable, size })}
            autoComplete="off"
            readOnly={readOnly || !isSearchable}
            placeholder={placeholder}
            value={inputValue}
            onChange={(event) => {
              setInputValue(event.target.value);
              setSearchText(event.target.value);
            }}
            disabled={disabled}
            data-select
          />

          <button
            type="button"
            css={styles.caretButton}
            onClick={() => {
              setIsOpen((previousState) => !previousState);
            }}
            disabled={disabled || readOnly || options.length === 0}
          >
            <SVGIcon name="chevronDown" width={caretSize} height={caretSize} style={styles.toggleIcon({ isOpen })} />
          </button>
        </div>
      </div>

      <Popover
        triggerRef={triggerRef}
        isOpen={isOpen}
        closePopover={() => setIsOpen(false)}
        animationType={AnimationType.slideDown}
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
                variant="text"
                buttonCss={styles.optionButton({
                  fontWeight: isFontWeight ? (option.value as number) : 'inherit',
                })}
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
              variant="text"
              disabled={inputValue === ''}
              icon={<SVGIcon name="delete" />}
              onClick={() => {
                clearOption();
                setInputValue('');
                setSearchText('');
                setIsOpen(false);
              }}
            >
              {__('Clear', __TUTOR_TEXT_DOMAIN__)}
            </Button>
          </div>
        )}
      </Popover>
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
  input: ({ isSearchable, size }: { isSearchable: boolean; size: Size }) => css`
    &[data-select] {
      ${typography.body()};
      width: 100%;
      height: 40px;
      background-color: ${colorTokens.background.white};
      border-radius: ${borderRadius[6]};
      border: 1px solid ${colorTokens.stroke.default};
      box-shadow: ${shadow.input};
      padding: ${spacing[8]} ${spacing[32]} ${spacing[8]} ${spacing[16]};
      color: ${colorTokens.text.primary};
      appearance: textfield;
      transition: all 0.15s ease-in-out;

      ${!isSearchable &&
      css`
        cursor: pointer;
      `}

      :focus {
        ${styleUtils.inputFocus};
      }

      :disabled {
        color: ${colorTokens.text.disable};
        cursor: not-allowed;
      }

      ::-webkit-outer-spin-button,
      ::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
      }

      ::placeholder {
        color: ${colorTokens.text.subdued};
      }

      ${size === 'small' &&
      css`
        height: 32px;
      `}
    }
  `,
  clearButton: ({ isDisabled = false }: { isDisabled: boolean }) => css`
    padding: ${spacing[4]} ${spacing[8]};
    border-top: 1px solid ${colorTokens.stroke.default};
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
        color: ${colorTokens.text.title};

        &:hover {
          text-decoration: underline;
        }
      `}
    }
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
        color: ${colorTokens.text.disable};
      }
    `}

    &:hover {
      background-color: ${colorTokens.background.hover};
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
        background-color: ${colorTokens.brand.blue};
        border-radius: 0 ${borderRadius[6]} ${borderRadius[6]} 0;
      }
    `}
  `,
  optionButton: ({ fontWeight = 'inherit' }: { fontWeight: string | number }) => css`
    width: 100%;
    justify-content: start;
    padding: ${spacing[6]} ${spacing[12]};
    font-weight: ${fontWeight};
    color: ${colorTokens.text.title};

    :hover {
      text-decoration: none;
    }

    :focus {
      box-shadow: none;
    }
  `,
  toggleIcon: ({ isOpen = false }: { isOpen: boolean }) => css`
    color: ${colorTokens.icon.default};
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
    display: flex;
    align-items: center;
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
