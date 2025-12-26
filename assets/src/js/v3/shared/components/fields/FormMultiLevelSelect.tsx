import { css, type SerializedStyles } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Popover from '@TutorShared/molecules/Popover';

import { borderRadius, colorTokens, fontWeight, lineHeight, shadow, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import Show from '@TutorShared/controls/Show';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { useDebounce } from '@TutorShared/hooks/useDebounce';
import { useCategoryListQuery, type CategoryWithChildren } from '@TutorShared/services/category';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { decodeHtmlEntities, generateTree } from '@TutorShared/utils/util';

import FormFieldWrapper from './FormFieldWrapper';

interface FormMultiLevelSelectProps extends FormControllerProps<number | null> {
  label?: string;
  disabled?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
  isInlineLabel?: boolean;
  clearable?: boolean;
  listItemsLabel?: string;
  optionsWrapperStyle?: SerializedStyles;
}

const FormMultiLevelSelect = ({
  label,
  field,
  fieldState,
  disabled,
  loading,
  placeholder,
  helpText,
  isInlineLabel,
  clearable,
  listItemsLabel,
  optionsWrapperStyle,
}: FormMultiLevelSelectProps) => {
  const triggerRef = useRef<HTMLDivElement>(null);
  const [isOpen, setIsOpen] = useState(false);
  const [searchValue, setSearchValue] = useState('');
  const debouncedSearchValue = useDebounce(searchValue, 300);
  const categoryListQuery = useCategoryListQuery(debouncedSearchValue);
  const options = generateTree(categoryListQuery.data ?? []);

  useEffect(() => {
    if (!isOpen) {
      setSearchValue('');
    }
  }, [isOpen]);

  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
      disabled={disabled || options.length === 0}
      loading={loading}
      placeholder={placeholder}
      helpText={helpText}
      isInlineLabel={isInlineLabel}
    >
      {(inputProps) => {
        return (
          <>
            <div css={styles.inputWrapper} ref={triggerRef}>
              <input
                {...inputProps}
                type="text"
                onClick={(event) => {
                  event.stopPropagation();
                  setIsOpen(true);
                }}
                onKeyDown={(event) => {
                  if (event.key === 'Enter') {
                    event.preventDefault();
                    setIsOpen(true);
                  }

                  if (event.key === 'Tab') {
                    setIsOpen(false);
                  }
                }}
                autoComplete="off"
                readOnly
                disabled={disabled || options.length === 0}
                value={field.value ? categoryListQuery.data?.find((option) => option.id === field.value)?.name : ''}
                placeholder={placeholder}
              />
              <button
                tabIndex={-1}
                type="button"
                disabled={disabled || options.length === 0}
                aria-label={__('Toggle options', __TUTOR_TEXT_DOMAIN__)}
                css={styles.toggleIcon(isOpen)}
                onClick={() => {
                  setIsOpen((prev) => !prev);
                }}
              >
                <SVGIcon name="chevronDown" width={20} height={20} />
              </button>
            </div>

            <Popover
              triggerRef={triggerRef}
              isOpen={isOpen}
              closePopover={() => setIsOpen(false)}
              dependencies={[options.length]}
              animationType={AnimationType.slideDown}
            >
              <div css={styles.categoryWrapper}>
                {!!listItemsLabel && <p css={styles.listItemLabel}>{listItemsLabel}</p>}
                <div css={styles.searchInput}>
                  <div css={styles.searchIcon}>
                    <SVGIcon name="search" width={24} height={24} />
                  </div>
                  <input
                    type="text"
                    placeholder={__('Search', __TUTOR_TEXT_DOMAIN__)}
                    value={searchValue}
                    onChange={(event) => {
                      setSearchValue(event.target.value);
                    }}
                  />
                </div>
                <Show
                  when={options.length > 0}
                  fallback={<div css={styles.notFound}>{__('No categories found.', __TUTOR_TEXT_DOMAIN__)}</div>}
                >
                  <div css={[styles.options, optionsWrapperStyle]}>
                    {options.map((option) => (
                      <Branch
                        key={option.id}
                        option={option}
                        onChange={(id) => {
                          field.onChange(id);
                          setIsOpen(false);
                        }}
                      />
                    ))}
                  </div>
                </Show>

                {clearable && (
                  <div css={styles.clearButton}>
                    <Button
                      variant="text"
                      onClick={() => {
                        field.onChange(null);
                        setIsOpen(false);
                      }}
                    >
                      {__('Clear selection', __TUTOR_TEXT_DOMAIN__)}
                    </Button>
                  </div>
                )}
              </div>
            </Popover>
          </>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormMultiLevelSelect;

interface BranchProps {
  option: CategoryWithChildren;
  onChange: (item: number) => void;
  level?: number; // Add level prop
}

export const Branch = ({ option, onChange, level = 0 }: BranchProps) => {
  const hasChildren = option.children.length > 0;

  const renderBranches = () => {
    if (!hasChildren) {
      return null;
    }

    return option.children.map((child) => {
      return <Branch key={child.id} option={child} onChange={onChange} level={level + 1} />;
    });
  };

  return (
    <div css={styles.branchItem(level)}>
      <button type="button" onClick={() => onChange(option.id)} title={option.name}>
        {decodeHtmlEntities(option.name)}
      </button>

      {renderBranches()}
    </div>
  );
};

const styles = {
  categoryWrapper: css`
    background-color: ${colorTokens.background.white};
    box-shadow: ${shadow.popover};
    border-radius: ${borderRadius[6]};
    border: 1px solid ${colorTokens.stroke.border};
    padding: ${spacing[8]} 0;
    min-width: 275px;
  `,
  options: css`
    max-height: 455px;
    ${styleUtils.overflowYAuto};
  `,
  notFound: css`
    ${styleUtils.display.flex()};
    align-items: center;
    ${typography.caption('regular')};
    padding: ${spacing[8]} ${spacing[16]};
    color: ${colorTokens.text.hints};
  `,
  searchInput: css`
    position: sticky;
    top: 0;
    padding: ${spacing[8]} ${spacing[16]};

    input {
      ${typography.body('regular')};
      width: 100%;
      border-radius: ${borderRadius[6]};
      border: 1px solid ${colorTokens.stroke.default};
      padding: ${spacing[4]} ${spacing[16]} ${spacing[4]} ${spacing[32]};
      color: ${colorTokens.text.title};
      appearance: textfield;

      :focus {
        ${styleUtils.inputFocus};
      }
    }
  `,
  searchIcon: css`
    position: absolute;
    left: ${spacing[24]};
    top: 50%;
    transform: translateY(-50%);
    color: ${colorTokens.icon.default};
    display: flex;
  `,
  branchItem: (level: number) => css`
    position: relative;
    z-index: ${zIndex.positive};

    button {
      ${styleUtils.resetButton};
      ${typography.body('regular')};
      ${styleUtils.text.ellipsis(1)};
      color: ${colorTokens.text.title};
      padding-left: calc(${spacing[24]} + ${spacing[24]} * ${level});
      line-height: ${lineHeight[36]};
      padding-right: ${spacing[16]};
      width: 100%;

      &:hover,
      &:focus,
      &:active {
        background-color: ${colorTokens.background.hover};
        color: ${colorTokens.text.title};
      }
    }
  `,
  toggleIcon: (isOpen: boolean) => css`
    ${styleUtils.resetButton};
    position: absolute;
    top: ${spacing[4]};
    right: ${spacing[4]};
    display: flex;
    align-items: center;
    transition: transform 0.3s ease-in-out;
    color: ${colorTokens.icon.default};
    padding: ${spacing[6]};

    &:focus,
    &:active,
    &:hover {
      background: none;
      color: ${colorTokens.icon.default};
    }

    ${isOpen &&
    css`
      transform: rotate(180deg);
    `}
  `,
  inputWrapper: css`
    position: relative;

    input:read-only {
      background-color: inherit;
    }
  `,
  clearButton: css`
    padding: ${spacing[8]} ${spacing[24]};
    box-shadow: ${shadow.dividerTop};

    & > button {
      padding: 0;
    }
  `,
  listItemLabel: css`
    ${typography.caption()};
    font-weight: ${fontWeight.medium};
    background-color: ${colorTokens.background.white};
    color: ${colorTokens.text.hints};
    padding: ${spacing[10]} ${spacing[16]};
  `,
  radioLabel: css`
    line-height: ${lineHeight[32]};
    padding-left: ${spacing[2]};
  `,
};
