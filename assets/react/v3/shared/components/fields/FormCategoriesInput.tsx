import React, { useState } from 'react';
import Button from '@Atoms/Button';
import Checkbox from '@Atoms/CheckBox';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@Config/styles';
import { css, SerializedStyles } from '@emotion/react';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import { CategoryWithChildren, useCategoryListQuery, useCreateTagMutation } from '@Services/category';
import { FormControllerProps } from '@Utils/form';
import { generateTree } from '@Utils/util';
import { produce } from 'immer';

import FormFieldWrapper from './FormFieldWrapper';
import { __ } from '@wordpress/i18n';
import { Controller, FieldValues } from 'react-hook-form';
import FormInput from './FormInput';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import FormMultiLevelSelect from './FormMultiLevelSelect';
import { styleUtils } from '@Utils/style-utils';
import LoadingSpinner from '@Atoms/LoadingSpinner';

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
  const createTagMutation = useCreateTagMutation();
  const [isOpen, setIsOpen] = useState(false);

  const form = useFormWithGlobalError();

  const { triggerRef, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
  });

  if (categoryListQuery.isLoading) {
    return <LoadingSpinner />;
  }

  const treeOptions = generateTree(categoryListQuery.data ?? []);

  const handleCreateCategory = async (data: FieldValues) => {
    if (data.name) {
      const response = await createTagMutation.mutateAsync({
        name: data.name,
        parent: data.parent,
      });

      form.reset();
    }

    setIsOpen(false);
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
                      })
                    );
                  }}
                />
              ))}

              <div ref={triggerRef}>
                <button css={styles.addNewButton} onClick={() => setIsOpen(true)}>
                  <SVGIcon width={24} height={24} name="plus" /> {__('Add new', 'tutor')}
                </button>
              </div>
            </div>

            <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)}>
              <div css={[styles.categoryFormWrapper, { left: position.left, top: position.top }]} ref={popoverRef}>
                <Controller
                  name="name"
                  control={form.control}
                  render={(controllerProps) => <FormInput {...controllerProps} placeholder="Category name" />}
                />
                <Controller
                  name="parent"
                  control={form.control}
                  render={(controllerProps) => (
                    <FormMultiLevelSelect
                      {...controllerProps}
                      placeholder="Select parent"
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
                  <Button variant="secondary" onClick={form.handleSubmit(handleCreateCategory)}>
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

export const Branch = ({ option, value, onChange, isLastChild }: BranchProps) => {
  const hasChildren = option.children.length > 0;

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
    <div css={styles.branchItem({ isLastChild, hasChildren, hasParent: option.parent !== 0 })}>
      <Checkbox
        checked={Array.isArray(value) ? value.includes(option.id) : value === option.id}
        label={option.name}
        onChange={() => {
          onChange(option.id);
        }}
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
  `,
  branchItem: ({ isLastChild, hasChildren, hasParent }: { isLastChild: boolean; hasChildren: boolean; hasParent: boolean }) => css`
    line-height: ${spacing[32]};
    position: relative;
    z-index: ${zIndex.positive};
    margin-left: ${spacing[20]};

    ${!isLastChild &&
    css`
      &:after {
        content: '';
        position: absolute;
        height: 100%;
        width: 1px;
        left: 9px;
        top: 25px;
        background-color: ${colorTokens.stroke.default};
        z-index: ${zIndex.level};
      }
    `}

    ${isLastChild && hasChildren &&
    css`
      &:after {
        content: '';
        position: absolute;
        height: calc(100% - 40px);
        width: 1px;
        left: 9px;
        top: 25px;
        background-color: ${colorTokens.stroke.default};
        z-index: ${zIndex.level};
      }
    `}

    ${hasParent &&
    css`
      &:before {
        content: '';
        position: absolute;
        height: 1px;
        width: 10px;
        left: -10px;
        top: ${spacing[16]};

        background-color: ${colorTokens.stroke.default};
        z-index: ${zIndex.level};
      }
    `}
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
};
