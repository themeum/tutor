import {
  DndContext,
  type DragEndEvent,
  DragOverlay,
  KeyboardSensor,
  PointerSensor,
  type UniqueIdentifier,
  closestCenter,
  useSensor,
  useSensors,
} from '@dnd-kit/core';
import { restrictToWindowEdges } from '@dnd-kit/modifiers';
import { SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { css } from '@emotion/react';
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { useNavigate } from 'react-router-dom';

import Button from '@Atoms/Button';
import { LoadingOverlay } from '@Atoms/LoadingSpinner';
import SVGIcon from '@Atoms/SVGIcon';
import EmptyState from '@Molecules/EmptyState';

import Topic from '@CourseBuilderComponents/curriculum/Topic';
import CanvasHead from '@CourseBuilderComponents/layouts/CanvasHead';
import Navigator from '@CourseBuilderComponents/layouts/Navigator';
import { CourseDetailsProvider } from '@CourseBuilderContexts/CourseDetailsContext';

import { colorTokens, containerMaxWidth, spacing } from '@Config/styles';
import For from '@Controls/For';
import Show from '@Controls/Show';
import {
  type CourseContentOrderPayload,
  type CourseTopic,
  type ID,
  useCourseTopicQuery,
  useUpdateCourseContentOrderMutation,
} from '@CourseBuilderServices/curriculum';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { droppableMeasuringStrategy } from '@Utils/dndkit';
import { styleUtils } from '@Utils/style-utils';
import { moveTo, nanoid } from '@Utils/util';

import curriculumEmptyState2x from '@Images/curriculum-empty-state-2x.webp';
import curriculumEmptyState from '@Images/curriculum-empty-state.webp';

const courseId = getCourseId();

export type CourseTopicWithCollapse = CourseTopic & { isCollapsed: boolean; isSaved: boolean };

const Curriculum = () => {
  const navigate = useNavigate();

  useEffect(() => {
    if (!courseId) {
      navigate('/', {
        replace: true,
      });
    }
  }, [navigate]);

  if (!courseId) {
    return null;
  }

  const [allCollapsed, setAllCollapsed] = useState(true);
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  const [content, setContent] = useState<CourseTopicWithCollapse[]>([]);

  const currentExpandedTopics = useRef<ID[]>([]);

  const courseCurriculumQuery = useCourseTopicQuery(courseId);
  const updateCourseContentOrderMutation = useUpdateCourseContentOrderMutation();

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (content.length === 0) {
      return;
    }

    setContent((previous) => {
      if (allCollapsed) {
        currentExpandedTopics.current = [];
      }

      if (!allCollapsed) {
        currentExpandedTopics.current = previous.reduce((acc, item) => {
          if (item.isSaved) {
            acc.push(item.id);
          }
          return acc;
        }, [] as ID[]);
      }

      return previous.map((item) => {
        if (!item.isSaved) {
          return item;
        }

        return { ...item, isCollapsed: allCollapsed };
      });
    });
  }, [allCollapsed]);

  useEffect(() => {
    if (!courseCurriculumQuery.data?.length) {
      return;
    }

    const initializeContent = (previousContent: CourseTopicWithCollapse[]) => {
      return courseCurriculumQuery.data.map((item, index) => {
        const isFirstItem = index === 0;
        const wasPreviouslyExpanded = currentExpandedTopics.current.includes(item.id);
        const shouldCollapse = previousContent.length ? !wasPreviouslyExpanded : !isFirstItem;

        if (isFirstItem && !previousContent.length) {
          currentExpandedTopics.current = [item.id];
        }

        return {
          ...item,
          isCollapsed: shouldCollapse,
          isSaved: true,
        };
      });
    };

    setContent(initializeContent);
  }, [courseCurriculumQuery.data]);

  const activeSortItem = useMemo(() => {
    if (!activeSortId) {
      return null;
    }
    return content.find((item) => item.id === activeSortId);
  }, [activeSortId, content]);

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

  if (courseCurriculumQuery.isLoading) {
    return <LoadingOverlay />;
  }

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event;
    if (!over) {
      setActiveSortId(null);
      return;
    }

    if (active.id !== over.id) {
      const activeIndex = content.findIndex((item) => item.id === active.id);
      const overIndex = content.findIndex((item) => item.id === over.id);

      const contentAfterSort = moveTo(content, activeIndex, overIndex);

      setContent(contentAfterSort);

      const convertedObject: CourseContentOrderPayload['tutor_topics_lessons_sorting'] = contentAfterSort.reduce(
        (topics, topic, topicIndex) => {
          let contentIndex = 0;
          topics[topicIndex] = {
            topic_id: topic.id,
            lesson_ids: topic.contents.reduce(
              (contents, content) => {
                contents[contentIndex] = content.ID;
                contentIndex++;

                return contents;
              },
              {} as { [key: ID]: ID },
            ),
          };
          return topics;
        },
        {} as { [key: ID]: { topic_id: ID; lesson_ids: { [key: ID]: ID } } },
      );

      updateCourseContentOrderMutation.mutate({
        tutor_topics_lessons_sorting: convertedObject,
      });
    }

    setActiveSortId(null);
  };

  const handleTopicDelete = (index: number, topicId: ID) => {
    setContent((previous) => previous.filter((_, idx) => idx !== index));
    currentExpandedTopics.current = currentExpandedTopics.current.filter((id) => id !== topicId);
  };

  const handleTopicCollapse = (topicId: ID) => {
    setContent((previous) =>
      previous.map((item) => {
        if (item.id === topicId) {
          return { ...item, isCollapsed: !item.isCollapsed };
        }
        return item;
      }),
    );

    if (!currentExpandedTopics.current.includes(topicId)) {
      currentExpandedTopics.current = [...currentExpandedTopics.current, topicId];
    } else {
      currentExpandedTopics.current = currentExpandedTopics.current.filter((id) => id !== topicId);
    }
  };

  const handleTopicSort = (index: number, topic: CourseTopicWithCollapse, activeIndex: number, overIndex: number) => {
    const previousContent = content;
    const contentAfterSort = () => {
      return content.map((item, idx) => {
        if (idx === index) {
          return {
            ...item,
            contents: moveTo(item.contents, activeIndex, overIndex),
          };
        }

        return item;
      });
    };
    setContent(contentAfterSort);

    const convertedObject: CourseContentOrderPayload['tutor_topics_lessons_sorting'] = contentAfterSort().reduce(
      (topics, topic, topicIndex) => {
        let contentIndex = 0;
        topics[topicIndex] = {
          topic_id: topic.id,
          lesson_ids: topic.contents.reduce(
            (contents, content) => {
              contents[contentIndex] = content.ID;
              contentIndex++;

              return contents;
            },
            {} as { [key: ID]: ID },
          ),
        };
        return topics;
      },
      {} as CourseContentOrderPayload['tutor_topics_lessons_sorting'],
    );

    updateCourseContentOrderMutation.mutate({
      tutor_topics_lessons_sorting: convertedObject,

      'content_parent[parent_topic_id]': topic.id,
      'content_parent[content_id]': topic.contents[activeIndex].ID,
    });

    if (updateCourseContentOrderMutation.isError) {
      setContent(previousContent);
    }
  };

  return (
    <CourseDetailsProvider>
      <div css={styles.container}>
        <div css={styles.wrapper}>
          <CanvasHead
            title={__('Curriculum', 'tutor')}
            backUrl="/basics"
            rightButton={
              <Show when={content.some((item) => item.isSaved)}>
                <Button variant="text" size="small" onClick={() => setAllCollapsed((previous) => !previous)}>
                  {allCollapsed ? __('Expand All', 'tutor') : __('Collapse All', 'tutor')}
                </Button>
              </Show>
            }
          />

          <div css={styles.content}>
            <Show
              when={
                !courseCurriculumQuery.isLoading &&
                courseCurriculumQuery.data &&
                (courseCurriculumQuery.data.length > 0 || content.length > 0)
              }
              fallback={
                <EmptyState
                  emptyStateImage={curriculumEmptyState}
                  emptyStateImage2x={curriculumEmptyState2x}
                  imageAltText={__('Empty State Illustration', 'tutor')}
                  title={__('Create the course journey from here!', 'tutor')}
                  description={__('Start building your course by adding Topics, Lessons, and Quizzes.', 'tutor')}
                  actions={
                    <Button
                      variant="secondary"
                      icon={<SVGIcon name="plusSquareBrand" width={24} height={25} />}
                      onClick={() => {
                        setContent((previous) => {
                          return [
                            {
                              id: nanoid(),
                              title: '',
                              summary: '',
                              contents: [],
                              isCollapsed: false,
                              isSaved: false,
                            },
                          ];
                        });
                      }}
                    >
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
                modifiers={[restrictToWindowEdges]}
                onDragStart={(event) => {
                  setActiveSortId(event.active.id);
                  setAllCollapsed(true);
                }}
                onDragEnd={(event) => handleDragEnd(event)}
              >
                <SortableContext
                  items={content.map((item) => ({ ...item, id: item.id }))}
                  strategy={verticalListSortingStrategy}
                >
                  <div css={styles.topicWrapper}>
                    <For each={content}>
                      {(topic, index) => {
                        return (
                          <Topic
                            key={topic.id}
                            topic={topic}
                            onDelete={() => handleTopicDelete(index, topic.id)}
                            onCollapse={(topicId) => handleTopicCollapse(topicId)}
                            onCopy={(topicId) => {
                              currentExpandedTopics.current = [topicId];
                            }}
                            onEdit={(topicId) => {
                              currentExpandedTopics.current = [topicId];
                            }}
                            onSort={(activeIndex, overIndex) => handleTopicSort(index, topic, activeIndex, overIndex)}
                          />
                        );
                      }}
                    </For>
                  </div>
                </SortableContext>

                {createPortal(
                  <DragOverlay>
                    <Show when={activeSortItem}>
                      {(item) => {
                        return <Topic topic={item} isOverlay />;
                      }}
                    </Show>
                  </DragOverlay>,
                  document.body,
                )}
              </DndContext>
            </Show>
          </div>
          <Show when={content.length > 0}>
            <div css={styles.addButtonWrapper}>
              <Button
                variant="secondary"
                icon={<SVGIcon name="plusSquareBrand" width={24} height={24} />}
                onClick={() => {
                  setContent((previous) => {
                    return [
                      ...previous.map((item) => ({ ...item, isCollapsed: true })),
                      {
                        id: nanoid(),
                        title: '',
                        summary: '',
                        contents: [],
                        isCollapsed: false,
                        isSaved: false,
                      },
                    ];
                  });
                  currentExpandedTopics.current = [];
                }}
              >
                {__('Add Topic', 'tutor')}
              </Button>
            </div>
          </Show>
        </div>
        <Navigator
          styleModifier={css`
          margin-block: 40px;
        `}
        />
      </div>
    </CourseDetailsProvider>
  );
};

export default Curriculum;

const styles = {
  container: css`
    margin-top: ${spacing[24]};
  `,
  wrapper: css`
    max-width: ${containerMaxWidth}px;
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
