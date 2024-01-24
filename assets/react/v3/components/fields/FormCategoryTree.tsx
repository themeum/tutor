import Checkbox from '@Atoms/CheckBox';
import { colorPalate, spacing, zIndex } from '@Config/styles';
import { css } from '@emotion/react';
import { CategoryWithChildren } from '@Services/category';
import { FormControllerProps } from '@Utils/form';
import produce from 'immer';

import FormFieldWrapper from './FormFieldWrapper';

interface FormCategoryTreeProps extends FormControllerProps<number[] | null> {
  options: CategoryWithChildren[];
  label?: string;
  value?: string;
  disabled?: boolean;
  isGiftCard?: boolean;
}

interface BranchProps {
  option: CategoryWithChildren;
  value: number[];
  onChange: (item: number) => void;
  isLastChild: boolean;
  isGiftCard?: boolean;
}

export const Branch = ({ option, value, onChange, isLastChild, isGiftCard = false }: BranchProps) => {
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
          isGiftCard={isGiftCard}
        />
      );
    });
  };

  return (
    <div css={styles.branchItem(option.level, hasVerticalBar, isLastChild)}>
      <Checkbox
        checked={value.includes(option.id)}
        label={option.name}
        onChange={() => {
          onChange(option.id);
        }}
        disabled={isGiftCard}
      />
      {renderBranches()}
    </div>
  );
};

const FormCategoryTree = ({ options, field, fieldState, disabled, isGiftCard = false }: FormCategoryTreeProps) => {
  const filteredOptions = isGiftCard ? options : options.filter((option) => !option.is_gift_card);

  return (
    <FormFieldWrapper field={field} fieldState={fieldState} disabled={disabled || isGiftCard}>
      {() => {
        return (
          <div css={styles.categoryWrapper}>
            {filteredOptions.map((option, index) => {
              return (
                <Branch
                  key={option.id}
                  option={option}
                  value={field.value || []}
                  isLastChild={index === filteredOptions.length - 1}
                  isGiftCard={isGiftCard}
                  onChange={(id) => {
                    field.onChange(
                      produce(field.value, (draft) => {
                        if (!draft) {
                          return [];
                        }

                        return draft.includes(id) ? draft.filter((item) => item !== id) : [...draft, id];
                      }),
                    );
                  }}
                />
              );
            })}
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormCategoryTree;

const styles = {
  categoryWrapper: css`
    position: relative;
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
        width: 12px;
        left: -${spacing[12]};
        top: ${spacing[16]};

        background-color: ${colorPalate.surface.neutral.hover};
        z-index: ${zIndex.level};
      }
    `}
  `,
};
