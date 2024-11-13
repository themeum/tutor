import {
  DndContext,
  type DragEndEvent,
  type DragMoveEvent,
  DragOverlay,
  KeyboardSensor,
  PointerSensor,
  type UniqueIdentifier,
  closestCorners,
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

import { colorTokens, containerMaxWidth, spacing } from '@Config/styles';
import For from '@Controls/For';
import Show from '@Controls/Show';
import {
  type Content,
  type CourseContentOrderPayload,
  type CourseTopic,
  type ID,
  useCourseTopicQuery,
  useUpdateCourseContentOrderMutation,
} from '@CourseBuilderServices/curriculum';
import { getCourseId, getIdWithoutPrefix } from '@CourseBuilderUtils/utils';
import { droppableMeasuringStrategy } from '@Utils/dndkit';
import { styleUtils } from '@Utils/style-utils';
import { moveTo, nanoid } from '@Utils/util';

import curriculumEmptyState2x from '@Images/curriculum-empty-state-2x.webp';
import curriculumEmptyState from '@Images/curriculum-empty-state.webp';
import TopicDragOverlay from '../components/curriculum/TopicDragOverlay';

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
  const [topics, setTopics] = useState<CourseTopicWithCollapse[]>([]);

  const currentExpandedTopics = useRef<ID[]>([]);

  const courseCurriculumQuery = useCourseTopicQuery(courseId);
  const updateCourseContentOrderMutation = useUpdateCourseContentOrderMutation();

  // biome-ignore lint/correctness/useExhaustiveDependencies: <explanation>
  useEffect(() => {
    if (topics.length === 0) {
      return;
    }

    setTopics((previous) => {
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
        const itemId = `topic-${item.id}`;
        const isFirstItem = index === 0;
        const wasPreviouslyExpanded = currentExpandedTopics.current.includes(itemId);
        const shouldCollapse = previousContent.length ? !wasPreviouslyExpanded : !isFirstItem;

        if (isFirstItem && !previousContent.length) {
          currentExpandedTopics.current = [itemId];
        }

        return {
          ...item,
          id: itemId,
          isCollapsed: shouldCollapse,
          isSaved: true,
          contents: item.contents.map((contentItem) => {
            return {
              ...contentItem,
              ID: `content-${contentItem.ID}`,
            };
          }),
        };
      });
    };

    setTopics(initializeContent);
  }, [courseCurriculumQuery.data]);

  const activeSortItem = useMemo(() => {
    if (!activeSortId) {
      return null;
    }

    const type = activeSortId.toString().includes('topic') ? 'topic' : 'content';
    const contentFlatMap = topics.flatMap((item) => item.contents);
    const item =
      type === 'topic'
        ? topics.find((item) => item.id === activeSortId)
        : contentFlatMap.find((item) => item.ID === activeSortId);

    return item;
  }, [activeSortId, topics]);

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

  const findValueOfItems = (id: UniqueIdentifier | undefined, type: string) => {
    if (type === 'topic') {
      return topics.find((item) => item.id === id);
    }
    if (type === 'content') {
      return topics.find((topic) => topic.contents.find((content) => content.ID === id));
    }
  };

  const handleDragMove = (event: DragMoveEvent) => {
    const { active, over } = event;

    if (!over || !active) {
      return;
    }

    if (active.id.toString().includes('content') && over.id.toString().includes('content') && active.id !== over.id) {
      const activeTopic = findValueOfItems(active.id, 'content');
      const overTopic = findValueOfItems(over.id, 'content');

      if (!activeTopic || !overTopic) return;

      const activeTopicIndex = topics.findIndex((topic) => topic.id === activeTopic.id);
      const overTopicIndex = topics.findIndex((topic) => topic.id === overTopic.id);

      if (overTopic.isCollapsed || activeTopicIndex === overTopicIndex) {
        console.log('return');
        return;
      }

      const activeContentIndex = activeTopic.contents.findIndex((content) => content.ID === active.id);
      const overContentIndex = overTopic.contents.findIndex((content) => content.ID === over.id);
      const newItems = [...topics];
      const [removedItem] = newItems[activeTopicIndex].contents.splice(activeContentIndex, 1);
      newItems[overTopicIndex].contents.splice(overContentIndex, 0, removedItem);
      setTopics(newItems);
    }

    if (active.id.toString().includes('content') && over?.id.toString().includes('topic') && active.id !== over.id) {
      const activeTopic = findValueOfItems(active.id, 'content');
      const overTopic = findValueOfItems(over.id, 'topic');

      if (!activeTopic || !overTopic || overTopic.contents.length > 0 || overTopic.isCollapsed) {
        return;
      }

      const activeTopicIndex = topics.findIndex((topic) => topic.id === activeTopic.id);
      const overTopicIndex = topics.findIndex((topic) => topic.id === overTopic.id);

      if (activeTopicIndex === overTopicIndex) {
        console.log('Same container');
        return;
      }

      const activeContentIndex = activeTopic.contents.findIndex((content) => content.ID === active.id);

      const newItems = [...topics];
      const [removedContent] = newItems[activeTopicIndex].contents.splice(activeContentIndex, 1);
      newItems[overTopicIndex].contents.push(removedContent);
      setTopics(newItems);
    }
  };

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event;

    if (!over) {
      setActiveSortId(null);
      return;
    }

    let topicAfterSort: CourseTopicWithCollapse[] = [...topics];

    if (
      active.id.toString().includes('topic') &&
      over?.id.toString().includes('topic') &&
      active &&
      active.id !== over.id
    ) {
      const activeTopicIndex = topics.findIndex((topic) => topic.id === active.id);
      const overTopicIndex = topics.findIndex((topic) => topic.id === over.id);
      topicAfterSort = moveTo(topicAfterSort, activeTopicIndex, overTopicIndex);

      setTopics(topicAfterSort);
    }

    if (
      active.id.toString().includes('content') &&
      over?.id.toString().includes('content') &&
      active &&
      active.id !== over.id
    ) {
      const activeTopic = findValueOfItems(active.id, 'content');
      const overTopic = findValueOfItems(over.id, 'content');

      if (!activeTopic || !overTopic || overTopic.isCollapsed) {
        return;
      }

      const activeTopicIndex = topics.findIndex((topic) => topic.id === activeTopic.id);
      const overTopicIndex = topics.findIndex((topic) => topic.id === overTopic.id);
      const activeContentIndex = activeTopic.contents.findIndex((content) => content.ID === active.id);
      const overContentIndex = overTopic.contents.findIndex((content) => content.ID === over.id);

      if (activeTopicIndex === overTopicIndex) {
        topicAfterSort[activeTopicIndex].contents = moveTo(
          topicAfterSort[activeTopicIndex].contents,
          activeContentIndex,
          overContentIndex,
        );
        setTopics(topicAfterSort);
      } else {
        const [removedContent] = topicAfterSort[activeTopicIndex].contents.splice(activeContentIndex, 1);
        topicAfterSort[overTopicIndex].contents.splice(overContentIndex, 0, removedContent);
        setTopics(topicAfterSort);
      }
    }

    if (
      active.id.toString().includes('content') &&
      over.id.toString().includes('topic') &&
      active &&
      active.id !== over.id
    ) {
      const activeTopic = findValueOfItems(active.id, 'content');
      const overTopic = findValueOfItems(over.id, 'topic');

      if (!activeTopic || !overTopic || overTopic.isCollapsed) {
        return;
      }

      const activeContainerIndex = topics.findIndex((topic) => topic.id === activeTopic.id);
      const overContainerIndex = topics.findIndex((topic) => topic.id === overTopic.id);
      const activeContentIndex = activeTopic.contents.findIndex((content) => content.ID === active.id);

      const [removedContent] = topicAfterSort[activeContainerIndex].contents.splice(activeContentIndex, 1);
      topicAfterSort[overContainerIndex].contents.push(removedContent);
      setTopics(topicAfterSort);
    }

    const convertedObject: CourseContentOrderPayload['tutor_topics_lessons_sorting'] = topicAfterSort.reduce(
      (topics, topic, topicIndex) => {
        let contentIndex = 0;
        topics[topicIndex] = {
          topic_id: getIdWithoutPrefix('topic-', topic.id),
          lesson_ids: topic.contents.reduce(
            (contents, content) => {
              contents[contentIndex] = getIdWithoutPrefix('content-', content.ID);
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

    setActiveSortId(null);
  };

  const handleTopicDelete = (index: number, topicId: ID) => {
    setTopics((previous) => previous.filter((_, idx) => idx !== index));
    currentExpandedTopics.current = currentExpandedTopics.current.filter((id) => id !== topicId);
  };

  const handleTopicCollapse = (topicId: ID) => {
    setTopics((previous) =>
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
    const previousContent = topics;
    const contentAfterSort = () => {
      return topics.map((item, idx) => {
        if (idx === index) {
          return {
            ...item,
            contents: moveTo(item.contents, activeIndex, overIndex),
          };
        }

        return item;
      });
    };
    setTopics(contentAfterSort);

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
      setTopics(previousContent);
    }
  };

  return (
    <div css={styles.container}>
      <div css={styles.wrapper}>
        <CanvasHead
          title={__('Curriculum', 'tutor')}
          backUrl="/basics"
          rightButton={
            <Show when={topics.some((item) => item.isSaved)}>
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
              (courseCurriculumQuery.data.length > 0 || topics.length > 0)
            }
            fallback={
              <EmptyState
                emptyStateImage={curriculumEmptyState}
                emptyStateImage2x={curriculumEmptyState2x}
                imageAltText={__('Empty State Illustration', 'tutor')}
                title={__('Start building your course!', 'tutor')}
                description={__('Add Topics, Lessons, and Quizzes to get started.', 'tutor')}
                actions={
                  <Button
                    variant="secondary"
                    icon={<SVGIcon name="plusSquareBrand" width={24} height={25} />}
                    onClick={() => {
                      setTopics((previous) => {
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
            <div css={styles.topicWrapper}>
              <DndContext
                sensors={sensors}
                collisionDetection={closestCorners}
                measuring={droppableMeasuringStrategy}
                modifiers={[restrictToWindowEdges]}
                onDragStart={(event) => {
                  setActiveSortId(event.active.id);
                }}
                onDragMove={(event) => handleDragMove(event)}
                onDragEnd={(event) => handleDragEnd(event)}
              >
                <SortableContext
                  items={topics.map((item) => ({ ...item, id: item.id }))}
                  strategy={verticalListSortingStrategy}
                >
                  <For each={topics}>
                    {(topic, index) => {
                      return (
                        topic.isSaved && (
                          <Topic
                            key={topic.id}
                            topic={{
                              ...topic,
                              isCollapsed: activeSortId?.toString().includes('topic') ? true : topic.isCollapsed,
                            }}
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
                        )
                      );
                    }}
                  </For>
                </SortableContext>

                {createPortal(
                  <DragOverlay>
                    <Show when={activeSortItem}>
                      {(item) => {
                        return (
                          <TopicDragOverlay
                            topicTitle={
                              activeSortId?.toString().includes('topic')
                                ? (item as CourseTopicWithCollapse).title
                                : (item as Content).post_title
                            }
                          />
                        );
                      }}
                    </Show>
                  </DragOverlay>,
                  document.body,
                )}
              </DndContext>

              <For each={topics}>
                {(topic, index) => {
                  return (
                    !topic.isSaved && (
                      <Topic
                        key={topic.id}
                        topic={{
                          ...topic,
                          isCollapsed: false,
                        }}
                        onDelete={() => handleTopicDelete(index, topic.id)}
                        onCollapse={(topicId) => handleTopicCollapse(topicId)}
                        onCopy={(topicId) => {
                          currentExpandedTopics.current = [topicId];
                        }}
                        onEdit={(topicId) => {
                          currentExpandedTopics.current = [topicId];
                        }}
                      />
                    )
                  );
                }}
              </For>
            </div>
          </Show>
        </div>
        <Show when={topics.length > 0}>
          <div css={styles.addButtonWrapper}>
            <Button
              variant="secondary"
              icon={<SVGIcon name="plusSquareBrand" width={24} height={24} />}
              onClick={() => {
                setTopics((previous) => {
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
  );
};

export default Curriculum;

const styles = {
  container: css`
    margin-top: ${spacing[32]};
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
