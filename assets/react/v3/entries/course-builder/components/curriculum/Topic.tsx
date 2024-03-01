import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { CurriculumTopic } from '@CourseBuilderServices/curriculum';

import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import React, { useEffect, useRef, useState } from 'react';
import TopicContent from './TopicContent';
import Show from '@Controls/Show';
import { noop, transformParams } from '@Utils/util';
import { isDefined } from '@Utils/types';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { Controller } from 'react-hook-form';
import FormInput from '@Components/fields/FormInput';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { __ } from '@wordpress/i18n';
import ThreeDots from '@Molecules/ThreeDots';
import ConfirmationPopover from '@Molecules/ConfirmationPopover';
import { AnimationType } from '@Hooks/useAnimation';

interface TopicProps {
  topic: CurriculumTopic;
  allCollapsed: boolean;
}

// @TODO: will be come from app config api later.
const hasLiveAddons = true;

const Topic = ({ topic, allCollapsed }: TopicProps) => {
  const [isCollapsed, setIsCollapsed] = useState(allCollapsed);
  const [isActive, setIsActive] = useState(false);
  const [isEdit, setIsEdit] = useState(false);
  const [isOpen, setIsOpen] = useState(false);
  const [isDeletePopoverOpen, setIsDeletePopoverOpen] = useState(false);

  const wrapperRef = useRef<HTMLDivElement>(null);
  const deleteRef = useRef<HTMLButtonElement>(null);

  const form = useFormWithGlobalError<{ title: string; summary: string }>({
    defaultValues: {
      title: topic.title,
      summary: topic.summary,
    },
  });

  useEffect(() => {
    const handleOutsideClick = (event: MouseEvent) => {
      if (isDefined(wrapperRef.current) && !wrapperRef.current.contains(event.target as HTMLDivElement)) {
        setIsActive(false);
      }
    };

    document.addEventListener('click', handleOutsideClick);

    return () => document.removeEventListener('click', handleOutsideClick);
  }, []);

  useEffect(() => {
    setIsCollapsed(allCollapsed);
  }, [allCollapsed]);

  return (
    <div
      css={styles.wrapper({ isActive: isActive || isEdit })}
      onClick={() => setIsActive(true)}
      onKeyDown={noop}
      tabIndex={-1}
      ref={wrapperRef}
    >
      <div css={styles.header({ isCollapsed, isEdit, isDeletePopoverOpen })}>
        <div css={styles.headerContent}>
          <div css={styles.grabberInput}>
            <SVGIcon name="dragVertical" width={24} height={24} />
            <Show
              when={isEdit}
              fallback={
                <div css={styles.title({ isEdit })} title={topic.title}>
                  {topic.title}
                </div>
              }
            >
              <div css={styles.title({ isEdit })}>
                <Controller
                  control={form.control}
                  name="title"
                  render={controllerProps => (
                    <FormInput {...controllerProps} placeholder={__('Add a title', 'tutor')} isSecondary />
                  )}
                />
              </div>
            </Show>
          </div>
          <div css={styles.actions}>
            <Show when={!isEdit}>
              <button type="button" css={styles.actionButton} data-visually-hidden onClick={() => setIsEdit(true)}>
                <SVGIcon name="edit" width={24} height={24} />
              </button>
            </Show>
            <button
              type="button"
              css={styles.actionButton}
              data-visually-hidden
              onClick={() => {
                alert('@TODO: will be implemented later');
              }}
            >
              <SVGIcon name="copyPaste" width={24} height={24} />
            </button>
            <button
              type="button"
              css={styles.actionButton}
              data-visually-hidden
              ref={deleteRef}
              onClick={() => {
                setIsDeletePopoverOpen(true);
              }}
            >
              <SVGIcon name="delete" width={24} height={24} />
            </button>
            <ConfirmationPopover
              isOpen={isDeletePopoverOpen}
              triggerRef={deleteRef}
              closePopover={() => setIsDeletePopoverOpen(false)}
              maxWidth="258px"
              title={`Delete topic "${topic.title}"`}
              message="Are you sure you want to delete this content from your course? This cannot be undone."
              animationType={AnimationType.slideUp}
              arrow="top"
              hideArrow
              confirmButton={{
                text: __('Delete', 'tutor'),
                variant: 'text',
                isDelete: true,
              }}
              cancelButton={{
                text: __('Cancel', 'tutor'),
                variant: 'text',
              }}
              onConfirmation={() => {
                //
              }}
            />

            <button type="button" css={styles.actionButton} onClick={() => setIsCollapsed(previous => !previous)}>
              <SVGIcon name={isCollapsed ? 'chevronDown' : 'chevronUp'} />
            </button>
          </div>
        </div>

        <Show when={!isCollapsed}>
          <Show when={isEdit} fallback={<div css={styles.description({ isEdit })}>{topic.summary}</div>}>
            <div css={styles.description({ isEdit })}>
              <Controller
                control={form.control}
                name="summary"
                render={controllerProps => (
                  <FormTextareaInput
                    {...controllerProps}
                    placeholder={__('Add a summary', 'tutor')}
                    isSecondary
                    rows={2}
                    enableResize
                  />
                )}
              />
            </div>
          </Show>
        </Show>

        <Show when={isEdit}>
          <div css={styles.footer}>
            <Button variant="text" onClick={() => setIsEdit(false)}>
              {__('Cancel', 'tutor')}
            </Button>
            <Button
              variant="outlined"
              size="small"
              onClick={form.handleSubmit(async values => {
                //@TODO: will be implemented later
                console.log({ values });
                setIsEdit(false);
              })}
            >
              {__('Ok', 'tutor')}
            </Button>
          </div>
        </Show>
      </div>
      <Show when={!isCollapsed}>
        <div css={styles.content}>
          <div>
            <TopicContent type="lesson" content={{ title: 'Lesson: topic 1' }} />
            <TopicContent type="quiz" content={{ title: 'Quiz' }} />
            <TopicContent type="assignment" content={{ title: 'Assignments' }} />
            <TopicContent type="zoom" content={{ title: 'Zoom live lesson' }} />
            <TopicContent type="meet" content={{ title: 'Google meet live lesson' }} />
          </div>
          <div css={styles.contentButtons}>
            <div css={[styleUtils.display.flex(), { gap: spacing[12] }]}>
              <Button
                variant="tertiary"
                icon={<SVGIcon name="plus" />}
                onClick={() => {
                  alert('@TODO: will be implemented later');
                }}
              >
                {__('Lesson', 'tutor')}
              </Button>
              <Button
                variant="tertiary"
                icon={<SVGIcon name="plus" />}
                onClick={() => {
                  alert('@TODO: will be implemented later');
                }}
              >
                {__('Quiz', 'tutor')}
              </Button>
              <Button
                variant="tertiary"
                icon={<SVGIcon name="plus" />}
                onClick={() => {
                  alert('@TODO: will be implemented later');
                }}
              >
                {__('Assignment', 'tutor')}
              </Button>
            </div>
            <div css={styles.footerButtons}>
              <Show
                when={hasLiveAddons}
                fallback={
                  <Button
                    variant="tertiary"
                    icon={<SVGIcon name="download" width={24} height={24} />}
                    onClick={() => {
                      alert('@TODO: will be implemented later');
                    }}
                  >
                    {__('Import Quiz', 'tutor')}
                  </Button>
                }
              >
                <ThreeDots
                  isOpen={isOpen}
                  onClick={() => setIsOpen(true)}
                  closePopover={() => setIsOpen(false)}
                  dotsOrientation="vertical"
                  maxWidth="220px"
                >
                  <ThreeDots.Option
                    text={__('Meet live lesson', 'tutor')}
                    icon={<SVGIcon width={24} height={24} name="googleMeet" />}
                  />
                  <ThreeDots.Option
                    text={__('Zoom live lesson', 'tutor')}
                    icon={<SVGIcon width={24} height={24} name="zoom" />}
                  />
                  <ThreeDots.Option
                    text={__('Import Quiz', 'tutor')}
                    icon={<SVGIcon name="download" width={24} height={24} />}
                  />
                </ThreeDots>
              </Show>
            </div>
          </div>
        </div>
      </Show>
    </div>
  );
};

