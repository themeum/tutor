import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';

import { LoadingSection } from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import EmptyState from '@Molecules/EmptyState';

import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import For from '@Controls/For';
import Show from '@Controls/Show';
import type { PrerequisiteCourses } from '@CourseBuilderServices/course';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { noop } from '@Utils/util';

import { useDebounce } from '@Hooks/useDebounce';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import { useSelectKeyboardNavigation } from '@Hooks/useSelectKeyboardNavigation';

import notFound2x from '@Images/not-found-2x.webp';
import notFound from '@Images/not-found.webp';

import FormFieldWrapper from './FormFieldWrapper';

type FormCoursePrerequisitesProps = {
  label?: string | React.ReactNode;
  placeholder?: string;
  options: PrerequisiteCourses[];
  onChange?: (selectedOption: PrerequisiteCourses[]) => void;
  handleSearchOnChange?: (searchText: string) => void;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  isSearchable?: boolean;
  isHidden?: boolean;
  responsive?: boolean;
  helpText?: string;
} & FormControllerProps<PrerequisiteCourses[] | null>;

const FormCoursePrerequisites = ({
  field,
  fieldState,
  options,
  onChange = noop,
  handleSearchOnChange,
  label,
  placeholder = '',
  disabled,
  readOnly,
  loading,
  isSearchable = false,
  helpText,
}: FormCoursePrerequisitesProps) => {
  const inputValue = field.value ?? [];
  const selectedIds = inputValue.map((course) => String(course.id));

  const activeItemRef = useRef<HTMLLIElement | null>(null);

  const [isOpen, setIsOpen] = useState(false);
  const [searchText, setSearchText] = useState('');
  const debouncedSearchText = useDebounce(searchText);

  const filteredOption = options.filter(
    (option) =>
      option.post_title.toLowerCase().includes(debouncedSearchText.toLowerCase()) &&
      !selectedIds.includes(String(option.id)),
  );

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

  const { activeIndex, setActiveIndex } = useSelectKeyboardNavigation({
    options: filteredOption.map((option) => ({
      label: option.post_title,
      value: option,
    })),
    isOpen,
    selectedValue: null,
    onSelect: (selectedOption) => {
      field.onChange([...inputValue, selectedOption.value]);
      onChange([...inputValue, selectedOption.value]);
      setIsOpen(false);
      setSearchText('');
    },
    onClose: () => {
      setIsOpen(false);
      setSearchText('');
      setSearchText('');
    },
  });

  const handleDeleteSelection = (id: number) => {
    if (Array.isArray(inputValue)) {
      const updatedValue = inputValue.filter((item) => item.id !== id);

      field.onChange(updatedValue);
      onChange(updatedValue);
    }
  };

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
      helpText={helpText}
    >
      {(inputProps) => {
        const { css: inputCss, ...restInputProps } = inputProps;

        return (
          <div css={styles.mainWrapper}>
            <div ref={triggerRef}>
              <div css={styles.inputWrapper}>
                <div css={styles.leftIcon}>
                  <SVGIcon name="search" width={24} height={24} />
                </div>
                <input
                  {...restInputProps}
                  onClick={() => setIsOpen((previousState) => !previousState)}
                  onKeyDown={(event) => {
                    if (event.key === 'Enter') {
                      setIsOpen(true);
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
            </div>

            <Show
              when={inputValue.length > 0}
              fallback={
                <Show when={!loading} fallback={<LoadingSection />}>
                  <EmptyState
                    size="small"
                    emptyStateImage={notFound}
                    emptyStateImage2x={notFound2x}
                    imageAltText={__('Illustration of a no course selected', 'tutor')}
                    title={__('No course selected', 'tutor')}
                    description={__('Select a course to add as a prerequisite.', 'tutor')}
                  />
                </Show>
              }
            >
              <div css={styles.courseList}>
                <For each={inputValue}>
                  {(course, index) => (
                    <div
                      key={index}
                      css={styles.courseCard({
                        onPopover: false,
                      })}
                    >
                      <div css={styles.imageWrapper}>
                        <img src={course.featured_image} alt={course.post_title} css={styles.image} />
                      </div>
                      <div css={styles.cardContent}>
                        <span css={styles.cardTitle}>{course.post_title}</span>
                        <p css={typography.tiny()}>{course.id}</p>
                      </div>
                      <button
                        type="button"
                        css={styles.removeButton}
                        data-visually-hidden
                        onClick={() => handleDeleteSelection(course.id)}
                      >
                        <SVGIcon name="times" width={14} height={14} />
                      </button>
                    </div>
                  )}
                </For>
              </div>
            </Show>

            <Portal
              isOpen={isOpen}
              onClickOutside={() => {
                setIsOpen(false);
                setSearchText('');
              }}
              onEscape={() => {
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
                  <Show
                    when={filteredOption.length > 0}
                    fallback={
                      <li css={styles.emptyOption}>
                        <p>{__('No courses found', 'tutor')}</p>
                      </li>
                    }
                  >
                    <For each={filteredOption}>
                      {(course, index) => (
                        <li key={course.id} ref={activeIndex === index ? activeItemRef : null}>
                          <button
                            type="button"
                            css={styles.courseCard({
                              onPopover: true,
                              isActive: activeIndex === index,
                            })}
                            onClick={() => {
                              field.onChange([...inputValue, course]);
                              onChange([...inputValue, course]);
                              setIsOpen(false);
                              setSearchText('');
                            }}
                            onMouseOver={() => setActiveIndex(index)}
                            onFocus={() => setActiveIndex(index)}
                            aria-selected={activeIndex === index}
                          >
                            <div css={styles.imageWrapper}>
                              <img src={course.featured_image} alt={course.post_title} css={styles.image} />
                            </div>
                            <div css={styles.cardContent}>
                              <span css={styles.cardTitle}>{course.post_title}</span>
                              <p css={typography.tiny()}>{course.id}</p>
                            </div>
                          </button>
                        </li>
                      )}
                    </For>
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

export default FormCoursePrerequisites;

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
  input: css`
    ${typography.body()};
    width: 100%;
    padding-right: ${spacing[32]};
    padding-left: ${spacing[36]};
    ${styleUtils.textEllipsis};
    border: 1px solid ${colorTokens.stroke.default};

    &.tutor-input-field {
      padding-right: ${spacing[32]};
      padding-left: ${spacing[36]};
    }
  `,
  courseList: css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[8]};
    max-height: 256px;
    height: 100%;
    margin-top: ${spacing[8]};
    ${styleUtils.overflowYAuto};
  `,
  optionsWrapper: css`
    position: absolute;
    width: 100%;
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
  `,
  courseCard: ({
    onPopover = false,
    isActive = false,
  }: {
    onPopover: boolean;
    isActive?: boolean;
  }) => css`
    ${styleUtils.resetButton};
    width: 100%;
    cursor: ${onPopover ? 'pointer' : 'default'};
    position: relative;
    padding: ${spacing[8]};
    border: 1px solid transparent;
    border-radius: ${borderRadius.card};
    display: grid;
    grid-template-columns: 76px 1fr;
    gap: ${spacing[10]};
    align-items: center;
    background-color: ${colorTokens.background.white};
    [data-visually-hidden] {
      opacity: 0;
    }

    ${
      isActive &&
      css`
        background-color: ${colorTokens.background.hover};
        border-color: ${colorTokens.stroke.default};
      `
    }

    &:hover {
      background-color: ${colorTokens.background.hover};

      ${
        !onPopover &&
        css`
          background-color: ${colorTokens.background.white};
          border-color: ${colorTokens.stroke.default};
        `
      }
      
      [data-visually-hidden] {
        opacity: 1;
      }
    }
  `,
  imageWrapper: css`
    height: 42px;
  `,
  image: css`
    width: 100%;
    height: 100%;
    border-radius: ${borderRadius[4]};
    object-fit: cover;
    object-position: center;
  `,
  cardContent: css`
    display: flex;
    flex-direction: column;
  `,
  cardTitle: css`
    ${typography.small('medium')};
    ${styleUtils.text.ellipsis(1)};
  `,
  removeButton: css`
    ${styleUtils.resetButton};
    position: absolute;
    top: 50%;
    right: ${spacing[8]};
    transform: translateY(-50%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: ${borderRadius.circle};
    background: ${colorTokens.background.white};

    svg {
      color: ${colorTokens.icon.default};
    }

    :hover {
      svg {
        color: ${colorTokens.icon.hover};
      }
    }

    :focus {
      box-shadow: ${shadow.focus};
    }
  `,
  emptyOption: css`
    ${typography.caption('medium')};
    padding: ${spacing[8]};
    text-align: center;
  `,
};
