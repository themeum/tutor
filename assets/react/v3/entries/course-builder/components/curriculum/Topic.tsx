import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import type { TopicContent as TopicContentType } from '@CourseBuilderServices/curriculum';

import FormInput from '@Components/fields/FormInput';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { useModal } from '@Components/modals/Modal';
import For from '@Controls/For';
import Show from '@Controls/Show';
import QuizModal from '@CourseBuilderComponents/modals/QuizModal';
import type { CourseTopicWithCollapse } from '@CourseBuilderPages/Curriculum';
import { AnimationType } from '@Hooks/useAnimation';
import { useCollapseExpandAnimation } from '@Hooks/useCollapseExpandAnimation';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import ConfirmationPopover from '@Molecules/ConfirmationPopover';
import ThreeDots from '@Molecules/ThreeDots';
import { animateLayoutChanges } from '@Utils/dndkit';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';
import { moveTo, nanoid, noop } from '@Utils/util';
import {
  DndContext,
  DragOverlay,
  KeyboardSensor,
  PointerSensor,
  type UniqueIdentifier,
  closestCenter,
  useSensor,
  useSensors,
} from '@dnd-kit/core';
import { restrictToVerticalAxis, restrictToWindowEdges } from '@dnd-kit/modifiers';
import {
  SortableContext,
  sortableKeyboardCoordinates,
  useSortable,
  verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { animated, useSpring } from '@react-spring/web';
import { __ } from '@wordpress/i18n';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { Controller } from 'react-hook-form';
import TopicContent from './TopicContent';
import AddLessonModal from '@CourseBuilderComponents/modals/AddLessonModal';
import AddAssignmentModal from '@CourseBuilderComponents/modals/AddAssignmentModal';

interface TopicProps {
  topic: CourseTopicWithCollapse;
  onDelete?: () => void;
  onCopy?: () => void;
  onSort?: (activeIndex: number, overIndex: number) => void;
  onCollapse?: () => void;
  isOverlay?: boolean;
}

const hasLiveAddons = true;

const Topic = ({ topic, onDelete, onCopy, onSort, onCollapse, isOverlay = false }: TopicProps) => {
  const [isActive, setIsActive] = useState(false);
  const [isEdit, setIsEdit] = useState(false);
  const [isOpen, setIsOpen] = useState(false);
  const [isDeletePopoverOpen, setIsDeletePopoverOpen] = useState(false);
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  // @TODO: will be controlled by the API
  const [content, setContent] = useState<TopicContentType[]>(topic.content);

  const topicRef = useRef<HTMLDivElement>(null);
  const descriptionRef = useRef<HTMLDivElement>(null);
  const wrapperRef = useRef<HTMLDivElement>(null);
  const deleteRef = useRef<HTMLButtonElement>(null);

  const [collapseAnimation, collapseAnimate] = useSpring(
    {
      height: !topic.isCollapsed ? topicRef.current?.scrollHeight : 0,
      opacity: !topic.isCollapsed ? 1 : 0,
      overflow: 'hidden',
      config: {
        duration: 300,
        easing: (t) => t * (2 - t),
      },
    },
    [content.length]
  );
  const collapseAnimationDescription = useCollapseExpandAnimation({
    ref: descriptionRef,
    isOpen: !topic.isCollapsed,
    heightCalculator: 'client',
  });

  const form = useFormWithGlobalError<{ title: string; summary: string }>({
    defaultValues: {
      title: topic.post_title,
      summary: topic.post_content,
    },
  });

  const { showModal } = useModal();

  useEffect(() => {
    const handleOutsideClick = (event: MouseEvent) => {
      if (isDefined(wrapperRef.current) && !wrapperRef.current.contains(event.target as HTMLDivElement)) {
        setIsActive(false);
      }
    };

    document.addEventListener('click', handleOutsideClick);

    return () => document.removeEventListener('click', handleOutsideClick);
  }, []);

  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 10,
      },
    }),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    })
  );

  const activeSortItem = useMemo(() => {
    return topic.content.find((item) => item.ID === activeSortId);
  }, [activeSortId, topic.content]);

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: topic.ID,
    animateLayoutChanges,
  });

  const combinedRef = useCallback(
    (node: HTMLDivElement) => {
      if (node) {
        setNodeRef(node);
        // biome-ignore lint/suspicious/noExplicitAny: <explanation>
        (wrapperRef as any).current = node;
      }
    },
    [setNodeRef]
  );

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.3 : undefined,
  };

  const createDuplicateContent = (data: TopicContentType) => {
    setContent((previousContent) => {
      const newContent = { ...data, ID: nanoid() };
      return [...previousContent, newContent];
    });
  };

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (isDefined(topicRef.current)) {
      collapseAnimate.start({
        height: !topic.isCollapsed ? topicRef.current.scrollHeight : 0,
        opacity: !topic.isCollapsed ? 1 : 0,
      });
    }
  }, [topic.isCollapsed, content.length]);

  return (
    <div
      {...attributes}
      css={styles.wrapper({ isActive: isActive || isEdit, isOverlay })}
      onClick={() => setIsActive(true)}
      onKeyDown={noop}
      tabIndex={-1}
      ref={combinedRef}
      style={style}
    >
      <div
        css={styles.header({
          isCollapsed: topic.isCollapsed,
          isEdit,
          isDeletePopoverOpen,
        })}
      >
        <div css={styles.headerContent}>
          <div {...listeners} css={styles.grabberInput({ isOverlay })}>
            <SVGIcon name="dragVertical" width={24} height={24} />

            <Show
              when={isEdit}
              fallback={
                <div css={styles.title({ isEdit })} title={topic.post_title} onDoubleClick={() => setIsEdit(true)}>
                  {topic.post_title}
                </div>
              }
            >
              <div css={styles.title({ isEdit })}>
                <Controller
                  control={form.control}
                  name="title"
                  render={(controllerProps) => (
                    <FormInput {...controllerProps} placeholder={__('Add a title', 'tutor')} isSecondary />
                  )}
                />
              </div>
            </Show>
          </div>
          <div css={styles.actions}>
            <Show when={!isEdit}>
              <button
                type="button"
                css={styles.actionButton}
                data-visually-hidden
                onClick={() => {
                  setIsEdit(true);
                }}
              >
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
              title={`Delete topic "${topic.post_title}"`}
              message="Are you sure you want to delete this content from your course? This cannot be undone."
              animationType={AnimationType.slideUp}
              arrow="auto"
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
                onDelete?.();
              }}
            />

            <button
              type="button"
              css={styles.actionButton}
              onClick={() => {
                onCollapse?.();
              }}
            >
              <SVGIcon name={topic.isCollapsed ? 'chevronDown' : 'chevronUp'} />
            </button>
          </div>
        </div>

        <Show
          when={isEdit}
          fallback={
            <animated.div style={{ ...collapseAnimationDescription }}>
              <div css={styles.description({ isEdit })} ref={descriptionRef} onDoubleClick={() => setIsEdit(true)}>
                {topic.post_content}
              </div>
            </animated.div>
          }
        >
          <div css={styles.description({ isEdit })}>
            <Controller
              control={form.control}
              name="summary"
              render={(controllerProps) => (
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

        <Show when={isEdit}>
          <div css={styles.footer}>
            <Button variant="text" size="small" onClick={() => setIsEdit(false)}>
              {__('Cancel', 'tutor')}
            </Button>
            <Button
              variant="secondary"
              size="small"
              onClick={form.handleSubmit(async (values) => {
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
      <animated.div style={{ ...collapseAnimation }}>
        <div css={styles.content} ref={topicRef}>
          <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            modifiers={[restrictToVerticalAxis, restrictToWindowEdges]}
            onDragStart={(event) => {
              setActiveSortId(event.active.id);
            }}
            onDragEnd={(event) => {
              const { active, over } = event;
              if (!over) {
                return;
              }

              if (active.id !== over.id) {
                const activeIndex = content.findIndex((item) => item.ID === active.id);
                const overIndex = content.findIndex((item) => item.ID === over.id);
                // Will be modified later
                // onSort?.(activeIndex, overIndex);
                setContent(moveTo(content, activeIndex, overIndex));
              }
            }}
          >
            <SortableContext
              items={content.map((item) => ({ ...item, id: item.ID }))}
              strategy={verticalListSortingStrategy}
            >
              <div>
                <For each={content}>
                  {(content) => {
                    return (
                      <TopicContent
                        key={content.ID}
                        type={content.type}
                        topic={topic}
                        content={{
                          id: content.ID,
                          title: content.post_title,
                          questionCount: content.type === 'quiz' ? content.questions.length : undefined,
                        }}
                        onCopy={() => createDuplicateContent(content)}
                      />
                    );
                  }}
                </For>
              </div>
            </SortableContext>

            {createPortal(
              <DragOverlay>
                <Show when={activeSortItem}>
                  {(item) => (
                    <TopicContent
                      topic={topic}
                      content={{
                        id: item.ID,
                        title: item.post_title,
                        questionCount: 0,
                      }}
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
                isOutlined
                size="small"
                icon={<SVGIcon name="plus" width={24} height={24} />}
                onClick={() => {
                  showModal({
                    component: AddLessonModal,
                    props: {
                      title: __('Lesson', 'tutor'),
                      icon: <SVGIcon name="lesson" width={24} height={24} />,
                      subtitle: `${__('Topic:', 'tutor')}  ${topic.post_title}`,
                    },
                  });
                }}
              >
                {__('Lesson', 'tutor')}
              </Button>
              <Button
                variant="tertiary"
                isOutlined
                size="small"
                icon={<SVGIcon name="plus" width={24} height={24} />}
                onClick={() => {
                  showModal({
                    component: QuizModal,
                    props: {
                      title: __('Quiz', 'tutor'),
                      icon: <SVGIcon name="quiz" width={24} height={24} />,
                      subtitle: `${__('Topic:', 'tutor')}  ${topic.post_title}`,
                    },
                  });
                }}
              >
                {__('Quiz', 'tutor')}
              </Button>
              <Button
                variant="tertiary"
                isOutlined
                size="small"
                icon={<SVGIcon name="plus" width={24} height={24} />}
                onClick={() => {
                  showModal({
                    component: AddAssignmentModal,
                    props: {
                      title: __('Assignment', 'tutor'),
                      icon: <SVGIcon name="assignment" width={24} height={24} />,
                      subtitle: `${__('Topic:', 'tutor')}  ${topic.post_title}`,
                    },
                  });
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
                    isOutlined
                    size="small"
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
                  isInverse
                  arrowPosition="auto"
                  hideArrow
                >
                  <ThreeDots.Option
                    text={__('Meet live lesson', 'tutor')}
                    icon={<SVGIcon width={24} height={24} name="googleMeetColorize" isColorIcon />}
                  />
                  <ThreeDots.Option
                    text={__('Zoom live lesson', 'tutor')}
                    icon={<SVGIcon width={24} height={24} name="zoomColorize" isColorIcon />}
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
      </animated.div>
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
    width: 100%;

    ${
      isActive &&
      css`
      border-color: ${colorTokens.stroke.brand};
      background-color: ${colorTokens.background.hover};
    `
    }

    :hover {
      background-color: ${colorTokens.background.hover};
    }

    ${
      isOverlay &&
      css`
      box-shadow: ${shadow.drag};
    `
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

    ${
      !isCollapsed &&
      css`
      border-bottom: 1px solid ${colorTokens.stroke.divider};
    `
    }

    ${
      isCollapsed &&
      !isEdit &&
      css`
      padding-bottom: 0;
    `
    }

    ${
      !isEdit &&
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
    `
    }
  `,
  headerContent: css`
    display: grid;
    grid-template-columns: 8fr 1fr;
    gap: ${spacing[12]};
    width: 100%;
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
    ${
      !isEdit &&
      css`
      ${styleUtils.text.ellipsis(1)};
    `
    }
  `,
  description: ({ isEdit }: { isEdit: boolean }) => css`
    ${typography.caption()};
    color: ${colorTokens.text.hints};
    padding-inline: ${spacing[8]};
    margin-left: ${spacing[24]};
    margin-bottom: ${spacing[8]};

    ${
      !isEdit &&
      css`
      ${styleUtils.text.ellipsis(2)};
    `
    }

    ${
      isEdit &&
      css`
      padding-right: 0;
    `
    }
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
