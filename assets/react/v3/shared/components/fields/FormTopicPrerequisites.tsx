import { css } from '@emotion/react';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect, useRef, useState } from 'react';

import { LoadingSection } from '@TutorShared/atoms/LoadingSpinner';
import SVGIcon from '@TutorShared/atoms/SVGIcon';
import EmptyState from '@TutorShared/molecules/EmptyState';
import Popover from '@TutorShared/molecules/Popover';

import type { Content, CourseTopic } from '@CourseBuilderServices/curriculum';
import { borderRadius, Breakpoint, colorTokens, shadow, spacing, zIndex } from '@TutorShared/config/styles';
import { typography } from '@TutorShared/config/typography';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { AnimationType } from '@TutorShared/hooks/useAnimation';
import { useDebounce } from '@TutorShared/hooks/useDebounce';
import type { FormControllerProps } from '@TutorShared/utils/form';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type ID } from '@TutorShared/utils/types';
import { noop } from '@TutorShared/utils/util';

import notFound2x from '@SharedImages/not-found-2x.webp';
import notFound from '@SharedImages/not-found.webp';

import FormFieldWrapper from './FormFieldWrapper';

type FormTopicPrerequisitesProps = {
  label?: string | React.ReactNode;
  placeholder?: string;
  options: CourseTopic[];
  onChange?: (selectedOption: ID[]) => void;
  handleSearchOnChange?: (searchText: string) => void;
  disabled?: boolean;
  readOnly?: boolean;
  loading?: boolean;
  isSearchable?: boolean;
  isHidden?: boolean;
  responsive?: boolean;
  helpText?: string;
} & FormControllerProps<ID[] | null>;

const icons = {
  lesson: {
    name: 'lesson',
    color: colorTokens.icon.default,
  },
  tutor_quiz: {
    name: 'quiz',
    color: colorTokens.design.warning,
  },
  tutor_assignments: {
    name: 'assignment',
    color: colorTokens.icon.processing,
  },
  tutor_zoom_meeting: {
    name: 'zoomColorize',
    color: '',
  },
  'tutor-google-meet': {
    name: 'googleMeetColorize',
    color: '',
  },
  tutor_h5p_quiz: {
    name: 'quiz',
    color: colorTokens.icon.warning,
  },
} as const;

