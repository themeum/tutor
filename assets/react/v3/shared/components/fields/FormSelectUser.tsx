import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';

import SVGIcon from '@Atoms/SVGIcon';

import { borderRadius, colorTokens, lineHeight, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';

import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';

import Show from '@Controls/Show';
import { useDebounce } from '@Hooks/useDebounce';
import { noop } from '@Utils/util';
import FormFieldWrapper from './FormFieldWrapper';

import { tutorConfig } from '@Config/config';
import { TutorRoles } from '@Config/constants';
import profileImage from '@Images/profile-photo.png';
import type { User } from '@Services/users';
export interface UserOption extends User {
  isRemoveAble?: boolean;
}

type FormSelectUserProps = {
  label?: string;
  placeholder?: string;
  options: UserOption[];
  onChange?: (selectedOption: UserOption | UserOption[]) => void;
  handleSearchOnChange?: (searchText: string) => void;
  isMultiSelect?: boolean;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  isSearchable?: boolean;
  isHidden?: boolean;
  responsive?: boolean;
  helpText?: string;
  emptyStateText?: string;
  isInstructorMode?: boolean;
} & FormControllerProps<UserOption | UserOption[] | null>;

const userPlaceholderData: UserOption = {
  id: 0,
  name: __('Click to select user', 'tutor'),
  email: 'example@example.com',
  avatar_url: 'https://gravatar.com/avatar',
};

const FormSelectUser = ({
  field,
  fieldState,
  options,
  onChange = noop,
  handleSearchOnChange,
  isMultiSelect = false,
  label,
  placeholder = '',
  disabled,
  readOnly,
  loading,
  isSearchable = false,
  helpText,
  emptyStateText = __('No user selected', 'tutor'),
  isInstructorMode = false,
}: FormSelectUserProps) => {
  const inputValue = field.value ?? (isMultiSelect ? [] : userPlaceholderData);
  const selectedIds = Array.isArray(inputValue) ? inputValue.map((item) => String(item.id)) : [String(inputValue.id)];
  const isCurrentUserAdmin = tutorConfig.current_user.roles.includes(TutorRoles.ADMINISTRATOR);

  const [isOpen, setIsOpen] = useState(false);

  const [searchText, setSearchText] = useState('');
  const debouncedSearchText = useDebounce(searchText);

  const filteredOption =
    options.filter((item) => {
      const matchesSearchText =
        item.name?.toLowerCase().includes(searchText.toLowerCase()) ||
        item.email?.toLowerCase().includes(searchText.toLowerCase());
      const isNotSelected = !selectedIds.includes(String(item.id));
      return matchesSearchText && isNotSelected;
    }) || [];

  useEffect(() => {
    if (handleSearchOnChange) {
      handleSearchOnChange(debouncedSearchText);
    } else {
      // Handle local filter
    }
  }, [debouncedSearchText, handleSearchOnChange]);

  const { triggerRef, triggerWidth, position, popoverRef } = usePortalPopover<HTMLDivElement, HTMLDivElement>({
    isOpen,
    isDropdown: true,
    dependencies: [filteredOption.length],
  });

  const handleDeleteSelection = (id: number) => {
    if (Array.isArray(inputValue)) {
      const updatedValue = inputValue.filter((item) => item.id !== id);

      field.onChange(updatedValue);
      onChange(updatedValue);
    }
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
            <div ref={triggerRef}>
              {!isMultiSelect && !Array.isArray(inputValue) && (
                <button
                  type="button"
                  css={styles.instructorItem({ isDefaultItem: true })}
                  onClick={() => setIsOpen((previousState) => !previousState)}
                  disabled={readOnly || disabled || filteredOption.length === 0}
                >
                  <div css={styles.instructorInfo}>
                    <img
                      src={inputValue.avatar_url ? inputValue.avatar_url : profileImage}
                      alt={inputValue.name}
                      css={styles.instructorAvatar}
                    />
                    <div>
                      <div css={styles.instructorName}>{inputValue.name}</div>
                      <div css={styles.instructorEmail}>{inputValue.email}</div>
                    </div>
                  </div>

                  <Show when={!loading}>
                    <div css={styles.toggleIcon({ isOpen })}>
                      <SVGIcon name="chevronDown" width={20} height={20} />
                    </div>
                  </Show>
                </button>
              )}

              {isMultiSelect && (
                <div css={styles.inputWrapper}>
                  <div css={styles.leftIcon}>
                    <SVGIcon name="search" width={24} height={24} />
                  </div>
                  <input
                    {...restInputProps}
                    onClick={() => setIsOpen((previousState) => !previousState)}
                    className="tutor-input-field"
                    css={[inputCss, styles.input]}
                    autoComplete="off"
                    readOnly={readOnly || !isSearchable}
                    placeholder={placeholder}
                    value={searchText}
                    onChange={(event) => {
                      setSearchText(event.target.value);
                    }}
                  />
                </div>
              )}
            </div>

            {isMultiSelect &&
              Array.isArray(inputValue) &&
              (inputValue.length > 0 ? (
                <div css={styles.instructorList}>
                  {inputValue.map((instructor) => (
                    <div key={instructor.id} css={styles.instructorItem({ isDefaultItem: false })}>
                      <div css={styles.instructorInfo}>
                        <img
                          src={instructor.avatar_url ? instructor.avatar_url : profileImage}
                          alt={instructor.name}
                          css={styles.instructorAvatar}
                        />
                        <div>
                          <div css={styles.instructorName}>{instructor.name}</div>
                          <div css={styles.instructorEmail}>{instructor.email}</div>
                        </div>
                      </div>

                      <Show
                        when={isInstructorMode}
                        fallback={
                          <button
                            type="button"
                            onClick={() => handleDeleteSelection(instructor.id)}
                            css={styles.instructorDeleteButton}
                            data-instructor-delete-button
                          >
                            <SVGIcon name="cross" width={32} height={32} />
                          </button>
                        }
                      >
                        <Show when={isCurrentUserAdmin || instructor.isRemoveAble}>
                          <button
                            type="button"
                            onClick={() => handleDeleteSelection(instructor.id)}
                            css={styles.instructorDeleteButton}
                            data-instructor-delete-button
                          >
                            <SVGIcon name="cross" width={32} height={32} />
                          </button>
                        </Show>
                      </Show>
                    </div>
                  ))}
                </div>
              ) : (
                <div css={styles.emptyState}>
                  <p>{emptyStateText}</p>
                </div>
              ))}
            <Portal
              isOpen={isOpen}
              onClickOutside={() => {
                setIsOpen(false);
                setSearchText('');
              }}
            >
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
                  {!isMultiSelect && (
                    <li css={styles.inputWrapperListItem}>
                      <div css={[styles.inputWrapper, styles.portalInputWrapper]}>
                        <div css={styles.leftIcon}>
                          <SVGIcon name="search" width={24} height={24} />
                        </div>
                        <input
                          {...restInputProps}
                          // biome-ignore lint/a11y/noAutofocus: <explanation>
                          autoFocus
                          className="tutor-input-field"
                          css={[inputCss, styles.input]}
                          autoComplete="off"
                          readOnly={readOnly || !isSearchable}
                          placeholder={placeholder}
                          value={searchText}
                          onChange={(event) => {
                            setSearchText(event.target.value);
                          }}
                        />
                      </div>
                    </li>
                  )}
                  <Show
                    when={filteredOption.length > 0}
                    fallback={
                      <li css={styles.noUserFound}>
                        <p>{__('No user found', 'tutor')}</p>
                      </li>
                    }
                  >
                    {filteredOption.map((instructor) => (
                      <li key={String(instructor.id)} css={styles.optionItem}>
                        <button
                          type="button"
                          css={styles.label}
                          onClick={() => {
                            const selectedValue = isInstructorMode
                              ? {
                                  ...instructor,
                                  isRemoveAble: true,
                                }
                              : instructor;
                            const newValue = Array.isArray(inputValue) ? [...inputValue, selectedValue] : selectedValue;
                            field.onChange(newValue);
                            setSearchText('');
                            onChange(newValue);
                            setIsOpen(false);
                          }}
                        >
                          <img
                            src={instructor.avatar_url ? instructor.avatar_url : profileImage}
                            alt={instructor.name}
                            css={styles.instructorAvatar}
                          />
                          <div>
                            <div css={styles.instructorName}>{instructor.name}</div>
                            <div css={styles.instructorEmail}>{instructor.email}</div>
                          </div>
                        </button>
                      </li>
                    ))}
                  </Show>
                </ul>
              </div>
            </Portal>
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default FormSelectUser;

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
  portalInputWrapper: css`
    padding: ${spacing[8]};
  `,
  inputWrapperListItem: css`
    padding: 0px;
  `,
  leftIcon: css`
    position: absolute;
    left: ${spacing[12]};
    top: 50%;
    transform: translateY(-50%);
    color: ${colorTokens.icon.default};
    display: flex;
  `,
  input: css`
    ${typography.body()};
    width: 100%;
    padding-right: ${spacing[32]};
    padding-left: ${spacing[36]};
    ${styleUtils.textEllipsis};
    border-color: transparent;

    :focus {
      outline: none;
      box-shadow: none;
    }

    &.tutor-input-field {
      padding-right: ${spacing[32]};
      padding-left: ${spacing[36]};
    }
  `,
  instructorList: css`
    display: flex;
    flex-direction: column;
    gap: ${spacing[8]};
    margin-top: ${spacing[8]};
  `,
  instructorItem: ({ isDefaultItem = false }) => css`
    ${styleUtils.resetButton};
    position: relative;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: ${spacing[8]} ${spacing[16]} ${spacing[8]} ${spacing[12]};
    border: 1px solid transparent;
    border-radius: ${borderRadius.input};
    background-color: ${colorTokens.bg.white};

    ${
      isDefaultItem &&
      css`
        border-color: ${colorTokens.stroke.default};
        cursor: pointer;
      `
    }

    &:hover {
      border-color: ${colorTokens.stroke.divider};

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
  toggleIcon: ({ isOpen = false }: { isOpen: boolean }) => css`
    position: absolute;
    top: 0;
    bottom: 0;
    right: ${spacing[8]};
    ${styleUtils.flexCenter()};
    color: ${colorTokens.icon.default};
    transition: transform 0.3s ease-in-out;

    ${
      isOpen &&
      css`
        transform: rotate(180deg);
      `
    }
  `,
  noUserFound: css`
    padding: ${spacing[8]};
    text-align: center;
  `,
  emptyState: css`
    ${styleUtils.flexCenter()};
    ${typography.caption()};
    margin-top: ${spacing[8]};
    padding: ${spacing[8]};
    background-color: ${colorTokens.background.white};
    border: 1px solid ${colorTokens.stroke.divider};
    border-radius: ${borderRadius[4]};
  `,
};
