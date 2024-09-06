import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, fontWeight, lineHeight, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import type { Category, CategoryWithChildren } from '@Services/category';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { generateTree } from '@Utils/util';
import { type SerializedStyles, css } from '@emotion/react';
import { useState } from 'react';

import { __ } from '@wordpress/i18n';
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
                onFocus={() => setIsOpen(true)}
                autoComplete="off"
                readOnly={true}
                value={Array.isArray(field.value) ? '' : options.find((item) => item.id === field.value)?.name ?? ''}
                placeholder={placeholder}
              />
              <button
                type="button"
                css={styleUtils.resetButton}
                onClick={() => {
                  setIsOpen((prev) => !prev);
                }}
              >
                <SVGIcon name="chevronDown" width={20} height={20} style={styles.toggleIcon(isOpen)} />
              </button>
            </div>

            <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)}>
              <div css={[styles.categoryWrapper, { left: position.left, top: position.top }]} ref={popoverRef}>
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
}

export const Branch = ({ option, onChange }: BranchProps) => {
  const hasChildren = option.children.length > 0;

  const renderBranches = () => {
    if (!hasChildren) {
      return null;
    }

    return option.children.map((child) => {
      return <Branch key={child.id} option={child} onChange={onChange} />;
    });
  };

  return (
    <div css={styles.branchItem}>
      <button type="button" onClick={() => onChange(option.id)}>
        {option.name}
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
    padding: ${spacing[8]} 0;
    min-width: 275px;
  `,
  options: css`
    max-height: 455px;
    overflow-y: auto;
  `,
  branchItem: css`
    position: relative;
    z-index: ${zIndex.positive};
    padding-left: ${spacing[24]};

    button {
      ${styleUtils.resetButton};
      line-height: ${lineHeight[36]};
      width: 100%;

      &:hover {
        color: ${colorTokens.text.brand};
      }
    }
  `,
  toggleIcon: (isOpen: boolean) => css`
    position: absolute;
    top: 12px;
    right: ${spacing[12]};
    transition: transform 0.3s ease-in-out;

    ${
      isOpen &&
      css`
      transform: rotate(180deg);
    `
    }
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
