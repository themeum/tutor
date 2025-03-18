import { type SerializedStyles, css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';

import Button from '@TutorShared/atoms/Button';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import { isRTL } from '@TutorShared/config/constants';
import { borderRadius, colorTokens, fontWeight, lineHeight, shadow, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import { Portal, usePortalPopover } from '@TutorShared/hooks/usePortalPopover';
import type { Category, CategoryWithChildren } from '@TutorShared/services/category';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { decodeHtmlEntities, generateTree } from '@TutorShared/utils/util';

import FormFieldWrapper from './FormFieldWrapper';

interface FormMultiLevelSelectProps extends FormControllerProps<number | null> {
  options: Category[];
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
  options,
  isInlineLabel,
  clearable,
  listItemsLabel,
  optionsWrapperStyle,
}: FormMultiLevelSelectProps) => {
  const treeOptions = generateTree(options);
  const [isOpen, setIsOpen] = useState(false);

  const { triggerRef, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
      disabled={disabled}
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
                readOnly={true}
                value={Array.isArray(field.value) ? '' : (options.find((item) => item.id === field.value)?.name ?? '')}
                placeholder={placeholder}
              />
              <button
                tabIndex={-1}
                type="button"
                css={styles.toggleIcon(isOpen)}
                onClick={() => {
                  setIsOpen((prev) => !prev);
                }}
              >
                <SVGIcon name="chevronDown" width={20} height={20} />
              </button>
            </div>

            <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)} onEscape={() => setIsOpen(false)}>
              <div
                css={[styles.categoryWrapper, { [isRTL ? 'right' : 'left']: position.left, top: position.top }]}
                ref={popoverRef}
              >
                {!!listItemsLabel && <p css={styles.listItemLabel}>{listItemsLabel}</p>}
                <div css={[styles.options, optionsWrapperStyle]}>
                  {treeOptions.map((option) => (
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

                {clearable && (
                  <div css={styles.clearButton}>
                    <Button
                      variant="text"
                      onClick={() => {
                        field.onChange(null);
                        setIsOpen(false);
                      }}
                    >
                      {__('Clear selection', 'tutor')}
                    </Button>
                  </div>
                )}
              </div>
            </Portal>
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
      <button type="button" onClick={() => onChange(option.id)}>
        {decodeHtmlEntities(option.name)}
      </button>

      {renderBranches()}
    </div>
  );
};

const styles = {
  categoryWrapper: css`
    position: absolute;
    background-color: ${colorTokens.background.white};
    box-shadow: ${shadow.popover};
    border-radius: ${borderRadius[6]};
    border: 1px solid ${colorTokens.stroke.border};
    padding: ${spacing[8]} 0;
    min-width: 275px;
  `,
  options: css`
    max-height: 455px;
    overflow-y: auto;
  `,
  branchItem: (level: number) => css`
    position: relative;
    z-index: ${zIndex.positive};

    button {
      ${styleUtils.resetButton};
      ${typography.body('regular')};
      color: ${colorTokens.text.title};
      padding-left: calc(${spacing[24]} + ${spacing[24]} * ${level});
      line-height: ${lineHeight[36]};
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
  `,
  clearButton: css`
    padding: ${spacing[8]} ${spacing[24]};

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
