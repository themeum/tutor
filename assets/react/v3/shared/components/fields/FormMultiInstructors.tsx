import { ReactNode, useState } from 'react';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, lineHeight, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { css } from '@emotion/react';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';

import FormFieldWrapper from './FormFieldWrapper';
import { noop } from '@Utils/util';
import { __ } from '@wordpress/i18n';
import { Instructor, useInstructorListQuery } from '@Services/instructors';
import { useDebounce } from '@Hooks/useDebounce';

type FormMultiInstructorsProps<Instructor> = {
  label?: string;
  placeholder?: string;
  onChange?: (selectedOption: Instructor[]) => void;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  isSearchable?: boolean;
  isHidden?: boolean;
  responsive?: boolean;
  helpText?: string;
  leftIcon?: ReactNode;
} & FormControllerProps<Instructor[]>;

const FormMultiInstructors = ({
  field,
  fieldState,
  onChange = noop,
  label,
  placeholder = '',
  disabled,
  readOnly,
  loading,
  isSearchable = false,
  helpText,
  leftIcon,
}: FormMultiInstructorsProps<Instructor>) => {
  const inputValue = field.value ?? [];

  const [isOpen, setIsOpen] = useState(false);
  const [searchText, setSearchText] = useState('');
  const debouncedSearchText = useDebounce(searchText);

  const instructorListQuery = useInstructorListQuery(
    debouncedSearchText,
    inputValue.map((item) => item.id)
  );

  const options =
    instructorListQuery.data?.map((item) => {
      return {
        id: item.id,
        name: item.name,
        email: item.email,
        avatar_urls: item.avatar_urls,
      };
    }) ?? [];

  const { triggerRef, triggerWidth, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
  });

  const handleDeleteSelection = (id: number) => {
    const updatedValue = inputValue.filter((item) => item.id !== id);

    field.onChange(updatedValue);
    onChange(updatedValue);
  };

  return (
    <FormFieldWrapper
      fieldState={fieldState}
      field={field}
      label={label}
      disabled={disabled}
      readOnly={readOnly}
      loading={loading}
      helpText={helpText}
    >
      {(inputProps) => {
        const { css: inputCss, ...restInputProps } = inputProps;

        return (
          <div css={styles.mainWrapper}>
            <div css={styles.inputWrapper} ref={triggerRef}>
              <div css={styles.leftIcon}>{leftIcon}</div>
              <input
                {...restInputProps}
                onClick={() => setIsOpen((previousState) => !previousState)}
                css={[inputCss, styles.input(!!leftIcon)]}
                autoComplete="off"
                readOnly={readOnly || !isSearchable}
                placeholder={placeholder}
                value={searchText}
                onChange={(event) => {
                  setSearchText(event.target.value);
                }}
              />
            </div>

            <div css={styles.instructorList}>
              {inputValue?.map((instructor) => (
                <div key={instructor.id} css={styles.instructorItem}>
                  <div css={styles.instructorInfo}>
                    <img src={instructor.avatar_urls[48]} alt={instructor.name} css={styles.instructorAvatar} />
                    <div>
                      <div css={styles.instructorName}>{instructor.name}</div>
                      <div css={styles.instructorEmail}>{instructor.email}</div>
                    </div>
                  </div>

                  <button
                    onClick={() => handleDeleteSelection(instructor.id)}
                    css={styles.instructorDeleteButton}
                    data-instructor-delete-button
                  >
                    <SVGIcon name="cross" width={32} height={32} />
                  </button>
                </div>
              ))}
            </div>

            <Portal isOpen={isOpen} onClickOutside={() => setIsOpen(false)}>
              <div
                css={[
                  styles.optionsWrapper,
                  {
                    left: position.left,
                    top: position.top,
                    maxWidth: triggerWidth,
                  },
                ]}
                ref={popoverRef}
              >
                <ul css={[styles.options]}>
                  {options.map((instructor) => (
                    <li key={String(instructor.id)} css={styles.optionItem}>
                      <button
                        css={styles.label}
                        onClick={() => {
                          field.onChange([...inputValue, instructor]);
                          setSearchText('');
                          onChange([...inputValue, instructor]);
                          setIsOpen(false);
                        }}
                      >
                        <img src={instructor.avatar_urls[48]} alt={instructor.name} css={styles.instructorAvatar} />
                        <div>
                          <div css={styles.instructorName}>{instructor.name}</div>
                          <div css={styles.instructorEmail}>{instructor.email}</div>
                        </div>
                      </button>
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

export default FormMultiInstructors;

const styles = {
  mainWrapper: css`
    width: 100%;
  `,
  inputWrapper: css`
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
  `,
  leftIcon: css`
    position: absolute;
    left: ${spacing[8]};
    top: ${spacing[8]};
    color: ${colorTokens.icon.default};
    display: flex;
  `,
  input: (hasLeftIcon: boolean) => css`
    ${typography.body()};
    width: 100%;
    padding-right: ${spacing[32]};
    ${styleUtils.textEllipsis};

    ${hasLeftIcon &&
    css`
      padding-left: ${spacing[36]};
    `}

    :focus {
      outline: none;
      box-shadow: ${shadow.focus};
    }
  `,
  instructorList: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
    margin-top: ${spacing[8]};
  `,
  instructorItem: css`
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: ${spacing[8]} ${spacing[16]} ${spacing[8]} ${spacing[12]};
    border: 1px solid transparent;

    &:hover {
      border-color: ${colorTokens.stroke.divider};
      border-radius: ${borderRadius[4]};

      [data-instructor-delete-button] {
        display: block;
      }
    }
  `,
  instructorInfo: css`
    display: flex;
    align-items: center;
    gap: ${spacing[10]};
  `,
  instructorAvatar: css`
    height: 40px;
    width: 40px;
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius.circle};
  `,
  instructorName: css`
    ${typography.caption()};
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
  `,
  instructorEmail: css`
    ${typography.small()};
    color: ${colorTokens.text.subdued};
  `,
  optionsWrapper: css`
    position: absolute;
    width: 100%;
  `,
  instructorDeleteButton: css`
    ${styleUtils.resetButton};
    display: flex;
    height: 32px;
    width: 32px;
    color: ${colorTokens.icon.default};
    border-radius: ${borderRadius[2]};
    display: none;

    &:focus {
      box-shadow: ${shadow.focus};
    }
  `,
  options: css`
    z-index: ${zIndex.dropdown};
    background-color: ${colorTokens.background.white};
    list-style-type: none;
    box-shadow: ${shadow.popover};
    padding: ${spacing[4]} 0;
    margin: 0;
    max-height: 400px;
    border-radius: ${borderRadius[6]};
    ${styleUtils.overflowYAuto};
    min-width: 200px;
  `,
  optionItem: css`
    ${typography.body()};
    min-height: 36px;
    height: 100%;
    width: 100%;
    display: flex;
    align-items: center;
    transition: background-color 0.3s ease-in-out;
    cursor: pointer;

    &:hover {
      background-color: ${colorTokens.background.hover};
    }
  `,
  label: css`
    ${styleUtils.resetButton};
    width: 100%;
    height: 100%;
    display: flex;
    gap: ${spacing[8]};
    padding: ${spacing[8]} ${spacing[12]};
    text-align: left;
    line-height: ${lineHeight[24]};
    word-break: break-all;
    cursor: pointer;
  `,
  optionsContainer: css`
    position: absolute;
    overflow: hidden auto;
    min-width: 16px;
    min-height: 16px;
    max-width: calc(100% - 32px);
    max-height: calc(100% - 32px);
  `,
};
