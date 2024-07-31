import Button from '@Atoms/Button';
import Checkbox from '@Atoms/CheckBox';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@Config/styles';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import { type CategoryWithChildren, useCategoryListQuery, useCreateCategoryMutation } from '@Services/category';
import type { FormControllerProps } from '@Utils/form';
import { generateTree, getCategoryLeftBarHeight } from '@Utils/util';
import { type SerializedStyles, css } from '@emotion/react';
import { produce } from 'immer';
import { useState } from 'react';

import LoadingSpinner from '@Atoms/LoadingSpinner';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { useIsScrolling } from '@Hooks/useIsScrolling';
import { styleUtils } from '@Utils/style-utils';
import { __ } from '@wordpress/i18n';
import { Controller, type FieldValues } from 'react-hook-form';
import FormFieldWrapper from './FormFieldWrapper';
import FormInput from './FormInput';
import FormMultiLevelSelect from './FormMultiLevelSelect';

interface FormMultiLevelInputProps extends FormControllerProps<number[]> {
  label?: string;
  disabled?: boolean;
  loading?: boolean;
  placeholder?: string;
  helpText?: string;
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
  optionsWrapperStyle,
}: FormMultiLevelInputProps) => {
  const categoryListQuery = useCategoryListQuery();
  const createCategoryMutation = useCreateCategoryMutation();
  const [isOpen, setIsOpen] = useState(false);
  const { ref: scrollElementRef, isScrolling } = useIsScrolling<HTMLDivElement>();

  const form = useFormWithGlobalError<{
    name: string;
    parent: number | null;
  }>({
    shouldFocusError: true,
  });

  const { triggerRef, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
  });

  if (categoryListQuery.isLoading) {
    return <LoadingSpinner />;
  }

  const treeOptions = generateTree(categoryListQuery.data ?? []);

  const handleCreateCategory = (data: FieldValues) => {
    if (data.name) {
      createCategoryMutation.mutate({
        name: data.name,
        parent: data.parent,
      });

      form.reset();
      setIsOpen(false);
    }
  };

  return (
    <FormFieldWrapper
      label={label}
      field={field}
      fieldState={fieldState}
      disabled={disabled}
      loading={loading}
      placeholder={placeholder}
      helpText={helpText}
    >
      {() => {
        return (
          <>
            <div css={[styles.options, optionsWrapperStyle]}>
              <div css={styles.categoryListWrapper} ref={scrollElementRef}>
                {treeOptions.map((option, index) => (
                  <Branch
                    key={option.id}
                    option={option}
                    value={field.value}
                    isLastChild={index === treeOptions.length - 1}
                    onChange={(id) => {
                      field.onChange(
                        produce(field.value, (draft) => {
                          if (Array.isArray(draft)) {
                            return draft.includes(id) ? draft.filter((item) => item !== id) : [...draft, id];
                          }
                          return [id];
                        }),
                      );
                    }}
                  />
                ))}
              </div>

              <div ref={triggerRef} css={styles.addButtonWrapper({ isActive: isScrolling })}>
                <button type="button" css={styles.addNewButton} onClick={() => setIsOpen(true)}>
                  <SVGIcon width={24} height={24} name="plus" /> {__('Add', 'tutor')}
                </button>
              </div>
            </div>

            <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)}>
              <div css={[styles.categoryFormWrapper, { left: position.left, top: position.top }]} ref={popoverRef}>
                <Controller
                  name="name"
                  control={form.control}
                  rules={{
                    required: __('Category name is required', 'tutor'),
                  }}
                  render={(controllerProps) => (
                    <FormInput {...controllerProps} placeholder={__('Category name', 'tutor')} selectOnFocus />
                  )}
                />
                <Controller
                  name="parent"
                  control={form.control}
                  rules={{
                    required: __('Parent category is required', 'tutor'),
                  }}
                  render={(controllerProps) => (
                    <FormMultiLevelSelect
                      {...controllerProps}
                      placeholder={__('Select parent', 'tutor')}
                      options={categoryListQuery.data ?? []}
                      clearable
                    />
                  )}
                />

                <div css={styles.categoryFormButtons}>
                  <Button
                    variant="text"
                    onClick={() => {
                      setIsOpen(false);
                      form.reset();
                    }}
                  >
                    {__('Cancel', 'tutor')}
                  </Button>
                  <Button
                    variant="secondary"
                    loading={createCategoryMutation.isPending}
                    onClick={form.handleSubmit(handleCreateCategory)}
                  >
                    {__('Ok', 'tutor')}
                  </Button>
                </div>
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
}

const getTotalNestedChildrenCount = (option: CategoryWithChildren): number => {
  return option.children.reduce((total, child) => total + getTotalNestedChildrenCount(child), option.children.length);
};

export const Branch = ({ option, value, onChange, isLastChild }: BranchProps) => {
  const totalChildren = getTotalNestedChildrenCount(option);
  const hasChildren = totalChildren > 0;

  const leftBarHeight = getCategoryLeftBarHeight(isLastChild, totalChildren);

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
        />
      );
    });
  };

  return (
    <div css={styles.branchItem({ leftBarHeight, hasParent: option.parent !== 0 })}>
      <Checkbox
        checked={Array.isArray(value) ? value.includes(option.id) : value === option.id}
        label={option.name}
        onChange={() => {
          onChange(option.id);
        }}
        labelCss={styles.checkboxLabel}
      />

      {renderBranches()}
    </div>
  );
};

const styles = {
  options: css`
    border: 1px solid ${colorTokens.stroke.disable};
    border-radius: ${borderRadius[8]};
    padding: ${spacing[8]} 0;
    background-color: ${colorTokens.bg.white};
  `,
  categoryListWrapper: css`
    max-height: 208px;
    overflow: auto;
  `,
  checkboxLabel: css`
    line-height: 1.88rem !important;
  `,
  branchItem: ({ leftBarHeight, hasParent }: { leftBarHeight: string; hasParent: boolean }) => css`
    line-height: ${spacing[32]};
    position: relative;
    z-index: ${zIndex.positive};
    margin-left: ${spacing[20]};

    &:after {
      content: '';
      position: absolute;
      height: ${leftBarHeight};
      width: 1px;
      left: 9px;
      top: 25px;
      background-color: ${colorTokens.stroke.divider};
      z-index: ${zIndex.level};
    }

    ${
      hasParent &&
      css`
      &:before {
        content: '';
        position: absolute;
        height: 1px;
        width: 10px;
        left: -10px;
        top: ${spacing[16]};

        background-color: ${colorTokens.stroke.divider};
        z-index: ${zIndex.level};
      }
    `
    }
  `,
  addNewButton: css`
    ${styleUtils.resetButton};
    color: ${colorTokens.brand.blue};
    padding: ${spacing[4]} ${spacing[16]};
    display: flex;
    align-items: center;
  `,
  categoryFormWrapper: css`
    position: absolute;
    background-color: ${colorTokens.background.white};
    box-shadow: ${shadow.popover};
    border-radius: ${borderRadius[6]};
    padding: ${spacing[16]};
    min-width: 306px;

    display: flex;
    flex-direction: column;
    gap: ${spacing[12]};
  `,
  categoryFormButtons: css`
    display: flex;
    justify-content: end;
    gap: ${spacing[8]};
  `,
  addButtonWrapper: ({ isActive = false }) => css`
    transition: box-shadow 0.3s ease-in-out;
    ${
      isActive &&
      css`
      box-shadow: ${shadow.scrollable};
    `
    }
  `,
};
