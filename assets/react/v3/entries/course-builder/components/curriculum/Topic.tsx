import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import { CourseTopic } from '@CourseBuilderServices/curriculum';

import { styleUtils } from '@Utils/style-utils';
import { css } from '@emotion/react';
import React, { useEffect, useMemo, useRef, useState } from 'react';
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
import For from '@Controls/For';
import {
  useSensors,
  useSensor,
  PointerSensor,
  KeyboardSensor,
  DndContext,
  closestCenter,
  UniqueIdentifier,
  DragOverlay,
} from '@dnd-kit/core';
import {
  AnimateLayoutChanges,
  SortableContext,
  defaultAnimateLayoutChanges,
  sortableKeyboardCoordinates,
  useSortable,
  verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { createPortal } from 'react-dom';

interface TopicProps {
  topic: CourseTopic;
  allCollapsed: boolean;
  onSort: (activeIndex: number, overIndex: number) => void;
  isOverlay?: boolean;
}

// @TODO: will be come from app config api later.
const hasLiveAddons = true;

const animateLayoutChanges: AnimateLayoutChanges = args => defaultAnimateLayoutChanges({ ...args, wasDragging: true });

const Topic = ({ topic, allCollapsed, onSort, isOverlay = false }: TopicProps) => {
  const [isCollapsed, setIsCollapsed] = useState(allCollapsed);
  const [isActive, setIsActive] = useState(false);
  const [isEdit, setIsEdit] = useState(false);
  const [isOpen, setIsOpen] = useState(false);

  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);

  const wrapperRef = useRef<HTMLDivElement>(null);
  const form = useFormWithGlobalError<{ title: string; summary: string }>({
    defaultValues: {
      title: topic.post_title,
      summary: topic.post_content,
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

  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 10,
      },
    }),
    useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates })
  );

  const activeSortItem = useMemo(() => {
    return topic.content.find(item => item.ID === activeSortId);
  }, [activeSortId, topic.content]);

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: topic.ID,
    animateLayoutChanges,
  });
  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.3 : undefined,
  };

  return (
    <div
      {...attributes}
      css={styles.wrapper({ isActive: isActive || isEdit, isOverlay })}
      onClick={() => setIsActive(true)}
      onKeyDown={noop}
      tabIndex={-1}
      ref={wrapperRef}
      style={style}
    >
      <div css={styles.header({ isCollapsed, isEdit })}>
        <div css={styles.headerContent} ref={setNodeRef}>
          <div {...listeners} css={styles.grabberInput({ isOverlay })}>
            <SVGIcon name="dragVertical" width={24} height={24} />

            <Show
              when={isEdit}
              fallback={
                <div css={styles.title({ isEdit })} title={topic.post_title}>
                  {topic.post_title}
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
              onClick={() => {
                alert('@TODO: will be implemented later');
              }}
            >
              <SVGIcon name="delete" width={24} height={24} />
            </button>
            <button type="button" css={styles.actionButton} onClick={() => setIsCollapsed(previous => !previous)}>
              <SVGIcon name={isCollapsed ? 'chevronDown' : 'chevronUp'} />
            </button>
          </div>
        </div>

        <Show when={!isCollapsed}>
          <Show when={isEdit} fallback={<div css={styles.description({ isEdit })}>{topic.post_content}</div>}>
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
          <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragStart={event => {
              setActiveSortId(event.active.id);
            }}
            onDragEnd={event => {
              const { active, over } = event;
              if (!over) {
                return;
              }

              if (active.id !== over.id) {
                const activeIndex = topic.content.findIndex(item => item.ID === active.id);
                const overIndex = topic.content.findIndex(item => item.ID === over.id);
                onSort(activeIndex, overIndex);
              }
            }}
          >
            <SortableContext
              items={topic.content.map(item => ({ ...item, id: item.ID }))}
              strategy={verticalListSortingStrategy}
            >
              <div>
                <For each={topic.content}>
                  {content => {
                    return (
                      <TopicContent
                        key={content.ID}
                        type={content.type}
                        content={{
                          id: content.ID,
                          title: content.post_title,
                          questionCount: content.type === 'quiz' ? content.questions.length : undefined,
                        }}
                      />
                    );
                  }}
                </For>
              </div>
            </SortableContext>

            {createPortal(
              <DragOverlay>
                <Show when={activeSortItem}>
                  {item => (
                    <TopicContent
                      content={{ id: item.ID, title: item.post_title, questionCount: 0 }}
                      type={item.type}
                      isDragging
                    />
                  )}
                </Show>
              </DragOverlay>,
              document.body
            )}
          </DndContext>

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
  wrapper: ({ isActive = false, isOverlay = false }) => css`
    border: 1px solid ${colorTokens.stroke.default};
    border-radius: ${borderRadius[8]};
    transition: background-color 0.3s ease-in-out, border-color 0.3s ease-in-out;
    background-color: ${colorTokens.bg.white};

    ${isActive &&
    css`
      border-color: ${colorTokens.stroke.brand};
      background-color: ${colorTokens.background.hover};
    `}

    :hover {
      background-color: ${colorTokens.background.hover};
    }

    ${isOverlay &&
    css`
      box-shadow: ${shadow.drag};
    `}
  `,

  header: ({ isCollapsed, isEdit }: { isCollapsed: boolean; isEdit: boolean }) => css`
    padding: ${spacing[12]} ${spacing[16]};
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};

    ${!isCollapsed &&
    css`
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    `}

    ${!isEdit &&
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
  grabberInput: ({ isOverlay = false }) => css`
    ${styleUtils.display.flex()};
    align-items: center;
    gap: ${spacing[8]};

    svg {
      color: ${colorTokens.color.black[40]};
      flex-shrink: 0;
    }
    cursor: ${isOverlay ? 'grabbing' : 'grab'};
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