const FormTopicPrerequisites = ({
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
}: FormTopicPrerequisitesProps) => {
  const inputValue = field.value || [];
  const selectedIds = inputValue.map((item) => String(item));
  const selectedOptions = options.reduce((contents, topic) => {
    return topic.contents.reduce((selectedContents, content) => {
      if (selectedIds.includes(String(content.ID))) {
        selectedContents.push(content);
      }

      return selectedContents;
    }, contents);
  }, [] as Content[]);

  const triggerRef = useRef<HTMLDivElement>(null);
  const [isOpen, setIsOpen] = useState(false);

  const [searchText, setSearchText] = useState('');
  const debouncedSearchText = useDebounce(searchText);

  const filteredOption = options.reduce((topics, topic) => {
    const contents = topic.contents.filter(
      (content) =>
        content.post_type !== 'tutor-google-meet' &&
        content.post_type !== 'tutor_zoom_meeting' &&
        !selectedIds.includes(String(content.ID)),
    );

    if (contents.length === 0) {
      return topics;
    }

    if (topic.title.toLowerCase().includes(debouncedSearchText.toLowerCase())) {
      topics.push({
        ...topic,
        contents,
      });
    } else {
      const filteredContents = topic.contents.filter((content) =>
        content.post_title.toLowerCase().includes(debouncedSearchText.toLowerCase()),
      );

      if (filteredContents.length === 0) {
        return topics;
      }

      topics.push({
        ...topic,
        contents: filteredContents,
      });
    }

    return topics;
  }, [] as CourseTopic[]);

  useEffect(() => {
    if (handleSearchOnChange) {
      handleSearchOnChange(debouncedSearchText);
    } else {
      // Handle local filter
    }
  }, [debouncedSearchText, handleSearchOnChange]);

  const handleDeleteSelection = (id: ID) => {
    if (Array.isArray(inputValue)) {
      const updatedValue = inputValue.filter((item) => String(item) !== String(id));

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
                    title={__('No topic content selected', __TUTOR_TEXT_DOMAIN__)}
                    description={__('Select a topic content to add as a prerequisite', __TUTOR_TEXT_DOMAIN__)}
                  />
                </Show>
              }
            >
              <div css={styles.courseList}>
                <For each={selectedOptions}>
                  {(content) => (
                    <div
                      key={content.ID}
                      css={styles.groupItems({
                        onPopover: false,
                      })}
                    >
                      <div
                        css={styles.iconAndTitle({
                          onPopover: false,
                        })}
                      >
                        <SVGIcon
                          name={icons[content.post_type as keyof typeof icons]?.name || 'lesson'}
                          width={24}
                          height={24}
                          style={css`
                            color: ${icons[content.post_type as keyof typeof icons].color};
                          `}
                        />
                        <span css={styles.title} title={content.post_title}>
                          {content.post_title}
                        </span>
                        <Show when={content.post_type === 'tutor_quiz' && content.total_question}>
                          <span data-question-count css={typography.tiny()}>
                            {
                              /* translators: %d is the number of questions */
                              sprintf(__('(%d questions)', __TUTOR_TEXT_DOMAIN__), content.total_question)
                            }
                          </span>
                        </Show>
                      </div>
                      <button
                        type="button"
                        css={styles.removeButton}
                        data-visually-hidden
                        onClick={() => handleDeleteSelection(content.ID)}
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
              dependencies={[filteredOption.length]}
              animationType={AnimationType.slideDown}
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
                      <p>{__('No topics content found', __TUTOR_TEXT_DOMAIN__)}</p>
                    </li>
                  }
                >
                  <For each={filteredOption}>
                    {(topic) => (
                      <li key={topic.id} css={styles.group}>
                        <span css={styles.groupTitle} title={topic.title}>
                          {topic.title}
                        </span>
                        <For each={topic.contents}>
                          {(content) => (
                            <button
                              key={content.ID}
                              type="button"
                              css={styles.groupItems({
                                onPopover: true,
                              })}
                              onClick={() => {
                                const updatedValue = [...inputValue, content.ID];
                                field.onChange(updatedValue);
                                onChange(updatedValue);
                                setIsOpen(false);
                                setSearchText('');
                              }}
                            >
                              <div
                                css={styles.iconAndTitle({
                                  onPopover: true,
                                })}
                                data-content-icon
                              >
                                <SVGIcon
                                  name={icons[content.post_type as keyof typeof icons]?.name || 'lesson'}
                                  width={24}
                                  height={24}
                                  style={css`
                                    color: ${icons[content.post_type as keyof typeof icons].color};
                                  `}
                                />
                                <span css={styles.title} title={content.post_title}>
                                  {content.post_title}
                                </span>
                                <Show when={content.post_type === 'tutor_quiz' && content.total_question}>
                                  <span data-question-count css={typography.tiny()}>
                                    {
                                      /* translators: %d is the number of questions */
                                      sprintf(__('(%d questions)', __TUTOR_TEXT_DOMAIN__), content.total_question)
                                    }
                                  </span>
                                </Show>
                              </div>
                            </button>
                          )}
                        </For>
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

export default FormTopicPrerequisites;

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
    padding: 1px; // fix the box-shadow issue
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
  group: css`
    padding: ${spacing[10]} 0;

    :not(:last-child) {
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    }
  `,
  groupTitle: css`
    ${typography.small('semiBold')};
    ${styleUtils.text.ellipsis(1)};
    padding-inline: ${spacing[10]};
    margin-bottom: ${spacing[8]};
  `,
  groupItems: ({ onPopover }: { onPopover: boolean }) => css`
    position: relative;
    width: 100%;
    padding: ${spacing[10]} ${spacing[8]};
    border-radius: ${borderRadius[6]};
    border: 1px solid transparent;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: ${spacing[8]};
    background-color: ${colorTokens.background.white};
    cursor: ${onPopover ? 'pointer' : 'default'};

    [data-content-icon] {
      display: flex;
      height: ${onPopover ? '20px' : '24px'};
    }

    [data-visually-hidden] {
      opacity: 0;
    }

    ${!onPopover &&
    css`
      box-shadow: ${shadow.card};
    `}

    &:hover,
    &:focus,
    &:active {
      background-color: ${colorTokens.background.hover};

      ${!onPopover &&
      css`
        background-color: ${colorTokens.background.white};
        border-color: ${colorTokens.stroke.default};

        [data-visually-hidden] {
          opacity: 1;
        }
      `}
    }

    ${Breakpoint.smallTablet} {
      [data-visually-hidden] {
        opacity: 1;
      }
    }
  `,
  title: css`
    ${typography.caption()};
    color: ${colorTokens.text.title};
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
  `,
  iconAndTitle: ({ onPopover }: { onPopover: boolean }) => css`
    ${styleUtils.text.ellipsis(1)};
    display: flex;
    align-items: center;
    gap: ${spacing[8]};

    ${onPopover &&
    css`
      padding-left: ${spacing[16]};
    `}

    svg {
      flex-shrink: 0;
    }

    span {
      text-align: left;
      ${styleUtils.text.ellipsis(1)};
    }

    [data-question-count] {
      flex-shrink: 0;
      text-decoration: none;
    }
  `,
};
