import {
  type AnimateLayoutChanges,
  SortableContext,
  defaultAnimateLayoutChanges,
  useSortable,
  verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { css } from '@emotion/react';
import { animated, useSpring } from '@react-spring/web';
import { useCallback, useEffect, useRef, useState } from 'react';

import TopicContentWrapper from '@CourseBuilderComponents/curriculum/TopicContentWrapper';
import TopicFooter from '@CourseBuilderComponents/curriculum/TopicFooter';
import TopicHeader from '@CourseBuilderComponents/curriculum/TopicHeader';
import type { CourseTopicWithCollapse } from '@CourseBuilderPages/Curriculum';

import { borderRadius, colorTokens, shadow, spacing } from '@TutorShared/config/styles';
import For from '@TutorShared/controls/For';
import Show from '@TutorShared/controls/Show';
import { styleUtils } from '@TutorShared/utils/style-utils';
import { type ID, isDefined } from '@TutorShared/utils/types';
import { noop } from '@TutorShared/utils/util';

interface TopicProps {
  topic: CourseTopicWithCollapse;
  onDelete?: () => void;
  onCopy?: (topicId: ID) => void;
  onSort?: (activeIndex: number, overIndex: number) => void;
  onCollapse?: (topicId: ID) => void;
  onEdit?: (topicId: ID) => void;
  isOverlay?: boolean;
}

const animateLayoutChanges: AnimateLayoutChanges = (args) =>
  defaultAnimateLayoutChanges({ ...args, wasDragging: true });

const Topic = ({ topic, onDelete, onCopy, onCollapse, onEdit, isOverlay = false }: TopicProps) => {
  const [isActive, setIsActive] = useState(false);
  const [isEdit, setIsEdit] = useState(!topic.isSaved);

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
    [topic.contents.length],
  );

  useEffect(() => {
    const handleOutsideClick = (event: MouseEvent) => {
      if (isDefined(wrapperRef.current) && !wrapperRef.current.contains(event.target as HTMLDivElement)) {
        setIsActive(false);
      }
    };

    document.addEventListener('click', handleOutsideClick);

    return () => document.removeEventListener('click', handleOutsideClick);
  }, [isEdit]);

  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: topic.id,
    data: {
      type: 'topic',
    },
    animateLayoutChanges,
  });

  const combinedRef = useCallback(
    (node: HTMLDivElement) => {
      if (node) {
        setNodeRef(node);
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
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

  useEffect(() => {
    if (isDefined(topicRef.current)) {
      collapseAnimate.start({
        height: !topic.isCollapsed ? topicRef.current.scrollHeight : 0,
        opacity: !topic.isCollapsed ? 1 : 0,
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [topic.isCollapsed, topic.contents.length]);

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
          <Show when={!topic.isCollapsed}>
            <Show when={topic.contents.length > 0}>
              <SortableContext
                items={topic.contents.map((item) => ({ ...item, id: item.ID }))}
                strategy={verticalListSortingStrategy}
              >
                <div>
                  <For each={topic.contents}>
                    {(content) => <TopicContentWrapper key={content.ID} topic={topic} content={content} />}
                  </For>
                </div>
              </SortableContext>
            </Show>

            <TopicFooter topic={topic} nextContentOrder={topic?.contents?.length + 1 || 1} />
          </Show>
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
    transition:
      background-color 0.3s ease-in-out,
      border-color 0.3s ease-in-out;
    background-color: ${colorTokens.bg.white};
    width: 100%;

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
  content: css`
    padding: ${spacing[16]};
    ${styleUtils.display.flex('column')};
    gap: ${spacing[12]};
  `,
};
