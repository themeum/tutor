import Radio from '@Atoms/Radio';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorPalate, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { isDefined, Option } from '@Utils/types';
import { noop } from '@Utils/util';
import { ReactNode, useState } from 'react';

import FormFieldWrapper from './FormFieldWrapper';

type StatusValue = 'public' | 'lock' | 'drip' | 'pending' | 'draft' | 'published' | 'trashed' | 'active' | 'inactive';
export interface StatusOption {
  label: string;
  value: StatusValue;
}
interface StatusVariant {
  backgroundColor: string;
  borderColor: string;
  color: string;
  leftIconColor: string;
  rightIconColor: string;
  leftIcon: ReactNode;
}

interface FormStatusSelectInputProps extends FormControllerProps<StatusValue | undefined> {
  label?: string;
  options: Option<StatusValue>[];
  placeholder?: string;
  onChange?: (selectedOption: Option<StatusValue>) => void;
  disabled?: boolean;
  loading?: boolean;
  disabledFieldOnChange?: boolean;
}

const FormStatusSelectInput = ({
  options,
  field,
  fieldState,
  onChange = noop,
  label,
  disabled,
  loading,
  disabledFieldOnChange = false,
}: FormStatusSelectInputProps) => {
  const [isOpen, setIsOpen] = useState(false);

  const { triggerRef, triggerWidth, position, popoverRef } = usePortalPopover<HTMLButtonElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  const selectedItem = options.find((option) => option.value === field.value);
  const status = field.value;
  if (!isDefined(status) || (isDefined(status) && !(status in statusVariants))) {
    return null;
  }

  return (
    <FormFieldWrapper
      fieldState={fieldState}
      field={field}
      label={label}
      disabled={disabled || options.length === 0}
      loading={loading}
    >
      {() => {
        const statusVariant = statusVariants[status];

        return (
          <div css={styles.mainWrapper}>
            <button
              type="button"
              css={styles.dropDownLabel(statusVariant, isOpen)}
              onClick={() => setIsOpen(true)}
              ref={triggerRef}
            >
              <div>
                {statusVariant.leftIcon}
                <span>{selectedItem?.label || ''}</span>
              </div>
              <SVGIcon name="arrowDown" width={12} height={8} />
            </button>
            <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)}>
              <div
                css={[styles.optionsWrapper, { left: position.left, top: position.top, maxWidth: triggerWidth }]}
                ref={popoverRef}
              >
                <ul css={styles.options}>
                  {options.map((option) => (
                    <li
                      key={option.value}
                      css={styles.optionItem({
                        isSelected: field.value === option.value,
                      })}
                    >
                      <Radio
                        label={option.label}
                        checked={field.value === option.value}
                        onChange={() => {
                          if (!disabledFieldOnChange) {
                            field.onChange(option.value);
                          }

                          setIsOpen(false);
                          onChange(option);
                        }}
                        labelCss={styles.radioLabel}
                      />
                    </li>
                  ))}
                </ul>
              </div>
            </Portal>
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormStatusSelectInput;

const styles = {
  mainWrapper: css`
    width: 100%;
  `,
  dropDownLabel: (statusVariant: StatusVariant, isOpen: boolean) => css`
    ${styleUtils.resetButton}
    width: 124px;

    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 42px;
    height: 24px;
    padding: 0 ${spacing[8]} 0 ${spacing[6]};

    border: 1px solid ${statusVariant.borderColor};
    background-color: ${statusVariant.backgroundColor};

    position: relative;

    div {
      display: flex;
      gap: ${spacing[4]};
      align-items: center;

      span {
        color: ${statusVariant.color};
      }

      svg {
        height: 16px;
        width: 16px;
        color: ${statusVariant.leftIconColor};
        transform: none;
      }
    }

    & svg {
      width: 12px;
      height: 8px;
      color: ${statusVariant.rightIconColor};
      transition: transform 0.3s ease-in-out;

      ${isOpen &&
      css`
        transform: rotate(180deg);
      `}
    }
  `,
  optionsWrapper: css`
    position: absolute;
    min-width: 170px;
    width: 100%;
  `,
  options: css`
    z-index: ${zIndex.dropdown};
    background-color: ${colorPalate.basic.white};
    list-style-type: none;
    box-shadow: ${shadow.popover};
    padding: ${spacing[4]} 0;
    margin: 0;
    max-height: 500px;
    overflow-y: auto;
    border-radius: ${borderRadius[6]};

    ::-webkit-scrollbar {
      background-color: ${colorPalate.basic.white};
      width: 10px;
    }

    ::-webkit-scrollbar-thumb {
      background-color: ${colorPalate.basic.secondary};
      border-radius: ${borderRadius[6]};
    }
  `,
  optionItem: ({ isSelected }: { isSelected: boolean }) => css`
    ${typography.body()};
    min-height: 40px;
    transition: background-color 0.3s ease-in-out;

    :hover {
      background-color: ${colorPalate.surface.hover};
    }

    ${isSelected &&
    css`
      background-color: ${colorPalate.surface.selected.default};
    `}
  `,
  radioLabel: css`
    line-height: 40px;
    padding-left: ${spacing[12]};
  `,
};

const statusVariants: Record<StatusValue, StatusVariant> = {
  public: {
    backgroundColor: colorPalate.surface.success.subDuedPressed,
    borderColor: colorPalate.border.success.neutral,
    color: colorPalate.text.success,
    leftIconColor: colorPalate.text.success,
    rightIconColor: colorPalate.text.success,
    leftIcon: <SVGIcon name="eye" width={16} height={16} />,
  },
  lock: {
    backgroundColor: colorPalate.basic.danger.fill40,
    borderColor: colorPalate.basic.danger.fill50,
    color: colorPalate.basic.danger.fill100,
    leftIconColor: colorPalate.basic.danger.fill100,
    rightIconColor: colorPalate.icon.critical,
    leftIcon: <SVGIcon name="lock" width={16} height={16} />,
  },
  drip: {
    backgroundColor: colorPalate.surface.selected.default,
    borderColor: colorPalate.basic.primary.fill60,
    color: colorPalate.basic.primary.fill100,
    leftIconColor: colorPalate.basic.primary.fill100,
    rightIconColor: colorPalate.icon.default,
    leftIcon: <SVGIcon name="drop" width={16} height={16} />,
  },
  pending: {
    backgroundColor: colorPalate.surface.warning.neutral,
    borderColor: colorPalate.basic.warning.fill50,
    color: colorPalate.text.warning,
    leftIconColor: colorPalate.icon.warning,
    rightIconColor: colorPalate.icon.warning,
    leftIcon: <SVGIcon name="info" width={16} height={16} />,
  },
  draft: {
    backgroundColor: colorPalate.surface.neutral.default,
    borderColor: colorPalate.border.neutral,
    color: colorPalate.text.default,
    leftIconColor: colorPalate.basic.black.fill70,
    rightIconColor: colorPalate.icon.default,
    leftIcon: <SVGIcon name="marksTotal" width={16} height={16} />,
  },
  inactive: {
    backgroundColor: colorPalate.surface.neutral.default,
    borderColor: colorPalate.border.neutral,
    color: colorPalate.text.default,
    leftIconColor: colorPalate.icon.neutral,
    rightIconColor: colorPalate.icon.default,
    leftIcon: <SVGIcon name="pauseCircle" width={16} height={16} />,
  },
  published: {
    backgroundColor: colorPalate.surface.success.neutral,
    borderColor: colorPalate.border.success.neutral,
    color: colorPalate.basic.success.fill100,
    leftIconColor: colorPalate.basic.success.fill100,
    rightIconColor: colorPalate.icon.success,
    leftIcon: <SVGIcon name="markCircle" width={16} height={16} />,
  },
  active: {
    backgroundColor: colorPalate.surface.success.neutral,
    borderColor: colorPalate.border.success.neutral,
    color: colorPalate.basic.success.fill100,
    leftIconColor: colorPalate.basic.success.fill100,
    rightIconColor: colorPalate.icon.success,
    leftIcon: <SVGIcon name="markCircle" width={16} height={16} />,
  },
  trashed: {
    backgroundColor: colorPalate.basic.danger.fill30,
    borderColor: colorPalate.border.critical.disabled,
    color: colorPalate.basic.danger.fill80,
    leftIconColor: colorPalate.icon.critical,
    rightIconColor: colorPalate.icon.critical,
    leftIcon: <SVGIcon name="crossCircle" width={16} height={16} />,
  },
};
