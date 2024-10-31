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
import { restrictToWindowEdges } from '@dnd-kit/modifiers';
import {
  SortableContext,
  sortableKeyboardCoordinates,
  useSortable,
  verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { animated, useSpring } from '@react-spring/web';
import {} from '@wordpress/i18n';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

import TopicFooter from '@CourseBuilderComponents/curriculum//TopicFooter';
import TopicHeader from '@CourseBuilderComponents/curriculum//TopicHeader';
import TopicContent from '@CourseBuilderComponents/curriculum/TopicContent';
import type { CourseTopicWithCollapse } from '@CourseBuilderPages/Curriculum';

import For from '@Controls/For';
import Show from '@Controls/Show';

import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import type { ID, Content as TopicContentType } from '@CourseBuilderServices/curriculum';
import {} from '@CourseBuilderUtils/utils';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { animateLayoutChanges } from '@Utils/dndkit';
import { styleUtils } from '@Utils/style-utils';
import { isDefined } from '@Utils/types';
import { moveTo, noop } from '@Utils/util';

interface TopicProps {
  topic: CourseTopicWithCollapse;
  onDelete?: () => void;
  onCopy?: (topicId: ID) => void;
  onSort?: (activeIndex: number, overIndex: number) => void;
  onCollapse?: (topicId: ID) => void;
  onEdit?: (topicId: ID) => void;
  isOverlay?: boolean;
}

interface TopicForm {
  title: string;
  summary: string;
}

const Topic = ({ topic, onDelete, onCopy, onSort, onCollapse, onEdit, isOverlay = false }: TopicProps) => {
  const form = useFormWithGlobalError<TopicForm>({
    defaultValues: {
      title: topic.title,
      summary: topic.summary,
    },
    shouldFocusError: true,
  });

  const [isActive, setIsActive] = useState(false);
  const [isEdit, setIsEdit] = useState(!topic.isSaved);
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  const [content, setContent] = useState<TopicContentType[]>(topic.contents);

  useEffect(() => {
    setContent(topic.contents);
  }, [topic]);

  const topicRef = useRef<HTMLDivElement>(null);
  const wrapperRef = useRef<HTMLDivElement>(null);

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
    [content.length],
  );

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    const handleOutsideClick = (event: MouseEvent) => {
      if (isDefined(wrapperRef.current) && !wrapperRef.current.contains(event.target as HTMLDivElement)) {
        setIsActive(false);
      }
    };

    document.addEventListener('click', handleOutsideClick);

    if (isEdit) {
      form.setFocus('title');
    }

    return () => document.removeEventListener('click', handleOutsideClick);
  }, [isEdit]);

  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 10,
      },
    }),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    }),
  );

  const activeSortItem = useMemo(() => {
    return topic.contents.find((item) => item.ID === activeSortId);
  }, [activeSortId, topic.contents]);

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: topic.id,
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
    [setNodeRef],
  );

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.3 : undefined,
    background: isDragging ? colorTokens.stroke.hover : undefined,
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
      {...(topic.isSaved ? attributes : {})}
      css={styles.wrapper({ isActive: isActive || isEdit, isOverlay })}
      onClick={() => setIsActive(true)}
      onKeyDown={noop}
      tabIndex={-1}
      ref={combinedRef}
      style={style}
    >
      <TopicHeader
        isActive={isActive}
        isDragging={isDragging}
        isEdit={isEdit}
        listeners={listeners}
        onCollapse={(topicId) => {
          onCollapse?.(topicId);
        }}
        onDelete={onDelete}
        onEdit={(topicId) => {
          onEdit?.(topicId);
        }}
        onCopy={(duplicatedId) => {
          onCopy?.(duplicatedId);
        }}
        topic={topic}
        setIsEdit={setIsEdit}
      />

      <animated.div style={{ ...collapseAnimation }}>
        <div css={styles.content} ref={topicRef}>
          <Show when={content.length > 0}>
            <DndContext
              sensors={sensors}
              collisionDetection={closestCenter}
              modifiers={[restrictToWindowEdges]}
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
                  onSort?.(activeIndex, overIndex);
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
                          type={content.post_type}
                          topic={topic}
                          content={{
                            id: content.ID,
                            title: content.post_title,
                            total_question: content.total_question || 0,
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
                    {(content) => (
                      <TopicContent
                        topic={topic}
                        content={{
                          id: content.ID,
                          title: content.post_title,
                          total_question: content.total_question || 0,
                        }}
                        type={content.post_type}
                        isOverlay
                      />
                    )}
                  </Show>
                </DragOverlay>,
                document.body,
              )}
            </DndContext>
          </Show>

          <TopicFooter topic={topic} />
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
  content: css`
    padding: ${spacing[16]};
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
  `,
  contentButtons: css`
    ${styleUtils.display.flex()};
    justify-content: space-between;
  `,
  footerButtons: css`
    display: flex;
    align-items: center;
  `,
  grabButton: ({
    isDragging = false,
  }: {
    isDragging: boolean;
  }) => css`
    ${styleUtils.resetButton};
    ${styleUtils.flexCenter()};
    cursor: ${isDragging ? 'grabbing' : 'grab'};

    :disabled {
      cursor: not-allowed;
    }
  `,
  threeDotButton: css`
    display: flex;
    align-items: center;
    gap: ${spacing[4]};
  `,
  contentButton: css`
    :hover:not(:disabled) {
      background-color: ${colorTokens.background.white};
      color: ${colorTokens.text.brand};
      box-shadow: inset 0 0 0 1px ${colorTokens.stroke.brand};
    }
  `,
};
