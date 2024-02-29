import Button from '@Atoms/Button';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import EmptyState from '@Molecules/EmptyState';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { colorTokens, spacing } from '@Config/styles';
import SVGIcon from '@Atoms/SVGIcon';
import emptyStateImage from '@CourseBuilderPublic/images/empty-state-illustration.webp';
import emptyStateImage2x from '@CourseBuilderPublic/images/empty-state-illustration-2x.webp';
import Show from '@Controls/Show';
import Topic from '@CourseBuilderComponents/curriculum/Topic';
import { CourseTopic, useCourseCurriculumQuery } from '@CourseBuilderServices/curriculum';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { LoadingOverlay } from '@Atoms/LoadingSpinner';
import For from '@Controls/For';
import { styleUtils } from '@Utils/style-utils';
import { useEffect, useMemo, useState } from 'react';
import { moveTo, nanoid, noop } from '@Utils/util';
import {
  DndContext,
  DragOverlay,
  KeyboardSensor,
  PointerSensor,
  UniqueIdentifier,
  closestCenter,
  useSensor,
  useSensors,
} from '@dnd-kit/core';
import { SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { createPortal } from 'react-dom';
import { droppableMeasuringStrategy } from '@Utils/dndkit';
import { restrictToVerticalAxis, restrictToWindowEdges } from '@dnd-kit/modifiers';

const courseId = getCourseId();
export type CourseTopicWithCollapse = CourseTopic & { isCollapsed: boolean };

const Curriculum = () => {
  const [allCollapsed, setAllCollapsed] = useState(true);
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  const [content, setContent] = useState<CourseTopicWithCollapse[]>([]);

  const courseCurriculumQuery = useCourseCurriculumQuery(courseId);

  useEffect(() => {
    if (!courseCurriculumQuery.data) {
      return;
    }
    setContent(courseCurriculumQuery.data.map((item, index) => ({ ...item, isCollapsed: index > 0 })));
  }, [courseCurriculumQuery.data]);

  useEffect(() => {
    setContent(previous => previous.map(item => ({ ...item, isCollapsed: allCollapsed })));
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
    if (!activeSortId) {
      return null;
    }

    return content.find(item => item.ID === activeSortId);
  }, [activeSortId, content]);

  if (courseCurriculumQuery.isLoading) {
    return <LoadingOverlay />;
  }

  if (!courseCurriculumQuery.data) {
    return null;
  }

  return (
    <div css={styles.container}>
      <div css={styles.wrapper}>
        <CanvasHead
          title={__('Curriculum', 'tutor')}
          rightButton={
            <Button variant="text" onClick={() => setAllCollapsed(previous => !previous)}>
              {allCollapsed ? __('Expand All', 'tutor') : __('Collapse All', 'tutor')}
            </Button>
          }
        />

        <div css={styles.content}>
          <Show
            when={content}
            fallback={
              <EmptyState
                emptyStateImage={emptyStateImage}
                emptyStateImage2x={emptyStateImage2x}
                imageAltText="Empty State Image"
                title="Create the course journey from here!"
                description="when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries"
                actions={
                  <Button variant="secondary" icon={<SVGIcon name="plusSquareBrand" width={24} height={25} />}>
                    {__('Add Topic', 'tutor')}
                  </Button>
                }
              />
            }
          >
            <DndContext
              sensors={sensors}
              collisionDetection={closestCenter}
              measuring={droppableMeasuringStrategy}
              modifiers={[restrictToVerticalAxis, restrictToWindowEdges]}
              onDragStart={event => {
                setActiveSortId(event.active.id);
                setAllCollapsed(true);
              }}
              onDragEnd={event => {
                const { active, over } = event;
                if (!over) {
                  setActiveSortId(null);
                  return;
                }

                if (active.id !== over.id) {
                  const activeIndex = content.findIndex(item => item.ID === active.id);
                  const overIndex = content.findIndex(item => item.ID === over.id);

                  setContent(previous => {
                    return moveTo(previous, activeIndex, overIndex);
                  });
                }
                setActiveSortId(null);
              }}
            >
              <SortableContext
                items={content.map(item => ({ ...item, id: item.ID }))}
                strategy={verticalListSortingStrategy}
              >
                <div css={styles.topicWrapper}>
                  <For each={content}>
                    {(topic, index) => {
                      return (
                        <Topic
                          key={topic.ID}
                          topic={topic}
                          allCollapsed={allCollapsed}
                          onSort={(activeIndex, overIndex) => {
                            setContent(previous => {
                              return previous.map((item, idx) => {
                                if (idx === index) {
                                  return { ...item, content: moveTo(item.content, activeIndex, overIndex) };
                                }

                                return item;
                              });
                            });
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
                    {item => {
                      return <Topic topic={item} allCollapsed={allCollapsed} onSort={noop} />;
                    }}
                  </Show>
                </DragOverlay>,
                document.body
              )}
            </DndContext>
          </Show>
        </div>
        <div css={styles.addButtonWrapper}>
          <Button
            variant="secondary"
            icon={<SVGIcon name="plusSquareBrand" width={24} height={24} />}
            onClick={() => {
              // @TODO: will be updated later.
              setContent(previous => {
                return [
                  ...previous.map(item => ({ ...item, isCollapsed: true })),
                  {
                    ID: nanoid(),
                    post_title: 'New Course Topic',
                    post_content: '',
                    post_name: '',
                    content: [],
                    isCollapsed: false,
                  },
                ];
              });
            }}
          >
            {__('Add Topic', 'tutor')}
          </Button>
        </div>
      </div>
    </div>
  );
};

export default Curriculum;

const styles = {
  container: css`
    padding: ${spacing[32]} ${spacing[64]};
  `,
  wrapper: css`
    max-width: 1076px;
    width: 100%;
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
  `,
  content: css`
    margin-top: ${spacing[16]};
  `,

  topicWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
  `,
  addButtonWrapper: css`
    path {
      stroke: ${colorTokens.icon.brand};
    }
  `,
};
