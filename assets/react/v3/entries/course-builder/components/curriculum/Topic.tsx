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
import { Controller, useFormContext } from 'react-hook-form';

import Button from '@Atoms/Button';
import SVGIcon from '@Atoms/SVGIcon';

import ConfirmationPopover from '@Molecules/ConfirmationPopover';
import ThreeDots from '@Molecules/ThreeDots';

import {
  type ID,
  type Content as TopicContentType,
  useDeleteTopicMutation,
  useDuplicateContentMutation,
  useSaveTopicMutation,
} from '@CourseBuilderServices/curriculum';

import FormInput from '@Components/fields/FormInput';
import FormTextareaInput from '@Components/fields/FormTextareaInput';
import { useModal } from '@Components/modals/Modal';

import TopicContent from '@CourseBuilderComponents/curriculum/TopicContent';
import AssignmentModal from '@CourseBuilderComponents/modals/AssignmentModal';
import LessonModal from '@CourseBuilderComponents/modals/LessonModal';
import QuizModal from '@CourseBuilderComponents/modals/QuizModal';
import type { CourseTopicWithCollapse } from '@CourseBuilderPages/Curriculum';

import For from '@Controls/For';
import Show from '@Controls/Show';

import { AnimationType } from '@Hooks/useAnimation';
import { useCollapseExpandAnimation } from '@Hooks/useCollapseExpandAnimation';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';

import { useToast } from '@Atoms/Toast';
import Tooltip from '@Atoms/Tooltip';
import { borderRadius, colorTokens, shadow, spacing } from '@Config/styles';
import { typography } from '@Config/typography';
import GoogleMeetForm from '@CourseBuilderComponents/additional/meeting/GoogleMeetForm';
import ZoomMeetingForm from '@CourseBuilderComponents/additional/meeting/ZoomMeetingForm';
import { useCourseDetails } from '@CourseBuilderContexts/CourseDetailsContext';
import type { CourseFormData } from '@CourseBuilderServices/course';
import { useImportQuizMutation } from '@CourseBuilderServices/quiz';
import { getCourseId } from '@CourseBuilderUtils/utils';
import { useFileUploader } from '@Molecules/FileUploader';
import Popover from '@Molecules/Popover';
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

const hasLiveAddons = true;

const courseId = getCourseId();

