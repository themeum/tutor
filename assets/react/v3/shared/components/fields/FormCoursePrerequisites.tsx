import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, shadow, spacing, zIndex } from '@Config/styles';
import { typography } from '@Config/typography';
import { Portal, usePortalPopover } from '@Hooks/usePortalPopover';
import type { FormControllerProps } from '@Utils/form';
import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import { useEffect, useState } from 'react';

import { useDebounce } from '@Hooks/useDebounce';
import { noop } from '@Utils/util';
import FormFieldWrapper from './FormFieldWrapper';

import type { PrerequisiteCourses } from '@CourseBuilderServices/course';
import { useIsScrolling } from '@Hooks/useIsScrolling';
import { __ } from '@wordpress/i18n';

type FormCoursePrerequisitesProps = {
  label?: string;
  placeholder?: string;
  options: PrerequisiteCourses[];
  onChange?: (selectedOption: string[]) => void;
  handleSearchOnChange?: (searchText: string) => void;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  isSearchable?: boolean;
  isHidden?: boolean;
  responsive?: boolean;
  helpText?: string;
} & FormControllerProps<string[] | null>;

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
  const selectedCourses = options.filter((option) => inputValue.includes(String(option.id)));

  const [isOpen, setIsOpen] = useState(false);

  const [searchText, setSearchText] = useState('');
  const debouncedSearchText = useDebounce(searchText);
  const { ref: scrollDivRef, isScrolling } = useIsScrolling({ defaultValue: true });

  const searchedOptions = options.filter((option) =>
    option.post_title.toLowerCase().includes(debouncedSearchText.toLowerCase())
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
  });

  const handleDeleteSelection = (id: number) => {
    if (Array.isArray(inputValue)) {
      const updatedValue = inputValue.filter((item) => item !== String(id));

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
              <div css={styles.inputWrapper}>
                <div css={styles.leftIcon}>
                  <SVGIcon name="search" width={24} height={24} />
                </div>
                <input
                  {...restInputProps}
                  onClick={() => setIsOpen((previousState) => !previousState)}
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

            {inputValue.length > 0 && (
              <div
                ref={scrollDivRef}
                css={styles.courseList({
                  isScrolling,
                })}
              >
                {selectedCourses.map((course) => (
                  <div key={course.id} css={styles.courseCard}>
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
                ))}
              </div>
            )}

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
                  {searchedOptions.length > 0 ? (
                    searchedOptions
                      .filter((item) => !inputValue.includes(String(item.id)))
                      .map((course) => (
                        <li key={course.id}>
                          <button
                            type="button"
                            css={styles.courseCard}
                            onClick={() => {
                              field.onChange([...inputValue, String(course.id)]);
                              onChange([...inputValue, String(course.id)]);
                              setIsOpen(false);
                              setSearchText('');
                            }}
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
                      ))
                  ) : (
                    <li css={styles.emptyOption}>
                      <p>{__('No courses found')}</p>
                    </li>
                  )}
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

    :focus {
      outline: none;
      box-shadow: ${shadow.focus}
    }
  `,
  courseList: ({
    isScrolling = false,
  }: {
    isScrolling: boolean;
  }) => css`
    ${styleUtils.display.flex('column')}
    gap: ${spacing[8]};
    max-height: 256px;
    height: 100%;
    margin-top: ${spacing[8]};
    ${styleUtils.overflowYAuto};

    ${
      isScrolling &&
      css`
        box-shadow: ${shadow.scrollable};
      `
    }
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
    min-width: 200px;
  `,
  courseCard: css`
    ${styleUtils.resetButton};
    width: 100%;
    cursor: pointer;
    position: relative;
    padding: ${spacing[8]};
    border: 1px solid transparent;
    border-radius: ${borderRadius.card};
    display: grid;
    grid-template-columns: 76px 1fr;
    gap: ${spacing[10]};
    align-items: center;
    transition: border 0.3s ease;
    background-color: ${colorTokens.background.white};
    [data-visually-hidden] {
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
    }

    &:hover {
      border-color: ${colorTokens.stroke.default};
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
    border-radius: ${borderRadius.card};
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
    transition: opacity 0.3s ease-in-out;

    svg {
      color: ${colorTokens.icon.default};
      transition: color 0.3s ease-in-out;
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