export default Topic;

const styles = {
  wrapper: ({ isActive = false }) => css`
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
    transition: background-color 0.3s ease-in-out, border-color 0.3s ease-in-out;

    ${isActive &&
    css`
      border-color: ${colorTokens.stroke.brand};
      background-color: ${colorTokens.background.hover};
    `}

    :hover {
      background-color: ${colorTokens.background.hover};
    }
  `,
  header: ({
    isCollapsed,
    isEdit,
    isDeletePopoverOpen,
  }: {
    isCollapsed: boolean;
    isEdit: boolean;
    isDeletePopoverOpen: boolean;
  }) => css`
    padding: ${spacing[12]} ${spacing[16]};
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};

    ${!isCollapsed &&
    css`
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    `}

    ${!isEdit &&
    !isDeletePopoverOpen &&
    css`
      [data-visually-hidden] {
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
      }

      :hover {
        [data-visually-hidden] {
          opacity: 1;
        }
      }
    `}
  `,
  headerContent: css`
    display: grid;
    grid-template-columns: 8fr 1fr;
    gap: ${spacing[12]};
  `,
  grabberInput: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};

    svg {
      color: ${colorTokens.color.black[40]};
      flex-shrink: 0;
    }
  `,
  actions: css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};
    justify-content: end;
  `,
  actionButton: css`
    ${styleUtils.resetButton};
    color: ${colorTokens.icon.default};
    display: flex;
    cursor: pointer;
  `,
  content: css`
    padding: ${spacing[16]};
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
  `,
  contentButtons: css`
    ${styleUtils.display.flex()};
    justify-content: space-between;
  `,
  title: ({ isEdit }: { isEdit: boolean }) => css`
    ${typography.body()};
    color: ${colorTokens.text.hints};
    width: 100%;
    ${!isEdit &&
    css`
      ${styleUtils.text.ellipsis(1)};
    `}
  `,
  description: ({ isEdit }: { isEdit: boolean }) => css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
    padding-inline: ${spacing[8]};
    margin-left: ${spacing[24]};
    margin-bottom: ${spacing[8]};

    ${!isEdit &&
    css`
      ${styleUtils.text.ellipsis(2)};
    `}

    ${isEdit &&
    css`
      padding-right: 0;
    `}
  `,
  footer: css`
    width: 100%;
    text-align: right;
    ${styleUtils.display.flex()};
    gap: ${spacing[8]};
    justify-content: end;
  `,
  footerButtons: css`
    display: flex;
    align-items: center;
  `,
};