const Topic = ({ topic, onDelete, onCopy, onSort, onCollapse, onEdit, isOverlay = false }: TopicProps) => {
  const courseDetailsForm = useFormContext<CourseFormData>();
  const form = useFormWithGlobalError<TopicForm>({
    defaultValues: {
      title: topic.title,
      summary: topic.summary,
    },
    shouldFocusError: true,
  });

  const [isActive, setIsActive] = useState(false);
  const [isEdit, setIsEdit] = useState(!topic.isSaved);
  const [isThreeDotOpen, setIsThreeDotOpen] = useState(false);
  const [meetingType, setMeetingType] = useState<'tutor-google-meet' | 'tutor_zoom_meeting' | null>(null);
  const [isDeletePopoverOpen, setIsDeletePopoverOpen] = useState(false);
  const [activeSortId, setActiveSortId] = useState<UniqueIdentifier | null>(null);
  const [content, setContent] = useState<TopicContentType[]>(topic.contents);

  useEffect(() => {
    setContent(topic.contents);
  }, [topic]);

  const topicRef = useRef<HTMLDivElement>(null);
  const descriptionRef = useRef<HTMLDivElement>(null);
  const wrapperRef = useRef<HTMLDivElement>(null);
  const deleteRef = useRef<HTMLButtonElement>(null);
  const triggerGoogleMeetRef = useRef<HTMLButtonElement>(null);
  const triggerZoomRef = useRef<HTMLButtonElement>(null);

  const saveTopicMutation = useSaveTopicMutation();
  const duplicateContentMutation = useDuplicateContentMutation();
  const deleteTopicMutation = useDeleteTopicMutation(courseId);
  const importQuizMutation = useImportQuizMutation();

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
  const collapseAnimationDescription = useCollapseExpandAnimation({
    ref: descriptionRef,
    isOpen: !topic.isCollapsed,
    heightCalculator: 'client',
  });

  const { showToast } = useToast();
  const { showModal } = useModal();
  const { fileInputRef, handleChange } = useFileUploader({
    acceptedTypes: ['.csv'],
    onUpload: async (files) => {
      await importQuizMutation.mutateAsync({
        topic_id: topic.id,
        csv_file: files[0],
      });
      setIsThreeDotOpen(false);
    },
    onError: (errorMessages) => {
      for (const message of errorMessages) {
        showToast({
          message,
          type: 'danger',
        });
      }
      setIsThreeDotOpen(false);
    },
  });

  const courseDetails = useCourseDetails();

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
  };

  const handleDuplicateTopic = async () => {
    const response = await duplicateContentMutation.mutateAsync({
      course_id: courseId,
      content_id: topic.id,
      content_type: 'topic',
    });

    if (response.data) {
      onCopy?.(response.data);
    }
  };

  const handleSubmit = async (values: TopicForm) => {
    const response = await saveTopicMutation.mutateAsync({
      ...(topic.isSaved && { topic_id: topic.id }),
      course_id: courseId,
      title: values.title,
      summary: values.summary,
    });

    if (response.data) {
      if (response.status_code === 201) {
        onEdit?.(response.data);
      }
      setIsEdit(false);
    }
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
    <>
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
          <div
            css={styles.headerContent({
              isSaved: topic.isSaved,
            })}
          >
            <div
              css={styles.grabberInput({ isOverlay })}
              onClick={() => onCollapse?.(topic.id)}
              onKeyDown={(event) => {
                event.stopPropagation();
                if (event.key === 'Enter' || event.key === ' ') {
                  onCollapse?.(topic.id);
                }
              }}
            >
              <button {...listeners} css={styles.grabButton({ isDragging: isDragging })} type="button">
                <SVGIcon name="dragVertical" width={24} height={24} />
              </button>

              <Show
                when={isEdit}
                fallback={
                  <div css={styles.title({ isEdit })} title={topic.title} onDoubleClick={() => setIsEdit(true)}>
                    {form.watch('title')}
                  </div>
                }
              >
                <div css={styles.title({ isEdit })}>
                  <Controller
                    control={form.control}
                    name="title"
                    rules={{ required: __('Title is required', 'tutor') }}
                    render={(controllerProps) => (
                      <FormInput
                        {...controllerProps}
                        placeholder={__('Add a title', 'tutor')}
                        isSecondary
                        selectOnFocus
                      />
                    )}
                  />
                </div>
              </Show>
            </div>
            <div css={styles.actions}>
              <Show when={!isEdit}>
                <Tooltip content={__('Edit', 'tutor')} delay={200}>
                  <button
                    type="button"
                    css={styles.actionButton}
                    disabled={!topic.isSaved}
                    data-visually-hidden
                    onClick={() => {
                      setIsEdit(true);
                      if (topic.isCollapsed) {
                        onCollapse?.(topic.id);
                      }
                    }}
                  >
                    <SVGIcon name="edit" width={24} height={24} />
                  </button>
                </Tooltip>
              </Show>
              <Show when={topic.isSaved}>
                <Tooltip content={__('Duplicate', 'tutor')} delay={200}>
                  <button
                    type="button"
                    css={styles.actionButton}
                    disabled={!topic.isSaved}
                    data-visually-hidden
                    onClick={handleDuplicateTopic}
                  >
                    <SVGIcon name="copyPaste" width={24} height={24} />
                  </button>
                </Tooltip>
              </Show>
              <Show when={topic.isSaved}>
                <Tooltip content={__('Delete', 'tutor')} delay={200}>
                  <button
                    type="button"
                    css={styles.actionButton}
                    disabled={!topic.isSaved}
                    data-visually-hidden
                    ref={deleteRef}
                    onClick={() => {
                      setIsDeletePopoverOpen(true);
                    }}
                  >
                    <SVGIcon name="delete" width={24} height={24} />
                  </button>
                </Tooltip>
              </Show>
              <ConfirmationPopover
                isOpen={isDeletePopoverOpen}
                triggerRef={deleteRef}
                closePopover={() => setIsDeletePopoverOpen(false)}
                maxWidth="258px"
                title={`${__('Delete meeting')} "${topic.title}"`}
                message={__(
                  'Are you sure you want to delete this content from your course? This cannot be undone.',
                  'tutor',
                )}
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
                onConfirmation={async () => {
                  await deleteTopicMutation.mutateAsync(topic.id);
                  setIsDeletePopoverOpen(false);
                  onDelete?.();
                }}
              />

              <Show when={topic.isSaved}>
                <button
                  type="button"
                  css={styles.actionButton}
                  disabled={!topic.isSaved}
                  onClick={() => {
                    onCollapse?.(topic.id);
                  }}
                >
                  <SVGIcon name={topic.isCollapsed ? 'chevronDown' : 'chevronUp'} />
                </button>
              </Show>
            </div>
          </div>

          <Show
            when={isEdit}
            fallback={
              <animated.div style={{ ...collapseAnimationDescription }}>
                <div css={styles.description({ isEdit })} ref={descriptionRef} onDoubleClick={() => setIsEdit(true)}>
                  {form.watch('summary')}
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
              <Button
                variant="text"
                size="small"
                onClick={() => {
                  if (!form.formState.isValid && !topic.isSaved) {
                    onDelete?.();
                  }
                  form.reset();
                  setIsEdit(false);
                }}
              >
                {__('Cancel', 'tutor')}
              </Button>
              <Button
                loading={saveTopicMutation.isPending}
                variant="secondary"
                size="small"
                onClick={form.handleSubmit(handleSubmit)}
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
                          onDelete={() => {
                            setContent((previousContent) => previousContent.filter((item) => item.ID !== content.ID));
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
                        isDragging
                      />
                    )}
                  </Show>
                </DragOverlay>,
                document.body,
              )}
            </DndContext>

            <div css={styles.contentButtons}>
              <div css={[styleUtils.display.flex(), { gap: spacing[12] }]}>
                <Button
                  variant="tertiary"
                  isOutlined
                  size="small"
                  icon={<SVGIcon name="plus" width={24} height={24} />}
                  disabled={!topic.isSaved}
                  onClick={() => {
                    showModal({
                      component: LessonModal,
                      props: {
                        contentDripType: courseDetailsForm.watch('contentDripType'),
                        topicId: topic.id,
                        title: __('Lesson', 'tutor'),
                        icon: <SVGIcon name="lesson" width={24} height={24} />,
                        subtitle: `${__('Topic:', 'tutor')}  ${topic.title}`,
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
                  disabled={!topic.isSaved}
                  onClick={() => {
                    showModal({
                      component: QuizModal,
                      props: {
                        topicId: topic.id,
                        contentDripType: courseDetailsForm.watch('contentDripType'),
                        title: __('Quiz', 'tutor'),
                        icon: <SVGIcon name="quiz" width={24} height={24} />,
                        subtitle: `${__('Topic:', 'tutor')}  ${topic.title}`,
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
                  disabled={!topic.isSaved}
                  onClick={() => {
                    showModal({
                      component: AssignmentModal,
                      props: {
                        topicId: topic.id,
                        contentDripType: courseDetailsForm.watch('contentDripType'),
                        title: __('Assignment', 'tutor'),
                        icon: <SVGIcon name="assignment" width={24} height={24} />,
                        subtitle: `${__('Topic:', 'tutor')}  ${topic.title}`,
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
                      disabled={!topic.isSaved}
                      onClick={() => {
                        fileInputRef?.current?.click();
                      }}
                    >
                      {__('Import Quiz', 'tutor')}
                    </Button>
                  }
                >
                  <ThreeDots
                    isOpen={isThreeDotOpen}
                    onClick={() => setIsThreeDotOpen(true)}
                    closePopover={() => setIsThreeDotOpen(false)}
                    disabled={!topic.isSaved}
                    dotsOrientation="vertical"
                    maxWidth="220px"
                    isInverse
                    arrowPosition="auto"
                    hideArrow
                  >
                    <ThreeDots.Option
                      text={
                        <span ref={triggerGoogleMeetRef} css={styleUtils.resetButton}>
                          {__('Meet live lesson', 'tutor')}
                        </span>
                      }
                      icon={<SVGIcon width={24} height={24} name="googleMeetColorize" isColorIcon />}
                      onClick={() => setMeetingType('tutor-google-meet')}
                    />
                    <ThreeDots.Option
                      text={
                        <span ref={triggerZoomRef} css={styleUtils.resetButton}>
                          {__('Zoom live lesson', 'tutor')}
                        </span>
                      }
                      icon={<SVGIcon width={24} height={24} name="zoomColorize" isColorIcon />}
                      onClick={() => setMeetingType('tutor_zoom_meeting')}
                    />
                    <ThreeDots.Option
                      text={__('Import Quiz', 'tutor')}
                      onClick={() => {
                        fileInputRef?.current?.click();
                      }}
                      icon={<SVGIcon name="downloadColorize" width={24} height={24} isColorIcon />}
                    />
                  </ThreeDots>
                </Show>
              </div>
            </div>
          </div>
        </animated.div>
      </div>

      {/* This input is added for file input trigger point and it will be accessed by both 'ThreeDot' and 'Import Quiz' button */}
      <input
        css={styleUtils.display.none}
        type="file"
        ref={fileInputRef}
        onChange={handleChange}
        multiple={false}
        accept=".csv"
      />

      <Popover
        triggerRef={triggerGoogleMeetRef}
        isOpen={meetingType === 'tutor-google-meet'}
        closePopover={() => {
          setMeetingType(null);
          setIsThreeDotOpen(false);
        }}
        maxWidth="306px"
      >
        <GoogleMeetForm
          topicId={topic.id}
          data={null}
          onCancel={() => {
            setMeetingType(null);
            setIsThreeDotOpen(false);
          }}
        />
      </Popover>
      <Popover
        triggerRef={triggerZoomRef}
        isOpen={meetingType === 'tutor_zoom_meeting'}
        closePopover={() => {
          setMeetingType(null);
          setIsThreeDotOpen(false);
        }}
        maxWidth="306px"
      >
        <ZoomMeetingForm
          topicId={topic.id}
          meetingHost={courseDetails?.zoom_users || {}}
          data={null}
          onCancel={() => {
            setMeetingType(null);
            setIsThreeDotOpen(false);
          }}
        />
      </Popover>
    </>
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
  headerContent: ({
    isSaved = true,
  }: {
    isSaved: boolean;
  }) => css`
    display: grid;
    grid-template-columns: ${isSaved ? '1fr auto' : '1fr'};
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

    :disabled {
      cursor: not-allowed;
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
  grabButton: ({
    isDragging = false,
  }: {
    isDragging: boolean;
  }) => css`
    ${styleUtils.resetButton};
    cursor: ${isDragging ? 'grabbing' : 'grab'};
  `,
};
