import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';

import SVGIcon from '@TutorShared/atoms/SVGIcon';
import Popover from '@TutorShared/molecules/Popover';

import { tutorConfig } from '@TutorShared/config/config';
import { TutorRoles } from '@TutorShared/config/constants';
import { borderRadius, Breakpoint, colorTokens, lineHeight, shadow, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';

import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { useDebounce } from '@TutorShared/hooks/useDebounce';
import { useSelectKeyboardNavigation } from '@TutorShared/hooks/useSelectKeyboardNavigation';

import { type CourseDetailsResponse } from '@CourseBuilderServices/course';
import Show from '@TutorShared/controls/Show';
import { withVisibilityControl } from '@TutorShared/hoc/withVisibilityControl';
import type { User } from '@TutorShared/services/users';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { noop } from '@TutorShared/utils/util';

import profileImage from '@SharedImages/profile-photo.png';

import FormFieldWrapper from './FormFieldWrapper';

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
  postAuthor?: CourseDetailsResponse['post_author'];
} & FormControllerProps<UserOption | UserOption[] | null>;

const userPlaceholderData: UserOption = {
  id: 0,
  name: __('Click to select user', __TUTOR_TEXT_DOMAIN__),
  email: 'example@example.com',
  avatar_url: 'https://gravatar.com/avatar',
};

const currentUser = tutorConfig.current_user;

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
  emptyStateText = __('No user selected', __TUTOR_TEXT_DOMAIN__),
  isInstructorMode = false,
  postAuthor,
}: FormSelectUserProps) => {
  const inputValue = field.value ?? (isMultiSelect ? [] : userPlaceholderData);
  const selectedIds = Array.isArray(inputValue) ? inputValue.map((item) => String(item.id)) : [String(inputValue.id)];
  const isCurrentUserAdmin = currentUser.roles?.includes(TutorRoles.ADMINISTRATOR);
  const isCurrentUserAuthor = String(currentUser.data.id) === String(postAuthor?.ID || '');

  const triggerRef = useRef<HTMLDivElement>(null);
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

  const { activeIndex, setActiveIndex } = useSelectKeyboardNavigation({
    options: filteredOption.map((option) => ({
      label: option.name,
      value: option,
    })),
    isOpen,
    onSelect: (selectedUser) => {
      handleUserSelection(selectedUser.value);
    },
    onClose: () => {
      setIsOpen(false);
      setSearchText('');
    },
    selectedValue: Array.isArray(inputValue) ? null : inputValue,
  });

  const handleUserSelection = (instructor: UserOption) => {
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
  };

  const handleDeleteSelection = (id: number) => {
    if (Array.isArray(inputValue)) {
      const updatedValue = inputValue.filter((item) => item.id !== id);

      field.onChange(updatedValue);
      onChange(updatedValue);
    }
  };

  const activeItemRef = useRef<HTMLLIElement>(null);

  useEffect(() => {
    if (isOpen && activeIndex >= 0 && activeItemRef.current) {
      activeItemRef.current.scrollIntoView({
        block: 'nearest',
        behavior: 'smooth',
      });
    }
  }, [isOpen, activeIndex]);

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
                    onClick={(event) => {
                      event.stopPropagation();
                      setIsOpen((previousState) => !previousState);
                    }}
                    onKeyDown={(event) => {
                      if (event.key === 'Enter') {
                        event.preventDefault();
                        setIsOpen((previousState) => !previousState);
                      }

                      if (event.key === 'Tab') {
                        setIsOpen(false);
                      }
                    }}
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
                        <Show when={isCurrentUserAdmin || isCurrentUserAuthor || instructor.isRemoveAble}>
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
            <Popover
              triggerRef={triggerRef}
              isOpen={isOpen}
              dependencies={[filteredOption.length]}
              animationType={AnimationType.slideDown}
              closePopover={() => {
                setIsOpen(false);
                setSearchText('');
              }}
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
                      <p>{__('No user found', __TUTOR_TEXT_DOMAIN__)}</p>
                    </li>
                  }
                >
                  {filteredOption.map((instructor, index) => (
                    <li
                      key={String(instructor.id)}
                      css={styles.optionItem}
                      data-active={activeIndex === index}
                      onMouseOver={() => setActiveIndex(index)}
                      onMouseLeave={() => index !== activeIndex && setActiveIndex(-1)}
                      ref={activeIndex === index ? activeItemRef : null}
                      onFocus={() => setActiveIndex(index)}
                    >
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
                        aria-selected={activeIndex === index}
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
            </Popover>
          </div>
        );
      }}
    </FormFieldWrapper>
  );
};

export default withVisibilityControl(FormSelectUser);

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
    position: sticky;
    top: 0px;
    padding: 0px;
    background-color: inherit;
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

    &:hover,
    &:focus,
    &:active {
      background-color: ${colorTokens.bg.white};
    }

    &:focus {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: 1px;
    }

    ${isDefaultItem &&
    css`
      border-color: ${colorTokens.stroke.default};
      cursor: pointer;
    `}

    &:hover {
      border-color: ${colorTokens.stroke.divider};

      [data-instructor-delete-button] {
        opacity: 1;
      }
    }

    ${Breakpoint.smallTablet} {
      [data-instructor-delete-button] {
        opacity: 1;
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
    ${styleUtils.crossButton};
    color: ${colorTokens.icon.default};
    opacity: 0;
    transition: none;

    &:hover,
    &:focus,
    &:active {
      background-color: ${colorTokens.bg.white};
    }

    &:focus {
      box-shadow: ${shadow.focus};
    }

    :focus-visible {
      opacity: 1;
    }
  `,
  options: css`
    z-index: ${zIndex.dropdown};
    background-color: ${colorTokens.background.white};
    list-style-type: none;
    box-shadow: ${shadow.popover};
    margin: ${spacing[4]} 0;
    margin: 0;
    max-height: 400px;
    border: 1px solid ${colorTokens.stroke.border};
    border-radius: ${borderRadius[6]};
    ${styleUtils.overflowYAuto};
    scrollbar-gutter: auto;
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

    &[data-active='true'] {
      background-color: ${colorTokens.background.hover};
    }

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

    &:hover,
    &:focus,
    &:active {
      background: none;
    }

    &:focus-visible {
      outline: 2px solid ${colorTokens.stroke.brand};
      outline-offset: -2px;
      border-radius: ${borderRadius[6]};
    }
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

    ${isOpen &&
    css`
      transform: rotate(180deg);
    `}
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
