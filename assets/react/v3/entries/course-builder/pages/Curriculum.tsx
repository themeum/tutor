import Button from '@Atoms/Button';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import EmptyState from '@Molecules/EmptyState';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { spacing } from '@Config/styles';
import SVGIcon from '@Atoms/SVGIcon';
import emptyStateImage from '@CourseBuilderPublic/images/empty-state-illustration.webp';
import emptyStateImage2x from '@CourseBuilderPublic/images/empty-state-illustration-2x.webp';
import Show from '@Controls/Show';
import Topic from '@CourseBuilderComponents/curriculum/Topic';
import { useCourseCurriculumQuery } from '@CourseBuilderServices/curriculum';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { LoadingOverlay } from '@Atoms/LoadingSpinner';
import For from '@Controls/For';
import { styleUtils } from '@Utils/style-utils';
import { useEffect, useMemo, useState } from 'react';
import { moveTo, noop } from '@Utils/util';
import {
  DndContext,
  DragOverlay,
  KeyboardSensor,
  MeasuringStrategy,
  PointerSensor,
  UniqueIdentifier,
  closestCenter,
  defaultDropAnimationSideEffects,
  useSensor,
  useSensors,
} from '@dnd-kit/core';
import { SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { createPortal } from 'react-dom';

const Curriculum = () => {
  const courseId = getCourseId();
  const courseCurriculumQuery = useCourseCurriculumQuery(courseId);
  const [allCollapsed, setAllCollapsed] = useState(true);
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);

  // @TODO: temporary code for handling sorting functionalities. Will be updated later once the API will be ready.
  const curriculumData = useMemo(() => {
    if (!courseCurriculumQuery.data) {
      return [];
    }
    return courseCurriculumQuery.data;
  }, [courseCurriculumQuery.data]);

  const [content, setContent] = useState(curriculumData);

  useEffect(() => {
    setContent(curriculumData);
  }, [curriculumData]);

  // @TODO: __^__ temporary code ends.

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

        <div>
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
              measuring={{
                droppable: {
                  strategy: MeasuringStrategy.Always,
                },
              }}
              onDragStart={event => {
                setActiveSortId(event.active.id);
                setAllCollapsed(true);
              }}
              onDragEnd={event => {
                const { active, over } = event;
                if (!over) {
                  return;
                }

                if (active.id !== over.id) {
                  const activeIndex = content.findIndex(item => item.ID === active.id);
                  const overIndex = content.findIndex(item => item.ID === over.id);

                  setContent(previous => {
                    return moveTo(previous, activeIndex, overIndex);
                  });
                }
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
                            // @TODO: will be implemented with real scenario later
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
                <DragOverlay adjustScale>
                  <Show when={activeSortItem}>
                    {item => {
                      return <Topic topic={item} allCollapsed={allCollapsed} onSort={noop} isOverlay />;
                    }}
                  </Show>
                </DragOverlay>,
                document.body
              )}
            </DndContext>
          </Show>
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
    gap: ${spacing[32]};
  `,

  topicWrapper: css`
    ${styleUtils.display.flex('column')};
    gap: ${spacing[16]};
  `,
};
