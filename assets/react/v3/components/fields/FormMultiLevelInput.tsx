import Button, { ButtonVariant } from '@Atoms/Button';
import Checkbox from '@Atoms/CheckBox';
import Radio from '@Atoms/Radio';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorPalate, fontWeight, lineHeight, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { css, SerializedStyles } from '@emotion/react';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import { useTranslation } from '@Hooks/useTranslation';
import { CategoryNode, CategoryWithChildren } from '@Services/category';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { generateTree } from '@Utils/util';
import produce from 'immer';
import { useState } from 'react';

import FormFieldWrapper from './FormFieldWrapper';

interface FormMultiLevelInputProps extends FormControllerProps<number | number[]> {
  options: CategoryNode[];
  selectMultiple?: boolean;
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

const FormMultiLevelInput = ({
  label,
  field,
  fieldState,
  disabled,
  loading,
  placeholder,
  helpText,
  options,
  selectMultiple,
  isInlineLabel,
  clearable,
  listItemsLabel,
  optionsWrapperStyle,
}: FormMultiLevelInputProps) => {
  const t = useTranslation();
  const treeOptions = generateTree(options);
  const [isOpen, setIsOpen] = useState(false);

  const { triggerRef, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  const nonEmptyPlaceholder =
    Array.isArray(field.value) && field.value.length > 0
      ? t('COM_SPPAGEBUILDER_STORE_GLOBAL_MULTI_SELECT_NON_EMPTY_PLACEHOLDER', { count: field.value.length })
      : '';

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
                type="text"
                {...inputProps}
                onFocus={() => setIsOpen(true)}
                autoComplete="off"
                readOnly={true}
                value={Array.isArray(field.value) ? '' : options.find((item) => item.id === field.value)?.name ?? ''}
                placeholder={nonEmptyPlaceholder ? nonEmptyPlaceholder : placeholder}
              />
              <button
                type="button"
                css={styleUtils.resetButton}
                onClick={() => {
                  setIsOpen((prev) => !prev);
                }}
              >
                <SVGIcon name="arrowDown" width={12} height={8} style={styles.toggleIcon(isOpen)} />
              </button>
            </div>

            <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)}>
              <div css={[styles.categoryWrapper, { left: position.left, top: position.top }]} ref={popoverRef}>
                {!!listItemsLabel && <p css={styles.listItemLabel}>{listItemsLabel}</p>}
                <div css={[styles.options, optionsWrapperStyle]}>
                  {treeOptions.map((option, index) => (
                    <Branch
                      key={option.id}
                      option={option}
                      value={field.value}
                      isLastChild={index === treeOptions.length - 1}
                      selectMultiple={selectMultiple}
                      fieldName={field.name}
                      onChange={(id) => {
                        field.onChange(
                          produce(field.value, (draft) => {
                            if (selectMultiple && Array.isArray(draft)) {
                              return draft.includes(id) ? draft.filter((item) => item !== id) : [...draft, id];
                            }
                            return id;
                          }),
                        );
                        if (!selectMultiple) {
                          setIsOpen(false);
                        }
                      }}
                    />
                  ))}
                </div>

                {clearable && (
                  <div css={styles.clearButton}>
                    <Button
                      variant={ButtonVariant.plain}
                      disabled={false}
                      onClick={() => {
                        field.onChange([]);
                      }}
                    >
                      {t('COM_SPPAGEBUILDER_STORE_FORM_SELECT_CLEAR')}
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

export default FormMultiLevelInput;

interface BranchProps {
  option: CategoryWithChildren;
  value: number | number[];
  onChange: (item: number) => void;
  isLastChild: boolean;
  selectMultiple?: boolean;
  fieldName?: string;
}

export const Branch = ({ option, value, onChange, isLastChild, selectMultiple, fieldName }: BranchProps) => {
  const hasChildren = option.children.length > 0;
  const hasVerticalBar = option.level > 1 || (option.level === 1 && !isLastChild);

  const renderBranches = () => {
    if (!hasChildren) {
      return null;
    }

    return option.children.map((child, idx) => {
      return (
        <Branch
          key={child.id}
          option={child}
          value={value}
          onChange={onChange}
          isLastChild={idx === option.children.length - 1}
          selectMultiple={selectMultiple}
        />
      );
    });
  };

  return (
    <div css={styles.branchItem(option.level, hasVerticalBar, isLastChild)}>
      {selectMultiple ? (
        <Checkbox
          checked={Array.isArray(value) ? value.includes(option.id) : value === option.id}
          label={option.name}
          onChange={() => {
            onChange(option.id);
          }}
        />
      ) : (
        <Radio
          checked={value === option.id}
          label={option.name}
          labelCss={styles.radioLabel}
          name={fieldName}
          onChange={() => {
            onChange(option.id);
          }}
        />
      )}

      {renderBranches()}
    </div>
  );
};

const styles = {
  categoryWrapper: css`
    position: absolute;
    background-color: ${colorPalate.basic.white};
    box-shadow: ${shadow.popover};
    border-radius: ${borderRadius[6]};
    padding: ${spacing[8]} 0;
    min-width: 275px;
  `,
  options: css`
    padding: 0 ${spacing[24]};
    max-height: 455px;
    overflow-y: auto;
  `,
  branchItem: (level: number, hasVerticalBar: boolean, isLastChild: boolean) => css`
    line-height: ${spacing[32]};
    position: relative;
    z-index: ${zIndex.positive};

    ${hasVerticalBar &&
    css`
      &:after {
        content: '';
        position: absolute;
        height: 100%;
        width: 1px;
        left: 9px;
        top: ${spacing[24]};
        background-color: ${colorPalate.surface.neutral.hover};
        z-index: ${zIndex.level};

        ${isLastChild &&
        css`
          height: 24px;
        `}

        ${level > 1 &&
        css`
          left: -${spacing[12]};
          top: -${spacing[8]};
        `}
      }
    `}

    ${level > 1 &&
    css`
      margin-left: 21px;

      &:before {
        content: '';
        position: absolute;
        height: 1px;
        width: 14px;
        left: -${spacing[12]};
        top: ${spacing[16]};

        background-color: ${colorPalate.surface.neutral.hover};
        z-index: ${zIndex.level};
      }
    `}
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
    ${typography.body()};
    font-weight: ${fontWeight.medium};
    background-color: ${colorPalate.surface.selected.default};
    color: ${colorPalate.text.neutral};
    padding: ${spacing[10]} ${spacing[16]};
  `,
  radioLabel: css`
    line-height: ${lineHeight[32]};
    padding-left: ${spacing[2]};
  `,
};
