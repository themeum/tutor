import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';

import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import EmptyState from '@TutorShared/molecules/EmptyState';
import Popover from '@TutorShared/molecules/Popover';

import { borderRadius, Breakpoint, colorTokens, shadow, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { useDebounce } from '@TutorShared/hooks/useDebounce';
import { useSelectKeyboardNavigation } from '@TutorShared/hooks/useSelectKeyboardNavigation';
import { type Course } from '@TutorShared/services/course';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { noop } from '@TutorShared/utils/util';

import notFound2x from '@SharedImages/not-found-2x.webp';
import notFound from '@SharedImages/not-found.webp';

import FormFieldWrapper from './FormFieldWrapper';

type FormCoursePrerequisitesProps = {
  label?: string | React.ReactNode;
  placeholder?: string;
  options: Course[];
  onChange?: (selectedOption: Course[]) => void;
  handleSearchOnChange?: (searchText: string) => void;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  isSearchable?: boolean;
  isHidden?: boolean;
  responsive?: boolean;
  helpText?: string;
} & FormControllerProps<Course[] | null>;

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

  const triggerRef = useRef<HTMLDivElement>(null);
  const activeItemRef = useRef<HTMLLIElement | null>(null);

  const [isOpen, setIsOpen] = useState(false);
  const [searchText, setSearchText] = useState('');
  const debouncedSearchText = useDebounce(searchText);

  const filteredOption = options.filter(
    (option) =>
      option.title.toLowerCase().includes(debouncedSearchText.toLowerCase()) &&
      !selectedIds.includes(String(option.id)),
  );

  useEffect(() => {
    if (handleSearchOnChange) {
      handleSearchOnChange(debouncedSearchText);
    } else {
      // Handle local filter
    }
  }, [debouncedSearchText, handleSearchOnChange]);

  const { activeIndex, setActiveIndex } = useSelectKeyboardNavigation({
    options: filteredOption.map((option) => ({
      label: option.title,
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
                  onClick={(event) => {
                    event.stopPropagation();
                    setIsOpen((previousState) => !previousState);
                  }}
                  onKeyDown={(event) => {
                    if (event.key === 'Enter') {
                      event.preventDefault();
                      setIsOpen(true);
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
            </div>

            <Show
              when={inputValue.length > 0}
              fallback={
                <Show when={!loading} fallback={<LoadingSection />}>
                  <EmptyState
                    size="small"
                    emptyStateImage={notFound}
                    emptyStateImage2x={notFound2x}
                    imageAltText={__('Illustration of a no course selected', __TUTOR_TEXT_DOMAIN__)}
                    title={__('No course selected', __TUTOR_TEXT_DOMAIN__)}
                    description={__('Select a course to add as a prerequisite.', __TUTOR_TEXT_DOMAIN__)}
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
                        <img src={course.image} alt={course.title} css={styles.image} />
                      </div>
                      <div css={styles.cardContent}>
                        <span css={styles.cardTitle}>{course.title}</span>
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

            <Popover
              triggerRef={triggerRef}
              isOpen={isOpen}
              animationType={AnimationType.slideDown}
              dependencies={[filteredOption.length]}
              closePopover={() => {
                setIsOpen(false);
                setSearchText('');
              }}
            >
              <ul css={[styles.options]}>
                <Show
                  when={filteredOption.length > 0}
                  fallback={
                    <li css={styles.emptyOption}>
                      <p>{__('No courses found', __TUTOR_TEXT_DOMAIN__)}</p>
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
                          onMouseLeave={() => index !== activeIndex && setActiveIndex(-1)}
                          onFocus={() => setActiveIndex(index)}
                          aria-selected={activeIndex === index}
                        >
                          <div css={styles.imageWrapper}>
                            <img src={course.image} alt={course.title} css={styles.image} />
                          </div>
                          <div css={styles.cardContent}>
                            <span css={styles.cardTitle}>{course.title}</span>
                            <p css={typography.tiny()}>{course.id}</p>
                          </div>
                        </button>
                      </li>
                    )}
                  </For>
                </Show>
              </ul>
            </Popover>
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
  courseCard: ({ onPopover = false, isActive = false }: { onPopover: boolean; isActive?: boolean }) => css`
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

    ${isActive &&
    css`
      background-color: ${colorTokens.background.hover};
      border-color: ${colorTokens.stroke.default};
    `}

    &:hover {
      background-color: ${colorTokens.background.hover};

      ${!onPopover &&
      css`
        background-color: ${colorTokens.background.white};
        border-color: ${colorTokens.stroke.default};
      `}

      [data-visually-hidden] {
        opacity: 1;
      }
    }

    ${Breakpoint.smallTablet} {
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

    &:focus,
    &:active,
    &:hover {
      background: ${colorTokens.background.white};
    }

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

    :focus-visible {
      opacity: 1;
    }
  `,
  emptyOption: css`
    ${typography.caption('medium')};
    padding: ${spacing[8]};
    text-align: center;
  `,
};
